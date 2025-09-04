<?php

namespace App\Services;

use App\Models\BusinessInquiry;
use App\Models\Customer;
use App\Models\FacebookPage;
use App\Models\PageBusinessConfig;
use App\Models\PageFaq;
use App\Models\InquirySession;
use Illuminate\Support\Facades\Log;

class QAMessageProcessor
{
    protected $facebookGraphAPI;
    
    public function __construct()
    {
        $this->facebookGraphAPI = app(FacebookGraphAPIService::class);
    }
    
    /**
     * Process incoming message from Facebook webhook
     */
    public function processMessage(array $messageData, FacebookPage $page)
    {
        try {
            $senderId = $messageData['sender']['id'];
            $messageText = $messageData['message']['text'] ?? '';
            $pageId = $page->id;
            
            // Get or create customer
            $customer = $this->getOrCreateCustomer($senderId, $page);
            
            // Get business configuration
            $config = PageBusinessConfig::where('facebook_page_id', $pageId)->first();
            if (!$config) {
                return $this->sendErrorMessage($page, $senderId);
            }
            
            // Check if customer is in an inquiry session
            $session = InquirySession::where('facebook_page_id', $pageId)
                ->where('customer_id', $customer->id)
                ->where('is_active', true)
                ->first();
            
            if ($session) {
                return $this->handleInquirySession($session, $messageText, $config, $page, $senderId);
            }
            
            // Handle FAQ selection or show welcome message
            return $this->handleFaqInteraction($messageText, $config, $page, $senderId, $customer);
            
        } catch (\Exception $e) {
            Log::error('QA Message Processing Error: ' . $e->getMessage(), [
                'page_id' => $page->id ?? null,
                'message_data' => $messageData
            ]);
            
            return false;
        }
    }
    
    /**
     * Handle FAQ interaction and menu display
     */
    protected function handleFaqInteraction($messageText, $config, $page, $senderId, $customer)
    {
        // Check if this is a greeting or first interaction
        if ($this->isGreeting($messageText) || empty($messageText)) {
            return $this->sendWelcomeMessage($config, $page, $senderId);
        }
        
        // Check if message matches any FAQ quick reply
        $faq = $this->findMatchingFaq($messageText, $page->id, $config->default_language ?? 'en');
        
        if ($faq) {
            return $this->processFaqResponse($faq, $config, $page, $senderId, $customer);
        }
        
        // If no match found, show main menu
        return $this->sendMainMenu($config, $page, $senderId);
    }
    
    /**
     * Handle active inquiry session
     */
    protected function handleInquirySession($session, $messageText, $config, $page, $senderId)
    {
        $currentStep = $session->current_step;
        $collectedData = $session->collected_data ?? [];
        
        // Process based on current step
        switch ($currentStep) {
            case 'name':
                if ($config->collect_name) {
                    $collectedData['customer_name'] = $messageText;
                    return $this->advanceToNextStep($session, $collectedData, $config, $page, $senderId);
                }
                break;
                
            case 'phone':
                if ($config->collect_phone) {
                    $collectedData['customer_phone'] = $messageText;
                    return $this->advanceToNextStep($session, $collectedData, $config, $page, $senderId);
                }
                break;
                
            case 'address':
                if ($config->collect_address) {
                    $collectedData['customer_address'] = $messageText;
                    return $this->advanceToNextStep($session, $collectedData, $config, $page, $senderId);
                }
                break;
                
            case 'budget':
                if ($config->collect_budget) {
                    $collectedData['budget_range'] = $messageText;
                    return $this->advanceToNextStep($session, $collectedData, $config, $page, $senderId);
                }
                break;
                
            case 'requirements':
                if ($config->collect_requirements) {
                    $collectedData['requirements'] = $messageText;
                    return $this->advanceToNextStep($session, $collectedData, $config, $page, $senderId);
                }
                break;
        }
        
        return false;
    }
    
    /**
     * Advance to next step in inquiry collection
     */
    protected function advanceToNextStep($session, $collectedData, $config, $page, $senderId)
    {
        $steps = $this->getCollectionSteps($config);
        $currentIndex = array_search($session->current_step, $steps);
        
        if ($currentIndex !== false && $currentIndex < count($steps) - 1) {
            // Move to next step
            $nextStep = $steps[$currentIndex + 1];
            $session->update([
                'current_step' => $nextStep,
                'collected_data' => $collectedData
            ]);
            
            return $this->askForStep($nextStep, $config, $page, $senderId);
        } else {
            // All data collected, create inquiry
            return $this->completeInquiry($session, $collectedData, $config, $page, $senderId);
        }
    }
    
    /**
     * Complete inquiry collection and create business inquiry
     */
    protected function completeInquiry($session, $collectedData, $config, $page, $senderId)
    {
        // Create business inquiry
        $inquiry = BusinessInquiry::create([
            'facebook_page_id' => $page->id,
            'client_id' => $page->client_id,
            'customer_id' => $session->customer_id,
            'type' => $config->inquiry_type ?? 'order',
            'customer_name' => $collectedData['customer_name'] ?? null,
            'customer_phone' => $collectedData['customer_phone'] ?? null,
            'customer_address' => $collectedData['customer_address'] ?? null,
            'budget_range' => $collectedData['budget_range'] ?? null,
            'requirements' => $collectedData['requirements'] ?? null,
            'language' => $config->default_language ?? 'en',
            'status' => 'pending',
            'priority' => 'medium',
            'extra_fields' => $collectedData
        ]);
        
        // Close session
        $session->update(['is_active' => false, 'completed_at' => now()]);
        
        // Send confirmation message
        $confirmationMessage = $this->getConfirmationMessage($inquiry, $config);
        return $this->sendMessage($page, $senderId, $confirmationMessage);
    }
    
    /**
     * Send welcome message
     */
    protected function sendWelcomeMessage($config, $page, $senderId)
    {
        $language = $config->default_language ?? 'en';
        $welcomeMessage = $language === 'bn' && $config->welcome_message_bn 
            ? $config->welcome_message_bn 
            : ($config->welcome_message_en ?? 'Welcome! How can we help you today?');
        
        $this->sendMessage($page, $senderId, $welcomeMessage);
        
        // Show main menu after welcome
        return $this->sendMainMenu($config, $page, $senderId);
    }
    
    /**
     * Send main FAQ menu
     */
    protected function sendMainMenu($config, $page, $senderId)
    {
        $language = $config->default_language ?? 'en';
        $faqs = PageFaq::where('facebook_page_id', $page->id)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
        
        if ($faqs->isEmpty()) {
            return $this->sendMessage($page, $senderId, 'No FAQs available at the moment.');
        }
        
        $menuText = $language === 'bn' 
            ? "আপনার প্রশ্ন নির্বাচন করুন:\n\n"
            : "Please select your question:\n\n";
        
        foreach ($faqs as $index => $faq) {
            $question = $faq->getQuestion($language);
            $quickReply = $faq->quick_reply_text ?: ($index + 1) . '️⃣';
            $menuText .= "{$quickReply} {$question}\n";
        }
        
        return $this->sendMessage($page, $senderId, $menuText);
    }
    
    /**
     * Process FAQ response
     */
    protected function processFaqResponse($faq, $config, $page, $senderId, $customer)
    {
        $language = $config->default_language ?? 'en';
        $answer = $faq->getAnswer($language);
        
        // Send the answer
        $this->sendMessage($page, $senderId, $answer);
        
        // Handle action type
        switch ($faq->action_type) {
            case 'start_inquiry':
                return $this->startInquiryCollection($config, $page, $senderId, $customer);
                
            case 'show_menu':
                return $this->sendMainMenu($config, $page, $senderId);
                
            case 'custom':
                return $this->handleCustomAction($faq->custom_action, $config, $page, $senderId);
                
            default: // 'answer_only'
                return true;
        }
    }
    
    /**
     * Start inquiry data collection
     */
    protected function startInquiryCollection($config, $page, $senderId, $customer)
    {
        $steps = $this->getCollectionSteps($config);
        
        if (empty($steps)) {
            return $this->sendMessage($page, $senderId, 'Thank you for your interest! We will contact you soon.');
        }
        
        // Create inquiry session
        $session = InquirySession::create([
            'facebook_page_id' => $page->id,
            'customer_id' => $customer->id,
            'current_step' => $steps[0],
            'is_active' => true,
            'collected_data' => []
        ]);
        
        return $this->askForStep($steps[0], $config, $page, $senderId);
    }
    
    /**
     * Get collection steps based on configuration
     */
    protected function getCollectionSteps($config)
    {
        $steps = [];
        
        if ($config->collect_name) $steps[] = 'name';
        if ($config->collect_phone) $steps[] = 'phone';
        if ($config->collect_address) $steps[] = 'address';
        if ($config->collect_budget) $steps[] = 'budget';
        if ($config->collect_requirements) $steps[] = 'requirements';
        
        return $steps;
    }
    
    /**
     * Ask for specific step information
     */
    protected function askForStep($step, $config, $page, $senderId)
    {
        $language = $config->default_language ?? 'en';
        
        $questions = [
            'name' => [
                'en' => 'Please provide your full name:',
                'bn' => 'অনুগ্রহ করে আপনার পূর্ণ নাম দিন:'
            ],
            'phone' => [
                'en' => 'Please provide your phone number:',
                'bn' => 'অনুগ্রহ করে আপনার ফোন নম্বর দিন:'
            ],
            'address' => [
                'en' => 'Please provide your address:',
                'bn' => 'অনুগ্রহ করে আপনার ঠিকানা দিন:'
            ],
            'budget' => [
                'en' => 'What is your budget range?',
                'bn' => 'আপনার বাজেট কত?'
            ],
            'requirements' => [
                'en' => 'Please describe your requirements in detail:',
                'bn' => 'অনুগ্রহ করে আপনার প্রয়োজনীয়তা বিস্তারিত বর্ণনা করুন:'
            ]
        ];
        
        $question = $questions[$step][$language] ?? $questions[$step]['en'];
        
        // Add budget options if asking for budget
        if ($step === 'budget' && !empty($config->budget_options)) {
            $question .= "\n\nOptions:\n";
            foreach ($config->budget_options as $index => $option) {
                $question .= ($index + 1) . ". {$option}\n";
            }
        }
        
        return $this->sendMessage($page, $senderId, $question);
    }
    
    /**
     * Find matching FAQ by quick reply text or question
     */
    protected function findMatchingFaq($messageText, $pageId, $language)
    {
        $messageText = strtolower(trim($messageText));
        
        // First try to match by quick reply text
        $faq = PageFaq::where('facebook_page_id', $pageId)
            ->where('is_active', true)
            ->where('quick_reply_text', 'LIKE', "%{$messageText}%")
            ->first();
        
        if ($faq) return $faq;
        
        // Try to match by question content
        return PageFaq::where('facebook_page_id', $pageId)
            ->where('is_active', true)
            ->where(function($query) use ($messageText, $language) {
                if ($language === 'bn') {
                    $query->where('question_bn', 'LIKE', "%{$messageText}%")
                          ->orWhere('question_en', 'LIKE', "%{$messageText}%");
                } else {
                    $query->where('question_en', 'LIKE', "%{$messageText}%");
                }
            })
            ->first();
    }
    
    /**
     * Get or create customer from sender ID
     */
    protected function getOrCreateCustomer($senderId, $page)
    {
        $customer = Customer::where('facebook_id', $senderId)
            ->where('client_id', $page->client_id)
            ->first();
        
        if (!$customer) {
            // Get user info from Facebook
            $userInfo = $this->facebookGraphAPI->getUserInfo($senderId, $page->page_access_token);
            
            $customer = Customer::create([
                'client_id' => $page->client_id,
                'facebook_id' => $senderId,
                'name' => $userInfo['first_name'] . ' ' . $userInfo['last_name'] ?? 'Unknown',
                'first_name' => $userInfo['first_name'] ?? '',
                'last_name' => $userInfo['last_name'] ?? '',
                'profile_pic' => $userInfo['profile_pic'] ?? '',
                'source' => 'facebook_messenger'
            ]);
        }
        
        return $customer;
    }
    
    /**
     * Send message via Facebook API
     */
    protected function sendMessage($page, $recipientId, $message)
    {
        return $this->facebookGraphAPI->sendMessage(
            $page->page_access_token,
            $recipientId,
            $message
        );
    }
    
    /**
     * Check if message is a greeting
     */
    protected function isGreeting($message)
    {
        $greetings = ['hi', 'hello', 'hey', 'start', 'hola', 'السلام عليكم', 'নমস্কার', 'হ্যালো'];
        $message = strtolower(trim($message));
        
        return in_array($message, $greetings) || empty($message);
    }
    
    /**
     * Get confirmation message for completed inquiry
     */
    protected function getConfirmationMessage($inquiry, $config)
    {
        $language = $config->default_language ?? 'en';
        $inquiryType = ucfirst($inquiry->type);
        
        if ($language === 'bn') {
            return "ধন্যবাদ! আপনার {$inquiryType} অনুসন্ধান #{$inquiry->inquiry_number} সফলভাবে জমা দেওয়া হয়েছে। আমরা শীঘ্রই আপনার সাথে যোগাযোগ করব। 🙏";
        }
        
        return "Thank you! Your {$inquiryType} inquiry #{$inquiry->inquiry_number} has been submitted successfully. We will contact you soon. 🙏";
    }
    
    /**
     * Handle custom actions
     */
    protected function handleCustomAction($action, $config, $page, $senderId)
    {
        // This can be extended based on custom requirements
        Log::info("Custom action triggered: {$action}");
        return true;
    }
    
    /**
     * Send error message
     */
    protected function sendErrorMessage($page, $senderId)
    {
        return $this->sendMessage($page, $senderId, 'Sorry, something went wrong. Please try again later.');
    }
}
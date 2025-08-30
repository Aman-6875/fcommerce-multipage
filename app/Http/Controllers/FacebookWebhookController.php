<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerMessage;
use App\Models\FacebookPage;
use App\Models\PageCustomer;
use App\Services\FacebookGraphAPIService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FacebookWebhookController extends Controller
{
    protected FacebookGraphAPIService $facebookService;

    public function __construct(FacebookGraphAPIService $facebookService)
    {
        $this->facebookService = $facebookService;
    }

    public function verify(Request $request)
    {
        Log::info('Webhook verification request received', ['params' => $request->all()]);
        $verifyToken = config('services.facebook.webhook_verify_token');

        if ($request->get('hub_mode') === 'subscribe' && $request->get('hub_verify_token') === $verifyToken) {
            Log::info('Webhook verified successfully');
            return response($request->get('hub_challenge'));
        }

        Log::warning('Webhook verification failed', ['request' => $request->all()]);
        return response('Verification failed', 403);
    }

    public function handle(Request $request)
    {
        Log::info('Facebook webhook POST request reached handle method.');
        Log::info('Facebook webhook received', ['payload' => $request->getContent()]);

        // Skip signature verification if in debug mode (for Postman testing)
        $debugMode = $request->header('X-Debug-Mode') === 'true';
        
        if (!$debugMode) {
            $signature = $request->header('X-Hub-Signature-256');
            if (!$signature || !$this->facebookService->verifyWebhookSignature($request->getContent(), $signature)) {
                Log::warning('Webhook signature verification failed');
                return response('Signature verification failed', 403);
            }
        } else {
            Log::info('Debug mode enabled - skipping signature verification');
        }

        $data = $request->json()->all();
        if (isset($data['entry'])) {
            foreach ($data['entry'] as $entry) {
                $this->processEntry($entry);
            }
        }

        return response('OK', 200);
    }

    protected function processEntry(array $entry): void
    {
        $pageId = $entry['id'];
        $facebookPage = FacebookPage::where('page_id', $pageId)->where('is_connected', true)->first();

        if (!$facebookPage) {
            Log::warning('Webhook for unknown or disconnected page', ['page_id' => $pageId]);
            return;
        }
        
        if (isset($entry['messaging'])) {
            foreach ($entry['messaging'] as $messagingEvent) {
                $this->processMessagingEvent($facebookPage, $messagingEvent);
            }
        }
    }

    protected function processMessagingEvent(FacebookPage $facebookPage, array $event): void
    {
        if (isset($event['sender']['id']) && $event['sender']['id'] !== $facebookPage->page_id) {
            $senderId = $event['sender']['id'];
            $customer = $this->findOrCreateCustomer($facebookPage, $senderId);

            if (isset($event['message'])) {
                $this->handleIncomingMessage($customer, $event['message'], $event);
            } elseif (isset($event['postback'])) {
                $this->handlePostback($customer, $event['postback'], $event);
            }
        }
    }

    protected function findOrCreateCustomer(FacebookPage $facebookPage, string $facebookUserId): Customer
    {
        // First, try to find existing page customer relationship by facebook_user_id
        $pageCustomer = \App\Models\PageCustomer::where('facebook_page_id', $facebookPage->id)
            ->where('facebook_user_id', $facebookUserId)
            ->with('customer')
            ->first();

        if ($pageCustomer) {
            // Update interaction stats
            $pageCustomer->recordInteraction();
            return $pageCustomer->customer;
        }

        // Check if we have any customer with this facebook_user_id (across all pages)
        // This handles the case where the same Facebook user interacts with multiple pages
        $existingCustomer = Customer::where('client_id', $facebookPage->client_id)
            ->where('facebook_user_id', $facebookUserId)
            ->first();

        if ($existingCustomer) {
            // Create page customer relationship for this page
            $pageCustomer = \App\Models\PageCustomer::findOrCreateForPage($facebookPage, $existingCustomer, $facebookUserId);
            $pageCustomer->recordInteraction();
            return $existingCustomer;
        }

        // No existing customer found - create new one
        // Note: This customer will be merged later when they provide phone number through workflow
        $customer = Customer::create([
            'client_id' => $facebookPage->client_id,
            'name' => 'Facebook User', // Will be updated from Facebook profile
            'facebook_user_id' => $facebookUserId,
            'phone' => null, // Will be collected during workflow
            'status' => 'active'
        ]);

        // Create page customer relationship
        $pageCustomer = \App\Models\PageCustomer::findOrCreateForPage($facebookPage, $customer, $facebookUserId);

        // Try to get Facebook profile information
        $facebookService = app(\App\Services\FacebookGraphAPIService::class);
        $facebookService->updateCustomerWithFacebookProfile($customer, $facebookPage);

        return $customer;
    }

    protected function handleIncomingMessage(Customer $customer, array $message, array $event): void
    {
        if (!isset($message['mid'])) {
            Log::warning('Received message event without a message ID (mid)', ['event' => $event]);
            return;
        }

        // Avoid processing duplicate messages
        if (CustomerMessage::whereJsonContains('message_data->facebook_message_id', $message['mid'])->exists()) {
            Log::info('Skipping duplicate message', ['mid' => $message['mid']]);
            return;
        }

        // Get page customer ID for this customer and current page context
        $pageCustomerId = $this->getPageCustomerId($customer, $event);

        // Store the message first
        CustomerMessage::create([
            'customer_id' => $customer->id,
            'page_customer_id' => $pageCustomerId,
            'client_id' => $customer->client_id,
            'message_type' => 'incoming',
            'message_content' => $message['text'] ?? '[Attachment]',
            'attachments' => $message['attachments'] ?? [],
            'message_data' => [
                'facebook_message_id' => $message['mid'],
                'timestamp' => $event['timestamp']
            ],
            'is_read' => false
        ]);

        // Check for active workflow and process message
        if (isset($message['text'])) {
            $this->handleWorkflowMessage($customer, $message['text']);
        }

        Log::info('Incoming message processed', ['customer_id' => $customer->id, 'mid' => $message['mid']]);
    }

    protected function handleWorkflowMessage(Customer $customer, string $messageText): void
    {
        Log::info('=== WORKFLOW MESSAGE HANDLING START ===', [
            'customer_id' => $customer->id,
            'message' => $messageText
        ]);

        // Check for active workflow conversation for this customer
        $conversation = \App\Models\ConversationState::where('customer_id', $customer->id)
            ->where('status', 'active')
            ->latest('last_activity_at')
            ->first();

        if (!$conversation) {
            Log::info('No active conversation - checking for workflow start');
            // No active workflow, check if message should start a workflow
            $this->checkForWorkflowStart($customer, $messageText);
            return;
        }

        Log::info('Active conversation found', [
            'conversation_id' => $conversation->id,
            'current_step_index' => $conversation->current_step_index,
            'current_step_id' => $conversation->getCurrentStep()['id'] ?? 'unknown'
        ]);

        Log::debug('Active conversation found', [
            'conversation_id' => $conversation->id,
            'current_step_index' => $conversation->current_step_index,
            'current_step_id' => $conversation->getCurrentStep()['id'] ?? 'unknown'
        ]);

        // Process message in workflow context
        $workflowEngine = app(\App\Services\WorkflowEngine::class);
        $result = $workflowEngine->processStepInput($conversation, $messageText);

        Log::info('Workflow processing result', [
            'success' => $result['success'],
            'show_next' => $result['show_next'] ?? false,
            'completed' => $result['completed'] ?? false,
            'message' => substr($result['message'] ?? '', 0, 100) . '...'
        ]);

        if ($result['success']) {
            if (isset($result['completed']) && $result['completed']) {
                Log::info('WORKFLOW COMPLETED - Sending completion message');
                // Workflow completed - send completion message
                $this->sendWorkflowResponse($customer, $result['message']);
            } elseif (isset($result['show_next']) && $result['show_next'] && $result['next_step']) {
                Log::info('SHOW_NEXT = TRUE - Sending two messages', [
                    'step_message' => substr($result['message'], 0, 50) . '...',
                    'next_step_id' => $result['next_step']['id'] ?? 'unknown'
                ]);
                // Move to next step and show it
                Log::info('SENDING MESSAGE 1: Step response');
                $this->sendWorkflowResponse($customer, $result['message']);
                
                Log::info('SENDING MESSAGE 2: Next step via sendWorkflowStep()');
                $workflowEngine->sendWorkflowStep($conversation);
            } else {
                Log::info('SENDING SINGLE RESPONSE MESSAGE');
                // Send response message only
                $this->sendWorkflowResponse($customer, $result['message']);
            }

            // Handle special response types
            if (isset($result['show_quick_replies']) && $result['show_quick_replies']) {
                $this->sendQuickReplies($customer, $result['suggestions'] ?? []);
            }
        } else {
            // Handle workflow error
            $errorMessage = $result['message'] ?? 'Sorry, there was an issue processing your request.';
            $this->sendWorkflowResponse($customer, $errorMessage);

            // Send suggestions if available
            if (isset($result['suggestions']) && !empty($result['suggestions'])) {
                $this->sendQuickReplies($customer, $result['suggestions']);
            }
        }
    }

    protected function checkForWorkflowStart(Customer $customer, string $messageText): void
    {
        // Get customer's Facebook page
        $facebookPage = $customer->client->facebookPages()
            ->where('is_connected', true)
            ->first();

        if (!$facebookPage) {
            return;
        }

        // Find active workflow for this page
        $workflow = \App\Models\Workflow::where('facebook_page_id', $facebookPage->id)
            ->where('is_active', true)
            ->latest('published_at')
            ->first();

        if (!$workflow) {
            return;
        }

        // Check for workflow trigger keywords
        $triggerKeywords = [
            'start', 'begin', 'order', 'shop', 'buy', 'purchase', 'hello', 'hi',
            'শুরু', 'অর্ডার', 'কিনতে', 'কেনাকাটা', 'হ্যালো', 'হাই'
        ];

        $messageWords = explode(' ', strtolower($messageText));
        $hasKeyword = !empty(array_intersect($messageWords, $triggerKeywords));

        if ($hasKeyword) {
            // Determine customer's preferred language
            $language = $this->detectLanguage($messageText, $workflow->supported_languages);

            // Start workflow
            $workflowEngine = app(\App\Services\WorkflowEngine::class);
            $conversation = $workflowEngine->startWorkflow($customer, $workflow->id, $language);

            if ($conversation) {
                // Send welcome message and first step
                $welcomeMessage = $language === 'bn'
                    ? "👋 স্বাগতম! আমি আপনাকে অর্ডার প্রক্রিয়ায় সাহায্য করব।"
                    : "👋 Welcome! I'll help you with your order process.";

                $this->sendWorkflowResponse($customer, $welcomeMessage);
                $workflowEngine->sendWorkflowStep($conversation);
            }
        }
    }

    protected function detectLanguage(string $text, array $supportedLanguages): string
    {
        // Simple language detection based on character sets
        $bengaliChars = preg_match('/[\x{0980}-\x{09FF}]/u', $text);
        
        if ($bengaliChars && in_array('bn', $supportedLanguages)) {
            return 'bn';
        }
        
        return in_array('en', $supportedLanguages) ? 'en' : $supportedLanguages[0];
    }

    protected function sendWorkflowResponse(Customer $customer, string $message): void
    {
        $facebookPage = $customer->client->facebookPages()
            ->where('is_connected', true)
            ->first();

        if (!$facebookPage || !$facebookPage->access_token) {
            Log::error('Cannot send workflow response: no Facebook page or token', [
                'customer_id' => $customer->id
            ]);
            return;
        }

        $facebookService = app(\App\Services\FacebookGraphAPIService::class);
        $result = $facebookService->sendTextMessage(
            $facebookPage->access_token,
            $customer->facebook_user_id,
            $message
        );

        // Log the sent message
        if ($result['success']) {
            // Get page customer ID for this customer
            $pageCustomer = \App\Models\PageCustomer::where('customer_id', $customer->id)
                ->whereHas('facebookPage', function($q) {
                    $q->where('is_connected', true);
                })
                ->first();

            \App\Models\CustomerMessage::create([
                'customer_id' => $customer->id,
                'page_customer_id' => $pageCustomer ? $pageCustomer->id : null,
                'client_id' => $customer->client_id,
                'message_type' => 'outgoing',
                'message_content' => $message,
                'message_data' => [
                    'type' => 'workflow_response',
                    'facebook_message_id' => $result['message_id'] ?? null,
                    'sent_at' => now()->toISOString(),
                    'platform' => 'facebook',
                    'status' => 'sent'
                ],
                'is_read' => true
            ]);
        }
    }

    protected function sendQuickReplies(Customer $customer, array $suggestions): void
    {
        $facebookPage = $customer->client->facebookPages()
            ->where('is_connected', true)
            ->first();

        if (!$facebookPage || !$facebookPage->access_token) {
            return;
        }

        $quickReplies = [];
        foreach (array_slice($suggestions, 0, 11) as $suggestion) { // Facebook allows max 11 quick replies
            $quickReplies[] = [
                'content_type' => 'text',
                'title' => Str::limit($suggestion, 20), // Facebook title limit
                'payload' => 'SUGGESTION_' . Str::slug($suggestion)
            ];
        }

        if (!empty($quickReplies)) {
            $facebookService = app(\App\Services\FacebookGraphAPIService::class);
            $message = $customer->getLanguage() === 'bn' 
                ? "অথবা নিচের বাটন থেকে নির্বাচন করুন:"
                : "Or choose from the options below:";

            $facebookService->sendQuickReply(
                $facebookPage->access_token,
                $customer->facebook_user_id,
                $message,
                $quickReplies
            );
        }
    }

    protected function handlePostback(Customer $customer, array $postback, array $event): void
    {
        $payload = $postback['payload'] ?? '';
        
        // Get page customer ID for this customer
        $pageCustomer = \App\Models\PageCustomer::where('customer_id', $customer->id)
            ->whereHas('facebookPage', function($q) {
                $q->where('is_connected', true);
            })
            ->first();

        // Store postback as message first
        CustomerMessage::create([
            'customer_id' => $customer->id,
            'page_customer_id' => $pageCustomer ? $pageCustomer->id : null,
            'client_id' => $customer->client_id,
            'message_type' => 'incoming',
            'message_content' => $postback['title'] ?? 'Postback',
            'message_data' => [
                'type' => 'postback',
                'payload' => $payload,
                'timestamp' => $event['timestamp']
            ],
            'is_read' => false
        ]);

        // Handle specific postback types
        if (preg_match('/^VIEW_PRODUCT_(\d+)$/', $payload, $matches)) {
            $this->handleViewProductPostback($customer, (int)$matches[1]);
        }

        Log::info('Postback processed', ['customer_id' => $customer->id, 'payload' => $payload]);
    }

    protected function handleViewProductPostback(Customer $customer, int $productId): void
    {
        try {
            // Get the product
            $product = \App\Models\Product::where('id', $productId)
                ->where('client_id', $customer->client_id)
                ->where('is_active', true)
                ->first();

            if (!$product) {
                Log::error('Product not found for view details', [
                    'product_id' => $productId,
                    'customer_id' => $customer->id
                ]);
                return;
            }

            // Get Facebook page for this customer to get the access token
            $facebookPage = $customer->client->facebookPages()
                ->where('is_connected', true)
                ->first();
                
            if (!$facebookPage || !$facebookPage->access_token) {
                Log::error('No connected Facebook page found for customer', [
                    'customer_id' => $customer->id,
                    'client_id' => $customer->client_id
                ]);
                return;
            }

            // Send product details using FacebookGraphAPIService
            $facebookService = app(\App\Services\FacebookGraphAPIService::class);
            $result = $facebookService->sendProductDetails(
                $facebookPage->access_token,
                $customer->facebook_user_id,
                $product->toArray()
            );

            if ($result['success']) {
                // Get page customer ID for this customer
                $pageCustomer = \App\Models\PageCustomer::where('customer_id', $customer->id)
                    ->whereHas('facebookPage', function($q) {
                        $q->where('is_connected', true);
                    })
                    ->first();

                // Log the sent product details as a message
                CustomerMessage::create([
                    'customer_id' => $customer->id,
                    'page_customer_id' => $pageCustomer ? $pageCustomer->id : null,
                    'client_id' => $customer->client_id,
                    'message_type' => 'outgoing',
                    'message_content' => 'Product Details: ' . $product->name,
                    'message_data' => [
                        'type' => 'product_details',
                        'product_id' => $productId,
                        'facebook_message_id' => $result['message_id'] ?? null,
                        'sent_at' => now()->toISOString(),
                        'platform' => 'facebook',
                        'status' => 'sent'
                    ],
                    'is_read' => true
                ]);

                Log::info('Product details sent successfully', [
                    'product_id' => $productId,
                    'customer_id' => $customer->id
                ]);
            } else {
                Log::error('Failed to send product details', [
                    'product_id' => $productId,
                    'customer_id' => $customer->id,
                    'error' => $result['error'] ?? 'Unknown error'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Exception handling view product postback', [
                'product_id' => $productId,
                'customer_id' => $customer->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function getPageCustomerId(Customer $customer, array $event): ?int
    {
        // Extract page ID from recipient in the event
        $pageId = $event['recipient']['id'] ?? null;
        if (!$pageId) {
            return null;
        }

        // Find the Facebook page
        $facebookPage = FacebookPage::where('page_id', $pageId)
            ->where('client_id', $customer->client_id)
            ->first();

        if (!$facebookPage) {
            return null;
        }

        // Find or create page customer relationship
        $pageCustomer = PageCustomer::findOrCreateForPage($facebookPage, $customer, $customer->facebook_user_id);
        
        return $pageCustomer->id;
    }
}

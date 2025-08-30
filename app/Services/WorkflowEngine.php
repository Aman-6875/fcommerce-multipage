<?php

namespace App\Services;

use App\Models\ConversationState;
use App\Models\Workflow;
use App\Models\Customer;
use App\Models\Order;
use App\Services\ProductSelectorService;
use App\Services\WorkflowMessageService;
use App\Services\FacebookGraphAPIService;
use App\Services\CustomerService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WorkflowEngine
{
    protected ProductSelectorService $productSelector;
    protected WorkflowMessageService $messageService;
    protected FacebookGraphAPIService $facebookService;
    protected CustomerService $customerService;

    public function __construct(
        ProductSelectorService $productSelector,
        WorkflowMessageService $messageService,
        FacebookGraphAPIService $facebookService,
        CustomerService $customerService
    ) {
        $this->productSelector = $productSelector;
        $this->messageService = $messageService;
        $this->facebookService = $facebookService;
        $this->customerService = $customerService;
    }

    /**
     * Start a new workflow conversation
     */
    public function startWorkflow(Customer $customer, int $workflowId, string $language = 'en'): ?ConversationState
    {
        $workflow = Workflow::find($workflowId);
        if (!$workflow || !$workflow->is_active) {
            Log::error('Workflow not found or inactive', ['workflow_id' => $workflowId]);
            return null;
        }

        if (!$workflow->supportsLanguage($language)) {
            $language = $workflow->default_language;
        }

        // End any existing active conversation for this customer on this page
        ConversationState::where('customer_id', $customer->id)
            ->where('facebook_page_id', $workflow->facebook_page_id)
            ->where('status', 'active')
            ->update(['status' => 'abandoned']);

        // Create new conversation
        $conversation = ConversationState::create([
            'customer_id' => $customer->id,
            'workflow_id' => $workflow->id,
            'facebook_page_id' => $workflow->facebook_page_id,
            'language' => $language,
            'status' => 'active',
            'current_step_index' => 0,
            'started_at' => now(),
            'last_activity_at' => now()
        ]);

        Log::info('Workflow started', [
            'conversation_id' => $conversation->id,
            'customer_id' => $customer->id,
            'workflow_id' => $workflowId,
            'language' => $language
        ]);

        return $conversation;
    }

    /**
     * Process input for current step
     */
    public function processStepInput(ConversationState $conversation, string $input): array
    {
        if (!$conversation->isActive()) {
            return ['success' => false, 'error' => 'conversation_not_active'];
        }

        $currentStep = $conversation->getCurrentStep();
        if (!$currentStep) {
            return ['success' => false, 'error' => 'no_current_step'];
        }

        $conversation->updateActivity();
        $stepType = $currentStep['type'];

        return match($stepType) {
            'product_selector' => $this->handleProductSelector($conversation, $input),
            'product_catalog' => $this->handleProductCatalog($conversation, $input),
            'choice_from_catalog' => $this->handleChoiceFromCatalog($conversation, $input),
            'form' => $this->handleFormStepEnhanced($conversation, $input),
            'choice' => $this->handleChoiceStep($conversation, $input),
            'info_display' => $this->handleInfoDisplay($conversation, $input),
            'confirmation' => $this->handleConfirmation($conversation, $input),
            default => ['success' => false, 'error' => 'unknown_step_type', 'step_type' => $stepType]
        };
    }

    /**
     * Handle product selection step
     */
    private function handleProductSelector(ConversationState $conversation, string $input): array
    {
        $step = $conversation->getCurrentStep();
        $config = $step['config'] ?? [];
        $language = $conversation->language;

        // Check retry limits
        $retryCount = $conversation->getStepRetryCount($step['id']);
        $maxRetries = $config['retry_attempts'] ?? 3;

        if ($retryCount >= $maxRetries) {
            return $this->handleMaxRetriesReached($conversation, $step);
        }

        // Parse product selection
        $result = $this->productSelector->parseProductSelection(
            $input,
            $conversation->workflow->client_id,
            $config,
            $language
        );

        if ($result['success']) {
            return $this->handleProductSelectionSuccess($conversation, $result, $step);
        } else {
            return $this->handleProductSelectionError($conversation, $result, $step);
        }
    }

    private function handleProductSelectionSuccess(ConversationState $conversation, array $result, array $step): array
    {
        $conversation->addStepResponse($step['id'], ['selected_products' => $result['products']]);
        $nextStep = $conversation->moveToNextStep();

        return [
            'success' => true,
            'message' => $this->messageService->getProductSelectionSuccess($result['products'], $step, $conversation->language),
            'next_step' => $nextStep,
            'show_next' => true,
        ];
    }

    private function handleProductSelectionError(ConversationState $conversation, array $result, array $step): array
    {
        $conversation->incrementStepRetryCount($step['id']);

        return [
            'success' => false,
            'message' => $this->messageService->getProductSelectionError($result, $step, $conversation->language),
            'suggestions' => $result['suggestions'] ?? [],
            'show_quick_replies' => !empty($result['suggestions']),
        ];
    }

    private function handleMaxRetriesReached(ConversationState $conversation, array $step): array
    {
        $conversation->update(['status' => 'failed']);
        Log::warning('Workflow failed due to max retries', [
            'conversation_id' => $conversation->id,
            'step_id' => $step['id'],
        ]);

        return [
            'success' => false,
            'error' => 'max_retries_reached',
            'message' => $this->messageService->getMaxRetriesMessage($step, $conversation->language),
        ];
    }

    /**
     * Handle form input step
     */
    private function handleFormStep(ConversationState $conversation, string $input): array
    {
        $step = $conversation->getCurrentStep();
        $fields = $step['fields'] ?? [];
        $language = $conversation->language;

        // If waiting for specific field input
        $waitingField = $conversation->getTempData('waiting_for_field');
        if ($waitingField) {
            return $this->handleFieldInput($conversation, $waitingField, $input, $step);
        }

        // Parse all fields from input
        $result = $this->parseFormInput($input, $fields, $language);

        if ($result['success']) {
            $conversation->addStepResponse($step['id'], $result['data']);
            $nextStep = $conversation->moveToNextStep();

            return [
                'success' => true,
                'message' => $this->messageService->getFormSuccess($result['data'], $step, $language),
                'data' => $result['data'],
                'next_step' => $nextStep,
                'show_next' => true
            ];
        } else {
            return $this->handleFormError($conversation, $result, $step);
        }
    }

    /**
     * Handle choice selection step
     */
    private function handleChoiceStep(ConversationState $conversation, string $input): array
    {
        $step = $conversation->getCurrentStep();
        $choices = $step['choices'] ?? [];
        $language = $conversation->language;

        $selectedChoice = $this->findChoice($input, $choices, $language);

        if ($selectedChoice) {
            $conversation->addStepResponse($step['id'], [
                'selected_choice' => $selectedChoice['id'],
                'choice_label' => $selectedChoice['labels'][$language] ?? $selectedChoice['labels']['en'],
                'input' => $input
            ]);

            // Handle conditional next steps
            if (isset($selectedChoice['next_step'])) {
                $nextStepId = $selectedChoice['next_step'];
                $conversation->jumpToStepById($nextStepId);
                $nextStep = $conversation->getCurrentStep();
            } else {
                $nextStep = $conversation->moveToNextStep();
            }

            return [
                'success' => true,
                'message' => $this->messageService->getChoiceSuccess($selectedChoice, $step, $language),
                'selected_choice' => $selectedChoice,
                'next_step' => $nextStep,
                'show_next' => true
            ];
        } else {
            $conversation->incrementStepRetryCount($step['id']);
            return [
                'success' => false,
                'message' => $this->messageService->getChoiceError($input, $choices, $step, $language),
                'suggestions' => array_map(function($choice) use ($language) {
                    return $choice['labels'][$language] ?? $choice['labels']['en'] ?? $choice['id'];
                }, $choices),
                'show_quick_replies' => true
            ];
        }
    }

    /**
     * Handle info display step
     */
    private function handleInfoDisplay(ConversationState $conversation, string $input): array
    {
        $step = $conversation->getCurrentStep();
        $language = $conversation->language;

        // Info display usually auto-progresses or waits for "continue"
        $continueKeywords = ['continue', 'next', 'ok', 'okay', 'yes', 'proceed'];
        $inputLower = strtolower(trim($input));

        if (in_array($inputLower, $continueKeywords) || ($step['auto_continue'] ?? false)) {
            $conversation->addStepResponse($step['id'], ['acknowledged' => true]);
            $nextStep = $conversation->moveToNextStep();

            return [
                'success' => true,
                'message' => $this->messageService->getInfoDisplaySuccess($step, $language),
                'next_step' => $nextStep,
                'show_next' => true
            ];
        }

        return [
            'success' => false,
            'message' => $this->messageService->getInfoDisplayWaiting($step, $language),
            'waiting' => true
        ];
    }

    /**
     * Handle confirmation step (usually final step)
     */
    private function handleConfirmation(ConversationState $conversation, string $input): array
    {
        $step = $conversation->getCurrentStep();
        $language = $conversation->language;

        $confirmKeywords = ['yes', 'confirm', 'ok', 'okay', 'submit', 'place order'];
        $cancelKeywords = ['no', 'cancel', 'back', 'edit'];
        $inputLower = strtolower(trim($input));

        if (in_array($inputLower, $confirmKeywords)) {
            return $this->completeWorkflow($conversation);
        } elseif (in_array($inputLower, $cancelKeywords)) {
            return $this->handleConfirmationCancel($conversation, $step);
        }

        return [
            'success' => false,
            'message' => $this->messageService->getConfirmationPrompt($step, $language),
            'waiting_confirmation' => true
        ];
    }

    /**
     * Complete the workflow and create order
     */
    public function completeWorkflow(ConversationState $conversation): array
    {
        try {
            // Extract all collected data
            $allResponses = $conversation->getAllResponses();
            
            // Update customer information from workflow responses
            $this->updateCustomerFromWorkflow($conversation, $allResponses);
            
            // Create order from workflow responses
            $order = $this->createOrderFromWorkflow($conversation, $allResponses);
            
            $conversation->complete();

            Log::info('Workflow completed successfully', [
                'conversation_id' => $conversation->id,
                'order_id' => $order->id
            ]);

            return [
                'success' => true,
                'completed' => true,
                'order' => $order,
                'message' => $this->messageService->getWorkflowCompletionMessage($order, $conversation->language)
            ];
        } catch (\Exception $e) {
            Log::error('Failed to complete workflow', [
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'completion_failed',
                'message' => $this->messageService->getWorkflowCompletionError($conversation->language)
            ];
        }
    }

    /**
     * Update customer information from workflow responses
     */
    private function updateCustomerFromWorkflow(ConversationState $conversation, array $responses): void
    {
        $customer = $conversation->customer;
        $customerInfo = $this->extractCustomerInfo($responses);
        
        if (!empty($customerInfo)) {
            // Use CustomerService for phone-first customer management
            $facebookPage = $conversation->facebookPage;
            $updatedCustomer = $this->customerService->updateFromWorkflowData($customer, $customerInfo, $facebookPage);
            
            // If customer was merged, update the conversation to point to the master customer
            if ($updatedCustomer->id !== $customer->id) {
                $conversation->update(['customer_id' => $updatedCustomer->id]);
                Log::info('Conversation updated to point to merged customer', [
                    'conversation_id' => $conversation->id,
                    'old_customer_id' => $customer->id,
                    'new_customer_id' => $updatedCustomer->id
                ]);
            }
            
            // Update interaction stats
            $updatedCustomer->update(['last_interaction' => now()]);
            $updatedCustomer->increment('interaction_count');
            
            Log::info('Customer information updated from workflow using phone-first strategy', [
                'customer_id' => $updatedCustomer->id,
                'conversation_id' => $conversation->id,
                'had_phone' => !empty($customerInfo['phone'])
            ]);
        }
    }

    /**
     * Create order from workflow responses
     */
    private function createOrderFromWorkflow(ConversationState $conversation, array $responses): Order
    {
        $customerInfo = $this->extractCustomerInfo($responses);
        $productSelections = $this->extractProductSelections($responses);
        $deliveryInfo = $this->extractDeliveryInfo($responses);
        $subtotalAmount = $this->calculateTotal($productSelections);

        if (empty($productSelections)) {
            throw new \Exception('No products selected for order');
        }

        // Calculate shipping charge based on delivery area
        $shippingCharge = $this->calculateShippingCharge($deliveryInfo);
        $finalTotal = $subtotalAmount + $shippingCharge;

        // Create the main order record
        $order = Order::create([
            'client_id' => $conversation->workflow->client_id,
            'facebook_page_id' => $conversation->facebook_page_id,
            'customer_id' => $conversation->customer_id,
            'facebook_user_id' => $conversation->customer->facebook_user_id,
            'order_number' => Order::generateOrderNumber($conversation->workflow->client_id),
            'shipping_charge' => $shippingCharge,
            'total_amount' => $finalTotal,
            'customer_info' => $customerInfo,
            'delivery_info' => $deliveryInfo,
            'status' => 'pending',
            'payment_method' => 'cod',
            'notes' => json_encode([
                'workflow_id' => $conversation->workflow_id,
                'conversation_id' => $conversation->id,
                'workflow_language' => $conversation->language
            ])
        ]);

        // Add all products to order_meta table
        foreach ($productSelections as $productSelection) {
            $product = \App\Models\Product::find($productSelection['id']);
            if ($product) {
                $order->addProduct($product, $productSelection['quantity']);
            }
        }

        // Update the total amount after adding products
        $order->updateTotalAmount();

        return $order;
    }

    /**
     * Send workflow step to customer via Facebook
     */
    public function sendWorkflowStep(ConversationState $conversation): bool
    {
        $step = $conversation->getCurrentStep();
        
        Log::info('=== sendWorkflowStep() CALLED ===', [
            'conversation_id' => $conversation->id,
            'current_step_index' => $conversation->current_step_index,
            'step_id' => $step['id'] ?? 'unknown',
            'step_type' => $step['type'] ?? 'unknown'
        ]);
        
        if (!$step) {
            Log::error('No current step found in sendWorkflowStep');
            return false;
        }

        $customer = $conversation->customer;
        $facebookPage = $conversation->facebookPage;
        
        if (!$facebookPage || !$facebookPage->access_token) {
            Log::error('No Facebook page or access token', [
                'conversation_id' => $conversation->id
            ]);
            return false;
        }

        // Special handling for product_catalog
        if ($step['type'] === 'product_catalog') {
            return $this->sendProductCatalogStep($conversation, $step);
        }

        if ($step['type'] === 'confirmation') {
            Log::info('Generating confirmation message');
            $message = $this->messageService->getConfirmationMessage($step, $conversation->language, $conversation);
        } else {
            Log::info('Generating context-aware step message', [
                'step_type' => $step['type'],
                'step_id' => $step['id']
            ]);
            $message = $this->getContextAwareStepMessage($step, $conversation->language, $conversation);
        }
        
        Log::info('Generated message for sendWorkflowStep', [
            'message_length' => strlen($message),
            'message_preview' => substr($message, 0, 100) . '...'
        ]);
        
        // Send message
        $result = $this->facebookService->sendTextMessage(
            $facebookPage->access_token,
            $customer->facebook_user_id,
            $message
        );

        // Send additional elements based on step type
        if ($result['success']) {
            $this->sendStepExtras($conversation, $step);
        }

        return $result['success'];
    }
    
    /**
     * Send product catalog step with product list
     */
    private function sendProductCatalogStep(ConversationState $conversation, array $step): bool
    {
        $customer = $conversation->customer;
        $facebookPage = $conversation->facebookPage;
        $language = $conversation->language;
        $clientId = $conversation->workflow->client_id;
        
        // Get the full catalog message with products
        $message = $this->messageService->getProductCatalogMessage($step, $language, $clientId, $facebookPage->id);
        
        // Send the catalog message
        $result = $this->facebookService->sendTextMessage(
            $facebookPage->access_token,
            $customer->facebook_user_id,
            $message
        );
        
        if ($result['success']) {
            Log::info('Product catalog sent successfully', [
                'conversation_id' => $conversation->id,
                'step_id' => $step['id']
            ]);
        } else {
            Log::error('Failed to send product catalog', [
                'conversation_id' => $conversation->id,
                'step_id' => $step['id'],
                'error' => $result['error'] ?? 'Unknown error'
            ]);
        }
        
        return $result['success'];
    }

    /**
     * Send additional step elements (quick replies, carousels, etc.)
     */
    private function sendStepExtras(ConversationState $conversation, array $step): void
    {
        $stepType = $step['type'];
        $customer = $conversation->customer;
        $facebookPage = $conversation->facebookPage;
        $language = $conversation->language;

        switch ($stepType) {
            case 'choice':
                $this->sendChoiceQuickReplies($conversation, $step);
                break;
            case 'product_selector':
                $this->sendProductCarousel($conversation, $step);
                break;
            case 'product_catalog':
                // For product catalog, the message already contains the catalog
                // No additional extras needed
                break;
        }
    }

    // Helper methods for data extraction
    public function extractCustomerInfo(array $responses): array
    {
        $customerInfo = [];
        
        foreach ($responses as $stepId => $stepResponse) {
            if (isset($stepResponse['name'])) {
                $customerInfo['name'] = $stepResponse['name'];
            }
            if (isset($stepResponse['phone'])) {
                $customerInfo['phone'] = $stepResponse['phone'];
            }
            if (isset($stepResponse['email'])) {
                $customerInfo['email'] = $stepResponse['email'];
            }
            if (isset($stepResponse['address'])) {
                $customerInfo['address'] = $stepResponse['address'];
            }
            // Also check for delivery address if no specific address field
            if (!isset($customerInfo['address']) && isset($stepResponse['delivery_address'])) {
                $customerInfo['address'] = $stepResponse['delivery_address'];
            }
        }
        
        return $customerInfo;
    }

    public function extractProductSelections(array $responses): array
    {
        $products = [];
        
        // Check for individual product selection
        $selectedProductId = null;
        $quantity = 1;
        $productDetails = null;
        
        foreach ($responses as $stepId => $stepResponse) {
            if (isset($stepResponse['selected_product_id'])) {
                $selectedProductId = $stepResponse['selected_product_id'];
            }
            if (isset($stepResponse['quantity'])) {
                $quantity = $stepResponse['quantity'];
            }
        }
        
        if ($selectedProductId) {
            // Get product details from database
            $product = \App\Models\Product::find($selectedProductId);
            if ($product) {
                $effectivePrice = $product->sale_price ?: $product->price;
                $products[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $effectivePrice,
                    'quantity' => $quantity,
                    'total' => $effectivePrice * $quantity
                ];
            }
        }
        
        return $products;
    }

    public function extractDeliveryInfo(array $responses): array
    {
        $deliveryInfo = [];
        
        foreach ($responses as $stepId => $stepResponse) {
            // Extract address from delivery_address step
            if (isset($stepResponse['address'])) {
                $deliveryInfo['address'] = $stepResponse['address'];
            }
            if (isset($stepResponse['landmark'])) {
                $deliveryInfo['landmark'] = $stepResponse['landmark'];
            }
            
            // Extract delivery area info from delivery_area step
            if (isset($stepResponse['selected_choice']) && $stepId === 'delivery_area') {
                $deliveryInfo['delivery_area'] = $stepResponse['choice_label'];
            }
            
            // Extract special instructions
            if (isset($stepResponse['notes'])) {
                $deliveryInfo['notes'] = $stepResponse['notes'];
            }
        }
        
        return $deliveryInfo;
    }

    public function calculateTotal(array $products): float
    {
        return collect($products)->sum('total');
    }

    /**
     * Calculate shipping charge based on delivery info
     */
    private function calculateShippingCharge(array $deliveryInfo): float
    {
        $deliveryArea = $deliveryInfo['delivery_area'] ?? '';
        
        // Extract shipping charge from delivery area choice
        if (str_contains($deliveryArea, 'Inside Dhaka') || str_contains($deliveryArea, 'inside_dhaka')) {
            return 60.0;
        } elseif (str_contains($deliveryArea, 'Outside Dhaka') || str_contains($deliveryArea, 'outside_dhaka')) {
            return 120.0;
        }
        
        // Default to inside Dhaka charge
        return 60.0;
    }

    /**
     * Get context-aware step message with placeholder replacements
     */
    private function getContextAwareStepMessage(array $step, string $language, ConversationState $conversation): string
    {
        $message = $this->messageService->getStepMessage($step, $language);
        
        // Replace common placeholders with conversation context
        $selectedProduct = $conversation->getTempData('selected_product');
        if ($selectedProduct) {
            $message = str_replace('{selected_product}', $selectedProduct['name'], $message);
        }
        
        // Add more placeholder replacements as needed
        $customerInfo = $conversation->getStepResponse('customer_info');
        if ($customerInfo && isset($customerInfo['name'])) {
            $message = str_replace('{customer_name}', $customerInfo['name'], $message);
        }
        
        return $message;
    }

    /**
     * Handle product catalog display step
     */
    private function handleProductCatalog(ConversationState $conversation, string $input): array
    {
        $step = $conversation->getCurrentStep();
        $config = $step['config'] ?? [];
        $language = $conversation->language;
        $clientId = $conversation->workflow->client_id;

        // Get available products
        $products = $this->productSelector->getProductsForFacebookPage($conversation->workflow->facebook_page_id);
        
        // Check if input is a number (product index)
        if (is_numeric(trim($input))) {
            $index = (int)trim($input) - 1; // Convert to 0-based index
            if (isset($products[$index])) {
                $selectedProduct = $products[$index];
                return $this->handleProductSelected($conversation, $selectedProduct, $step);
            }
        }

        // Try to find product by name
        $productName = trim($input);
        $selectedProduct = $products->first(function($product) use ($productName) {
            return strtolower($product->name) === strtolower($productName);
        });

        if ($selectedProduct) {
            return $this->handleProductSelected($conversation, $selectedProduct, $step);
        }

        // Try fuzzy matching
        $suggestions = $products->filter(function($product) use ($productName) {
            return str_contains(strtolower($product->name), strtolower($productName)) ||
                   str_contains(strtolower($productName), strtolower($product->name));
        })->take(3);

        // Increment retry count
        $conversation->incrementStepRetryCount($step['id']);
        $retryCount = $conversation->getStepRetryCount($step['id']);
        $maxRetries = $config['retry_attempts'] ?? 3;

        if ($retryCount >= $maxRetries) {
            return $this->handleMaxRetriesReached($conversation, $step);
        }

        return [
            'success' => false,
            'message' => $this->messageService->getProductSelectionFromCatalogError($input, $suggestions, $step, $language),
            'suggestions' => $suggestions->pluck('name')->toArray(),
            'show_quick_replies' => $suggestions->isNotEmpty()
        ];
    }

    /**
     * Handle product selection from catalog
     */
    private function handleChoiceFromCatalog(ConversationState $conversation, string $input): array
    {
        $step = $conversation->getCurrentStep();
        $config = $step['config'] ?? [];
        $language = $conversation->language;
        $clientId = $conversation->workflow->client_id;

        // Get available products
        $products = $this->productSelector->getProductsForFacebookPage($conversation->workflow->facebook_page_id);
        
        // Check if input is a number (product index)
        if (is_numeric(trim($input))) {
            $index = (int)trim($input) - 1; // Convert to 0-based index
            if (isset($products[$index])) {
                $selectedProduct = $products[$index];
                return $this->handleProductSelected($conversation, $selectedProduct, $step);
            }
        }

        // Try to find product by name
    $productName = trim($input);
        $selectedProduct = $products->first(function($product) use ($productName) {
            return strtolower($product->name) === strtolower($productName);
        });

        if ($selectedProduct) {
            return $this->handleProductSelected($conversation, $selectedProduct, $step);
        }

        // Try fuzzy matching
        $suggestions = $products->filter(function($product) use ($productName) {
            return str_contains(strtolower($product->name), strtolower($productName)) ||
                   str_contains(strtolower($productName), strtolower($product->name));
        })->take(3);

        // Increment retry count
        $conversation->incrementStepRetryCount($step['id']);
        $retryCount = $conversation->getStepRetryCount($step['id']);
        $maxRetries = $config['retry_attempts'] ?? 3;

        if ($retryCount >= $maxRetries) {
            return $this->handleMaxRetriesReached($conversation, $step);
        }

        return [
            'success' => false,
            'message' => $this->messageService->getProductSelectionFromCatalogError($input, $suggestions, $step, $language),
            'suggestions' => $suggestions->pluck('name')->toArray(),
            'show_quick_replies' => $suggestions->isNotEmpty()
        ];
    }

    /**
     * Handle successful product selection
     */
    private function handleProductSelected(ConversationState $conversation, $product, array $step): array
    {
        // Store selected product in conversation state
        $conversation->setTempData('selected_product', [
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->sale_price ?: $product->price,
            'sku' => $product->sku
        ]);

        $conversation->addStepResponse($step['id'], ['selected_product_id' => $product->id]);
        $nextStep = $conversation->moveToNextStep();

        Log::info( [
            'success' => true,
            'message' => $this->messageService->getProductSelectedSuccess($product, $step, $conversation->language),
            'selected_product' => $product,
            'next_step' => $nextStep,
            'show_next' => true
        ]);

        return [
            'success' => true,
            'message' => $this->messageService->getProductSelectedSuccess($product, $step, $conversation->language),
            'selected_product' => $product,
            'next_step' => $nextStep,
            'show_next' => true
        ];
    }

    /**
     * Enhanced form handling with context replacement
     */
    private function handleFormStepEnhanced(ConversationState $conversation, string $input): array
    {
        $step = $conversation->getCurrentStep();
        $fields = $step['fields'] ?? [];
        $language = $conversation->language;

        // Replace placeholders in step labels with context
        $step = $this->replaceStepPlaceholders($step, $conversation);

        // If waiting for specific field input
        $waitingField = $conversation->getTempData('waiting_for_field');
        if ($waitingField) {
            return $this->handleFieldInput($conversation, $waitingField, $input, $step);
        }

        // Parse all fields from input
        $result = $this->parseFormInput($input, $fields, $language);

        if ($result['success']) {
            $conversation->addStepResponse($step['id'], $result['data']);
            $nextStep = $conversation->moveToNextStep();

            return [
                'success' => true,
                'message' => $this->messageService->getFormSuccess($result['data'], $step, $language),
                'data' => $result['data'],
                'next_step' => $nextStep,
                'show_next' => true
            ];
        } else {
            return $this->handleFormError($conversation, $result, $step);
        }
    }

    /**
     * Replace placeholders in step content
     */
    private function replaceStepPlaceholders(array $step, ConversationState $conversation): array
    {
        $selectedProduct = $conversation->getTempData('selected_product');
        $allResponses = $conversation->getAllResponses();
        
        // Replace product placeholders
        if ($selectedProduct) {
            $step = $this->replaceInArray($step, '{selected_product}', $selectedProduct['name']);
        }

        // Replace customer info placeholders
        $customerInfo = $this->extractCustomerInfo($allResponses);
        foreach ($customerInfo as $key => $value) {
            $step = $this->replaceInArray($step, '{customer_' . $key . '}', $value);
        }

        return $step;
    }

    /**
     * Recursively replace placeholders in array
     */
    private function replaceInArray(array $array, string $search, string $replace): array
    {
        array_walk_recursive($array, function(&$value) use ($search, $replace) {
            if (is_string($value)) {
                $value = str_replace($search, $replace, $value);
            }
        });
        return $array;
    }

    /**
     * Parse form input handling for different field types
     */
    private function parseFormInput(string $input, array $fields, string $language): array
    {
        $input = trim($input);
        $data = [];
        $errors = [];
        
        // For single field forms
        if (count($fields) === 1) {
            $field = $fields[0];
            $result = $this->validateFieldInput($input, $field);
            
            if ($result['valid']) {
                $data[$field['name']] = $result['value'];
            } else {
                $errors[] = [
                    'field' => $field['name'],
                    'error' => $result['error'],
                    'message' => $result['message']
                ];
            }
        } else {
            // For multi-field forms - parse line by line
            $lines = array_filter(array_map('trim', explode("\n", $input)));
            
            foreach ($fields as $index => $field) {
                if (isset($lines[$index]) && !empty($lines[$index])) {
                    $result = $this->validateFieldInput($lines[$index], $field);
                    
                    if ($result['valid']) {
                        $data[$field['name']] = $result['value'];
                    } else {
                        $errors[] = [
                            'field' => $field['name'],
                            'error' => $result['error'],
                            'message' => $result['message']
                        ];
                    }
                } else {
                    // Missing required field
                    if ($field['required'] ?? false) {
                        $errors[] = [
                            'field' => $field['name'],
                            'error' => 'missing',
                            'message' => "Please provide " . ($field['labels']['en'] ?? $field['name'])
                        ];
                    }
                }
            }
        }
        
        return [
            'success' => empty($errors),
            'data' => $data,
            'errors' => $errors
        ];
    }
    
    /**
     * Validate individual field input
     */
    private function validateFieldInput(string $input, array $field): array
    {
        $value = trim($input);
        $validation = $field['validation'] ?? [];
        
        // Check if required
        if (($field['required'] ?? false) && empty($value)) {
            return [
                'valid' => false,
                'error' => 'required',
                'message' => 'This field is required'
            ];
        }
        
        // Skip validation if empty and not required
        if (empty($value)) {
            return ['valid' => true, 'value' => $value];
        }
        
        // Type-specific validation
        switch ($field['type']) {
            case 'number':
                if (!is_numeric($value)) {
                    return [
                        'valid' => false,
                        'error' => 'invalid_number',
                        'message' => 'Please enter a valid number'
                    ];
                }
                $numValue = (int)$value;
                if (isset($validation['min']) && $numValue < $validation['min']) {
                    return [
                        'valid' => false,
                        'error' => 'min_value',
                        'message' => "Minimum value is {" . $validation['min'] . "}"
                    ];
                }
                if (isset($validation['max']) && $numValue > $validation['max']) {
                    return [
                        'valid' => false,
                        'error' => 'max_value',
                        'message' => "Maximum value is {" . $validation['max'] . "}"
                    ];
                }
                $value = $numValue;
                break;
                
            case 'tel':
                if (isset($validation['pattern'])) {
                    if (!preg_match('/' . $validation['pattern'] . '/', $value)) {
                        return [
                            'valid' => false,
                            'error' => 'invalid_phone',
                            'message' => 'Please enter a valid phone number (11 digits starting with 01)'
                        ];
                    }
                }
                break;
                
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return [
                        'valid' => false,
                        'error' => 'invalid_email',
                        'message' => 'Please enter a valid email address'
                    ];
                }
                break;
        }
        
        // Length validation
        if (isset($validation['min_length']) && strlen($value) < $validation['min_length']) {
            return [
                'valid' => false,
                'error' => 'min_length',
                'message' => "Minimum length is {" . $validation['min_length'] . " characters"
            ];
        }
        
        if (isset($validation['max_length']) && strlen($value) > $validation['max_length']) {
            return [
                'valid' => false,
                'error' => 'max_length',
                'message' => "Maximum length is {" . $validation['max_length'] . " characters"
            ];
        }
        
        return ['valid' => true, 'value' => $value];
    }
    
    /**
     * Handle form validation errors
     */
    private function handleFormError(ConversationState $conversation, array $result, array $step): array
    {
        $conversation->incrementStepRetryCount($step['id']);
        $language = $conversation->language;
        
        $message = $language === 'bn'
            ? "❌ দুঃখিত, তথ্যটি সঠিক নয়।\n\n"
            : "❌ Sorry, the information is not valid.\n\n";
            
        if (isset($result['errors'])) {
            foreach ($result['errors'] as $error) {
                $message .= "- " . $error['message'] . "\n";
            }
        } else {
            $message .= "- " . ($result['message'] ?? 'Invalid input') . "\n";
        }
        
        $message .= "\n" . ($language === 'bn'
            ? 'অনুগ্রহ করে আবার চেষ্টা করুন।'
            : 'Please try again.');
            
        return [
            'success' => false,
            'message' => $message,
            'errors' => $result['errors'] ?? []
        ];
    }
    
    /**
     * Handle field input (for sequential field collection)
     */
    private function handleFieldInput(ConversationState $conversation, string $fieldName, string $input, array $step): array
    {
        // This would handle sequential field input - simplified for now
        return $this->handleFormStepEnhanced($conversation, $input);
    }
    
    /**
     * Find choice from choices array
     */
    private function findChoice(string $input, array $choices, string $language): ?array
    {
        $inputLower = strtolower(trim($input));
        
        foreach ($choices as $choice) {
            $labels = $choice['labels'];
            $choiceLabel = strtolower($labels[$language] ?? $labels['en'] ?? $choice['id']);
            $choiceId = strtolower($choice['id']);
            
            // Exact match
            if ($inputLower === $choiceLabel || $inputLower === $choiceId) {
                return $choice;
            }
            
            // Partial match - check if input starts with choice or choice starts with input
            if (str_starts_with($choiceLabel, $inputLower) || str_starts_with($inputLower, $choiceLabel)) {
                return $choice;
            }
            
            // Check for common variations
            if ($choiceId === 'no_more' && (str_contains($inputLower, 'no') || str_contains($inputLower, 'continue'))) {
                return $choice;
            }
            
            if ($choiceId === 'yes_more' && (str_contains($inputLower, 'yes') || str_contains($inputLower, 'more') || str_contains($inputLower, 'add'))) {
                return $choice;
            }
        }
        
        return null;
    }
    
    /**
     * Send choice quick replies
     */
    private function sendChoiceQuickReplies(ConversationState $conversation, array $step): void
    {
        $customer = $conversation->customer;
        $facebookPage = $conversation->facebookPage;
        $language = $conversation->language;
        $choices = $step['choices'] ?? [];
        
        if (!$facebookPage || !$facebookPage->access_token) {
            return;
        }
        
        $quickReplies = [];
        foreach (array_slice($choices, 0, 11) as $choice) { // Facebook allows max 11
            $choiceLabel = $choice['labels'][$language] ?? $choice['labels']['en'] ?? $choice['id'];
            $quickReplies[] = [
                'content_type' => 'text',
                'title' => substr($choiceLabel, 0, 20), // Facebook title limit
                'payload' => 'CHOICE_' . strtoupper($choice['id'])
            ];
        }
        
        if (!empty($quickReplies)) {
            $message = $language === 'bn' 
                ? "নিচের অপশনগুলো থেকে নির্বাচন করুন:"
                : "Choose from the options below:";
                
            $this->facebookService->sendQuickReply(
                $facebookPage->access_token,
                $customer->facebook_user_id,
                $message,
                $quickReplies
            );
        }
    }
    
    /**
     * Send product carousel
     */
    private function sendProductCarousel(ConversationState $conversation, array $step): void
    {
        // This would send a product carousel for product_selector steps
        // For now, we'll keep it simple as the improved workflow uses product_catalog instead
    }
    
    /**
     * Handle confirmation cancel
     */
    private function handleConfirmationCancel(ConversationState $conversation, array $step): array
    {
        $language = $conversation->language;
        
        return [
            'success' => false,
            'message' => $language === 'bn'
                ? "অর্ডার বাতিল করা হয়েছে। আবার শুরু করতে 'hello' টাইপ করুন।"
                : "Order cancelled. Type 'hello' to start again.",
            'cancelled' => true
        ];
    }
    
    // Additional helper methods would go here...
}

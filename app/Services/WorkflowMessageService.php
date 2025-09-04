<?php

namespace App\Services;

use App\Models\Order;

class WorkflowMessageService
{
    /**
     * Get step message for customer
     */
    public function getStepMessage(array $step, string $language): string
    {
        // Special handling for product_catalog type
        if ($step['type'] === 'product_catalog') {
            // We need client_id to get products, but we don't have it here
            // Return a basic message for now, the actual catalog will be handled elsewhere
            $labels = $step['labels'][$language] ?? $step['labels']['en'] ?? [];
            $title = $labels['title'] ?? 'Our Products';
            $description = $labels['description'] ?? '';
            
            $message = "📦 **{$title}**\n\n";
            if ($description) {
                $message .= $description . "\n\n";
            }
            return $message;
        }
        
        $labels = $step['labels'][$language] ?? $step['labels']['en'] ?? [];
        $title = $labels['title'] ?? 'Step';
        $description = $labels['description'] ?? '';

        $message = "📝 **{$title}**\n\n";
        if ($description) {
            $message .= $description . "\n\n";
        }

        // Add step-specific instructions
        $stepType = $step['type'];
        $instructions = $this->getStepInstructions($step, $language);
        if ($instructions) {
            $message .= $instructions;
        }

        return $message;
    }

    /**
     * Get step-specific instructions
     */
    private function getStepInstructions(array $step, string $language): string
    {
        $labels = $step['labels'][$language] ?? $step['labels']['en'] ?? [];
        $stepType = $step['type'];

        switch ($stepType) {
            case 'product_selector':
                return $labels['format_help'] ?? ($language === 'bn' 
                    ? "পণ্যের নাম টাইপ করুন বা একাধিকের জন্য কমা দিয়ে আলাদা করুন।"
                    : "Type product name or separate multiple products with commas.");

            case 'form':
                $fields = $step['fields'] ?? $step['config']['fields'] ?? [];
                if (count($fields) === 1) {
                    $field = $fields[0];
                    $fieldLabel = $field['labels'][$language] ?? $field['labels']['en'] ?? $field['name'];
                    return $language === 'bn' ? "{$fieldLabel} লিখুন:" : "Please enter {$fieldLabel}:";
                } else {
                    $message = $language === 'bn'
                        ? "নিচের তথ্যগুলো প্রদান করুন:"
                        : "Please provide the following information:";
                    
                    foreach ($fields as $field) {
                        $fieldLabel = $field['labels'][$language] ?? $field['labels']['en'] ?? $field['name'];
                        $message .= "\n- " . $fieldLabel;
                    }
                    
                    return $message;
                }

            case 'choice':
                return $language === 'bn'
                    ? "নিচের অপশনগুলো থেকে একটি বেছে নিন:"
                    : "Please choose one of the options below:";

            case 'info_display':
                return $language === 'bn'
                    ? "'এগিয়ে যান' টাইপ করুন চালিয়ে যেতে।"
                    : "Type 'continue' to proceed.";

            case 'confirmation':
                return $language === 'bn'
                    ? "'হ্যাঁ' টাইপ করুন নিশ্চিত করতে বা 'না' টাইপ করুন বাতিল করতে।"
                    : "Type 'yes' to confirm or 'no' to cancel.";

            default:
                return '';
        }
    }

    /**
     * Product selection success message
     */
    public function getProductSelectionSuccess(array $products, array $labels, string $language): string
    {
        if (count($products) === 1) {
            $product = $products[0];
            $template = $labels['success_single'] ?? ($language === 'bn' 
                ? "✅ নির্বাচিত: {product}" 
                : "✅ Selected: {product}");
            return str_replace('{product}', $product['name'], $template);
        }

        $template = $labels['success_multiple'] ?? ($language === 'bn' 
            ? "✅ {count}টি পণ্য নির্বাচিত" 
            : "✅ Selected {count} products");
        $message = str_replace('{count}', count($products), $template) . "\n\n";

        foreach ($products as $index => $product) {
            $message .= ($index + 1) . ". {$product['name']}";
            if ($product['quantity'] > 1) {
                $message .= " x{$product['quantity']}";
            }
            $message .= " - ৳" . number_format($product['total'], 0) . "\n";
        }

        return $message;
    }

    /**
     * Product selection error message
     */
    public function getProductSelectionError(array $result, array $labels, string $language): string
    {
        $message = "";

        foreach ($result['errors'] as $error) {
            $errorTemplate = count($error['suggestions']) > 1
                ? ($labels['error_multiple_matches'] ?? ($language === 'bn'
                    ? "🤔 '{input}' এর জন্য একাধিক পণ্য পাওয়া গেছে। স্পেসিফিক হন:"
                    : "🤔 Found multiple matches for '{input}'. Please be specific:"))
                : ($labels['error_not_found'] ?? ($language === 'bn'
                    ? "❌ '{input}' পাওয়া যায়নি। আপনি কি বোঝাতে চেয়েছেন:"
                    : "❌ Couldn't find '{input}'. Did you mean:"));

            $message .= str_replace('{input}', $error['input'], $errorTemplate) . "\n";

            if (!empty($error['suggestions'])) {
                foreach ($error['suggestions'] as $suggestion) {
                    $message .= "• {$suggestion}\n";
                }
            }
            $message .= "\n";
        }

        $retryMessage = $labels['retry_message'] ?? ($language === 'bn'
            ? "আবার চেষ্টা করুন বা উপরের সাজেশন থেকে বেছে নিন."
            : "Please try again or choose from suggestions above.");

        $message .= $retryMessage;
        return $message;
    }

    /**
     * Form success message
     */
    public function getFormSuccess(array $data, array $step, string $language): string
    {
        $labels = $step['labels'][$language] ?? $step['labels']['en'] ?? [];
        
        $message = $labels['success'] ?? ($language === 'bn'
            ? "✅ তথ্য সংরক্ষিত হয়েছে."
            : "✅ Information saved.");

        return $message;
    }

    /**
     * Choice success message
     */
    public function getChoiceSuccess(array $selectedChoice, array $step, string $language): string
    {
        $choiceLabel = $selectedChoice['labels'][$language] ?? $selectedChoice['labels']['en'] ?? $selectedChoice['id'];
        
        return $language === 'bn'
            ? "✅ আপনি নির্বাচন করেছেন: {$choiceLabel}"
            : "✅ You selected: {$choiceLabel}";
    }

    /**
     * Choice error message
     */
    public function getChoiceError(string $input, array $choices, array $step, string $language): string
    {
        $message = $language === 'bn'
            ? "❌ '{$input}' সঠিক অপশন নয়। অনুগ্রহ করে নিচের অপশনগুলো থেকে বেছে নিন:\n\n"
            : "❌ '{$input}' is not a valid option. Please choose from:\n\n";

        foreach ($choices as $index => $choice) {
            $choiceLabel = $choice['labels'][$language] ?? $choice['labels']['en'] ?? $choice['id'];
            $message .= ($index + 1) . ". {$choiceLabel}\n";
        }

        return $message;
    }

    /**
     * Workflow completion message
     */
    public function getWorkflowCompletionMessage(Order $order, string $language): string
    {
        if ($language === 'bn') {
            return "🎉 **অভিনন্দন! আপনার অর্ডার সম্পন্ন হয়েছে।**\n\n" .
                   "📋 অর্ডার নম্বর: **{$order->order_number}**\n" .
                   "💰 মোট: **৳" . number_format($order->total_amount, 0) . "**\n\n" .
                   "📞 আমরা শীঘ্রই আপনার সাথে যোগাযোগ করব।\n" .
                   "ধন্যবাদ! 🙏";
        }

        return "🎉 **Congratulations! Your order has been completed.**\n\n" .
               "📋 Order Number: **{$order->order_number}**\n" .
               "💰 Total: **৳" . number_format($order->total_amount, 0) . "**\n\n" .
               "📞 We will contact you soon.\n" .
               "Thank you! 🙏";
    }

    /**
     * Workflow completion error message
     */
    public function getWorkflowCompletionError(string $language): string
    {
        return $language === 'bn'
            ? "❌ দুঃখিত, আপনার অর্ডার প্রক্রিয়া করতে সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন."
            : "❌ Sorry, there was an issue processing your order. Please try again.";
    }

    /**
     * Info display success message
     */
    public function getInfoDisplaySuccess(array $step, string $language): string
    {
        $labels = $step['labels'][$language] ?? $step['labels']['en'] ?? [];
        return $labels['continue_message'] ?? ($language === 'bn' ? "এগিয়ে যাচ্ছি..." : "Continuing...");
    }

    /**
     * Info display waiting message
     */
    public function getInfoDisplayWaiting(array $step, string $language): string
    {
        return $language === 'bn'
            ? "এগিয়ে যেতে 'চালিয়ে যান' টাইপ করুন."
            : "Type 'continue' to proceed.";
    }

    /**
     * Confirmation prompt message
     */
    public function getConfirmationPrompt(array $step, string $language): string
    {
        return $language === 'bn'
            ? "অনুগ্রহ করে 'হ্যাঁ' টাইপ করুন নিশ্চিত করতে বা 'না' টাইপ করুন বাতিল করতে."
            : "Please type 'yes' to confirm or 'no' to cancel.";
    }

    public function getMaxRetriesMessage(array $step, string $language): string
    {
        return $language === 'bn'
            ? "দুঃখিত, আমি আপনার অনুরোধটি বুঝতে পারছি না। অনুগ্রহ করে পরে আবার চেষ্টা করুন."
            : "Sorry, I'm having trouble understanding your request. Please try again later.";
    }

    /**
     * Format product list
     */
    public function formatProductList($products, string $language): string
    {
        $title = $language === 'bn' ? "📦 উপলব্ধ পণ্যসমূহ:" : "📦 Available Products:";
        $message = $title . "\n\n";

        foreach ($products as $index => $product) {
            $price = $product->sale_price ?: $product->price;
            $message .= ($index + 1) . ". {$product->name} - ৳" . number_format($price, 0) . "\n";
        }

        return $message;
    }

    /**
     * Get product catalog message
     */
    public function getProductCatalogMessage(array $step, string $language, int $clientId, ?int $facebookPageId = null): string
    {
        $labels = $step['labels'][$language] ?? $step['labels']['en'] ?? [];
        $title = $labels['title'] ?? ($language === 'bn' ? 'আমাদের পণ্যসমূহ' : 'Our Products');
        $description = $labels['description'] ?? '';
        
        $message = "📝 **{$title}**\n\n";
        if ($description) {
            $message .= $description . "\n\n";
        }
        
        // Get products and format them
        $productService = app(ProductSelectorService::class);
        if ($facebookPageId) {
            $products = $productService->getProductsForFacebookPage($facebookPageId);
        } else {
            $products = $productService->getClientProducts($clientId);
        }
        $message .= $this->formatProductList($products, $language);
        
        // Add format help
        $formatHelp = $labels['format_help'] ?? '';
        if ($formatHelp) {
            $message .= "\n\n💡 " . $formatHelp;
        }
        
        return $message;
    }

    /**
     * Get product selected success message
     */
    public function getProductSelectedSuccess($product, array $step, string $language): string
    {
        $price = $product->sale_price ?: $product->price;
        
        return $language === 'bn'
            ? "✅ নির্বাচিত: **{$product->name}** - ৳" . number_format($price, 0)
            : "✅ Selected: **{$product->name}** - ৳" . number_format($price, 0);
    }

    /**
     * Get product selection from catalog error message
     */
    public function getProductSelectionFromCatalogError(string $input, $suggestions, array $step, string $language): string
    {
        $labels = $step['labels'][$language] ?? $step['labels']['en'] ?? [];
        
        $message = $labels['error_not_found'] ?? ($language === 'bn'
            ? "❌ '{$input}' পণ্যটি খুঁজে পাওয়া যায়নি."
            : "❌ Product '{$input}' not found.");
        
        if ($suggestions && $suggestions->isNotEmpty()) {
            $message .= "\n\n" . ($language === 'bn' ? 'আপনি কি এগুলোর কোনটা খুঁজছেন?' : 'Did you mean one of these?') . "\n";
            foreach ($suggestions as $index => $suggestion) {
                $message .= ($index + 1) . ". {$suggestion->name}\n";
            }
        }
        
        $message .= "\n\n" . ($labels['retry_message'] ?? ($language === 'bn'
            ? 'অনুগ্রহ করে উপরের তালিকা থেকে সঠিক পণ্যের নাম বা নম্বর টাইপ করুন.'
            : 'Please type the correct product name or number from the list above.'));
        
        return $message;
    }

    /**
     * Get confirmation message with all details
     */
    public function getConfirmationMessage(array $step, string $language, \App\Models\ConversationState $conversation): string
    {
        $labels = $step['labels'][$language] ?? $step['labels']['en'] ?? [];
        $title = $labels['title'] ?? 'Confirm Your Order';
        $description = $labels['description'] ?? '';

        // Get all data from conversation
        $allResponses = $conversation->getAllResponses();
        $workflowEngine = app(\App\Services\WorkflowEngine::class);

        // Extract data using WorkflowEngine's public helpers
        $customerInfo = $workflowEngine->extractCustomerInfo($allResponses);
        $productSelections = $workflowEngine->extractProductSelections($allResponses);
        $deliveryInfo = $workflowEngine->extractDeliveryInfo($allResponses);
        $subtotal = $workflowEngine->calculateTotal($productSelections);
        
        // Calculate shipping charge
        $shippingCharge = $this->calculateShippingCharge($deliveryInfo);
        $totalAmount = $subtotal + $shippingCharge;

        // Format product summary
        $productSummary = '';
        if (empty($productSelections)) {
            $productSummary = 'No products selected.';
        } else {
            foreach ($productSelections as $product) {
                $productSummary .= "- {$product['name']} x{$product['quantity']} - ৳" . number_format($product['total'], 0) . "\n";
            }
        }

        // Format customer info
        $customerInfoSummary = "Name: {$customerInfo['name']}\nPhone: {$customerInfo['phone']}";
        if (!empty($customerInfo['email'])) {
            $customerInfoSummary .= "\nEmail: {$customerInfo['email']}";
        }

        // Format delivery info
        $deliveryInfoSummary = "Address: {$deliveryInfo['address']}";
        if (!empty($deliveryInfo['delivery_area'])) {
            $deliveryInfoSummary .= "\nArea: {$deliveryInfo['delivery_area']}";
        }
        if (!empty($deliveryInfo['notes'])) {
            $deliveryInfoSummary .= "\nNotes: {$deliveryInfo['notes']}";
        }

        // Format payment summary with breakdown
        $paymentSummary = "Subtotal: ৳" . number_format($subtotal, 0) . "\n";
        $paymentSummary .= "Shipping: ৳" . number_format($shippingCharge, 0) . "\n";
        $paymentSummary .= "**Total: ৳" . number_format($totalAmount, 0) . "**\n";
        $paymentSummary .= "Method: Cash on Delivery";

        // Replace placeholders
        $description = str_replace('{product_summary}', trim($productSummary), $description);
        $description = str_replace('{customer_info}', trim($customerInfoSummary), $description);
        $description = str_replace('{delivery_info}', trim($deliveryInfoSummary), $description);
        $description = str_replace('{payment_summary}', trim($paymentSummary), $description);

        $message = "📝 **{$title}**\n\n" . $description;

        $instructions = $this->getStepInstructions($step, $language);
        if ($instructions) {
            $message .= "\n\n" . $instructions;
        }

        return $message;
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
}
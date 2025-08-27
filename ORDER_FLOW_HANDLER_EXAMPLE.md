# Order Flow Handler Example

This shows how to handle the pure Messenger order flow in your webhook controller.

## Enhanced Webhook Handler

Add this to your `FacebookWebhookController.php`:

```php
/**
 * Handle postback (button clicks) with order flow
 */
protected function handlePostback(Customer $customer, array $postback): void
{
    $payload = $postback['payload'] ?? '';
    $title = $postback['title'] ?? '';
    
    // Get Facebook page for sending responses
    $facebookPage = FacebookPage::where('client_id', $customer->client_id)
        ->where('is_connected', true)
        ->first();
    
    if (!$facebookPage) {
        Log::error('No connected Facebook page found for customer', [
            'customer_id' => $customer->id
        ]);
        return;
    }
    
    $facebookService = app(FacebookGraphAPIService::class);
    $pageToken = $facebookPage->access_token;
    $userId = $customer->facebook_user_id;
    
    // Store the postback as a message
    CustomerMessage::create([
        'customer_id' => $customer->id,
        'client_id' => $customer->client_id,
        'message_type' => 'incoming',
        'message_content' => $title,
        'message_data' => [
            'type' => 'postback',
            'payload' => $payload,
            'timestamp' => now()->timestamp
        ],
        'is_read' => false
    ]);

    // Handle different postback types
    switch (true) {
        // Main menu options
        case $payload === 'BROWSE_PRODUCTS':
            $this->handleBrowseProducts($facebookService, $pageToken, $userId, $customer);
            break;
            
        case $payload === 'TRACK_ORDERS':
            $this->handleTrackOrders($facebookService, $pageToken, $userId, $customer);
            break;
            
        case $payload === 'CUSTOMER_SUPPORT':
            $this->handleCustomerSupport($facebookService, $pageToken, $userId, $customer);
            break;

        // Product ordering
        case str_starts_with($payload, 'ORDER_'):
            $productId = str_replace('ORDER_', '', $payload);
            $this->startOrderFlow($facebookService, $pageToken, $userId, $customer, $productId);
            break;

        // Quantity selection
        case str_starts_with($payload, 'QTY_'):
            $this->handleQuantitySelection($facebookService, $pageToken, $userId, $customer, $payload);
            break;

        // Location selection
        case str_starts_with($payload, 'CITY_'):
            $this->handleCitySelection($facebookService, $pageToken, $userId, $customer, $payload);
            break;

        case str_starts_with($payload, 'AREA_'):
            $this->handleAreaSelection($facebookService, $pageToken, $userId, $customer, $payload);
            break;

        // Payment method
        case str_starts_with($payload, 'PAYMENT_'):
            $this->handlePaymentSelection($facebookService, $pageToken, $userId, $customer, $payload);
            break;

        // Order confirmation
        case str_starts_with($payload, 'CONFIRM_ORDER_'):
            $tempOrderId = str_replace('CONFIRM_ORDER_', '', $payload);
            $this->confirmOrder($facebookService, $pageToken, $userId, $customer, $tempOrderId);
            break;

        // Order tracking
        case str_starts_with($payload, 'TRACK_ORDER_'):
            $orderNumber = str_replace('TRACK_ORDER_', '', $payload);
            $this->showOrderTracking($facebookService, $pageToken, $userId, $customer, $orderNumber);
            break;

        default:
            // Unknown postback - send main menu
            $facebookService->sendMainMenu($pageToken, $userId);
    }

    Log::info('Postback processed', [
        'customer_id' => $customer->id,
        'payload' => $payload,
        'title' => $title
    ]);
}

/**
 * Handle browse products
 */
protected function handleBrowseProducts(FacebookGraphAPIService $facebookService, string $pageToken, string $userId, Customer $customer): void
{
    // Get products from database or hardcoded for demo
    $products = [
        [
            'id' => 'IP15PRO',
            'name' => 'iPhone 15 Pro',
            'description' => '128GB, Natural Titanium - ৳145,000',
            'image_url' => 'https://your-cdn.com/iphone15pro.jpg'
        ],
        [
            'id' => 'IP15',
            'name' => 'iPhone 15',
            'description' => '128GB, Blue - ৳125,000',
            'image_url' => 'https://your-cdn.com/iphone15.jpg'
        ],
        [
            'id' => 'S24',
            'name' => 'Samsung Galaxy S24',
            'description' => '256GB, Titanium - ৳135,000',
            'image_url' => 'https://your-cdn.com/galaxy-s24.jpg'
        ]
    ];

    $facebookService->sendProductCarousel($pageToken, $userId, $products);
}

/**
 * Start order flow for a product
 */
protected function startOrderFlow(FacebookGraphAPIService $facebookService, string $pageToken, string $userId, Customer $customer, string $productId): void
{
    // Create temporary order session
    $tempOrderData = [
        'customer_id' => $customer->id,
        'product_id' => $productId,
        'step' => 'quantity',
        'data' => [],
        'created_at' => now()
    ];
    
    // Store in customer's custom fields temporarily
    $customFields = $customer->custom_fields ?? [];
    $customFields['temp_order'] = $tempOrderData;
    $customer->update(['custom_fields' => $customFields]);

    // Start quantity collection
    $facebookService->startOrderCollection($pageToken, $userId, $productId);
}

/**
 * Handle quantity selection
 */
protected function handleQuantitySelection(FacebookGraphAPIService $facebookService, string $pageToken, string $userId, Customer $customer, string $payload): void
{
    // Parse payload: QTY_1_IP15PRO
    $parts = explode('_', $payload);
    $quantity = $parts[1];
    $productId = $parts[2] ?? '';

    // Update temp order
    $customFields = $customer->custom_fields ?? [];
    $tempOrder = $customFields['temp_order'] ?? [];
    $tempOrder['data']['quantity'] = $quantity;
    $tempOrder['data']['product_id'] = $productId;
    $tempOrder['step'] = 'location';
    $customFields['temp_order'] = $tempOrder;
    $customer->update(['custom_fields' => $customFields]);

    if ($quantity === 'OTHER') {
        $facebookService->sendTextMessage($pageToken, $userId, 
            "Please type the quantity you want (example: 5)");
        return;
    }

    // Proceed to location collection
    $facebookService->collectDeliveryLocation($pageToken, $userId);
}

/**
 * Handle city selection
 */
protected function handleCitySelection(FacebookGraphAPIService $facebookService, string $pageToken, string $userId, Customer $customer, string $payload): void
{
    $city = str_replace('CITY_', '', $payload);
    
    // Update temp order
    $customFields = $customer->custom_fields ?? [];
    $tempOrder = $customFields['temp_order'] ?? [];
    $tempOrder['data']['city'] = $city;
    $customFields['temp_order'] = $tempOrder;
    $customer->update(['custom_fields' => $customFields]);

    if ($city === 'DHAKA') {
        // Ask for specific area in Dhaka
        $facebookService->sendQuickReply($pageToken, $userId,
            "Which area in Dhaka?",
            [
                ['content_type' => 'text', 'title' => 'Dhanmondi', 'payload' => 'AREA_DHANMONDI'],
                ['content_type' => 'text', 'title' => 'Gulshan', 'payload' => 'AREA_GULSHAN'],
                ['content_type' => 'text', 'title' => 'Uttara', 'payload' => 'AREA_UTTARA'],
                ['content_type' => 'text', 'title' => 'Other Area', 'payload' => 'AREA_OTHER']
            ]
        );
    } else {
        // For other cities, ask for complete address
        $facebookService->sendTextMessage($pageToken, $userId,
            "Please send your complete delivery address:\n\nExample: House 123, Main Road, Your Area, {$city}"
        );
        
        // Update step
        $tempOrder['step'] = 'address';
        $customFields['temp_order'] = $tempOrder;
        $customer->update(['custom_fields' => $customFields]);
    }
}

/**
 * Handle area selection (for Dhaka)
 */
protected function handleAreaSelection(FacebookGraphAPIService $facebookService, string $pageToken, string $userId, Customer $customer, string $payload): void
{
    $area = str_replace('AREA_', '', $payload);
    
    // Update temp order
    $customFields = $customer->custom_fields ?? [];
    $tempOrder = $customFields['temp_order'] ?? [];
    $tempOrder['data']['area'] = $area;
    $tempOrder['step'] = 'address';
    $customFields['temp_order'] = $tempOrder;
    $customer->update(['custom_fields' => $customFields]);

    if ($area === 'OTHER') {
        $facebookService->sendTextMessage($pageToken, $userId,
            "Please send your complete delivery address in Dhaka"
        );
    } else {
        $facebookService->sendTextMessage($pageToken, $userId,
            "Please send your complete address in {$area}:\n\nExample: House 123, Road 5, {$area}, Dhaka"
        );
    }
}

/**
 * Handle payment method selection
 */
protected function handlePaymentSelection(FacebookGraphAPIService $facebookService, string $pageToken, string $userId, Customer $customer, string $payload): void
{
    $paymentMethod = str_replace('PAYMENT_', '', $payload);
    
    // Update temp order
    $customFields = $customer->custom_fields ?? [];
    $tempOrder = $customFields['temp_order'] ?? [];
    $tempOrder['data']['payment_method'] = $paymentMethod;
    $tempOrder['step'] = 'confirmation';
    $customFields['temp_order'] = $tempOrder;
    $customer->update(['custom_fields' => $customFields]);

    // Show order summary for confirmation
    $this->showOrderConfirmation($facebookService, $pageToken, $userId, $customer, $tempOrder);
}

/**
 * Show order confirmation
 */
protected function showOrderConfirmation(FacebookGraphAPIService $facebookService, string $pageToken, string $userId, Customer $customer, array $tempOrder): void
{
    $data = $tempOrder['data'];
    
    // Get product details (from database or hardcoded)
    $productDetails = $this->getProductDetails($data['product_id']);
    $quantity = (int) $data['quantity'];
    $subtotal = $productDetails['price'] * $quantity;
    $deliveryCharge = ($data['city'] === 'DHAKA') ? 60 : 100;
    $total = $subtotal + $deliveryCharge;

    $orderSummary = [
        'temp_order_id' => uniqid('TEMP_'),
        'product_name' => $productDetails['name'],
        'quantity' => $quantity,
        'total_price' => $total,
        'delivery_address' => $data['full_address'] ?? $data['city'],
        'payment_method' => $this->formatPaymentMethod($data['payment_method'])
    ];

    $facebookService->confirmOrderDetails($pageToken, $userId, $orderSummary);
}

/**
 * Confirm and create actual order
 */
protected function confirmOrder(FacebookGraphAPIService $facebookService, string $pageToken, string $userId, Customer $customer, string $tempOrderId): void
{
    $customFields = $customer->custom_fields ?? [];
    $tempOrder = $customFields['temp_order'] ?? [];
    $data = $tempOrder['data'];

    // Create actual order in database
    $productDetails = $this->getProductDetails($data['product_id']);
    $quantity = (int) $data['quantity'];
    $subtotal = $productDetails['price'] * $quantity;
    $deliveryCharge = ($data['city'] === 'DHAKA') ? 60 : 100;
    $total = $subtotal + $deliveryCharge;

    $order = Order::create([
        'client_id' => $customer->client_id,
        'customer_id' => $customer->id,
        'order_number' => 'ORD-' . date('Y') . '-' . str_pad(Order::count() + 1, 6, '0', STR_PAD_LEFT),
        'product_name' => $productDetails['name'],
        'quantity' => $quantity,
        'unit_price' => $productDetails['price'],
        'total_amount' => $total,
        'customer_info' => [
            'name' => $customer->name,
            'phone' => $customer->phone ?? 'Not provided',
            'address' => $data['full_address'] ?? $data['city']
        ],
        'delivery_info' => [
            'city' => $data['city'],
            'area' => $data['area'] ?? null,
            'charge' => $deliveryCharge
        ],
        'payment_method' => strtolower(str_replace('_', ' ', $data['payment_method'])),
        'status' => 'pending'
    ]);

    // Clear temp order data
    unset($customFields['temp_order']);
    $customer->update(['custom_fields' => $customFields]);

    // Send receipt
    $facebookService->sendOrderReceipt($pageToken, $userId, [
        'customer_name' => $customer->name,
        'order_number' => $order->order_number,
        'currency' => 'BDT',
        'payment_method' => $this->formatPaymentMethod($data['payment_method']),
        'subtotal' => $subtotal,
        'shipping_cost' => $deliveryCharge,
        'total' => $total,
        'items' => [
            [
                'title' => $productDetails['name'],
                'subtitle' => $productDetails['description'] ?? '',
                'quantity' => $quantity,
                'price' => $productDetails['price']
            ]
        ]
    ]);

    // Send order management options
    $facebookService->sendOrderActions($pageToken, $userId, $order->order_number);
    
    Log::info('Order created successfully', [
        'order_id' => $order->id,
        'customer_id' => $customer->id,
        'total' => $total
    ]);
}

/**
 * Get product details (replace with your database logic)
 */
protected function getProductDetails(string $productId): array
{
    $products = [
        'IP15PRO' => [
            'name' => 'iPhone 15 Pro',
            'description' => '128GB, Natural Titanium',
            'price' => 145000
        ],
        'IP15' => [
            'name' => 'iPhone 15',
            'description' => '128GB, Blue',
            'price' => 125000
        ],
        'S24' => [
            'name' => 'Samsung Galaxy S24',
            'description' => '256GB, Titanium',
            'price' => 135000
        ]
    ];

    return $products[$productId] ?? [
        'name' => 'Unknown Product',
        'description' => 'Product not found',
        'price' => 0
    ];
}

/**
 * Format payment method for display
 */
protected function formatPaymentMethod(string $method): string
{
    return match($method) {
        'COD' => 'Cash on Delivery',
        'MOBILE' => 'Mobile Banking',
        'BANK' => 'Bank Transfer',
        default => ucfirst(strtolower($method))
    };
}
```

## Enhanced Message Handler for Address Collection

Also add this to handle text messages during address collection:

```php
/**
 * Enhanced message handling for order flow
 */
protected function handleIncomingMessage(Customer $customer, array $message): void
{
    $messageText = $message['text'] ?? '';
    $attachments = $message['attachments'] ?? [];

    // Check if customer is in order flow
    $customFields = $customer->custom_fields ?? [];
    $tempOrder = $customFields['temp_order'] ?? null;

    if ($tempOrder && $tempOrder['step'] === 'address' && !empty($messageText)) {
        $this->handleAddressInput($customer, $messageText);
        return;
    }

    if ($tempOrder && $tempOrder['step'] === 'quantity' && is_numeric($messageText)) {
        $this->handleQuantityInput($customer, (int) $messageText);
        return;
    }

    // Store the message (existing logic)
    $customerMessage = CustomerMessage::create([
        'customer_id' => $customer->id,
        'client_id' => $customer->client_id,
        'message_type' => 'incoming',
        'message_content' => $messageText,
        'attachments' => $attachments,
        'message_data' => [
            'facebook_message_id' => $message['mid'] ?? null,
            'timestamp' => $message['timestamp'] ?? now()->timestamp
        ],
        'is_read' => false
    ]);

    // Extract contact info (existing logic)
    $this->extractContactInfo($customer, $messageText);

    // If not in order flow, handle as regular message
    $this->handleRegularMessage($customer, $messageText);
}

/**
 * Handle address input during order flow
 */
protected function handleAddressInput(Customer $customer, string $address): void
{
    $customFields = $customer->custom_fields ?? [];
    $tempOrder = $customFields['temp_order'] ?? [];
    
    // Save the full address
    $tempOrder['data']['full_address'] = $address;
    $tempOrder['step'] = 'payment';
    $customFields['temp_order'] = $tempOrder;
    $customer->update(['custom_fields' => $customFields]);

    // Also extract and save address to customer profile if not already saved
    if (empty($customer->address)) {
        $customer->update(['address' => $address]);
    }

    // Get Facebook page for response
    $facebookPage = FacebookPage::where('client_id', $customer->client_id)
        ->where('is_connected', true)
        ->first();
    
    if ($facebookPage) {
        $facebookService = app(FacebookGraphAPIService::class);
        
        // Calculate total for payment selection
        $productDetails = $this->getProductDetails($tempOrder['data']['product_id']);
        $quantity = (int) $tempOrder['data']['quantity'];
        $subtotal = $productDetails['price'] * $quantity;
        $deliveryCharge = ($tempOrder['data']['city'] === 'DHAKA') ? 60 : 100;
        $total = $subtotal + $deliveryCharge;
        
        $facebookService->collectPaymentMethod(
            $facebookPage->access_token, 
            $customer->facebook_user_id, 
            $total
        );
    }
}

/**
 * Handle quantity input when user types custom quantity
 */
protected function handleQuantityInput(Customer $customer, int $quantity): void
{
    if ($quantity < 1 || $quantity > 100) {
        // Send error message
        $facebookPage = FacebookPage::where('client_id', $customer->client_id)
            ->where('is_connected', true)
            ->first();
        
        if ($facebookPage) {
            $facebookService = app(FacebookGraphAPIService::class);
            $facebookService->sendTextMessage(
                $facebookPage->access_token,
                $customer->facebook_user_id,
                "Please enter a valid quantity between 1 and 100."
            );
        }
        return;
    }

    // Update temp order with custom quantity
    $customFields = $customer->custom_fields ?? [];
    $tempOrder = $customFields['temp_order'] ?? [];
    $tempOrder['data']['quantity'] = $quantity;
    $tempOrder['step'] = 'location';
    $customFields['temp_order'] = $tempOrder;
    $customer->update(['custom_fields' => $customFields]);

    // Proceed to location collection
    $facebookPage = FacebookPage::where('client_id', $customer->client_id)
        ->where('is_connected', true)
        ->first();
    
    if ($facebookPage) {
        $facebookService = app(FacebookGraphAPIService::class);
        $facebookService->collectDeliveryLocation(
            $facebookPage->access_token,
            $customer->facebook_user_id
        );
    }
}
```

This complete handler manages the entire pure-Messenger order flow without any external redirects! 🚀
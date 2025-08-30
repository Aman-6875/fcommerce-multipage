<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderMeta;
use App\Models\Product;
use App\Models\Client;
use App\Models\Customer;
use App\Models\FacebookPage;
use App\Models\PageCustomer;
use App\Services\CustomerService;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrdersExport;

class OrderService
{
    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function createOrder(Client $client, array $data): Order
    {
        DB::beginTransaction();
        
        try {
            // Handle customer creation if needed
            $customerId = $this->handleCustomer($client, $data);
            
            // Handle page customer relationship for Facebook orders
            $pageCustomerId = $this->handlePageCustomer($client, $data, $customerId);
            
            // Calculate totals
            $subtotal = 0;
            $products = collect($data['products']);
            
            foreach ($products as $productData) {
                $product = Product::findOrFail($productData['product_id']);
                $unitPrice = $product->sale_price ?: $product->price;
                $quantity = $productData['quantity'];
                $itemDiscount = $productData['discount_amount'] ?? 0;
                
                $itemTotal = ($unitPrice * $quantity) - $itemDiscount;
                $subtotal += $itemTotal;
            }
            
            // Apply order-level discount
            $orderDiscount = $this->calculateOrderDiscount($subtotal, $data['discount_amount'] ?? 0, $data['discount_type'] ?? 'fixed');
            $finalSubtotal = $subtotal - $orderDiscount;
            
            $shippingCharge = $data['shipping_charge'] ?? 0;
            $totalAmount = $finalSubtotal + $shippingCharge;
            $advancePayment = $data['advance_payment'] ?? 0;
            
            // Get Facebook page ID if provided
            $facebookPageId = null;
            if (!empty($data['facebook_page_id']) && $data['facebook_page_id'] !== '') {
                $facebookPage = FacebookPage::where('id', $data['facebook_page_id'])
                    ->where('client_id', $client->id)
                    ->first();
                $facebookPageId = $facebookPage ? $facebookPage->id : null;
            }
              
            // Create order
            $order = Order::create([
                'client_id' => $client->id,
                'customer_id' => $customerId,
                'page_customer_id' => $pageCustomerId,
                'facebook_page_id' => $facebookPageId,
                'order_number' => Order::generateOrderNumber($client->id),
                'subtotal' => $finalSubtotal,
                'shipping_charge' => $shippingCharge,
                'total_amount' => $totalAmount,
                'advance_payment' => $advancePayment,
                'discount_amount' => $orderDiscount,
                'discount_type' => $data['discount_type'] ?? 'fixed',
                'customer_info' => $data['customer_info'],
                'delivery_info' => $data['delivery_info'] ?? null,
                'status' => 'pending',
                'payment_method' => $data['payment_method'],
                'notes' => $data['notes'] ?? null,
            ]);
            
            // Create order meta for each product
            foreach ($products as $productData) {
                $product = Product::findOrFail($productData['product_id']);
                $unitPrice = $product->sale_price ?: $product->price;
                $quantity = $productData['quantity'];
                $itemDiscount = $productData['discount_amount'] ?? 0;
                $itemTotal = ($unitPrice * $quantity) - $itemDiscount;
                
                OrderMeta::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $itemDiscount,
                    'total_price' => $itemTotal,
                    'product_snapshot' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'price' => $product->price,
                        'sale_price' => $product->sale_price,
                        'category' => $product->category,
                        'image_url' => $product->image_url,
                        'description' => $product->description,
                    ]
                ]);
            }
            
            DB::commit();
            return $order->load('orderMeta.product');
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    public function updateOrder(Order $order, array $data): Order
    {
        DB::beginTransaction();
        
        try {
            // Handle customer creation if needed
            $customerId = $this->handleCustomer($order->client, $data);
            
            // Handle page customer relationship for Facebook orders
            $pageCustomerId = $this->handlePageCustomer($order->client, $data, $customerId);
            
            // Delete existing order meta
            $order->orderMeta()->delete();
            
            // Calculate new totals
            $subtotal = 0;
            $products = collect($data['products']);
            
            foreach ($products as $productData) {
                $product = Product::findOrFail($productData['product_id']);
                $unitPrice = $product->sale_price ?: $product->price;
                $quantity = $productData['quantity'];
                $itemDiscount = $productData['discount_amount'] ?? 0;
                
                $itemTotal = ($unitPrice * $quantity) - $itemDiscount;
                $subtotal += $itemTotal;
            }
            
            // Apply order-level discount
            $orderDiscount = $this->calculateOrderDiscount($subtotal, $data['discount_amount'] ?? 0, $data['discount_type'] ?? 'fixed');
            $finalSubtotal = $subtotal - $orderDiscount;
            
            $shippingCharge = $data['shipping_charge'] ?? 0;
            $totalAmount = $finalSubtotal + $shippingCharge;
            $advancePayment = $data['advance_payment'] ?? 0;
            
            // Get Facebook page ID if provided
            $facebookPageId = null;
            if (!empty($data['facebook_page_id'])) {
                $facebookPage = FacebookPage::where('page_id', $data['facebook_page_id'])
                    ->where('client_id', $order->client_id)
                    ->first();
                $facebookPageId = $facebookPage ? $facebookPage->id : null;
            }
            
            // Update order
            $order->update([
                'customer_id' => $customerId,
                'page_customer_id' => $pageCustomerId,
                'facebook_page_id' => $facebookPageId,
                'subtotal' => $finalSubtotal,
                'shipping_charge' => $shippingCharge,
                'total_amount' => $totalAmount,
                'advance_payment' => $advancePayment,
                'discount_amount' => $orderDiscount,
                'discount_type' => $data['discount_type'] ?? 'fixed',
                'customer_info' => $data['customer_info'],
                'delivery_info' => $data['delivery_info'] ?? null,
                'payment_method' => $data['payment_method'],
                'notes' => $data['notes'] ?? null,
            ]);
            
            // Create new order meta for each product
            foreach ($products as $productData) {
                $product = Product::findOrFail($productData['product_id']);
                $unitPrice = $product->sale_price ?: $product->price;
                $quantity = $productData['quantity'];
                $itemDiscount = $productData['discount_amount'] ?? 0;
                $itemTotal = ($unitPrice * $quantity) - $itemDiscount;
                
                OrderMeta::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $itemDiscount,
                    'total_price' => $itemTotal,
                    'product_snapshot' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'price' => $product->price,
                        'sale_price' => $product->sale_price,
                        'category' => $product->category,
                        'image_url' => $product->image_url,
                        'description' => $product->description,
                    ]
                ]);
            }
            
            DB::commit();
            return $order->load('orderMeta.product');
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    private function calculateOrderDiscount(float $subtotal, float $discountAmount, string $discountType): float
    {
        if ($discountType === 'percentage') {
            return ($subtotal * $discountAmount) / 100;
        }
        
        return min($discountAmount, $subtotal); // Discount cannot exceed subtotal
    }
    
    public function getOrderAnalytics(Client $client, array $filters = [])
    {
        $query = Order::where('client_id', $client->id);
        
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        
        $totalOrders = (clone $query)->count();
        $totalRevenue = (clone $query)->sum('total_amount');
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        
        $statusBreakdown = (clone $query)
            ->selectRaw('status, COUNT(*) as count, SUM(total_amount) as revenue')
            ->groupBy('status')
            ->get()
            ->keyBy('status');
        
        $topProducts = OrderMeta::whereHas('order', function ($q) use ($client, $filters) {
            $q->where('client_id', $client->id);
            if (!empty($filters['date_from'])) {
                $q->whereDate('created_at', '>=', $filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $q->whereDate('created_at', '<=', $filters['date_to']);
            }
        })
        ->selectRaw('product_id, product_name, SUM(quantity) as total_quantity, SUM(total_price) as total_revenue')
        ->groupBy('product_id', 'product_name')
        ->orderBy('total_quantity', 'desc')
        ->limit(10)
        ->get();
        
        return [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'average_order_value' => $averageOrderValue,
            'status_breakdown' => $statusBreakdown,
            'top_products' => $topProducts,
        ];
    }
    
    private function handleCustomer(Client $client, array $data): int
    {
        // If customer_id is provided and valid, use it and just update the info (no facebook_user_id creation)
        if (!empty($data['customer_id']) && $data['customer_id'] !== '' && $data['customer_id'] !== null) {
            $customer = Customer::where('id', $data['customer_id'])
                ->where('client_id', $client->id)
                ->first();
            
            if ($customer) {
                // If phone number is being changed, check for existing customer with that phone
                $newPhone = $data['customer_info']['phone'] ?? $customer->phone;
                if ($newPhone && $newPhone !== $customer->phone) {
                    // Find existing customer with this phone (without facebook operations)
                    $existingPhoneCustomer = Customer::where('client_id', $client->id)
                        ->where('phone', $newPhone)
                        ->where('id', '!=', $customer->id)
                        ->first();
                    
                    if ($existingPhoneCustomer) {
                        // Merge into the existing phone customer (keep existing customer, update current with new info)
                        $existingPhoneCustomer->update([
                            'name' => $data['customer_info']['name'] ?? $existingPhoneCustomer->name,
                            'email' => $data['customer_info']['email'] ?? $existingPhoneCustomer->email,
                            'address' => $data['customer_info']['address'] ?? $existingPhoneCustomer->address,
                        ]);
                        
                        return $existingPhoneCustomer->id;
                    }
                }
                
                // Just update the existing customer (no facebook_user_id manipulation)
                if (isset($data['customer_info'])) {
                    $customer->update([
                        'name' => $data['customer_info']['name'] ?? $customer->name,
                        'phone' => $data['customer_info']['phone'] ?? $customer->phone,
                        'email' => $data['customer_info']['email'] ?? $customer->email,
                        'address' => $data['customer_info']['address'] ?? $customer->address,
                    ]);
                }
                return $customer->id;
            }
        }
        
        // Create new customer - check for existing customer by phone first (without facebook operations)
        $phone = $data['customer_info']['phone'];
        $name = $data['customer_info']['name'];
        
        // Check if customer with this phone already exists
        $existingCustomer = Customer::where('client_id', $client->id)
            ->where('phone', $phone)
            ->first();
        
        if ($existingCustomer) {
            // Update existing customer info and return
            $existingCustomer->update([
                'name' => $name ?? $existingCustomer->name,
                'email' => $data['customer_info']['email'] ?? $existingCustomer->email,
                'address' => $data['customer_info']['address'] ?? $existingCustomer->address,
            ]);
            
            return $existingCustomer->id;
        }
        
        // Create completely new customer (without facebook_user_id to avoid conflicts)
        $customer = Customer::create([
            'client_id' => $client->id,
            'name' => $name ?: 'Customer',
            'phone' => $phone,
            'email' => $data['customer_info']['email'] ?? null,
            'address' => $data['customer_info']['address'] ?? null,
            'status' => 'active',
            'first_interaction' => now(),
            'last_interaction' => now(),
            'interaction_count' => 1,
        ]);
        
        return $customer->id;
    }

    public function exportOrdersToExcel(Client $client, array $filters = [])
    {
        return Excel::download(new OrdersExport($client, $filters), 'orders-' . now()->format('Y-m-d') . '.xlsx');
    }

    private function handlePageCustomer(Client $client, array $data, int $customerId): ?int
    {
        // Only create page customer relationship if there's a Facebook page involved
        if (empty($data['facebook_page_id'])) {
            return null;
        }

        $facebookPage = FacebookPage::where('page_id', $data['facebook_page_id'])
            ->where('client_id', $client->id)
            ->first();

        if (!$facebookPage) {
            return null;
        }

        $customer = Customer::find($customerId);
        if (!$customer) {
            return null;
        }

        // Find or create page customer relationship
        $pageCustomer = PageCustomer::findOrCreateForPage($facebookPage, $customer);
        
        return $pageCustomer->id;
    }
}
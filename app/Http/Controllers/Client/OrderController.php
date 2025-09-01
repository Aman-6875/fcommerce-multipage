<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Services\OrderService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Vinkla\Hashids\Facades\Hashids;

class OrderController extends Controller
{
    protected OrderService $orderService;
    protected NotificationService $notificationService;

    public function __construct(OrderService $orderService, NotificationService $notificationService)
    {
        $this->orderService = $orderService;
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $client = Auth::guard('client')->user();
        
        $query = Order::where('client_id', $client->id)
            ->with(['customer', 'orderMeta.product']);

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($customerQ) use ($search) {
                      $customerQ->where('name', 'like', "%{$search}%")
                               ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('client.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        // Check authorization manually since we're using client guard (use loose comparison for type safety)
        if (auth('client')->id() != $order->client_id) {
            abort(403, 'This action is unauthorized.');
        }
        
        $order->load(['customer', 'orderMeta.product', 'facebookPage']);
        
        return view('client.orders.show', compact('order'));
    }

    public function create()
    {
        $client = Auth::guard('client')->user();
        $customers = Customer::where('client_id', $client->id)->get();
        $products = Product::where('client_id', $client->id)->where('is_active', true)->get();
        
        return view('client.orders.create', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'facebook_page_id' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.discount_amount' => 'nullable|numeric|min:0',
            'shipping_charge' => 'required|numeric|min:0',
            'advance_payment' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:fixed,percentage',
            'customer_info' => 'required|array',
            'customer_info.name' => 'required|string',
            'customer_info.phone' => 'required|string',
            'customer_info.email' => 'nullable|email',
            'customer_info.address' => 'nullable|string',
            'delivery_info' => 'nullable|array',
            'payment_method' => 'required|in:cod,online,bank_transfer',
            'notes' => 'nullable|string|max:1000',
        ]);

        $client = Auth::guard('client')->user();
        
        try {
            DB::beginTransaction();
            
            $order = $this->orderService->createOrder($client, $validated);
            
            DB::commit();
            
            // Check if request is AJAX
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => __('client.order_created_successfully'),
                    'order_id' => $order->id,
                    'redirect_url' => route('client.orders.show', $order)
                ]);
            }
            
            return redirect()->route('client.orders.show', $order)
                ->with('success', __('client.order_created_successfully'));
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Check if request is AJAX
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('client.failed_to_create_order') . ': ' . $e->getMessage(),
                    'errors' => ['error' => $e->getMessage()]
                ], 500);
            }
            
            return back()->withInput()->withErrors(['error' => __('client.failed_to_create_order') . ': ' . $e->getMessage()]);
        }
    }

    public function edit(Order $order)
    {
        // Check authorization manually since we're using client guard (use loose comparison for type safety)
        if (auth('client')->id() != $order->client_id) {
            abort(403, 'This action is unauthorized.');
        }
        
        if (in_array($order->status, ['shipped', 'delivered', 'cancelled'])) {
            return redirect()->route('client.orders.show', $order)
                ->withErrors(['error' => __('client.cannot_edit_order_status', ['status' => __('common.' . $order->status)])]);
        }
        
        $client = Auth::guard('client')->user();
        $customers = Customer::where('client_id', $client->id)->get();
        $products = Product::where('client_id', $client->id)->where('is_active', true)->get();
        
        $order->load('orderMeta.product');
        
        return view('client.orders.edit', compact('order', 'customers', 'products'));
    }

    public function update(Request $request, Order $order)
    {
        // Check authorization manually since we're using client guard (use loose comparison for type safety)
        if (auth('client')->id() != $order->client_id) {
            abort(403, 'This action is unauthorized.');
        }
        
        if (in_array($order->status, ['shipped', 'delivered', 'cancelled'])) {
            return redirect()->route('client.orders.show', $order)
                ->withErrors(['error' => __('client.cannot_edit_order_status', ['status' => __('common.' . $order->status)])]);
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.discount_amount' => 'nullable|numeric|min:0',
            'shipping_charge' => 'required|numeric|min:0',
            'advance_payment' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:fixed,percentage',
            'customer_info' => 'required|array',
            'customer_info.name' => 'required|string',
            'customer_info.phone' => 'required|string',
            'customer_info.email' => 'nullable|email',
            'customer_info.address' => 'nullable|string',
            'delivery_info' => 'nullable|array',
            'payment_method' => 'required|in:cod,online,bank_transfer',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();
            
            $this->orderService->updateOrder($order, $validated);
            
            DB::commit();
            
            return redirect()->route('client.orders.show', $order)
                ->with('success', __('client.order_updated_successfully'));
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => __('client.failed_to_update_order') . ': ' . $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, Order $order)
    {
        // Check authorization manually since we're using client guard (use loose comparison for type safety)
        if (auth('client')->id() != $order->client_id) {
            abort(403, 'This action is unauthorized.');
        }
        
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])],
            'notes' => 'nullable|string|max:500'
        ]);

        $oldStatus = $order->status;
        
        try {
            DB::beginTransaction();
            
            $order->update([
                'status' => $validated['status'],
                'notes' => $validated['notes'] ? $order->notes . "\n" . $validated['notes'] : $order->notes,
            ]);

            if ($validated['status'] === 'confirmed' && !$order->confirmed_at) {
                $order->update(['confirmed_at' => now()]);
            } elseif ($validated['status'] === 'shipped' && !$order->shipped_at) {
                $order->update(['shipped_at' => now()]);
            } elseif ($validated['status'] === 'delivered' && !$order->delivered_at) {
                $order->update(['delivered_at' => now()]);
            }
            
            DB::commit();
            
            // Send notification to customer about status change
            $this->notificationService->sendOrderStatusUpdate($order, $oldStatus, $validated['status']);
            
            return redirect()->back()->with('success', __('client.order_status_updated_successfully'));
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => __('client.failed_to_update_status') . ': ' . $e->getMessage()]);
        }
    }

    public function destroy(Order $order)
    {
        // Check authorization manually since we're using client guard (use loose comparison for type safety)
        if (auth('client')->id() != $order->client_id) {
            abort(403, 'This action is unauthorized.');
        }
        
        if (!in_array($order->status, ['pending', 'cancelled'])) {
            return redirect()->route('client.orders.index')
                ->withErrors(['error' => __('client.cannot_delete_order_status', ['status' => __('common.' . $order->status)])]);
        }

        try {
            DB::beginTransaction();
            
            $order->orderMeta()->delete();
            $order->delete();
            
            DB::commit();
            
            return redirect()->route('client.orders.index')
                ->with('success', __('client.order_deleted_successfully'));
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => __('client.failed_to_delete_order') . ': ' . $e->getMessage()]);
        }
    }

    public function printInvoice(Request $request, Order $order)
    {
        // Check authorization manually since we're using client guard (use loose comparison for type safety)
        if (auth('client')->id() != $order->client_id) {
            abort(403, 'This action is unauthorized.');
        }
        
        $order->load(['customer', 'orderMeta.product', 'client', 'facebookPage']);
        
        // Get invoice settings from client preferences
        $invoiceSettings = $this->getInvoiceSettings($order->client);
        
        // Determine format
        $format = $request->get('format', 'a4'); // a4, thermal, pdf, print
        $language = $request->get('lang', $invoiceSettings['language'] ?? 'en');
        
        // Set app locale for this request
        app()->setLocale($language);
        
        // Currency symbol
        $currencySymbol = $invoiceSettings['currency_symbol'] ?? '৳';
        
        // Facebook page info
        $facebookPage = $order->facebookPage;
       
        $data = compact('order', 'invoiceSettings', 'format', 'language', 'currencySymbol', 'facebookPage');
        
        if ($format === 'pdf') {
            return $this->generatePDFInvoice($data);
        }
        
        return view('client.orders.invoice', $data);
    }
    
    private function getInvoiceSettings($client): array
    {
        // Default settings - in real implementation, these would come from client settings table
        return [
            'title' => __('client.invoice'),
            'language' => 'en',
            'currency_symbol' => '৳',
            'primary_color' => '#007bff',
            'secondary_color' => '#6c757d',
            'text_color' => '#333333',
            'border_color' => '#dee2e6',
            'font_family' => "'Helvetica Neue', Arial, sans-serif",
            'show_page_info' => true,
            'show_contact_info' => true,
            'show_sku' => true,
            'show_qr_code' => true,
            'business_phone' => $client->phone ?? '',
            'business_email' => $client->email ?? '',
            'business_address' => $client->address ?? '',
            'payment_instructions' => __('client.invoice_payment_instructions'),
            'default_notes' => __('client.invoice_default_notes'),
            'footer_text' => __('client.invoice_footer_text'),
        ];
    }
    
    private function generatePDFInvoice(array $data)
    {
        // This would use a PDF library like dompdf or mpdf
        // For now, just return the HTML view with PDF-friendly styles
        $data['format'] = 'pdf';
        $html = view('client.orders.invoice', $data)->render();
        
        // In real implementation:
        // $pdf = PDF::loadHTML($html);
        // return $pdf->download('invoice-' . $data['order']->order_number . '.pdf');
        
        return response($html)->header('Content-Type', 'text/html');
    }
    
    public function sendInvoiceToCustomer(Order $order)
    {
        // Check authorization manually since we're using client guard (use loose comparison for type safety)
        if (auth('client')->id() != $order->client_id) {
            return response()->json(['success' => false, 'message' => 'This action is unauthorized.'], 403);
        }
        
        try {
            // Generate invoice URL
            $hash = Hashids::encode($order->id);
            $invoiceUrl = route('public.invoice', ['hash' => $hash]);
            
            // Send through NotificationService
            $message = $this->buildInvoiceMessage($order, $invoiceUrl);
            $sent = $this->notificationService->sendInvoiceToCustomer($order, $message, $invoiceUrl);
            
            if ($sent) {
                return response()->json(['success' => true, 'message' => __('client.invoice_sent_successfully')]);
            } else {
                return response()->json(['success' => false, 'message' => __('client.failed_to_send_invoice')], 422);
            }
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    private function buildInvoiceMessage(Order $order, string $invoiceUrl): string
    {
        $customerName = $order->customer_info['name'] ?? __('client.valued_customer');
        $orderNumber = $order->order_number;
        $total = '৳' . number_format($order->total_amount, 2);
        
        return __('client.invoice_message_template', [
            'order_number' => $orderNumber,
            'customer_name' => $customerName,
            'total' => $total,
            'status' => __('common.' . $order->status),
            'invoice_url' => $invoiceUrl
        ]);
    }

    public function exportOrders(Request $request)
    {
        $client = Auth::guard('client')->user();
        
        return $this->orderService->exportOrdersToExcel($client, $request->all());
    }

    public function searchCustomers(Request $request)
    {
        $client = Auth::guard('client')->user();
        $term = $request->get('term', '');
        $pageId = $request->get('page_id'); // This is the Facebook Page ID (string)

        $query = Customer::where('client_id', $client->id);

        // Filter by Facebook page if provided
        if ($pageId) {
            // Find the FacebookPage record by page_id
            $facebookPage = $client->facebookPages()
                ->where('page_id', $pageId)
                ->where('is_connected', true)
                ->first();
                
            if ($facebookPage) {
                // Only get customers who have interacted with this specific page
                $query->whereHas('pageCustomers', function ($q) use ($facebookPage) {
                    $q->where('facebook_page_id', $facebookPage->id)
                      ->where('status', 'active');
                });
            }
        }

        // Search by name or phone
        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        $customers = $query->select('id', 'name', 'phone', 'email', 'address')
                          ->with(['pageCustomers.facebookPage' => function ($q) use ($pageId) {
                              if ($pageId) {
                                  $q->where('page_id', $pageId);
                              }
                          }])
                          ->limit(10)
                          ->get();

        return response()->json([
            'success' => true,
            'data' => $customers->map(function ($customer) use ($pageId) {
                $pageName = null;
                
                if ($pageId) {
                    $pageCustomer = $customer->pageCustomers->first();
                    $pageName = $pageCustomer?->facebookPage?->page_name;
                } else {
                    // If no specific page, show the first page they're associated with
                    $firstPageCustomer = $customer->pageCustomers->first();
                    $pageName = $firstPageCustomer?->facebookPage?->page_name;
                }
                
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'address' => $customer->address,
                    'page_name' => $pageName,
                    'display_name' => "{$customer->name} - {$customer->phone}"
                ];
            })
        ]);
    }

    /**
     * Update existing customers with information from their orders
     * This method can be called to fix existing data where customers 
     * provided info in orders but Customer records weren't updated
     */
    public function updateCustomersFromOrders(Request $request)
    {
        $client = Auth::guard('client')->user();
        $updatedCount = 0;
        
        // Get all customers who have orders but incomplete profile data
        $customers = Customer::where('client_id', $client->id)
            ->where(function ($q) {
                $q->where('name', 'Facebook User')
                  ->orWhereNull('phone')
                  ->orWhere('phone', '');
            })
            ->whereHas('orders')
            ->with(['orders' => function ($q) {
                $q->whereNotNull('customer_info')
                  ->orderBy('created_at', 'desc');
            }])
            ->get();
        
        foreach ($customers as $customer) {
            $updateData = [];
            
            // Get the most recent order with customer info
            $latestOrder = $customer->orders->first();
            if ($latestOrder && $latestOrder->customer_info) {
                $customerInfo = is_array($latestOrder->customer_info) 
                    ? $latestOrder->customer_info 
                    : json_decode($latestOrder->customer_info, true);
                
                // Update name if it's still default
                if (($customer->name === 'Facebook User' || empty($customer->name)) && !empty($customerInfo['name'])) {
                    $updateData['name'] = $customerInfo['name'];
                }
                
                // Update phone if missing
                if (empty($customer->phone) && !empty($customerInfo['phone'])) {
                    $updateData['phone'] = $customerInfo['phone'];
                }
                
                // Update email if missing
                if (empty($customer->email) && !empty($customerInfo['email'])) {
                    $updateData['email'] = $customerInfo['email'];
                }
                
                // Update address if missing
                if (empty($customer->address) && !empty($customerInfo['address'])) {
                    $updateData['address'] = $customerInfo['address'];
                }
                
                if (!empty($updateData)) {
                    $customer->update($updateData);
                    $updatedCount++;
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => __('client.customer_records_updated_from_orders', ['count' => $updatedCount]),
            'updated_count' => $updatedCount
        ]);
    }

    /**
     * Update existing customers with Facebook profile information
     */
    public function updateCustomersFromFacebook(Request $request)
    {
        $client = Auth::guard('client')->user();
        $updatedCount = 0;
        
        // Get all customers who still have default "Facebook User" name
        $customers = Customer::where('client_id', $client->id)
            ->where('name', 'Facebook User')
            ->whereNotNull('facebook_user_id')
            ->with('client.facebookPages')
            ->get();
        
        $facebookService = app(\App\Services\FacebookGraphAPIService::class);
        
        foreach ($customers as $customer) {
            // Find the Facebook page this customer came from
            $sourcePageId = $customer->profile_data['source_page_id'] ?? null;
            if (!$sourcePageId) {
                continue;
            }
            
            $facebookPage = $customer->client->facebookPages()
                ->where('page_id', $sourcePageId)
                ->where('is_connected', true)
                ->first();
            
            if ($facebookPage && $facebookPage->access_token) {
                $facebookService->updateCustomerWithFacebookProfile($customer, $facebookPage);
                $updatedCount++;
                
                // Small delay to avoid rate limiting
                usleep(100000); // 0.1 second delay
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => __('client.customer_records_updated_from_facebook', ['count' => $updatedCount]),
            'updated_count' => $updatedCount
        ]);
    }

    /**
     * Complete customer data update - combines both order data and Facebook profile
     */
    public function updateAllCustomerData(Request $request)
    {
        $ordersResult = $this->updateCustomersFromOrders($request);
        $facebookResult = $this->updateCustomersFromFacebook($request);
        
        $ordersData = $ordersResult->getData(true);
        $facebookData = $facebookResult->getData(true);
        
        $totalUpdated = $ordersData['updated_count'] + $facebookData['updated_count'];
        
        return response()->json([
            'success' => true,
            'message' => __('client.complete_customer_data_update', ['total' => $totalUpdated]),
            'orders_updated' => $ordersData['updated_count'],
            'facebook_updated' => $facebookData['updated_count'],
            'total_updated' => $totalUpdated
        ]);
    }
}
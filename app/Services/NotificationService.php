<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Customer;
use App\Models\FacebookPage;
use App\Services\FacebookGraphAPIService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    protected FacebookGraphAPIService $facebookService;
    
    public function __construct(FacebookGraphAPIService $facebookService)
    {
        $this->facebookService = $facebookService;
    }
    
    public function sendOrderStatusUpdate(Order $order, string $oldStatus, string $newStatus): bool
    {
        if (!$order->customer || !$order->customer->facebook_user_id) {
            Log::warning('Cannot send notification: Missing customer or Facebook user ID', [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id
            ]);
            return false;
        }
        
        $message = $this->getStatusUpdateMessage($order, $oldStatus, $newStatus);
        
        // Check if we can send a regular message (within 24 hours of last customer interaction)
        if ($this->canSendRegularMessage($order->customer)) {
            return $this->sendRegularMessage($order, $message);
        }
        
        // If we can't send regular message, try alternative approaches
        return $this->sendAlternativeNotification($order, $message);
    }
    
    private function canSendRegularMessage(Customer $customer): bool
    {
        $lastInteraction = $customer->last_interaction;
        
        if (!$lastInteraction) {
            return false;
        }
        
        $hoursSinceLastMessage = Carbon::parse($lastInteraction)->diffInHours(now());
        
        return $hoursSinceLastMessage < 24;
    }
    
    private function sendRegularMessage(Order $order, string $message): bool
    {
        try {
            $facebookPage = $order->facebookPage ?? $order->customer->facebookPage;
            
            if (!$facebookPage || !$facebookPage->access_token) {
                Log::error('Facebook page or access token not found', [
                    'order_id' => $order->id,
                    'facebook_page_id' => $order->facebook_page_id
                ]);
                return false;
            }
            
            $response = $this->facebookService->sendMessage(
                $order->customer->facebook_user_id,
                $message,
                $facebookPage->access_token
            );
            
            if ($response) {
                $this->logNotification($order->id, 'regular_message', 'sent', $message);
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            Log::error('Failed to send regular message', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    private function sendAlternativeNotification(Order $order, string $message): bool
    {
        // Strategy 1: Try sending a message template (if approved by Facebook)
        if ($this->sendMessageTemplate($order, $message)) {
            return true;
        }
        
        // Strategy 2: Send SMS as fallback (if phone number available)
        if ($this->sendSMSNotification($order, $message)) {
            return true;
        }
        
        // Strategy 3: Create a proactive engagement prompt
        if ($this->createEngagementPrompt($order)) {
            return true;
        }
        
        // Strategy 4: Store notification for later delivery
        return $this->storeNotificationForLater($order, $message);
    }
    
    private function sendMessageTemplate(Order $order, string $message): bool
    {
        try {
            $facebookPage = $order->facebookPage ?? $order->customer->facebookPage;
            
            if (!$facebookPage || !$facebookPage->access_token) {
                return false;
            }
            
            // Try to send using a pre-approved message template
            $templateData = $this->getOrderUpdateTemplate($order);
            
            $response = $this->facebookService->sendTemplate(
                $order->customer->facebook_user_id,
                $templateData,
                $facebookPage->access_token
            );
            
            if ($response) {
                $this->logNotification($order->id, 'message_template', 'sent', $message);
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            Log::info('Message template failed, trying next strategy', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    private function sendSMSNotification(Order $order, string $message): bool
    {
        $phoneNumber = $order->customer_info['phone'] ?? $order->customer->phone ?? null;
        
        if (!$phoneNumber) {
            return false;
        }
        
        try {
            // TODO: Implement SMS service (Twilio, etc.)
            // For now, just log that we would send SMS
            Log::info('SMS notification would be sent', [
                'order_id' => $order->id,
                'phone' => $phoneNumber,
                'message' => $message
            ]);
            
            $this->logNotification($order->id, 'sms', 'pending', $message);
            return true;
            
        } catch (\Exception $e) {
            Log::error('SMS notification failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    private function createEngagementPrompt(Order $order): bool
    {
        try {
            // Create an attractive post or story to prompt customer engagement
            $facebookPage = $order->facebookPage ?? $order->customer->facebookPage;
            
            if (!$facebookPage) {
                return false;
            }
            
            $postContent = "📦 We have important updates for recent orders! If you've placed an order recently, please send us a message to get your latest order status. We're here to help! 💬";
            
            // Post to page to encourage customers to message
            $response = $this->facebookService->createPost($postContent, $facebookPage->access_token);
            
            if ($response) {
                $this->logNotification($order->id, 'engagement_prompt', 'posted', $postContent);
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            Log::error('Engagement prompt failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    private function storeNotificationForLater(Order $order, string $message): bool
    {
        try {
            DB::table('pending_notifications')->insert([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'message' => $message,
                'notification_type' => 'order_status_update',
                'status' => 'pending',
                'attempts' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->logNotification($order->id, 'stored_for_later', 'stored', $message);
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to store notification for later', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    private function getStatusUpdateMessage(Order $order, string $oldStatus, string $newStatus): string
    {
        $customerName = $order->customer_info['name'] ?? 'Valued Customer';
        $orderNumber = $order->order_number;
        
        $statusMessages = [
            'confirmed' => "✅ Great news {$customerName}! Your order #{$orderNumber} has been confirmed and is now being prepared.",
            'processing' => "📦 Hi {$customerName}! Your order #{$orderNumber} is now being processed. We'll update you once it's ready for shipping.",
            'shipped' => "🚛 Exciting news {$customerName}! Your order #{$orderNumber} has been shipped and is on its way to you. You'll receive it soon!",
            'delivered' => "🎉 Congratulations {$customerName}! Your order #{$orderNumber} has been delivered. We hope you love your purchase!",
            'cancelled' => "❌ Hi {$customerName}, unfortunately your order #{$orderNumber} has been cancelled. Please contact us if you have any questions."
        ];
        
        return $statusMessages[$newStatus] ?? "📋 Hi {$customerName}! Your order #{$orderNumber} status has been updated to: " . ucfirst($newStatus);
    }
    
    private function getOrderUpdateTemplate(Order $order): array
    {
        return [
            'template_type' => 'generic',
            'elements' => [
                [
                    'title' => 'Order Update - #' . $order->order_number,
                    'subtitle' => 'Your order status: ' . ucfirst($order->status),
                    'buttons' => [
                        [
                            'type' => 'web_url',
                            'url' => route('order.track', ['token' => $order->tracking_token ?? 'temp']),
                            'title' => 'Track Order'
                        ],
                        [
                            'type' => 'postback',
                            'title' => 'Contact Support',
                            'payload' => 'CONTACT_SUPPORT_' . $order->id
                        ]
                    ]
                ]
            ]
        ];
    }
    
    private function logNotification(int $orderId, string $type, string $status, string $message): void
    {
        try {
            DB::table('notification_logs')->insert([
                'order_id' => $orderId,
                'notification_type' => $type,
                'status' => $status,
                'message' => $message,
                'sent_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log notification', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function processPendingNotifications(): void
    {
        $pendingNotifications = DB::table('pending_notifications')
            ->where('status', 'pending')
            ->where('attempts', '<', 5)
            ->get();
            
        foreach ($pendingNotifications as $notification) {
            $customer = Customer::find($notification->customer_id);
            
            if ($customer && $this->canSendRegularMessage($customer)) {
                $order = Order::find($notification->order_id);
                
                if ($order && $this->sendRegularMessage($order, $notification->message)) {
                    DB::table('pending_notifications')
                        ->where('id', $notification->id)
                        ->update([
                            'status' => 'sent',
                            'sent_at' => now(),
                            'updated_at' => now()
                        ]);
                } else {
                    DB::table('pending_notifications')
                        ->where('id', $notification->id)
                        ->increment('attempts');
                }
            }
        }
    }
    
    public function sendInvoiceToCustomer(Order $order, string $message, string $invoiceUrl): bool
    {
        if (!$order->customer || !$order->customer->facebook_user_id) {
            Log::warning('Cannot send invoice: Missing customer or Facebook user ID', [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id
            ]);
            return false;
        }
        
        // Check if we can send a regular message (within 24 hours of last customer interaction)
        if ($this->canSendRegularMessage($order->customer)) {
            return $this->sendInvoiceMessage($order, $message, $invoiceUrl);
        }
        
        // If we can't send regular message, try alternative approaches
        return $this->sendAlternativeInvoiceNotification($order, $message, $invoiceUrl);
    }
    
    private function sendInvoiceMessage(Order $order, string $message, string $invoiceUrl): bool
    {
        try {
            $facebookPage = $order->facebookPage ?? $order->customer->facebookPage;
            
            if (!$facebookPage || !$facebookPage->access_token) {
                Log::error('Facebook page or access token not found for invoice', [
                    'order_id' => $order->id,
                    'facebook_page_id' => $order->facebook_page_id
                ]);
                return false;
            }
            
            // Send message with invoice link as a card/template
            $messageData = [
                'attachment' => [
                    'type' => 'template',
                    'payload' => [
                        'template_type' => 'generic',
                        'elements' => [
                            [
                                'title' => 'Invoice #' . $order->order_number,
                                'subtitle' => 'Total: ৳' . number_format($order->total_amount, 2) . ' | Status: ' . ucfirst($order->status),
                                'image_url' => $order->facebookPage->page_picture ?? '',
                                'buttons' => [
                                    [
                                        'type' => 'web_url',
                                        'url' => $invoiceUrl,
                                        'title' => 'View Invoice',
                                        'webview_height_ratio' => 'tall'
                                    ],
                                    [
                                        'type' => 'web_url',
                                        'url' => $invoiceUrl . '?format=thermal',
                                        'title' => 'Thermal View'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
            
            $response = $this->facebookService->sendTemplateMessage(
                $order->customer->facebook_user_id,
                $messageData,
                $facebookPage->access_token
            );
            
            if ($response) {
                $this->logNotification($order->id, 'invoice_sent', 'sent', $message);
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            Log::error('Failed to send invoice message', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    private function sendAlternativeInvoiceNotification(Order $order, string $message, string $invoiceUrl): bool
    {
        // Strategy 1: Try sending via message template (if approved by Facebook)
        if ($this->sendInvoiceTemplate($order, $invoiceUrl)) {
            return true;
        }
        
        // Strategy 2: Send SMS with invoice link (if phone number available)
        if ($this->sendInvoiceSMS($order, $invoiceUrl)) {
            return true;
        }
        
        // Strategy 3: Create an engagement prompt about invoice
        if ($this->createInvoiceEngagementPrompt($order)) {
            return true;
        }
        
        // Strategy 4: Store invoice notification for later delivery
        return $this->storeInvoiceNotificationForLater($order, $message, $invoiceUrl);
    }
    
    private function sendInvoiceTemplate(Order $order, string $invoiceUrl): bool
    {
        try {
            $facebookPage = $order->facebookPage ?? $order->customer->facebookPage;
            
            if (!$facebookPage || !$facebookPage->access_token) {
                return false;
            }
            
            // Try to send using a pre-approved invoice template
            $templateData = [
                'template_type' => 'receipt',
                'recipient_name' => $order->customer_info['name'] ?? 'Customer',
                'order_number' => $order->order_number,
                'currency' => 'BDT',
                'payment_method' => ucfirst($order->payment_method),
                'order_url' => $invoiceUrl,
                'timestamp' => $order->created_at->timestamp,
                'elements' => $order->orderMeta->map(function ($meta) {
                    return [
                        'title' => $meta->product_name,
                        'subtitle' => $meta->product_sku ?? '',
                        'quantity' => $meta->quantity,
                        'price' => $meta->unit_price,
                        'currency' => 'BDT',
                    ];
                })->toArray(),
                'summary' => [
                    'subtotal' => $order->orderMeta->sum('total_price'),
                    'shipping_cost' => $order->shipping_charge ?? 0,
                    'total_tax' => 0,
                    'total_cost' => $order->total_amount
                ]
            ];
            
            $response = $this->facebookService->sendReceiptTemplate(
                $order->customer->facebook_user_id,
                $templateData,
                $facebookPage->access_token
            );
            
            if ($response) {
                $this->logNotification($order->id, 'invoice_template', 'sent', 'Receipt template sent');
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            Log::info('Invoice template failed, trying next strategy', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    private function sendInvoiceSMS(Order $order, string $invoiceUrl): bool
    {
        $phoneNumber = $order->customer_info['phone'] ?? $order->customer->phone ?? null;
        
        if (!$phoneNumber) {
            return false;
        }
        
        try {
            $message = "📋 Invoice for Order #{$order->order_number}\n" .
                      "Total: ৳" . number_format($order->total_amount, 2) . "\n" .
                      "View: {$invoiceUrl}\n" .
                      "Thank you for your business!";
                      
            // TODO: Implement SMS service (Twilio, etc.)
            Log::info('Invoice SMS would be sent', [
                'order_id' => $order->id,
                'phone' => $phoneNumber,
                'url' => $invoiceUrl
            ]);
            
            $this->logNotification($order->id, 'invoice_sms', 'pending', $message);
            return true;
            
        } catch (\Exception $e) {
            Log::error('Invoice SMS failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    private function createInvoiceEngagementPrompt(Order $order): bool
    {
        try {
            $facebookPage = $order->facebookPage ?? $order->customer->facebookPage;
            
            if (!$facebookPage) {
                return false;
            }
            
            $postContent = "📋 **Invoice Ready!** \n\n" .
                          "We've prepared your invoice for recent orders. " .
                          "Please send us a message to receive your invoice instantly! 💬\n\n" .
                          "#Invoice #OrderReady #CustomerService";
            
            $response = $this->facebookService->createPost($postContent, $facebookPage->access_token);
            
            if ($response) {
                $this->logNotification($order->id, 'invoice_engagement_prompt', 'posted', $postContent);
                return true;
            }
            
            return false;
            
        } catch (\Exception $e) {
            Log::error('Invoice engagement prompt failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    private function storeInvoiceNotificationForLater(Order $order, string $message, string $invoiceUrl): bool
    {
        try {
            DB::table('pending_notifications')->insert([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'message' => $message,
                'notification_type' => 'invoice_delivery',
                'status' => 'pending',
                'attempts' => 0,
                'metadata' => json_encode(['invoice_url' => $invoiceUrl]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $this->logNotification($order->id, 'invoice_stored_for_later', 'stored', $message);
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to store invoice notification for later', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
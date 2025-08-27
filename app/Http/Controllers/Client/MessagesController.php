<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MessagesController extends Controller
{
    public function index($customerId = null)
    {
        $customers = auth('client')->user()->customers()
            ->with(['messages' => function($query) {
                $query->latest()->limit(1);
            }])
            ->withCount(['messages as unread_count' => function($query) {
                $query->where('is_read', false)->where('message_type', 'incoming');
            }])
            ->orderBy('last_interaction', 'desc')
            ->get();
        
        $selectedCustomer = null;
        $messages = collect();
        
        if ($customerId) {
            $selectedCustomer = auth('client')->user()->customers()->find($customerId);
            if ($selectedCustomer) {
                $messages = $selectedCustomer->messages()
                    ->orderBy('created_at', 'asc')
                    ->limit(100)
                    ->get();
                
                // Mark incoming messages as read
                $selectedCustomer->messages()
                    ->where('message_type', 'incoming')
                    ->where('is_read', false)
                    ->update(['is_read' => true]);
            }
        }
        
        return view('client.messages', compact('customers', 'selectedCustomer', 'messages'));
    }

    public function getCustomers(Request $request)
    {
        $customers = auth('client')->user()->customers()
            ->with(['messages' => function($query) {
                $query->latest()->limit(1);
            }])
            ->withCount(['messages as unread_count' => function($query) {
                $query->where('is_read', false)->where('message_type', 'incoming');
            }])
            ->orderBy('last_interaction', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'customers' => $customers->map(function($customer) {
                $lastMessage = $customer->messages->first();
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'profile_picture' => $customer->profile_data['profile_picture'] ?? null,
                    'unread_count' => $customer->unread_count,
                    'last_interaction' => $customer->last_interaction ? $customer->last_interaction->diffForHumans() : null,
                    'last_message' => $lastMessage ? [
                        'id' => $lastMessage->id,
                        'content' => \Str::limit($lastMessage->message_content, 50),
                        'time' => $lastMessage->created_at->format('M j, g:i A'),
                        'type' => $lastMessage->message_type
                    ] : null,
                    'page_name' => $customer->profile_data['page_name'] ?? null,
                    'facebook_page' => $customer->facebook_page ? [
                        'id' => $customer->facebook_page->id,
                        'page_id' => $customer->facebook_page->page_id,
                        'page_name' => $customer->facebook_page->page_name
                    ] : null
                ];
            })
        ]);
    }

    public function getMessages(Request $request, $customerId)
    {
        $customer = auth('client')->user()->customers()->find($customerId);
        
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found'], 404);
        }

        $lastMessageId = $request->get('last_message_id', 0);
        
        // Get messages newer than the last message ID (for auto-update)
        $query = $customer->messages()->orderBy('created_at', 'asc');
        
        if ($lastMessageId > 0) {
            $query->where('id', '>', $lastMessageId);
        } else {
            $query->limit(100);
        }
        
        $messages = $query->get();
        
        // Mark incoming messages as read
        $customer->messages()
            ->where('message_type', 'incoming')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'profile_picture' => $customer->profile_data['profile_picture'] ?? null,
                'page_name' => $customer->profile_data['page_name'] ?? null,
                'facebook_page' => $customer->facebook_page ? [
                    'id' => $customer->facebook_page->id,
                    'page_id' => $customer->facebook_page->page_id,
                    'page_name' => $customer->facebook_page->page_name
                ] : null
            ],
            'messages' => $messages->map(function($message) {
                return [
                    'id' => $message->id,
                    'content' => $message->message_content,
                    'type' => $message->message_type,
                    'time' => $message->created_at->format('M j, g:i A'),
                    'timestamp' => $message->created_at->timestamp,
                    'is_read' => $message->is_read,
                    'delivered' => isset($message->message_data['delivered_at'])
                ];
            }),
            'last_message_id' => $messages->isNotEmpty() ? $messages->last()->id : $lastMessageId
        ]);
    }

    public function sendMessage(Request $request, $customerId)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $customer = auth('client')->user()->customers()->find($customerId);
        
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found'], 404);
        }

        try {
            // Create message record
            $message = CustomerMessage::create([
                'customer_id' => $customer->id,
                'client_id' => auth('client')->id(),
                'message_type' => 'outgoing',
                'message_content' => $request->message,
                'message_data' => [
                    'sent_at' => now()->toISOString(),
                    'platform' => 'facebook',
                    'status' => 'pending'
                ],
                'is_read' => true
            ]);

            // Update customer interaction
            $customer->updateInteraction();

            // Try to send via Facebook API
            $facebookSent = false;
            $facebookError = null;
            
            try {
                if ($customer->facebook_user_id && $customer->client && $customer->client->facebookPages()->first()) {
                    $facebookPage = $customer->client->facebookPages()->where('is_connected', true)->first();
                    
                    if ($facebookPage && $facebookPage->access_token) {
                        // Use Facebook Graph API to send message
                        $response = $this->sendToFacebook($facebookPage->access_token, $customer->facebook_user_id, $request->message);
                        $facebookSent = $response['success'] ?? false;
                        $facebookError = $response['error'] ?? null;
                    }
                }
            } catch (\Exception $e) {
                Log::error('Facebook API error: ' . $e->getMessage());
                $facebookError = $e->getMessage();
            }

            // Update message status based on Facebook API result
            $messageData = [
                'sent_at' => now()->toISOString(),
                'platform' => 'facebook',
                'status' => $facebookSent ? 'sent' : 'failed',
                'facebook_sent' => $facebookSent,
                'facebook_error' => $facebookError
            ];

            if ($facebookSent) {
                $messageData['delivered_at'] = now()->toISOString();
            }

            $message->update([
                'message_data' => array_merge($message->message_data, $messageData)
            ]);

            return response()->json([
                'success' => true,
                'facebook_sent' => $facebookSent,
                'facebook_error' => $facebookError,
                'message' => [
                    'id' => $message->id,
                    'content' => $message->message_content,
                    'type' => $message->message_type,
                    'time' => $message->created_at->format('M j, g:i A'),
                    'timestamp' => $message->created_at->timestamp,
                    'is_read' => true,
                    'delivered' => $facebookSent,
                    'status' => $facebookSent ? 'sent' : 'failed',
                    'facebook_sent' => $facebookSent
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending message: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to send message'], 500);
        }
    }

    public function markAsRead(Request $request, $customerId)
    {
        $customer = auth('client')->user()->customers()->find($customerId);
        
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found'], 404);
        }

        $customer->messages()
            ->where('message_type', 'incoming')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function getUnreadCount(Request $request)
    {
        $totalUnread = CustomerMessage::where('client_id', auth('client')->id())
            ->where('message_type', 'incoming')
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $totalUnread
        ]);
    }

    /**
     * Send message via Facebook Graph API
     */
    private function sendToFacebook($accessToken, $recipientId, $messageText)
    {
        try {
            $url = "https://graph.facebook.com/v" . config('services.facebook.version', '18.0') . "/me/messages";
            
            $data = [
                'recipient' => ['id' => $recipientId],
                'message' => ['text' => $messageText],
                'access_token' => $accessToken
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen(json_encode($data))
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                Log::error('Facebook API cURL error: ' . $error);
                return ['success' => false, 'error' => $error];
            }

            $responseData = json_decode($response, true);

            if ($httpCode === 200 && isset($responseData['message_id'])) {
                Log::info('Facebook message sent successfully', [
                    'message_id' => $responseData['message_id'],
                    'recipient_id' => $recipientId
                ]);
                
                return [
                    'success' => true,
                    'message_id' => $responseData['message_id'],
                    'response' => $responseData
                ];
            } else {
                $errorMsg = $responseData['error']['message'] ?? 'Unknown Facebook API error';
                Log::error('Facebook API error', [
                    'http_code' => $httpCode,
                    'response' => $response,
                    'error' => $errorMsg
                ]);
                
                return ['success' => false, 'error' => $errorMsg];
            }

        } catch (\Exception $e) {
            Log::error('Exception in Facebook API call: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function sendProductCarousel(Request $request, $customerId)
    {
        $request->validate([
            'product_ids' => 'required|array|min:1|max:3',
            'product_ids.*' => 'exists:products,id',
            'facebook_page_id' => 'required|exists:facebook_pages,id'
        ]);

        $client = auth('client')->user();
        $customer = $client->customers()->find($customerId);
        
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found'], 404);
        }

        // Get Facebook page
        $facebookPage = $client->facebookPages()->find($request->facebook_page_id);
        if (!$facebookPage || !$facebookPage->is_connected) {
            return response()->json(['success' => false, 'message' => 'Facebook page not found or not connected'], 404);
        }

        // Get products - ensure they belong to this page
        $products = \App\Models\Product::where('client_id', $client->id)
            ->where('facebook_page_id', $request->facebook_page_id)
            ->whereIn('id', $request->product_ids)
            ->where('is_active', true)
            ->get();

        if ($products->count() !== count($request->product_ids)) {
            return response()->json(['success' => false, 'message' => 'Some products not found or not available'], 404);
        }

        try {
            // Use Facebook Graph API service to send product carousel
            $facebookService = app(\App\Services\FacebookGraphAPIService::class);
            $result = $facebookService->sendProductCarousel(
                $facebookPage->access_token,
                $customer->facebook_user_id,
                $products->toArray()
            );

            if ($result['success']) {
                // Log the sent carousel as a message
                $productNames = $products->pluck('name')->toArray();
                $messageContent = 'Product Carousel: ' . implode(', ', $productNames);
                
                CustomerMessage::create([
                    'customer_id' => $customer->id,
                    'client_id' => $client->id,
                    'message_type' => 'outgoing',
                    'message_content' => $messageContent,
                    'message_data' => [
                        'type' => 'product_carousel',
                        'products' => $products->map(function($product) {
                            return [
                                'id' => $product->id,
                                'name' => $product->name,
                                'price' => $product->effective_price,
                                'image_url' => $product->image_url,
                                'product_link' => $product->product_link
                            ];
                        }),
                        'facebook_message_id' => $result['message_id'] ?? null,
                        'sent_at' => now()->toISOString(),
                        'platform' => 'facebook',
                        'status' => 'sent'
                    ],
                    'is_read' => true
                ]);

                // Update customer interaction
                $customer->updateInteraction();

                return response()->json([
                    'success' => true,
                    'message' => 'Product carousel sent successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send product carousel: ' . ($result['error'] ?? 'Unknown error')
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error sending product carousel', [
                'customer_id' => $customerId,
                'products' => $request->product_ids,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send product carousel'
            ], 500);
        }
    }
}
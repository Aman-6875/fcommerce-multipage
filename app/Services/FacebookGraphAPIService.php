<?php

namespace App\Services;

use App\Models\FacebookPage;
use App\Models\Customer;
use App\Models\CustomerMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookGraphAPIService
{
    protected string $baseUrl = 'https://graph.facebook.com/v18.0';
    
    public function __construct()
    {
        // App credentials are used from config services
    }

    public function getLoginUrl(string $redirectUri): string
    {
        $params = [
            'client_id' => config('services.facebook.app_id'),
            'redirect_uri' => $redirectUri,
            'scope' => 'pages_messaging,pages_read_engagement,read_page_mailboxes,pages_manage_metadata',
            'response_type' => 'code',
            'state' => csrf_token()
        ];
        return "https://www.facebook.com/v18.0/dialog/oauth?" . http_build_query($params);
    }

    public function getAccessToken(string $code, string $redirectUri): ?array
    {
        try {
            $response = Http::get($this->baseUrl . '/oauth/access_token', [
                'client_id' => config('services.facebook.app_id'),
                'client_secret' => config('services.facebook.app_secret'),
                'redirect_uri' => $redirectUri,
                'code' => $code
            ]);

            if ($response->successful()) {
                Log::info('Successfully retrieved access token.');
                return $response->json();
            }

            Log::error('Facebook access token error', ['response_body' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::critical('Exception in getAccessToken', ['message' => $e->getMessage()]);
            return null;
        }
    }

    public function getUserPages(string $accessToken): ?array
    {
        try {
            $response = Http::get($this->baseUrl . '/me/accounts', [
                'access_token' => $accessToken,
                'fields' => 'id,name,category,picture,access_token,tasks'
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Facebook pages error', ['response_body' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::critical('Exception in getUserPages', ['message' => $e->getMessage()]);
            return null;
        }
    }

    public function subscribeToWebhooks(string $pageId, string $pageAccessToken): bool
    {
        try {
            $fields = 'messages,messaging_postbacks,messaging_optins,message_deliveries,message_reads';
            Log::info('Subscribing page to webhook fields', ['page_id' => $pageId, 'fields' => $fields]);

            $response = Http::post($this->baseUrl . "/{$pageId}/subscribed_apps", [
                'access_token' => $pageAccessToken,
                'subscribed_fields' => $fields
            ]);

            if ($response->successful() && $response->json()['success']) {
                Log::info('Successfully subscribed page to webhooks', ['page_id' => $pageId]);
                return true;
            }

            Log::error('Webhook subscription failed', [
                'page_id' => $pageId,
                'response_status' => $response->status(),
                'response_body' => $response->body()
            ]);
            return false;
        } catch (\Exception $e) {
            Log::critical('Exception in subscribeToWebhooks', [
                'page_id' => $pageId,
                'message' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $appSecret = config('services.facebook.app_secret');
        if (!$appSecret) {
            Log::error('Facebook app secret is not configured.');
            return false;
        }
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $appSecret);
        return hash_equals($expectedSignature, $signature);
    }

    public function testPageConnection(FacebookPage $page): bool
    {
        try {
            $pageInfo = $this->getPageInfo($page->page_id, $page->access_token);
            if ($pageInfo) {
                $page->updateSyncTime();
                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('Failed to test page connection', ['page_id' => $page->page_id, 'error' => $e->getMessage()]);
            return false;
        }
    }
    
    public function getPageInfo(string $pageId, string $accessToken): ?array
    {
        try {
            $response = Http::get($this->baseUrl . "/{$pageId}", [
                'access_token' => $accessToken,
                'fields' => 'id,name,category,picture,fan_count,about,website,phone,emails'
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Facebook page info error', ['response_body' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::critical('Exception in getPageInfo', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Send product carousel to Facebook Messenger user
     */
    public function sendProductCarousel(string $pageToken, string $userId, array $products): array
    {
        if (empty($products) || count($products) > 10) {
            return ['success' => false, 'error' => 'Invalid product count. Must be 1-10 products.'];
        }

        try {
            $elements = [];
            
            foreach ($products as $product) {
                $effectivePrice = $product['sale_price'] ?? $product['price'];
                $formattedPrice = '৳' . number_format($effectivePrice, 0);
                
                // Build subtitle with price info
                $subtitle = $formattedPrice;
                if ($product['sale_price']) {
                    $subtitle .= ' (Sale Price)';
                }
                
                $element = [
                    'title' => $product['name'],
                    'subtitle' => $subtitle,
                    'default_action' => [
                        'type' => 'web_url',
                        'url' => $product['product_link'] ?? 'https://facebook.com',
                        'webview_height_ratio' => 'tall'
                    ]
                ];
                
                // Add image if available
                if (!empty($product['image_url'])) {
                    $element['image_url'] = $product['image_url'];
                }
                
                // Add action buttons
                $buttons = [];
                
                // View Details button
                $buttons[] = [
                    'type' => 'postback',
                    'title' => 'View Details',
                    'payload' => 'VIEW_PRODUCT_' . $product['id']
                ];
                
                // More Info button (opens external link)
                if (!empty($product['product_link'])) {
                    $buttons[] = [
                        'type' => 'web_url',
                        'title' => 'More Info',
                        'url' => $product['product_link']
                    ];
                }
                
                $element['buttons'] = $buttons;
                $elements[] = $element;
            }

            $messageData = [
                'recipient' => ['id' => $userId],
                'message' => [
                    'attachment' => [
                        'type' => 'template',
                        'payload' => [
                            'template_type' => 'generic',
                            'elements' => $elements
                        ]
                    ]
                ]
            ];

            $response = Http::post($this->baseUrl . '/me/messages', [
                'access_token' => $pageToken,
            ] + $messageData);

            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('Product carousel sent successfully', [
                    'user_id' => $userId,
                    'product_count' => count($products),
                    'message_id' => $responseData['message_id'] ?? null
                ]);
                
                return [
                    'success' => true,
                    'message_id' => $responseData['message_id'] ?? null,
                    'response' => $responseData
                ];
            } else {
                $errorMsg = 'Failed to send product carousel';
                if ($response->json('error.message')) {
                    $errorMsg .= ': ' . $response->json('error.message');
                }
                
                Log::error('Facebook product carousel error', [
                    'user_id' => $userId,
                    'products' => count($products),
                    'response_status' => $response->status(),
                    'response_body' => $response->body()
                ]);
                
                return ['success' => false, 'error' => $errorMsg];
            }

        } catch (\Exception $e) {
            Log::error('Exception sending product carousel', [
                'user_id' => $userId,
                'products' => count($products),
                'message' => $e->getMessage()
            ]);
            
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send quick reply message with buttons
     */
    public function sendQuickReply(string $pageToken, string $userId, string $text, array $quickReplies): array
    {
        try {
            $messageData = [
                'recipient' => ['id' => $userId],
                'message' => [
                    'text' => $text,
                    'quick_replies' => $quickReplies
                ]
            ];

            $response = Http::post($this->baseUrl . '/me/messages', [
                'access_token' => $pageToken,
            ] + $messageData);

            if ($response->successful()) {
                $responseData = $response->json();
                return [
                    'success' => true,
                    'message_id' => $responseData['message_id'] ?? null
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response->json('error.message', 'Unknown error')
                ];
            }

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send text message to user
     */
    public function sendTextMessage(string $pageToken, string $userId, string $text): array
    {
        try {
            $messageData = [
                'recipient' => ['id' => $userId],
                'message' => ['text' => $text]
            ];

            $response = Http::post($this->baseUrl . '/me/messages', [
                'access_token' => $pageToken,
            ] + $messageData);

            if ($response->successful()) {
                return ['success' => true, 'message_id' => $response->json('message_id')];
            } else {
                return ['success' => false, 'error' => $response->json('error.message', 'Unknown error')];
            }

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send generic template message (for buttons, cards, etc.)
     */
    public function sendGenericTemplate(string $pageToken, string $userId, array $elements): array
    {
        try {
            $messageData = [
                'recipient' => ['id' => $userId],
                'message' => [
                    'attachment' => [
                        'type' => 'template',
                        'payload' => [
                            'template_type' => 'generic',
                            'elements' => $elements
                        ]
                    ]
                ]
            ];

            $response = Http::post($this->baseUrl . '/me/messages', [
                'access_token' => $pageToken,
            ] + $messageData);

            if ($response->successful()) {
                $responseData = $response->json();
                return [
                    'success' => true,
                    'message_id' => $responseData['message_id'] ?? null
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response->json('error.message', 'Unknown error')
                ];
            }

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send detailed product information as a formatted message
     */
    public function sendProductDetails(string $pageToken, string $userId, array $product): array
    {
        try {
            $effectivePrice = $product['sale_price'] ?? $product['price'];
            $originalPrice = $product['price'];
            
            $message = "📦 **{$product['name']}**\n\n";
            
            if (!empty($product['description'])) {
                $message .= "📝 **Description:**\n{$product['description']}\n\n";
            }
            
            // Price information
            $message .= "💰 **Price:** ৳" . number_format($effectivePrice, 0);
            if ($product['sale_price'] && $product['sale_price'] < $originalPrice) {
                $message .= " (Sale Price)\n";
                $message .= "~~Original: ৳" . number_format($originalPrice, 0) . "~~\n";
                $savings = $originalPrice - $product['sale_price'];
                $message .= "💵 **You Save:** ৳" . number_format($savings, 0) . "\n\n";
            } else {
                $message .= "\n\n";
            }
            
            // Stock information
            if (isset($product['stock_quantity'])) {
                if ($product['track_stock'] && $product['stock_quantity'] > 0) {
                    if ($product['stock_quantity'] <= 5) {
                        $message .= "⚠️ **Stock:** Only {$product['stock_quantity']} left!\n\n";
                    } else {
                        $message .= "✅ **Stock:** Available ({$product['stock_quantity']} units)\n\n";
                    }
                } elseif ($product['track_stock'] && $product['stock_quantity'] <= 0) {
                    $message .= "❌ **Out of Stock**\n\n";
                } else {
                    $message .= "✅ **Stock:** Available\n\n";
                }
            }
            
            // Category
            if (!empty($product['category'])) {
                $message .= "🏷️ **Category:** {$product['category']}\n";
            }
            
            // SKU
            if (!empty($product['sku'])) {
                $message .= "🔖 **SKU:** {$product['sku']}\n";
            }
            
            $message .= "\n📞 **Interested?** Reply with your contact details (name, phone, address) to place an order!";
            
            return $this->sendTextMessage($pageToken, $userId, $message);

        } catch (\Exception $e) {
            Log::error('Exception sending product details', [
                'user_id' => $userId,
                'product_id' => $product['id'] ?? 'unknown',
                'message' => $e->getMessage()
            ]);
            
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get user profile information from Facebook Graph API
     * Note: Phone numbers are not available through Facebook API
     */
    public function getUserProfile(string $pageToken, string $userId): ?array
    {
        try {
            $response = Http::get($this->baseUrl . "/{$userId}", [
                'fields' => 'id,name,first_name,last_name,profile_pic',
                'access_token' => $pageToken
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Successfully fetched user profile', [
                    'user_id' => $userId,
                    'name' => $data['name'] ?? 'Unknown'
                ]);
                return $data;
            }

            Log::warning('Failed to fetch user profile', [
                'user_id' => $userId,
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            
            return null;

        } catch (\Exception $e) {
            Log::error('Exception fetching user profile', [
                'user_id' => $userId,
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Update customer with Facebook profile information
     */
    public function updateCustomerWithFacebookProfile(Customer $customer, FacebookPage $facebookPage): void
    {
        try {
            if (!$customer->facebook_user_id || !$facebookPage->access_token) {
                return;
            }

            $profile = $this->getUserProfile($facebookPage->access_token, $customer->facebook_user_id);
            
            if ($profile) {
                $updateData = [];
                
                // Only update name if it's still default "Facebook User"
                if (($customer->name === 'Facebook User' || empty($customer->name)) && !empty($profile['name'])) {
                    $updateData['name'] = $profile['name'];
                }
                
                // Update profile data
                $profileData = $customer->profile_data ?? [];
                $profileData['facebook_profile'] = [
                    'name' => $profile['name'] ?? null,
                    'first_name' => $profile['first_name'] ?? null,
                    'last_name' => $profile['last_name'] ?? null,
                    'profile_pic' => $profile['profile_pic'] ?? null,
                    'updated_at' => now()->toISOString()
                ];
                $updateData['profile_data'] = $profileData;
                
                if (!empty($updateData)) {
                    $customer->update($updateData);
                    
                    Log::info('Customer updated with Facebook profile', [
                        'customer_id' => $customer->id,
                        'facebook_name' => $profile['name'] ?? 'Unknown'
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to update customer with Facebook profile', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function sendTemplateMessage(string $recipientId, array $messageData, string $pageAccessToken): bool
    {
        try {
            $url = $this->baseUrl . '/me/messages';

            $payload = [
                'recipient' => [
                    'id' => $recipientId,
                ],
                'message'   => $messageData,
            ];

            $response = Http::withToken($pageAccessToken)
                ->post($url, $payload);

            if ($response->successful()) {
                Log::info('Facebook template message sent successfully', [
                    'recipient_id' => $recipientId,
                    'response' => $response->json(),
                ]);
                return true;
            }

            Log::error('Failed to send Facebook template message', [
                'recipient_id' => $recipientId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('Exception while sending Facebook template message', [
                'recipient_id' => $recipientId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}

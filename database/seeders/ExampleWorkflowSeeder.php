<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Workflow;
use App\Models\Client;
use App\Models\FacebookPage;

class ExampleWorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all clients with Facebook pages
        $clients = Client::with('facebookPages')->get();
        
        if ($clients->isEmpty()) {
            $this->command->error('No clients found. Please create a client first.');
            return;
        }

        foreach ($clients as $client) {
            if ($client->facebookPages->isEmpty()) {
                $this->command->info("Client {$client->name} has no Facebook pages. Skipping...");
                continue;
            }

            foreach ($client->facebookPages as $page) {
                // Check if workflow already exists for this page
                $existingWorkflow = Workflow::where('client_id', $client->id)
                    ->where('facebook_page_id', $page->id)
                    ->first();

                if ($existingWorkflow) {
                    $this->command->info("Workflow already exists for page: {$page->page_name}");
                    continue;
                }

                $workflow = Workflow::create([
                    'client_id' => $client->id,
                    'facebook_page_id' => $page->id,
                    'name' => 'Improved Order Workflow',
                    'description' => 'An enhanced workflow that shows products first, guides through selection with quantities, asks for more products, and provides clear customer information guidance.',
                    'definition' => $this->getImprovedWorkflowDefinition(),
                    'supported_languages' => ['en', 'bn'],
                    'default_language' => 'en',
                    'is_active' => false, // Start as draft
                    'version' => 2
                ]);

                $this->command->info("Created example workflow for client: {$client->name}, page: {$page->page_name}");
            }
        }

        $this->command->info('Example workflows created successfully!');
        $this->command->info('Go to your client panel > Workflows to review and publish them.');
    }

    private function getImprovedWorkflowDefinition(): array
    {
        // Read the improved workflow from the JSON file
        $workflowPath = base_path('improved_workflow.json');
        if (file_exists($workflowPath)) {
            $workflowContent = file_get_contents($workflowPath);
            return json_decode($workflowContent, true);
        }
        
        // Fallback to inline definition if file doesn't exist
        return $this->getInlineImprovedWorkflow();
    }
    
    private function getInlineImprovedWorkflow(): array
    {
        return [
            "steps" => [
                [
                    "id" => "welcome",
                    "type" => "info_display",
                    "labels" => [
                        "en" => [
                            "title" => "Welcome to Our Store",
                            "description" => "Hi! I'm here to help you place an order. Let's get started!",
                            "continue_message" => "Ready to help you!"
                        ],
                        "bn" => [
                            "title" => "আমাদের দোকানে স্বাগতম",
                            "description" => "হাই! আমি আপনাকে অর্ডার দিতে সাহায্য করব। শুরু করা যাক!",
                            "continue_message" => "আপনাকে সাহায্য করতে প্রস্তুত!"
                        ]
                    ],
                    "config" => [
                        "auto_continue" => true
                    ]
                ],
                [
                    "id" => "product_selection",
                    "type" => "product_selector",
                    "labels" => [
                        "en" => [
                            "title" => "Select Products",
                            "description" => "What would you like to order today?",
                            "format_help" => "Type product names or 'Product1, Product2' for multiple items",
                            "quantity_prompt" => "How many {product} do you want?",
                            "success_single" => "✅ Selected: {product}",
                            "success_multiple" => "✅ Selected {count} products",
                            "error_not_found" => "❌ Couldn't find '{input}'. Did you mean:",
                            "error_multiple_matches" => "🤔 Found multiple matches for '{input}'. Please be specific:",
                            "retry_message" => "Please try again or choose from suggestions above",
                            "max_attempts_reached" => "Let me show you all available products:"
                        ],
                        "bn" => [
                            "title" => "পণ্য নির্বাচন করুন",
                            "description" => "আজ আপনি কী অর্ডার করতে চান?",
                            "format_help" => "পণ্যের নাম টাইপ করুন বা একাধিকের জন্য 'পণ্য১, পণ্য২'",
                            "quantity_prompt" => "{product} এর কতটি চান?",
                            "success_single" => "✅ নির্বাচিত: {product}",
                            "success_multiple" => "✅ {count}টি পণ্য নির্বাচিত",
                            "error_not_found" => "❌ '{input}' পাওয়া যায়নি। আপনি কি বোঝাতে চেয়েছেন:",
                            "error_multiple_matches" => "🤔 '{input}' এর জন্য একাধিক পণ্য পাওয়া গেছে। স্পেসিফিক হন:",
                            "retry_message" => "আবার চেষ্টা করুন বা উপরের সাজেশন থেকে বেছে নিন",
                            "max_attempts_reached" => "আমি সব পণ্য দেখাচ্ছি:"
                        ]
                    ],
                    "config" => [
                        "multiple" => true,
                        "min_products" => 1,
                        "max_products" => 5,
                        "allow_quantity" => true,
                        "show_suggestions" => true,
                        "retry_attempts" => 3
                    ],
                    "validation" => [
                        "required" => true,
                        "custom" => "validate_products"
                    ]
                ],
                [
                    "id" => "customer_info",
                    "type" => "form",
                    "labels" => [
                        "en" => [
                            "title" => "Customer Information",
                            "description" => "Please provide your contact details for delivery",
                            "success" => "✅ Information saved successfully!"
                        ],
                        "bn" => [
                            "title" => "গ্রাহকের তথ্য",
                            "description" => "ডেলিভারির জন্য আপনার যোগাযোগের তথ্য দিন",
                            "success" => "✅ তথ্য সফলভাবে সংরক্ষিত হয়েছে!"
                        ]
                    ],
                    "fields" => [
                        [
                            "name" => "name",
                            "type" => "text",
                            "required" => true,
                            "labels" => [
                                "en" => "Full Name",
                                "bn" => "পূর্ণ নাম"
                            ],
                            "validation" => [
                                "min_length" => 2,
                                "max_length" => 100
                            ]
                        ],
                        [
                            "name" => "phone",
                            "type" => "tel",
                            "required" => true,
                            "labels" => [
                                "en" => "Phone Number",
                                "bn" => "মোবাইল নম্বর"
                            ],
                            "validation" => [
                                "pattern" => "^[0-9+\\-\\s]{10,15}$"
                            ]
                        ]
                    ]
                ],
                [
                    "id" => "delivery_area",
                    "type" => "choice",
                    "labels" => [
                        "en" => [
                            "title" => "Delivery Area",
                            "description" => "Where should we deliver your order?"
                        ],
                        "bn" => [
                            "title" => "ডেলিভারি এলাকা",
                            "description" => "আমরা আপনার অর্ডার কোথায় পৌঁছে দেব?"
                        ]
                    ],
                    "choices" => [
                        [
                            "id" => "inside_dhaka",
                            "labels" => [
                                "en" => "Inside Dhaka",
                                "bn" => "ঢাকার ভিতরে"
                            ],
                            "shipping_charge" => 60
                        ],
                        [
                            "id" => "outside_dhaka",
                            "labels" => [
                                "en" => "Outside Dhaka",
                                "bn" => "ঢাকার বাইরে"
                            ],
                            "shipping_charge" => 120
                        ]
                    ]
                ],
                [
                    "id" => "delivery_address",
                    "type" => "form",
                    "labels" => [
                        "en" => [
                            "title" => "Delivery Address",
                            "description" => "Where exactly should we deliver your order?"
                        ],
                        "bn" => [
                            "title" => "ডেলিভারি ঠিকানা",
                            "description" => "আপনার অর্ডার ঠিক কোথায় পৌঁছে দেব?"
                        ]
                    ],
                    "fields" => [
                        [
                            "name" => "address",
                            "type" => "textarea",
                            "required" => true,
                            "labels" => [
                                "en" => "Complete Address",
                                "bn" => "সম্পূর্ণ ঠিকানা"
                            ],
                            "validation" => [
                                "min_length" => 10,
                                "max_length" => 300
                            ]
                        ]
                    ]
                ],
                [
                    "id" => "order_confirmation",
                    "type" => "confirmation",
                    "labels" => [
                        "en" => [
                            "title" => "Confirm Your Order",
                            "description" => "Please review your order details below and confirm:",
                            "confirm_button" => "Yes, Place Order",
                            "cancel_button" => "No, Edit Order"
                        ],
                        "bn" => [
                            "title" => "আপনার অর্ডার নিশ্চিত করুন",
                            "description" => "নিচে আপনার অর্ডারের বিবরণ দেখুন এবং নিশ্চিত করুন:",
                            "confirm_button" => "হ্যাঁ, অর্ডার দিন",
                            "cancel_button" => "না, অর্ডার এডিট করুন"
                        ]
                    ],
                    "config" => [
                        "show_summary" => true,
                        "show_total" => true,
                        "show_customer_info" => true,
                        "show_delivery_info" => true,
                        "allow_edit" => false
                    ]
                ]
            ]
        ];
    }
    
    // Keep the old workflow for reference
    private function getWorkflowDefinition(): array
    {
        return [
            "steps" => [
                [
                    "id" => "welcome",
                    "type" => "info_display",
                    "labels" => [
                        "en" => [
                            "title" => "Welcome to Our Store",
                            "description" => "Hi! I'm here to help you place an order. Let's get started!",
                            "continue_message" => "Ready to help you!"
                        ],
                        "bn" => [
                            "title" => "আমাদের দোকানে স্বাগতম",
                            "description" => "হাই! আমি আপনাকে অর্ডার দিতে সাহায্য করব। শুরু করা যাক!",
                            "continue_message" => "আপনাকে সাহায্য করতে প্রস্তুত!"
                        ]
                    ],
                    "config" => [
                        "auto_continue" => true
                    ]
                ],
                [
                    "id" => "product_selection",
                    "type" => "product_selector",
                    "labels" => [
                        "en" => [
                            "title" => "Select Products",
                            "description" => "What would you like to order today?",
                            "format_help" => "Type product names or 'Product1, Product2' for multiple items",
                            "quantity_prompt" => "How many {product} do you want?",
                            "success_single" => "✅ Selected: {product}",
                            "success_multiple" => "✅ Selected {count} products",
                            "error_not_found" => "❌ Couldn't find '{input}'. Did you mean:",
                            "error_multiple_matches" => "🤔 Found multiple matches for '{input}'. Please be specific:",
                            "retry_message" => "Please try again or choose from suggestions above",
                            "max_attempts_reached" => "Let me show you all available products:"
                        ],
                        "bn" => [
                            "title" => "পণ্য নির্বাচন করুন",
                            "description" => "আজ আপনি কী অর্ডার করতে চান?",
                            "format_help" => "পণ্যের নাম টাইপ করুন বা একাধিকের জন্য 'পণ্য১, পণ্য২'",
                            "quantity_prompt" => "{product} এর কতটি চান?",
                            "success_single" => "✅ নির্বাচিত: {product}",
                            "success_multiple" => "✅ {count}টি পণ্য নির্বাচিত",
                            "error_not_found" => "❌ '{input}' পাওয়া যায়নি। আপনি কি বোঝাতে চেয়েছেন:",
                            "error_multiple_matches" => "🤔 '{input}' এর জন্য একাধিক পণ্য পাওয়া গেছে। স্পেসিফিক হন:",
                            "retry_message" => "আবার চেষ্টা করুন বা উপরের সাজেশন থেকে বেছে নিন",
                            "max_attempts_reached" => "আমি সব পণ্য দেখাচ্ছি:"
                        ]
                    ],
                    "config" => [
                        "multiple" => true,
                        "min_products" => 1,
                        "max_products" => 5,
                        "allow_quantity" => true,
                        "show_suggestions" => true,
                        "retry_attempts" => 3
                    ],
                    "validation" => [
                        "required" => true,
                        "custom" => "validate_products"
                    ]
                ],
                [
                    "id" => "customer_info",
                    "type" => "form",
                    "labels" => [
                        "en" => [
                            "title" => "Customer Information",
                            "description" => "Please provide your contact details for delivery",
                            "success" => "✅ Information saved successfully!"
                        ],
                        "bn" => [
                            "title" => "গ্রাহকের তথ্য",
                            "description" => "ডেলিভারির জন্য আপনার যোগাযোগের তথ্য দিন",
                            "success" => "✅ তথ্য সফলভাবে সংরক্ষিত হয়েছে!"
                        ]
                    ],
                    "fields" => [
                        [
                            "name" => "name",
                            "type" => "text",
                            "required" => true,
                            "labels" => [
                                "en" => "Full Name",
                                "bn" => "পূর্ণ নাম"
                            ],
                            "validation" => [
                                "min_length" => 2,
                                "max_length" => 100
                            ]
                        ],
                        [
                            "name" => "phone",
                            "type" => "tel",
                            "required" => true,
                            "labels" => [
                                "en" => "Phone Number",
                                "bn" => "মোবাইল নম্বর"
                            ],
                            "validation" => [
                                "pattern" => "^[0-9+\\-\\s]{10,15}$"
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
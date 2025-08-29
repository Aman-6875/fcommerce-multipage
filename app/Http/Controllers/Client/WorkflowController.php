<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Workflow;
use App\Models\FacebookPage;
use App\Models\ConversationState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class WorkflowController extends Controller
{
    /**
     * Display workflows for a specific page
     */
    public function index(Request $request)
    {
        $client = auth('client')->user();
        $facebookPages = $client->facebookPages()->where('is_connected', true)->get();
        
        if ($facebookPages->isEmpty()) {
            return redirect()->route('client.facebook.index')
                ->with('error', 'Please connect a Facebook page first.');
        }

        $selectedPageId = $request->get('page_id', $facebookPages->first()->id);
        $selectedPage = $facebookPages->where('id', $selectedPageId)->first();
        
        if (!$selectedPage) {
            $selectedPage = $facebookPages->first();
            $selectedPageId = $selectedPage->id;
        }

        $workflows = Workflow::where('client_id', $client->id)
            ->where('facebook_page_id', $selectedPageId)
            ->withCount(['conversationStates as total_conversations'])
            ->withCount(['conversationStates as active_conversations' => function($query) {
                $query->where('status', 'active');
            }])
            ->withCount(['conversationStates as completed_conversations' => function($query) {
                $query->where('status', 'completed');
            }])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('client.workflows.index', compact('workflows', 'facebookPages', 'selectedPage'));
    }

    /**
     * Show workflow creation form
     */
    public function create(Request $request)
    {
        $client = auth('client')->user();
        $facebookPages = $client->facebookPages()->where('is_connected', true)->get();
        
        if ($facebookPages->isEmpty()) {
            return redirect()->route('client.facebook.index')
                ->with('error', 'Please connect a Facebook page first.');
        }

        $selectedPageId = $request->get('page_id', $facebookPages->first()->id);
        $defaultTemplate = $this->getDefaultWorkflowTemplate();
        
        return view('client.workflows.create', compact('facebookPages', 'selectedPageId', 'defaultTemplate'));
    }

    /**
     * Create workflow from default template
     */
    public function createFromTemplate(Request $request)
    {
        $client = auth('client')->user();
        
        $request->validate([
            'facebook_page_id' => [
                'required',
                'exists:facebook_pages,id',
                Rule::in($client->facebookPages->pluck('id'))
            ],
            'name' => 'nullable|string|max:255'
        ]);

        $facebookPage = $client->facebookPages()->find($request->facebook_page_id);
        
        if (!$facebookPage) {
            return back()->with('error', 'Invalid Facebook page selected.');
        }

        // Check if workflow already exists for this page
        $existingWorkflow = Workflow::where('client_id', $client->id)
            ->where('facebook_page_id', $request->facebook_page_id)
            ->first();

        if ($existingWorkflow) {
            return redirect()->route('client.workflows.edit', $existingWorkflow)
                ->with('info', 'Workflow already exists for this page. You can edit it here.');
        }

        try {
            $workflow = Workflow::create([
                'client_id' => $client->id,
                'facebook_page_id' => $request->facebook_page_id,
                'name' => $request->name ?: "Complete Order Workflow - {$facebookPage->page_name}",
                'description' => 'A comprehensive workflow that guides customers through the complete ordering process from product selection to order confirmation.',
                'definition' => $this->getDefaultWorkflowTemplate(),
                'supported_languages' => ['en', 'bn'],
                'default_language' => 'en',
                'is_active' => false,
                'version' => 1
            ]);

            return redirect()->route('client.workflows.edit', $workflow)
                ->with('success', 'Default workflow created successfully! You can customize it and publish when ready.');

        } catch (\Exception $e) {
            Log::error('Default workflow creation failed', [
                'client_id' => $client->id,
                'facebook_page_id' => $request->facebook_page_id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to create workflow. Please try again.');
        }
    }

    /**
     * Store a new workflow
     */
    public function store(Request $request)
    {
        $client = auth('client')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'facebook_page_id' => [
                'required',
                'exists:facebook_pages,id',
                Rule::in($client->facebookPages->pluck('id'))
            ],
            'supported_languages' => 'required|array|min:1',
            'supported_languages.*' => 'in:en,bn',
            'default_language' => 'required|in:en,bn',
            'workflow_definition' => 'required|json'
        ]);

        try {
            $workflowDefinition = json_decode($request->workflow_definition, true);
            
            // Validate workflow definition structure
            $this->validateWorkflowDefinition($workflowDefinition);

            $workflow = Workflow::create([
                'client_id' => $client->id,
                'facebook_page_id' => $request->facebook_page_id,
                'name' => $request->name,
                'description' => $request->description,
                'definition' => $workflowDefinition,
                'supported_languages' => $request->supported_languages,
                'default_language' => $request->default_language,
                'is_active' => false, // Start as draft
                'version' => 1
            ]);

            return redirect()->route('client.workflows.edit', $workflow)
                ->with('success', 'Workflow created successfully! You can now add steps and publish it.');

        } catch (\Exception $e) {
            Log::error('Workflow creation failed', [
                'client_id' => $client->id,
                'error' => $e->getMessage()
            ]);

            return back()->withInput()
                ->withErrors(['workflow_definition' => 'Invalid workflow definition.']);
        }
    }

    /**
     * Show workflow details
     */
    public function show(Workflow $workflow)
    {
        $this->authorizeWorkflow($workflow);
        
        $workflow->loadCount([
            'conversationStates as total_conversations',
            'conversationStates as active_conversations' => function($query) {
                $query->where('status', 'active');
            },
            'conversationStates as completed_conversations' => function($query) {
                $query->where('status', 'completed');
            }
        ]);

        // Get recent conversations
        $recentConversations = ConversationState::where('workflow_id', $workflow->id)
            ->with(['customer'])
            ->orderByDesc('last_activity_at')
            ->limit(10)
            ->get();

        return view('client.workflows.show', compact('workflow', 'recentConversations'));
    }

    /**
     * Show workflow edit form
     */
    public function edit(Workflow $workflow)
    {
        $this->authorizeWorkflow($workflow);
        
        $client = auth('client')->user();
        $facebookPages = $client->facebookPages()->where('is_connected', true)->get();
        
        return view('client.workflows.edit', compact('workflow', 'facebookPages'));
    }

    /**
     * Update workflow
     */
    public function update(Request $request, Workflow $workflow)
    {
        $this->authorizeWorkflow($workflow);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'supported_languages' => 'required|array|min:1',
            'supported_languages.*' => 'in:en,bn',
            'default_language' => 'required|in:en,bn',
            'workflow_definition' => 'required|json'
        ]);

        try {
            $workflowDefinition = json_decode($request->workflow_definition, true);
            $this->validateWorkflowDefinition($workflowDefinition);

            // If workflow is published and has changes, create new version
            if ($workflow->isPublished() && $this->hasSignificantChanges($workflow, $workflowDefinition)) {
                $newWorkflow = $workflow->createNewVersion();
                $newWorkflow->update([
                    'name' => $request->name,
                    'description' => $request->description,
                    'definition' => $workflowDefinition,
                    'supported_languages' => $request->supported_languages,
                    'default_language' => $request->default_language
                ]);
                
                return redirect()->route('client.workflows.edit', $newWorkflow)
                    ->with('success', 'New workflow version created. Review and publish when ready.');
            } else {
                // Update existing workflow
                $workflow->update([
                    'name' => $request->name,
                    'description' => $request->description,
                    'definition' => $workflowDefinition,
                    'supported_languages' => $request->supported_languages,
                    'default_language' => $request->default_language
                ]);
                
                return redirect()->route('client.workflows.edit', $workflow)
                    ->with('success', 'Workflow updated successfully.');
            }

        } catch (\Exception $e) {
            Log::error('Workflow update failed', [
                'workflow_id' => $workflow->id,
                'error' => $e->getMessage()
            ]);

            return back()->withInput()
                ->withErrors(['workflow_definition' => 'Invalid workflow definition.']);
        }
    }

    /**
     * Publish workflow
     */
    public function publish(Workflow $workflow)
    {
        $this->authorizeWorkflow($workflow);

        if ($workflow->isPublished()) {
            return back()->with('error', 'Workflow is already published.');
        }

        try {
            // Validate workflow is complete before publishing
            $this->validateWorkflowForPublication($workflow);
            
            // Unpublish any other active workflow for this page
            Workflow::where('facebook_page_id', $workflow->facebook_page_id)
                ->where('id', '!=', $workflow->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $workflow->publish();

            return back()->with('success', 'Workflow published successfully!');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Unpublish workflow
     */
    public function unpublish(Workflow $workflow)
    {
        $this->authorizeWorkflow($workflow);

        if (!$workflow->isPublished()) {
            return back()->with('error', 'Workflow is not published.');
        }

        $workflow->unpublish();

        return back()->with('success', 'Workflow unpublished successfully.');
    }

    /**
     * Delete workflow
     */
    public function destroy(Workflow $workflow)
    {
        $this->authorizeWorkflow($workflow);

        if ($workflow->isPublished()) {
            return back()->with('error', 'Cannot delete a published workflow. Unpublish it first.');
        }

        if ($workflow->conversationStates()->count() > 0) {
            return back()->with('error', 'Cannot delete workflow with existing conversations.');
        }

        $workflow->delete();

        return redirect()->route('client.workflows.index')
            ->with('success', 'Workflow deleted successfully.');
    }

    /**
     * Get workflow analytics
     */
    public function analytics(Workflow $workflow)
    {
        $this->authorizeWorkflow($workflow);

        // Get conversation statistics
        $stats = [
            'total_conversations' => $workflow->conversationStates()->count(),
            'completed_conversations' => $workflow->conversationStates()->where('status', 'completed')->count(),
            'active_conversations' => $workflow->conversationStates()->where('status', 'active')->count(),
            'abandoned_conversations' => $workflow->conversationStates()->where('status', 'abandoned')->count(),
        ];

        // Completion rate
        $stats['completion_rate'] = $stats['total_conversations'] > 0 
            ? round(($stats['completed_conversations'] / $stats['total_conversations']) * 100, 2)
            : 0;

        // Step dropout analysis
        $stepAnalysis = $this->getStepDropoutAnalysis($workflow);

        // Language usage
        $languageStats = $workflow->conversationStates()
            ->selectRaw('language, COUNT(*) as count')
            ->groupBy('language')
            ->get();

        return response()->json([
            'stats' => $stats,
            'step_analysis' => $stepAnalysis,
            'language_stats' => $languageStats
        ]);
    }

    /**
     * Get step-by-step dropdown analysis
     */
    private function getStepDropoutAnalysis(Workflow $workflow): array
    {
        $steps = $workflow->getSteps();
        $analysis = [];

        foreach ($steps as $index => $step) {
            $reachedStep = $workflow->conversationStates()
                ->where('current_step_index', '>=', $index)
                ->count();
                
            $completedStep = $workflow->conversationStates()
                ->where('current_step_index', '>', $index)
                ->orWhere(function($query) use ($index) {
                    $query->where('current_step_index', $index)
                          ->where('status', 'completed');
                })
                ->count();

            $dropoutRate = $reachedStep > 0 
                ? round((($reachedStep - $completedStep) / $reachedStep) * 100, 2)
                : 0;

            $analysis[] = [
                'step_index' => $index,
                'step_id' => $step['id'],
                'step_name' => $step['labels']['en']['title'] ?? $step['id'],
                'reached' => $reachedStep,
                'completed' => $completedStep,
                'dropout_rate' => $dropoutRate
            ];
        }

        return $analysis;
    }

    /**
     * Authorize workflow access
     */
    private function authorizeWorkflow(Workflow $workflow)
    {
        if ($workflow->client_id !== auth('client')->id()) {
            abort(404);
        }
    }

    /**
     * Validate workflow definition structure
     */
    private function validateWorkflowDefinition(array $definition)
    {
        if (!isset($definition['steps']) || !is_array($definition['steps'])) {
            throw new \Exception('Workflow must have steps array.');
        }

        if (empty($definition['steps'])) {
            throw new \Exception('Workflow must have at least one step.');
        }

        foreach ($definition['steps'] as $index => $step) {
            if (!isset($step['id']) || !isset($step['type'])) {
                throw new \Exception("Step {$index} must have 'id' and 'type'.");
            }

            if (!isset($step['labels']) || !is_array($step['labels'])) {
                throw new \Exception("Step {$index} must have labels array.");
            }
        }
    }

    /**
     * Check if workflow has significant changes that require versioning
     */
    private function hasSignificantChanges(Workflow $workflow, array $newDefinition): bool
    {
        $oldSteps = $workflow->getSteps();
        $newSteps = $newDefinition['steps'] ?? [];

        // Check if step count changed
        if (count($oldSteps) !== count($newSteps)) {
            return true;
        }

        // Check if step types or order changed
        foreach ($oldSteps as $index => $oldStep) {
            $newStep = $newSteps[$index] ?? [];
            
            if ($oldStep['id'] !== ($newStep['id'] ?? '') || 
                $oldStep['type'] !== ($newStep['type'] ?? '')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate workflow is ready for publication
     */
    private function validateWorkflowForPublication(Workflow $workflow)
    {
        $steps = $workflow->getSteps();
        
        if (empty($steps)) {
            throw new \Exception('Cannot publish workflow without steps.');
        }

        // Check if all steps have required labels
        foreach ($steps as $step) {
            foreach ($workflow->supported_languages as $lang) {
                if (!isset($step['labels'][$lang]['title'])) {
                    throw new \Exception("Step '{$step['id']}' missing title for language '{$lang}'.");
                }
            }
        }

        // Check if workflow has at least one completion step
        $hasCompletionStep = collect($steps)->contains('type', 'confirmation');
        if (!$hasCompletionStep) {
            Log::warning('Workflow published without confirmation step', [
                'workflow_id' => $workflow->id
            ]);
        }
    }

    /**
     * Get default workflow template that all clients can use
     */
    private function getDefaultWorkflowTemplate(): array
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
                        ],
                        [
                            "name" => "email",
                            "type" => "email",
                            "required" => false,
                            "labels" => [
                                "en" => "Email Address (Optional)",
                                "bn" => "ইমেইল ঠিকানা (ঐচ্ছিক)"
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
                        ],
                        [
                            "name" => "landmark",
                            "type" => "text",
                            "required" => false,
                            "labels" => [
                                "en" => "Nearby Landmark (Optional)",
                                "bn" => "কাছাকাছি ল্যান্ডমার্ক (ঐচ্ছিক)"
                            ]
                        ]
                    ]
                ],
                [
                    "id" => "payment_method",
                    "type" => "choice",
                    "labels" => [
                        "en" => [
                            "title" => "Payment Method",
                            "description" => "How would you like to pay?"
                        ],
                        "bn" => [
                            "title" => "পেমেন্ট পদ্ধতি",
                            "description" => "আপনি কীভাবে পেমেন্ট করতে চান?"
                        ]
                    ],
                    "choices" => [
                        [
                            "id" => "cash_on_delivery",
                            "labels" => [
                                "en" => "Cash on Delivery",
                                "bn" => "ক্যাশ অন ডেলিভারি"
                            ],
                            "is_default" => true
                        ],
                        [
                            "id" => "bkash",
                            "labels" => [
                                "en" => "bKash",
                                "bn" => "বিকাশ"
                            ]
                        ],
                        [
                            "id" => "nagad",
                            "labels" => [
                                "en" => "Nagad",
                                "bn" => "নগদ"
                            ]
                        ]
                    ]
                ],
                [
                    "id" => "special_instructions",
                    "type" => "form",
                    "labels" => [
                        "en" => [
                            "title" => "Special Instructions",
                            "description" => "Any special requests or delivery instructions? (Optional)"
                        ],
                        "bn" => [
                            "title" => "বিশেষ নির্দেশনা",
                            "description" => "কোন বিশেষ অনুরোধ বা ডেলিভারির নির্দেশনা? (ঐচ্ছিক)"
                        ]
                    ],
                    "fields" => [
                        [
                            "name" => "notes",
                            "type" => "textarea",
                            "required" => false,
                            "labels" => [
                                "en" => "Special Instructions or Notes",
                                "bn" => "বিশেষ নির্দেশনা বা নোট"
                            ],
                            "validation" => [
                                "max_length" => 500
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
}
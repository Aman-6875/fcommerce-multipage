<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Client\ClientAuthController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\FacebookController;
use App\Http\Controllers\FacebookWebhookController;
use App\Http\Controllers\PublicInvoiceController;

// Language switching route
Route::get('/set-language/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'bn'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('set-language');

// Landing Page
Route::middleware(['web', App\Http\Middleware\SetLanguage::class])->get('/', function () {
    session()->flush();
    return view('landing.index');
})->name('home');

// Legal Pages (Required for Facebook App Publishing)
Route::get('/privacy-policy', function () {
    return view('legal.privacy-policy');
})->name('privacy-policy');

Route::get('/terms-of-service', function () {
    return view('legal.terms-of-service');
})->name('terms-of-service');

Route::get('/data-deletion', [App\Http\Controllers\DataDeletionController::class, 'show'])->name('data-deletion');
Route::post('/data-deletion', [App\Http\Controllers\DataDeletionController::class, 'submit'])->name('data-deletion.submit');

// Public Invoice Route
Route::get('/invoice/{hash}', [PublicInvoiceController::class, 'show'])->name('public.invoice');

// Global routes that landing page expects
Route::get('/login', function () {
    return redirect()->route('client.login');
})->name('login');

Route::get('/register', function () {
    return redirect()->route('client.register');
})->name('register');

Route::get('/dashboard', function () {
    if (auth('admin')->check()) {
        return redirect()->route('admin.dashboard');
    } elseif (auth('client')->check()) {
        return redirect()->route('client.dashboard');
    }
    return redirect()->route('client.login');
})->name('dashboard');

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['web', App\Http\Middleware\SetLanguage::class])->group(function () {
    // Admin Auth Routes (guest middleware)
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login']);
    });

    // Admin Protected Routes
    Route::middleware('auth:admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/search', [DashboardController::class, 'search'])->name('search');
        
        // Admin user management (super admin only)
        Route::get('/register', [AdminAuthController::class, 'showRegistrationForm'])->name('register');
        Route::post('/register', [AdminAuthController::class, 'register']);

        // Client Management Routes
        Route::get('clients/premium', [\App\Http\Controllers\Admin\ClientManagementController::class, 'premium'])->name('clients.premium');
        Route::resource('clients', \App\Http\Controllers\Admin\ClientManagementController::class);

        // Upgrade Request Management Routes
        Route::prefix('upgrade-requests')->name('upgrade-requests.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\UpgradeRequestController::class, 'index'])->name('index');
            Route::get('/{upgradeRequest}', [\App\Http\Controllers\Admin\UpgradeRequestController::class, 'show'])->name('show');
            Route::post('/{upgradeRequest}/approve', [\App\Http\Controllers\Admin\UpgradeRequestController::class, 'approve'])->name('approve');
            Route::post('/{upgradeRequest}/reject', [\App\Http\Controllers\Admin\UpgradeRequestController::class, 'reject'])->name('reject');
            Route::post('/bulk-action', [\App\Http\Controllers\Admin\UpgradeRequestController::class, 'bulkAction'])->name('bulk-action');
        });

        // Order Management Routes
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\OrderManagementController::class, 'index'])->name('index');
            Route::get('/pending', [\App\Http\Controllers\Admin\OrderManagementController::class, 'pending'])->name('pending');
            Route::get('/delivered', [\App\Http\Controllers\Admin\OrderManagementController::class, 'delivered'])->name('delivered');
            Route::get('/{order}', [\App\Http\Controllers\Admin\OrderManagementController::class, 'show'])->name('show');
            Route::patch('/{order}/status', [\App\Http\Controllers\Admin\OrderManagementController::class, 'updateStatus'])->name('update-status');
        });

        // Language switcher
        Route::get('language/{locale}', function ($locale) {
            app()->setLocale($locale);
            session()->put('locale', $locale);
            return redirect()->back();
        })->name('language');

        // Note: Orders are managed by clients, not admin

        // Service Management Routes (to be implemented)
        Route::prefix('services')->name('services.')->group(function () {
            Route::get('/', function() { return view('admin.services.index'); })->name('index');
            Route::get('/calendar', function() { return view('admin.services.calendar'); })->name('calendar');
        });

        // Reports Routes
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/revenue', [\App\Http\Controllers\Admin\ReportController::class, 'revenue'])->name('revenue');
            Route::get('/clients', [\App\Http\Controllers\Admin\ReportController::class, 'clients'])->name('clients');
            Route::get('/orders', [\App\Http\Controllers\Admin\ReportController::class, 'orders'])->name('orders');
        });

        // Settings Routes
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/general', [\App\Http\Controllers\Admin\SettingsController::class, 'general'])->name('general');
            Route::post('/general', [\App\Http\Controllers\Admin\SettingsController::class, 'updateGeneral'])->name('general.update');
            Route::get('/users', [\App\Http\Controllers\Admin\SettingsController::class, 'users'])->name('users');
            Route::post('/users', [\App\Http\Controllers\Admin\SettingsController::class, 'createUser'])->name('users.store');
            Route::get('/payments', [\App\Http\Controllers\Admin\SettingsController::class, 'payments'])->name('payments');
            Route::post('/payments', [\App\Http\Controllers\Admin\SettingsController::class, 'updatePayments'])->name('payments.update');
        });

        // Profile route
        Route::get('/profile', [\App\Http\Controllers\Admin\AdminAuthController::class, 'profile'])->name('profile');
    });
});

// Client Routes  
Route::prefix('client')->name('client.')->middleware(['web', App\Http\Middleware\SetLanguage::class])->group(function () {
    // Client Auth Routes (guest middleware)
    Route::middleware('guest:client')->group(function () {
        Route::get('/login', [ClientAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [ClientAuthController::class, 'login']);
        Route::get('/register', [ClientAuthController::class, 'showRegistrationForm'])->name('register');
        Route::post('/register', [ClientAuthController::class, 'register']);
    });

    // Client Protected Routes
    Route::middleware('auth:client')->group(function () {
        Route::get('/dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [ClientAuthController::class, 'logout'])->name('logout');
        
        // Facebook Pages Management
        Route::prefix('facebook')->name('facebook.')->group(function () {
            Route::get('/', [FacebookController::class, 'index'])->name('index');
            Route::get('/connect', [FacebookController::class, 'connect'])->name('connect');
            Route::get('/callback', [FacebookController::class, 'callback'])->name('callback');
            Route::get('/select-pages', [FacebookController::class, 'selectPages'])->name('select-pages');
            Route::post('/connect-pages', [FacebookController::class, 'connectPages'])->name('connect-pages');
            Route::post('/select-page', [FacebookController::class, 'selectPage'])->name('select-page');
            Route::post('/disconnect/{facebookPage}', [FacebookController::class, 'disconnect'])->name('disconnect');
            Route::post('/test/{facebookPage}', [FacebookController::class, 'testConnection'])->name('test');
            Route::post('/sync/{facebookPage}', [FacebookController::class, 'syncMessages'])->name('sync');
            Route::post('/sync-customer/{facebookPage}/{customer}', [FacebookController::class, 'syncCustomerMessages'])->name('sync-customer');
        });
        
        // Client features
        Route::get('/profile', function() { 
            return view('client.profile'); 
        })->name('profile');
        
        Route::get('/facebook-pages', [FacebookController::class, 'index'])->name('facebook-pages'); // Backward compatibility
        
        Route::get('/customers', function() { 
            $customers = collect(); // Customers will be loaded via the relationship once defined
            return view('client.customers', compact('customers')); 
        })->name('customers');
        
        // Messages routes
        Route::get('/messages/{customer?}', [\App\Http\Controllers\Client\MessagesController::class, 'index'])->name('messages');
        Route::get('/api/customers', [\App\Http\Controllers\Client\MessagesController::class, 'getCustomers'])->name('api.customers');
        Route::get('/api/messages/{customer}', [\App\Http\Controllers\Client\MessagesController::class, 'getMessages'])->name('api.messages');
        Route::post('/api/messages/{customer}', [\App\Http\Controllers\Client\MessagesController::class, 'sendMessage'])->name('api.send-message');
        Route::post('/api/messages/{customer}/read', [\App\Http\Controllers\Client\MessagesController::class, 'markAsRead'])->name('api.mark-read');
        Route::get('/api/messages/unread-count', [\App\Http\Controllers\Client\MessagesController::class, 'getUnreadCount'])->name('api.unread-count');
        
        // Product Management routes
        Route::resource('products', \App\Http\Controllers\Client\ProductController::class);
        
        // Product selection routes
        Route::get('/products/modal/{pageId}', [\App\Http\Controllers\Client\ProductController::class, 'getModalProducts'])->name('products.modal');
        Route::post('/messages/{customer}/send-products', [\App\Http\Controllers\Client\MessagesController::class, 'sendProductCarousel'])->name('messages.send-products');
        
        // Products API for order creation (page-specific)
        Route::get('/api/products/{pageId}', [\App\Http\Controllers\Client\ProductController::class, 'getProductsJson'])->name('api.products');
        
        // Workflow Management routes
        Route::prefix('workflows')->name('workflows.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Client\WorkflowController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Client\WorkflowController::class, 'create'])->name('create');
            Route::post('/create-from-template', [\App\Http\Controllers\Client\WorkflowController::class, 'createFromTemplate'])->name('create-from-template');
            Route::post('/', [\App\Http\Controllers\Client\WorkflowController::class, 'store'])->name('store');
            Route::get('/{workflow}', [\App\Http\Controllers\Client\WorkflowController::class, 'show'])->name('show');
            Route::get('/{workflow}/edit', [\App\Http\Controllers\Client\WorkflowController::class, 'edit'])->name('edit');
            Route::put('/{workflow}', [\App\Http\Controllers\Client\WorkflowController::class, 'update'])->name('update');
            Route::delete('/{workflow}', [\App\Http\Controllers\Client\WorkflowController::class, 'destroy'])->name('destroy');
            
            // Workflow actions
            Route::patch('/{workflow}/publish', [\App\Http\Controllers\Client\WorkflowController::class, 'publish'])->name('publish');
            Route::patch('/{workflow}/unpublish', [\App\Http\Controllers\Client\WorkflowController::class, 'unpublish'])->name('unpublish');
            
            // Analytics
            Route::get('/{workflow}/analytics', [\App\Http\Controllers\Client\WorkflowController::class, 'analytics'])->name('analytics');
        });
        
        // Chatbot & FAQ Management routes  
        Route::prefix('chat-bot')->name('chat-bot.')->group(function () {
            // FAQ Management
            Route::prefix('faqs')->name('faqs.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Client\ChatBot\FaqController::class, 'index'])->name('index');
                Route::get('/create', [\App\Http\Controllers\Client\ChatBot\FaqController::class, 'create'])->name('create');
                Route::post('/', [\App\Http\Controllers\Client\ChatBot\FaqController::class, 'store'])->name('store');
                Route::get('/{faq}/edit', [\App\Http\Controllers\Client\ChatBot\FaqController::class, 'edit'])->name('edit');
                Route::put('/{faq}', [\App\Http\Controllers\Client\ChatBot\FaqController::class, 'update'])->name('update');
                Route::delete('/{faq}', [\App\Http\Controllers\Client\ChatBot\FaqController::class, 'destroy'])->name('destroy');
                
                // FAQ Actions
                Route::patch('/{faq}/toggle-status', [\App\Http\Controllers\Client\ChatBot\FaqController::class, 'toggleStatus'])->name('toggle-status');
                Route::post('/reorder', [\App\Http\Controllers\Client\ChatBot\FaqController::class, 'reorder'])->name('reorder');
                Route::post('/quick-setup', [\App\Http\Controllers\Client\ChatBot\FaqController::class, 'quickSetup'])->name('quick-setup');
            });
            
            // Business Settings
            Route::prefix('settings')->name('settings.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Client\ChatBot\SettingsController::class, 'index'])->name('index');
                Route::post('/update', [\App\Http\Controllers\Client\ChatBot\SettingsController::class, 'update'])->name('update');
                Route::post('/reset-defaults', [\App\Http\Controllers\Client\ChatBot\SettingsController::class, 'resetDefaults'])->name('reset-defaults');
                Route::get('/preview/{language?}', [\App\Http\Controllers\Client\ChatBot\SettingsController::class, 'preview'])->name('preview');
            });
            
            // Customer Inquiries
            Route::prefix('inquiries')->name('inquiries.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Client\ChatBot\InquiryController::class, 'index'])->name('index');
                Route::get('/{inquiry}', [\App\Http\Controllers\Client\ChatBot\InquiryController::class, 'show'])->name('show');
                Route::patch('/{inquiry}/status', [\App\Http\Controllers\Client\ChatBot\InquiryController::class, 'updateStatus'])->name('update-status');
                Route::delete('/{inquiry}', [\App\Http\Controllers\Client\ChatBot\InquiryController::class, 'destroy'])->name('destroy');
                Route::patch('/{inquiry}/priority', [\App\Http\Controllers\Client\ChatBot\InquiryController::class, 'updatePriority'])->name('update-priority');
                Route::post('/{inquiry}/notes', [\App\Http\Controllers\Client\ChatBot\InquiryController::class, 'addNote'])->name('add-note');
                
                // Bulk actions
                Route::post('/bulk-update', [\App\Http\Controllers\Client\ChatBot\InquiryController::class, 'bulkUpdate'])->name('bulk-update');
                Route::get('/export/{format}', [\App\Http\Controllers\Client\ChatBot\InquiryController::class, 'export'])->name('export');
                
                // API endpoints for datatables
                Route::get('/api/list', [\App\Http\Controllers\Client\ChatBot\InquiryController::class, 'getInquiriesJson'])->name('api.list');
            });
        });
        
        // Orders Management routes
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Client\OrderController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Client\OrderController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Client\OrderController::class, 'store'])->name('store');
            Route::get('/{order}', [\App\Http\Controllers\Client\OrderController::class, 'show'])->name('show');
            Route::get('/{order}/edit', [\App\Http\Controllers\Client\OrderController::class, 'edit'])->name('edit');
            Route::put('/{order}', [\App\Http\Controllers\Client\OrderController::class, 'update'])->name('update');
            Route::delete('/{order}', [\App\Http\Controllers\Client\OrderController::class, 'destroy'])->name('destroy');
            Route::patch('/{order}/status', [\App\Http\Controllers\Client\OrderController::class, 'updateStatus'])->name('update-status');
            Route::get('/{order}/invoice', [\App\Http\Controllers\Client\OrderController::class, 'printInvoice'])->name('invoice');
            Route::post('/{order}/send-invoice', [\App\Http\Controllers\Client\OrderController::class, 'sendInvoiceToCustomer'])->name('send-invoice');
            Route::get('/export/excel', [\App\Http\Controllers\Client\OrderController::class, 'exportOrders'])->name('export');
            Route::get('/search-customers', [\App\Http\Controllers\Client\OrderController::class, 'searchCustomers'])->name('search-customers');
            Route::post('/update-customers-from-orders', [\App\Http\Controllers\Client\OrderController::class, 'updateCustomersFromOrders'])->name('update-customers-from-orders');
            Route::post('/update-customers-from-facebook', [\App\Http\Controllers\Client\OrderController::class, 'updateCustomersFromFacebook'])->name('update-customers-from-facebook');
            Route::post('/update-all-customer-data', [\App\Http\Controllers\Client\OrderController::class, 'updateAllCustomerData'])->name('update-all-customer-data');
        });
        
        Route::get('/services', function() { 
            $services = collect(); // Services will be loaded via the relationship once defined
            return view('client.services', compact('services')); 
        })->name('services');
        
        Route::get('/settings', function() { 
            return view('client.settings'); 
        })->name('settings');
        
        // Upgrade routes
        Route::prefix('upgrade')->name('upgrade.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Client\UpgradeController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Client\UpgradeController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Client\UpgradeController::class, 'store'])->name('store');
            Route::get('/{upgradeRequest}', [\App\Http\Controllers\Client\UpgradeController::class, 'show'])->name('show');
        });
        
        // Settings routes
        Route::put('/settings', [\App\Http\Controllers\Client\SettingsController::class, 'updateAccount'])->name('settings.update');
        Route::put('/settings/password', [\App\Http\Controllers\Client\SettingsController::class, 'updatePassword'])->name('password.update');
        Route::put('/settings/notifications', [\App\Http\Controllers\Client\SettingsController::class, 'updateNotifications'])->name('notifications.update');
        Route::put('/settings/business', [\App\Http\Controllers\Client\SettingsController::class, 'updateBusiness'])->name('business.update');
        Route::delete('/account', [\App\Http\Controllers\Client\SettingsController::class, 'deleteAccount'])->name('account.delete');
    });
});

// Facebook Webhook Routes (outside authentication)
Route::prefix('webhooks/facebook')->group(function () {
    Route::get('/', [FacebookWebhookController::class, 'verify']);
    Route::post('/', [FacebookWebhookController::class, 'handle']);
});

// Webhook test route to see if any requests hit the server
Route::any('/webhook-test', function(\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Log::info('WEBHOOK TEST ENDPOINT HIT', [
        'method' => $request->method(),
        'ip' => $request->ip(),
        'user_agent' => $request->userAgent(),
        'all_params' => $request->all(),
        'headers' => $request->headers->all(),
        'body' => $request->getContent()
    ]);
    return response('Webhook test endpoint - check logs', 200);
});

// Debug info endpoint
Route::get('/debug-webhook', function() {
    $pages = \App\Models\FacebookPage::with('client')->get();
    $clients = \App\Models\Client::withCount(['customers', 'facebookPages'])->get();
    
    $debugInfo = [
        'timestamp' => now(),
        'webhook_url' => url('/webhooks/facebook'),
        'test_url' => url('/webhook-test'),
        'app_url' => config('app.url'),
        'facebook_config' => [
            'app_id' => \App\Helpers\SettingsHelper::getFacebookAppId(),
            'webhook_verify_token' => \App\Helpers\SettingsHelper::getFacebookWebhookVerifyToken(),
            'app_secret_set' => !empty(\App\Helpers\SettingsHelper::getFacebookAppSecret())
        ],
        'clients' => $clients->map(function($client) {
            return [
                'name' => $client->name,
                'email' => $client->email,
                'customers_count' => $client->customers_count,
                'pages_count' => $client->facebook_pages_count
            ];
        }),
        'pages' => $pages->map(function($page) {
            return [
                'page_name' => $page->page_name,
                'page_id' => $page->page_id,
                'client_name' => $page->client->name ?? 'Unknown',
                'is_connected' => $page->is_connected,
                'has_access_token' => !empty($page->access_token)
            ];
        })
    ];
    
    return response()->json($debugInfo, 200, [], JSON_PRETTY_PRINT);
});
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Client\ClientAuthController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\FacebookController;
use App\Http\Controllers\FacebookWebhookController;

// Language switching route
Route::get('/set-language/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'bn'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('set-language');

// Landing Page
Route::middleware(['web', App\Http\Middleware\SetLanguage::class])->get('/', function () {
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

        // Language switcher
        Route::get('language/{locale}', function ($locale) {
            app()->setLocale($locale);
            session()->put('locale', $locale);
            return redirect()->back();
        })->name('language');

        // Order Management Routes (to be implemented)
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', function() { return view('admin.orders.index'); })->name('index');
            Route::get('/pending', function() { return view('admin.orders.pending'); })->name('pending');
        });

        // Service Management Routes (to be implemented)
        Route::prefix('services')->name('services.')->group(function () {
            Route::get('/', function() { return view('admin.services.index'); })->name('index');
            Route::get('/calendar', function() { return view('admin.services.calendar'); })->name('calendar');
        });

        // Reports Routes (to be implemented)
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/revenue', function() { return view('admin.reports.revenue'); })->name('revenue');
            Route::get('/clients', function() { return view('admin.reports.clients'); })->name('clients');
        });

        // Settings Routes (to be implemented)
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/general', function() { return view('admin.settings.general'); })->name('general');
            Route::get('/users', function() { return view('admin.settings.users'); })->name('users');
        });

        // Profile route
        Route::get('/profile', function() { return view('admin.profile'); })->name('profile');
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
            $customers = auth('client')->user()->customers()->latest()->get() ?? collect();
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
        
        Route::get('/orders', function() { 
            $orders = auth('client')->user()->orders()->latest()->get() ?? collect();
            return view('client.orders', compact('orders')); 
        })->name('orders');
        
        Route::get('/services', function() { 
            $services = auth('client')->user()->services()->latest()->get() ?? collect();
            return view('client.services', compact('services')); 
        })->name('services');
        
        Route::get('/settings', function() { 
            return view('client.settings'); 
        })->name('settings');
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
            'app_id' => config('services.facebook.app_id'),
            'webhook_verify_token' => config('services.facebook.webhook_verify_token'),
            'app_secret_set' => !empty(config('services.facebook.app_secret'))
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
<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\FacebookPage;
use App\Models\Customer;
use App\Services\FacebookGraphAPIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FacebookController extends Controller
{
    protected FacebookGraphAPIService $facebookService;

    public function __construct(FacebookGraphAPIService $facebookService)
    {
        $this->facebookService = $facebookService;
        $this->middleware('auth:client');
    }

    /**
     * Show Facebook pages management
     */
    public function index()
    {
        $client = Auth::guard('client')->user();
        $facebookPages = $client->facebookPages()->get();
        
        return view('client.facebook.index', compact('facebookPages'));
    }

    /**
     * Redirect to Facebook for authentication
     */
    public function connect()
    {
        $client = Auth::guard('client')->user();
        
        if (!$client->canAddNewPage()) {
            return back()->with('error', __('client.facebook.page_limit_reached'));
        }

        $redirectUri = route('client.facebook.callback');
        $loginUrl = $this->facebookService->getLoginUrl($redirectUri);
        
        Log::info('Redirecting client to Facebook for authentication', ['client_id' => $client->id]);
        return redirect($loginUrl);
    }

    /**
     * Handle Facebook callback
     */
    public function callback(Request $request)
    {
        $client = Auth::guard('client')->user();
        Log::info('Handling Facebook callback', ['client_id' => $client->id, 'request_data' => $request->all()]);

        if (!$request->state || $request->state !== csrf_token()) {
            Log::error('Facebook callback state mismatch', ['client_id' => $client->id]);
            return redirect()->route('client.facebook.index')->with('error', __('client.facebook.invalid_request'));
        }

        if ($request->has('error')) {
            Log::error('Facebook auth error during callback', [
                'error' => $request->error,
                'error_description' => $request->error_description,
                'client_id' => $client->id
            ]);
            return redirect()->route('client.facebook.index')->with('error', __('client.facebook.auth_cancelled'));
        }

        $tokenData = $this->facebookService->getAccessToken($request->code, route('client.facebook.callback'));

        if (!$tokenData || !isset($tokenData['access_token'])) {
            Log::error('Failed to get access token from Facebook', ['client_id' => $client->id]);
            return redirect()->route('client.facebook.index')->with('error', __('client.facebook.auth_failed'));
        }

        Log::info('Successfully obtained access token', ['client_id' => $client->id]);
        $pagesData = $this->facebookService->getUserPages($tokenData['access_token']);

        if (!$pagesData || !isset($pagesData['data'])) {
            Log::warning('No pages found for user', ['client_id' => $client->id]);
            return redirect()->route('client.facebook.index')->with('error', __('client.facebook.no_pages_found'));
        }

        session(['facebook_pages_data' => $pagesData['data']]);
        Log::info('Pages stored in session, redirecting to selection', ['client_id' => $client->id, 'page_count' => count($pagesData['data'])]);

        return redirect()->route('client.facebook.select-pages');
    }

    /**
     * Show page selection interface
     */
    public function selectPages()
    {
        $pagesData = session('facebook_pages_data');
        
        if (!$pagesData) {
            return redirect()->route('client.facebook.index')->with('error', __('client.facebook.session_expired'));
        }

        $client = Auth::guard('client')->user();
        $remainingSlots = $client->getRemainingPageSlots();

        return view('client.facebook.select-pages', compact('pagesData', 'remainingSlots'));
    }

    /**
     * Connect selected pages
     */
    public function connectPages(Request $request)
    {
        $request->validate([
            'selected_pages' => 'required|array|min:1',
            'selected_pages.*' => 'required|string'
        ]);

        $client = Auth::guard('client')->user();
        $pagesData = session('facebook_pages_data');

        if (!$pagesData) {
            Log::error('Session expired before page connection', ['client_id' => $client->id]);
            return redirect()->route('client.facebook.index')->with('error', __('client.facebook.session_expired'));
        }

        $connectedCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($request->selected_pages as $pageId) {
                if (!$client->canAddNewPage()) {
                    $errors[] = __('client.facebook.page_limit_reached');
                    break;
                }

                $pageData = collect($pagesData)->firstWhere('id', $pageId);
                
                if (!$pageData) {
                    $errors[] = __('client.facebook.invalid_page', ['page_id' => $pageId]);
                    continue;
                }

                if (FacebookPage::where('page_id', $pageId)->exists()) {
                    $existingPage = FacebookPage::where('page_id', $pageId)->first();
                    if ($existingPage->client_id === $client->id) {
                        $errors[] = __('client.facebook.page_already_connected', ['page_name' => $pageData['name']]);
                    } else {
                        $errors[] = __('client.facebook.page_connected_to_another_client', ['page_name' => $pageData['name']]);
                    }
                    continue;
                }

                $facebookPage = FacebookPage::create([
                    'client_id' => $client->id,
                    'page_id' => $pageData['id'],
                    'page_name' => $pageData['name'],
                    'access_token' => $pageData['access_token'],
                    'page_data' => [
                        'category' => $pageData['category'] ?? null,
                        'picture' => $pageData['picture']['data']['url'] ?? null,
                        'tasks' => $pageData['tasks'] ?? []
                    ],
                    'is_connected' => false
                ]);

                if ($this->setupPageWebhooks($facebookPage)) {
                    $facebookPage->update(['is_connected' => true]);
                    $connectedCount++;
                    Log::info('Successfully connected and subscribed page', ['page_id' => $pageId, 'client_id' => $client->id]);
                } else {
                    $errors[] = __('client.facebook.webhook_setup_failed', ['page_name' => $pageData['name']]);
                    Log::error('Webhook setup failed for page', ['page_id' => $pageId, 'client_id' => $client->id]);
                }
            }

            DB::commit();
            session()->forget('facebook_pages_data');

            $message = __('client.facebook.pages_connected', ['count' => $connectedCount]);
            if (!empty($errors)) {
                $message .= ' ' . __('client.facebook.some_errors_occurred');
            }

            return redirect()->route('client.facebook.index')
                ->with($connectedCount > 0 ? 'success' : 'warning', $message)
                ->with('page_connection_errors', $errors);

        } catch (\Exception $e) {
            DB::rollback();
            Log::critical('Fatal error during page connection', [
                'client_id' => $client->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('client.facebook.index')->with('error', __('client.facebook.connection_failed'));
        }
    }
    
    /**
     * Setup webhooks for a Facebook page. This is a critical step.
     */
    protected function setupPageWebhooks(FacebookPage $facebookPage): bool
    {
        try {
            Log::info('Attempting to subscribe page to webhooks', ['page_id' => $facebookPage->page_id]);
            
            $isSubscribed = $this->facebookService->subscribeToWebhooks(
                $facebookPage->page_id,
                $facebookPage->access_token
            );

            if ($isSubscribed) {
                Log::info('Successfully subscribed page to webhooks', ['page_id' => $facebookPage->page_id]);
                return true;
            }

            Log::error('Failed to subscribe page to webhooks, Facebook API returned false.', [
                'page_id' => $facebookPage->page_id,
                'client_id' => $facebookPage->client_id
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Exception during webhook subscription', [
                'page_id' => $facebookPage->page_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Disconnect a Facebook page
     */
    public function disconnect(FacebookPage $facebookPage)
    {
        $this->authorize('manage', $facebookPage);
        
        // Optional: Add logic to unsubscribe from webhooks before disconnecting
        // $this->facebookService->unsubscribeFromWebhooks($facebookPage->page_id, $facebookPage->access_token);

        $facebookPage->update([
            'is_connected' => false,
            'access_token' => null, // Or encrypt it if you want to keep it
        ]);

        Log::info('Disconnected page', ['page_id' => $facebookPage->page_id, 'client_id' => $facebookPage->client_id]);

        return back()->with('success', __('client.facebook.page_disconnected', [
            'page_name' => $facebookPage->page_name
        ]));
    }

    /**
     * Test page connection
     */
    public function testConnection(FacebookPage $facebookPage)
    {
        $this->authorize('manage', $facebookPage);

        if ($this->facebookService->testPageConnection($facebookPage)) {
            return back()->with('success', __('client.facebook.connection_test_success', [
                'page_name' => $facebookPage->page_name
            ]));
        }

        return back()->with('error', __('client.facebook.connection_test_failed', [
            'page_name' => $facebookPage->page_name
        ]));
    }

    /**
     * Sync messages for a specific page
     */
    public function syncMessages(FacebookPage $facebookPage)
    {
        $this->authorize('manage', $facebookPage);

        try {
            $result = $this->facebookService->syncAllPageMessages($facebookPage, 50);

            if (isset($result['error'])) {
                return back()->with('error', __('client.facebook.sync_failed', ['error' => $result['error']]));
            }

            $message = __('client.facebook.sync_success', [
                'page_name' => $facebookPage->page_name,
                'customers' => $result['synced_customers'] ?? 0
            ]);

            return back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Manual message sync error', [
                'page_id' => $facebookPage->page_id,
                'client_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return back()->with('error', __('client.facebook.sync_failed', ['error' => 'Unexpected error occurred']));
        }
    }

    /**
     * Sync messages for specific customer
     */
    public function syncCustomerMessages(FacebookPage $facebookPage, Customer $customer)
    {
        $this->authorize('manage', $facebookPage);
        if ($customer->client_id !== Auth::id()) {
            abort(403);
        }

        try {
            $result = $this->facebookService->syncCustomerMessages($facebookPage, $customer, 100);

            if (isset($result['error'])) {
                return response()->json(['success' => false, 'message' => $result['error']]);
            }

            return response()->json([
                'success' => true,
                'message' => __('client.facebook.customer_sync_success'),
                'synced_count' => $result['synced_count'] ?? 0,
                'messages' => $result['messages'] ?? []
            ]);

        } catch (\Exception $e) {
            Log::error('Customer message sync error', [
                'page_id' => $facebookPage->page_id,
                'customer_id' => $customer->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to sync customer messages']);
        }
    }
}

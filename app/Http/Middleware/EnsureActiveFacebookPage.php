<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\FacebookPage;

class EnsureActiveFacebookPage
{
    /**
     * Routes that require page selection
     */
    protected $pageRequiredRoutes = [
        'client.messages*',
        'client.customers*', 
        'client.products*',
        'client.workflows*',
        'client.orders*'
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for non-authenticated client requests
        if (!auth('client')->check()) {
            return $next($request);
        }
        
        // Check if this route needs page selection
        $needsPageSelection = false;
        foreach ($this->pageRequiredRoutes as $pattern) {
            if ($request->routeIs($pattern)) {
                $needsPageSelection = true;
                break;
            }
        }
        
        // If route doesn't need page selection, continue
        if (!$needsPageSelection) {
            return $next($request);
        }
        
        $client = auth('client')->user();
        
        // Check if client has any connected Facebook pages
        $connectedPages = FacebookPage::where('client_id', $client->id)
            ->where('is_connected', true)
            ->get();
            
        // If no connected pages, redirect to Facebook setup
        if ($connectedPages->isEmpty()) {
            return redirect()->route('client.facebook.index')
                ->with('error', 'Please connect a Facebook page first to access this feature.');
        }
        
        $activePageId = getActiveSessionPageId();
        
        // If no active page is set, auto-select the first connected page
        if (!$activePageId) {
            $firstConnectedPage = $connectedPages->first();
            setActiveSessionPageId($firstConnectedPage->id);
        } else {
            // Validate that the selected page still exists and is connected
            $selectedPage = $connectedPages->where('id', $activePageId)->first();
                
            if (!$selectedPage) {
                // Reset to first available page if selected page is no longer valid
                $firstConnectedPage = $connectedPages->first();
                setActiveSessionPageId($firstConnectedPage->id);
            }
        }
        
        return $next($request);
    }
}
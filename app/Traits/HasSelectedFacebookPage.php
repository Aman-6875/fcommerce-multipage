<?php

namespace App\Traits;

use App\Models\FacebookPage;
use Illuminate\Support\Facades\Auth;

trait HasSelectedFacebookPage
{
    /**
     * Get the currently selected Facebook page from session
     */
    protected function getSelectedFacebookPage(): ?FacebookPage
    {
        $client = Auth::guard('client')->user();
        
        if (!$client) {
            return null;
        }
        
        $selectedPageId = session('selected_facebook_page_id');
        
        if (!$selectedPageId) {
            // If no page is selected, auto-select the first connected page
            $firstPage = $client->facebookPages()
                ->where('is_connected', true)
                ->first();
                
            if ($firstPage) {
                session(['selected_facebook_page_id' => $firstPage->id]);
                return $firstPage;
            }
            
            return null;
        }
        
        return $client->facebookPages()
            ->where('id', $selectedPageId)
            ->where('is_connected', true)
            ->first();
    }

    /**
     * Get all connected Facebook pages for the current client
     */
    protected function getConnectedFacebookPages()
    {
        $client = Auth::guard('client')->user();
        
        if (!$client) {
            return collect();
        }
        
        return $client->facebookPages()
            ->where('is_connected', true)
            ->get();
    }

    /**
     * Check if a page is selected
     */
    protected function hasSelectedPage(): bool
    {
        return $this->getSelectedFacebookPage() !== null;
    }
}
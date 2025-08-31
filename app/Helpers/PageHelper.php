<?php

if (!function_exists('getActiveSessionPageId')) {
    function getActiveSessionPageId()
    {
        return session('selected_facebook_page_id');
    }
}

if (!function_exists('setActiveSessionPageId')) {
    function setActiveSessionPageId($pageId)
    {
        if ($pageId === null) {
            session()->forget('selected_facebook_page_id');
        } else {
            session(['selected_facebook_page_id' => $pageId]);
        }
    }
}

if (!function_exists('hasActiveSessionPage')) {
    function hasActiveSessionPage()
    {
        $pageId = getActiveSessionPageId();
        return !empty($pageId);
    }
}
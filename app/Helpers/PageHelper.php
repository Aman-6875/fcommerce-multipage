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
        session(['selected_facebook_page_id' => $pageId]);
    }
}
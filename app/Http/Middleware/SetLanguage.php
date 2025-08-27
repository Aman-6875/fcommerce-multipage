<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $availableLocales = ['en', 'bn'];
        $defaultLocale = 'bn';
        
        // Check for locale in URL parameter
        if ($request->has('lang') && in_array($request->get('lang'), $availableLocales)) {
            $locale = $request->get('lang');
            session(['locale' => $locale]);
        }
        // Check for locale in session
        elseif (session()->has('locale') && in_array(session('locale'), $availableLocales)) {
            $locale = session('locale');
        }
        // Check for client's language preference
        elseif (auth('client')->check() && auth('client')->user()->settings) {
            $clientLang = auth('client')->user()->settings['language'] ?? $defaultLocale;
            $locale = in_array($clientLang, $availableLocales) ? $clientLang : $defaultLocale;
        }
        // Use default locale
        else {
            $locale = $defaultLocale;
        }
        
        app()->setLocale($locale);
        
        return $next($request);
    }
}

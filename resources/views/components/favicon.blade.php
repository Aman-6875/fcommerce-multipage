{{-- Favicon and App Icon References --}}
<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicons/favicon-16x16.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicons/favicon-48x48.png') }}">
<link rel="icon" type="image/png" sizes="64x64" href="{{ asset('favicons/favicon-64x64.png') }}">
<link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicons/favicon-96x96.png') }}">
<link rel="icon" type="image/png" sizes="128x128" href="{{ asset('favicons/favicon-128x128.png') }}">

{{-- Apple Touch Icons --}}
<link rel="apple-touch-icon" href="{{ asset('favicons/apple-touch-icon.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicons/apple-touch-icon.png') }}">

{{-- Android Chrome Icons --}}
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicons/android-chrome-192x192.png') }}">
<link rel="icon" type="image/png" sizes="512x512" href="{{ asset('favicons/android-chrome-512x512.png') }}">

{{-- Web App Manifest --}}
<link rel="manifest" href="{{ asset('manifest.json') }}">

{{-- Microsoft Tiles --}}
<meta name="msapplication-TileImage" content="{{ asset('favicons/android-chrome-192x192.png') }}">
<meta name="msapplication-TileColor" content="#667eea">

{{-- Theme Colors --}}
<meta name="theme-color" content="#667eea">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'FB Auto') }}">
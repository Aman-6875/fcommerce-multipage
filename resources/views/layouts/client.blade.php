<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>
    
    {{-- Favicons and App Icons --}}
    @include('components.favicon')

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('css/bootstrap1.min.css') }}" />
    <!-- themefy CSS -->
    <link rel="stylesheet" href="{{ asset('vendors/themefy_icon/themify-icons.css') }}" />
    <!-- select2 CSS -->
    <link rel="stylesheet" href="{{ asset('vendors/niceselect/css/nice-select.css') }}" />
    <!-- owl carousel CSS -->
    <link rel="stylesheet" href="{{ asset('vendors/owl_carousel/css/owl.carousel.css') }}" />
    <!-- gijgo css -->
    <link rel="stylesheet" href="{{ asset('vendors/gijgo/gijgo.min.css') }}" />
    <!-- font awesome CSS -->
    <link rel="stylesheet" href="{{ asset('vendors/font_awesome/css/all.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendors/tagsinput/tagsinput.css') }}" />
    <!-- date picker -->
    <link rel="stylesheet" href="{{ asset('vendors/datepicker/date-picker.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendors/vectormap-home/vectormap-2.0.2.css') }}" />
    <!-- scrollabe  -->
    <link rel="stylesheet" href="{{ asset('vendors/scroll/scrollable.css') }}" />
    <!-- datatable CSS -->
    <link rel="stylesheet" href="{{ asset('vendors/datatable/css/jquery.dataTables.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendors/datatable/css/responsive.dataTables.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendors/datatable/css/buttons.dataTables.min.css') }}" />
    <!-- text editor css -->
    <link rel="stylesheet" href="{{ asset('vendors/text_editor/summernote-bs4.css') }}" />
    <!-- morris css -->
    <link rel="stylesheet" href="{{ asset('vendors/morris/morris.css') }}">
    <!-- metarial icon css -->
    <link rel="stylesheet" href="{{ asset('vendors/material_icon/material-icons.css') }}" />
    <!-- menu css  -->
    <link rel="stylesheet" href="{{ asset('css/metisMenu.css') }}">
    <!-- style CSS -->
    <link rel="stylesheet" href="{{ asset('css/style1.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/colors/default.css') }}" id="colorSkinCSS">
    
    <!-- Custom Modal Fix CSS -->
    <style>
        /* AGGRESSIVE Modal Fix - Override template's extreme z-index values */
        .modal {
            z-index: 999999999 !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
        }
        
        .modal-backdrop {
            z-index: 999999998 !important;
            background-color: rgba(0, 0, 0, 0.1) !important; /* Very light backdrop */
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            pointer-events: none !important; /* Allow clicks to pass through */
        }
        
        .modal.show {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            pointer-events: auto !important; /* Enable clicks on modal itself */
        }
        
        .modal-dialog {
            z-index: 999999999 !important;
            position: relative !important;
            margin: 50px auto 30px auto !important;
            pointer-events: auto !important;
            background-color: transparent !important;
            width: 90vw !important;
            max-width: 500px !important;
        }
        
        /* AGGRESSIVE modal width override */
        .modal-dialog.modal-lg,
        #importModal .modal-dialog {
            width: 90vw !important;
            max-width: 800px !important;
            min-width: 600px !important;
        }
        
        /* Force responsive modal sizing with viewport units */
        @media (min-width: 576px) {
            .modal-dialog {
                width: 80vw !important;
                max-width: 600px !important;
                margin: 60px auto 1.75rem auto !important;
            }
            
            .modal-dialog.modal-lg,
            #importModal .modal-dialog {
                width: 70vw !important;
                max-width: 700px !important;
                min-width: 600px !important;
            }
        }
        
        @media (min-width: 768px) {
            .modal-dialog.modal-lg,
            #importModal .modal-dialog {
                width: 60vw !important;
                max-width: 750px !important;
                min-width: 650px !important;
            }
        }
        
        @media (min-width: 992px) {
            .modal-dialog.modal-lg,
            #importModal .modal-dialog {
                width: 50vw !important;
                max-width: 850px !important;
                min-width: 700px !important;
            }
        }
        
        @media (min-width: 1200px) {
            .modal-dialog.modal-lg,
            #importModal .modal-dialog {
                width: 40vw !important;
                max-width: 900px !important;
                min-width: 800px !important;
            }
        }
        
        .modal-content {
            background-color: #fff !important;
            border: none !important;
            border-radius: 8px !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
            pointer-events: auto !important;
            position: relative !important;
            z-index: 1000000000 !important;
            width: 100% !important;
            min-height: 300px !important;
        }
        
        .modal-header {
            background-color: #f8f9fa !important;
            border-bottom: 1px solid #dee2e6 !important;
            border-radius: 8px 8px 0 0 !important;
        }
        
        .modal-header .btn-close {
            background: none !important;
            border: none !important;
            font-size: 1.25rem !important;
            opacity: 0.5 !important;
        }
        
        .modal-header .btn-close:hover {
            opacity: 1 !important;
        }
        
        .modal-body {
            padding: 20px !important;
        }
        
        .modal-footer {
            background-color: #f8f9fa !important;
            border-top: 1px solid #dee2e6 !important;
            border-radius: 0 0 8px 8px !important;
            padding: 15px 20px !important;
        }
        
        /* Fix for template CSS conflicts */
        body.modal-open {
            overflow: hidden !important;
        }
        
        /* Ensure modal is clickable - remove backdrop interference */
        .modal-backdrop.show {
            opacity: 0.1 !important;
            pointer-events: none !important;
        }
        
        .modal-backdrop.fade {
            opacity: 0 !important;
        }
        
        /* Fix form elements in modal */
        .modal .form-control {
            background-color: #fff !important;
            border: 1px solid #ced4da !important;
            color: #495057 !important;
        }
        
        .modal .form-control:focus {
            border-color: #80bdff !important;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
        }
        
        /* Fix select dropdown in modal */
        .modal select.form-control {
            appearance: auto !important;
            background-image: none !important;
        }
        
        /* Ensure all modal elements are clickable */
        .modal * {
            pointer-events: auto !important;
        }
        
        .modal input,
        .modal select,
        .modal textarea,
        .modal button {
            pointer-events: auto !important;
            position: relative !important;
            z-index: 1000000001 !important;
        }
        
        /* Fix button styles in modal */
        .modal .btn-primary {
            background-color: #007bff !important;
            border-color: #007bff !important;
            color: #fff !important;
        }
        
        .modal .btn-primary:hover {
            background-color: #0056b3 !important;
            border-color: #0056b3 !important;
        }
        
        .modal .btn-secondary {
            background-color: #6c757d !important;
            border-color: #6c757d !important;
            color: #fff !important;
        }
        
        /* Fix any sidebar overlay issues */
        .main_content.dashboard_part {
            z-index: auto !important;
        }
        
        .sidebar {
            z-index: 1030 !important;
        }
        
        /* Removed duplicate modal-dialog styles - using the ones defined above */
        
        /* Fix for Bengali text in modal */
        .modal .form-label,
        .modal .form-control,
        .modal .btn {
            font-family: inherit !important;
        }
        
        /* Override template's custom modal styles */
        .cs_modal .modal-content,
        .modal-content {
            background-color: #fff !important;
            border: 1px solid rgba(0, 0, 0, 0.2) !important;
            border-radius: 8px !important;
            box-shadow: 0 3px 9px rgba(0, 0, 0, 0.5) !important;
            outline: 0 !important;
        }
        
        .cs_modal .modal-header,
        .modal-header {
            background-color: #f8f9fa !important;
            border-bottom: 1px solid #dee2e6 !important;
            padding: 15px 20px !important;
            border-top-left-radius: 7px !important;
            border-top-right-radius: 7px !important;
        }
        
        .cs_modal .modal-header h5,
        .modal-header .modal-title {
            color: #333 !important;
            font-weight: 600 !important;
            margin: 0 !important;
        }
        
        .cs_modal .modal-body,
        .modal-body {
            padding: 20px !important;
            background-color: #fff !important;
        }
        
        /* Fix sidebar z-index conflict */
        .vertical-scroll.ps-container.ps-theme-default.ps-active-y {
            z-index: 1020 !important;
        }
        
        /* Fix for very high z-index elements in template */
        .alert,
        .dropdown-menu,
        .tooltip,
        .popover {
            z-index: 1030 !important;
        }
        
        /* Force modal to be on top with extreme z-index */
        .modal.fade.show {
            z-index: 999999999 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        
        .modal-backdrop.fade.show {
            z-index: 999999998 !important;
            opacity: 0.5 !important;
        }
        
        /* Disable template elements that might interfere */
        #back-top {
            z-index: 99 !important;
        }
        
        .nice_Select .list,
        .nice_Select2 .list {
            z-index: 9999 !important;
        }
        
        /* Force modal interaction */
        body.modal-open .modal {
            overflow-x: hidden !important;
            overflow-y: auto !important;
        }
        
        /* Prevent any sidebar interference */
        .sidebar.vertical-scroll.ps-container.ps-theme-default.ps-active-y {
            z-index: 999 !important;
        }
        
        /* Ensure modal is clickable */
        .modal-dialog {
            cursor: default !important;
        }
        
        .modal-content * {
            pointer-events: auto !important;
        }
        
        /* NUCLEAR OPTION - Force modal visibility and interaction */
        body.modal-open .modal.show {
            display: flex !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 2147483647 !important; /* Maximum possible z-index */
            pointer-events: auto !important;
            background-color: rgba(0, 0, 0, 0.3) !important; /* Lighter backdrop */
            align-items: center !important;
            justify-content: center !important;
        }
        
        body.modal-open .modal.show .modal-dialog {
            z-index: 2147483647 !important;
            pointer-events: auto !important;
            position: relative !important;
        }
        
        body.modal-open .modal.show .modal-content {
            background-color: #ffffff !important;
            border-radius: 12px !important;
            pointer-events: auto !important;
            position: relative !important;
            z-index: 2147483647 !important;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2) !important;
            border: none !important;
            overflow: hidden !important;
        }
        
        /* Force all modal elements to be interactive */
        .modal.show * {
            pointer-events: auto !important;
        }
        
        /* Override any template CSS that might set pointer-events: none */
        .modal input,
        .modal select,
        .modal textarea,
        .modal button,
        .modal a {
            pointer-events: auto !important;
        }
        
        /* Reduce topbar height to prevent modal title hiding */
        .header_iner {
            padding: 5px 15px !important;
            min-height: 40px !important;
            height: 40px !important;
        }
        
        .plan_status .alert {
            padding: 4px 8px !important;
            margin-bottom: 0 !important;
            font-size: 12px !important;
        }
        
        .language_switcher .btn {
            padding: 4px 8px !important;
            font-size: 12px !important;
        }
        
        .header_notification_warp .bell_notification_clicker {
            padding: 8px !important;
        }
        
        .profile_info img {
            width: 35px !important;
            height: 35px !important;
        }
        
        .profile_info_iner {
            margin-left: 8px !important;
        }
        
        .profile_author_name h5 {
            font-size: 14px !important;
            margin-bottom: 0 !important;
        }
        
        .profile_author_name p {
            font-size: 11px !important;
            margin-bottom: 0 !important;
        }
        
        /* Additional modal positioning fixes */
        .modal.show {
            padding-top: 60px !important;
        }
        
        .modal-dialog.modal-xl {
            margin: 60px auto 30px auto !important;
        }
        
        @media (max-width: 575px) {
            .modal.show {
                padding-top: 50px !important;
            }
            .modal-dialog {
                margin: 50px 10px 30px 10px !important;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body class="crm_body_bg">

<!-- sidebar  -->
<nav class="sidebar vertical-scroll ps-container ps-theme-default ps-active-y">
    <div class="logo d-flex justify-content-between">
        <a href="{{ route('client.dashboard') }}">
            <img src="{{ asset('img/logo.png') }}" alt="{{ config('app.name') }}">
        </a>
        <div class="sidebar_close_icon d-lg-none">
            <i class="ti-close"></i>
        </div>
    </div>
    
    <!-- Facebook Page Switcher -->
    @php
        $connectedPages = auth('client')->user()->facebookPages()->where('is_connected', true)->get();
        $selectedPageId = getActiveSessionPageId();
        $selectedPage = null;
        
        // Only process if client has connected pages
        if ($connectedPages->count() > 0) {
            $selectedPage = $connectedPages->where('id', $selectedPageId)->first();
            
            // If no page is selected, auto-select the first one
            if (!$selectedPage) {
                $selectedPage = $connectedPages->first();
                if ($selectedPage) {
                    setActiveSessionPageId($selectedPage->id);
                }
            }
        }
    @endphp
    
    @if($connectedPages->count() > 0 && $selectedPage)
    <div class="page-switcher" style="padding: 15px; border-bottom: 1px solid #e6e6e6; margin-bottom: 10px;">
        <div style="margin-bottom: 8px;">
            <small style="color: #888; font-size: 11px; text-transform: uppercase; font-weight: 600;">WORKING ON</small>
        </div>
        
        @if($selectedPage)
        @php
            $pagePicture = $selectedPage->page_data['picture'] ?? 'https://via.placeholder.com/32';
        @endphp
        <div class="current-page" style="display: flex; align-items: center; margin-bottom: 10px;">
            <img src="{{ $pagePicture }}" 
                 style="width: 24px; height: 24px; border-radius: 50%; margin-right: 8px;" 
                 alt="{{ $selectedPage->page_name }}">
            <div style="flex: 1; min-width: 0;">
                <div style="font-size: 13px; font-weight: 600; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    {{ $selectedPage->page_name }}
                </div>
            </div>
        </div>
        @endif
        
        @if($connectedPages->count() > 1)
        <form method="POST" action="{{ route('client.facebook.select-page') }}" style="margin: 0;">
            @csrf
            <select name="page_id" class="form-control form-control-sm" style="font-size: 12px;" onchange="this.form.submit()">
                @foreach($connectedPages as $page)
                <option value="{{ $page->id }}" {{ $selectedPage && $selectedPage->id === $page->id ? 'selected' : '' }}>
                    {{ Str::limit($page->page_name, 25) }}
                </option>
                @endforeach
            </select>
        </form>
        @endif
    </div>
    @endif
    
    <ul id="sidebar_menu">
        <li class="{{ request()->routeIs('client.dashboard') ? 'mm-active' : '' }}">
            <a href="{{ route('client.dashboard') }}" aria-expanded="false">
                <div class="icon_menu">
                    <img src="{{ asset('img/menu-icon/dashboard.svg') }}" alt="">
                </div>
                <span>{{ __('client.dashboard') }}</span>
            </a>
        </li>
        
        <li class="{{ request()->routeIs('client.facebook-pages') ? 'mm-active' : '' }}">
            <a href="{{ route('client.facebook-pages') }}" aria-expanded="false">
                <div class="icon_menu">
                    <img src="{{ asset('img/menu-icon/2.svg') }}" alt="">
                </div>
                <span>{{ __('client.facebook_pages') }}</span>
                @if(auth('client')->user()->facebookPages()->where('is_connected', false)->count() > 0)
                    <span class="badge badge-warning">{{ auth('client')->user()->facebookPages()->where('is_connected', false)->count() }}</span>
                @endif
            </a>
        </li>

        <li class="{{ request()->routeIs('client.customers') ? 'mm-active' : '' }}">
            <a href="{{ route('client.customers') }}" aria-expanded="false">
                <div class="icon_menu">
                    <img src="{{ asset('img/menu-icon/3.svg') }}" alt="">
                </div>
                <span>{{ __('client.customers') }}</span>
                <span class="badge badge-info">{{ auth('client')->user()->customers()->count() }}</span>
            </a>
        </li>

        <li class="{{ request()->routeIs('client.messages') ? 'mm-active' : '' }}">
            <a href="{{ route('client.messages') }}" aria-expanded="false">
                <div class="icon_menu">
                    <img src="{{ asset('img/icon/msg.svg') }}" alt="">
                </div>
                <span>{{ __('client.messages') }}</span>
                @php
                    $unreadCount = auth('client')->user()->customers()->withCount(['messages as unread_count' => function($query) {
                        $query->where('is_read', false)->where('message_type', 'incoming');
                    }])->get()->sum('unread_count');
                @endphp
                @if($unreadCount > 0)
                    <span class="badge badge-danger">{{ $unreadCount }}</span>
                @endif
            </a>
        </li>

        <li class="{{ request()->routeIs('client.products*') ? 'mm-active' : '' }}">
            <a href="{{ route('client.products.index') }}" aria-expanded="false">
                <div class="icon_menu">
                    <i class="fas fa-shopping-bag" style="font-size: 18px; color: #667eea;"></i>
                </div>
                <span>Products</span>
                <span class="badge badge-info">{{ auth('client')->user()->products()->where('is_active', true)->count() }}</span>
            </a>
        </li>

        <li class="{{ request()->routeIs('client.workflows*') ? 'mm-active' : '' }}">
            <a href="{{ route('client.workflows.index') }}" aria-expanded="false">
                <div class="icon_menu">
                    <i class="fas fa-project-diagram" style="font-size: 18px; color: #4facfe;"></i>
                </div>
                <span>Workflows</span>
                @php
                    $activeWorkflows = auth('client')->user()->workflows()->where('is_active', true)->count();
                @endphp
                @if($activeWorkflows > 0)
                    <span class="badge badge-success">{{ $activeWorkflows }}</span>
                @endif
            </a>
        </li>

        <li class="{{ request()->routeIs('client.orders*') ? 'mm-active' : '' }}">
            <a href="{{ route('client.orders.index') }}" aria-expanded="false">
                <div class="icon_menu">
                    <img src="{{ asset('img/menu-icon/6.svg') }}" alt="">
                </div>
                <span>{{ __('client.orders') }}</span>
                @if(auth('client')->user()->orders()->where('status', 'pending')->count() > 0)
                    <span class="badge badge-warning">{{ auth('client')->user()->orders()->where('status', 'pending')->count() }}</span>
                @endif
            </a>
        </li>

        <li class="{{ request()->routeIs('client.services') ? 'mm-active' : '' }}">
            <a href="{{ route('client.services') }}" aria-expanded="false">
                <div class="icon_menu">
                    <img src="{{ asset('img/menu-icon/7.svg') }}" alt="">
                </div>
                <span>{{ __('client.services') }}</span>
            </a>
        </li>

        @if(auth('client')->user()->isPremium())
            <li class="{{ request()->routeIs('client.automation') ? 'mm-active' : '' }}">
                <a href="#" aria-expanded="false">
                    <div class="icon_menu">
                        <img src="{{ asset('img/menu-icon/8.svg') }}" alt="">
                    </div>
                    <span>{{ __('client.automation') }}</span>
                    <span class="badge badge-success">Premium</span>
                </a>
            </li>
        @endif

        <li class="{{ request()->routeIs('client.settings') ? 'mm-active' : '' }}">
            <a href="{{ route('client.settings') }}" aria-expanded="false">
                <div class="icon_menu">
                    <img src="{{ asset('img/menu-icon/9.svg') }}" alt="">
                </div>
                <span>{{ __('common.settings') }}</span>
            </a>
        </li>
    </ul>
</nav>

<!-- main content part here -->
<section class="main_content dashboard_part large_header_bg">
    <!-- menu  -->
    <div class="container-fluid g-0">
        <div class="row">
            <div class="col-lg-12 p-0 ">
                <div class="header_iner d-flex justify-content-between align-items-center">
                    <div class="sidebar_icon d-lg-none">
                        <i class="ti-menu"></i>
                    </div>
                    
                    <!-- Plan Status & Active Page Info -->
                    <div class="plan_status d-flex align-items-center">
                        @if(auth('client')->user()->isFree())
                            @php
                                $trialDaysLeft = max(0, 10 - floor(auth('client')->user()->created_at->diffInDays(now())));
                                $hasReachedLimits = auth('client')->user()->hasReachedFreeLimits();
                            @endphp
                            @if($hasReachedLimits)
                                <div class="alert alert-danger alert-sm me-3">
                                    <strong>{{ __('client.trial_expired') }}!</strong> <a href="{{ route('client.upgrade.index') }}" class="btn btn-sm btn-light">{{ __('client.upgrade_now') }}</a>
                                </div>
                            @else
                                <div class="alert alert-warning alert-sm me-3">
                                    <strong>{{ __('client.free_trial') }}:</strong> {{ $trialDaysLeft }} {{ __('client.days_left') }}
                                </div>
                            @endif
                        @else
                            <div class="alert alert-success alert-sm me-3">
                                <i class="fas fa-crown"></i> {{ ucfirst(auth('client')->user()->plan_type) }} Plan
                            </div>
                        @endif
                    </div>

                    <div class="header_right d-flex justify-content-between align-items-center">
                        <!-- Language Switcher -->
                        <div class="language_switcher d-flex align-items-center me-3">
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    {{ app()->getLocale() == 'bn' ? '🇧🇩 বাংলা' : '🇺🇸 English' }}
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                                    <li><a class="dropdown-item" href="{{ route('set-language', 'en') }}">🇺🇸 English</a></li>
                                    <li><a class="dropdown-item" href="{{ route('set-language', 'bn') }}">🇧🇩 বাংলা</a></li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="header_notification_warp d-flex align-items-center">
                            <li>
                                <a class="bell_notification_clicker nav-link-notify" href="#"> 
                                    <img src="{{ asset('img/icon/bell.svg') }}" alt="">
                                    @if(auth('client')->user()->orders()->where('status', 'pending')->count() > 0)
                                        <span class="notification_count">{{ auth('client')->user()->orders()->where('status', 'pending')->count() }}</span>
                                    @endif
                                </a>
                                <!-- Menu_NOtification_Wrap  -->
                                <div class="Menu_NOtification_Wrap">
                                    <div class="notification_Header">
                                        <h4>{{ __('common.notifications') }}</h4>
                                    </div>
                                    <div class="Notification_body">
                                        <!-- notification content will be loaded via AJAX -->
                                    </div>
                                </div>
                            </li>
                        </div>
                        <div class="profile_info">
                            <img src="{{ asset('img/customers/1.png') }}" alt="#">
                            <div class="profile_info_iner">
                                <div class="profile_author_name">
                                    <p>{{ ucfirst(auth('client')->user()->plan_type) }} Plan</p>
                                    <h5>{{ auth('client')->user()->name }}</h5>
                                </div>
                                <div class="profile_info_details">
                                    <a href="{{ route('client.profile') }}">{{ __('common.profile') }}</a>
                                    <a href="{{ route('client.settings') }}">{{ __('common.settings') }}</a>
                                    @if(auth('client')->user()->isFree())
                                        <a href="{{ route('client.upgrade.index') }}" class="text-warning"><i class="fas fa-crown"></i> {{ __('client.upgrade_now') }}</a>
                                    @endif
                                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">{{ __('common.logout') }}</a>
                                    <form id="logout-form" action="{{ route('client.logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- main body -->
    <div class="main_content_iner overly_inner">
        <div class="container-fluid p-0 ">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(auth('client')->user()->hasReachedFreeLimits())
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <h5><i class="fas fa-exclamation-triangle"></i> {{ __('client.upgrade_required') }}</h5>
                    <p>{{ __('client.free_limits_reached_message') }}</p>
                    <a href="{{ route('client.upgrade.index') }}" class="btn btn-warning">{{ __('client.view_plans') }}</a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('common.close') }}"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</section>

<!-- footer part -->
<div class="footer_part">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="footer_iner text-center">
                    <p>{{ date('Y') }} © {{ config('app.name') }} - Your Facebook Automation Partner</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jquery -->
<script src="{{ asset('js/jquery1-3.4.1.min.js') }}"></script>
<!-- popper js -->
<script src="{{ asset('js/popper1.min.js') }}"></script>
<!-- bootstrap js -->
<script src="{{ asset('js/bootstrap.min.js') }}"></script>
<!-- sidebar menu  -->
<script src="{{ asset('js/metisMenu.js') }}"></script>
<!-- scrollabe  -->
<script src="{{ asset('vendors/scroll/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('vendors/scroll/scrollable-custom.js') }}"></script>
<!-- custom js -->
<script src="{{ asset('js/custom.js') }}"></script>

<!-- AGGRESSIVE Modal Fix JavaScript -->
<script>
$(document).ready(function() {
    // AGGRESSIVE modal backdrop fixes
    $(document).on('show.bs.modal', '.modal', function () {
        var $modal = $(this);
        var zIndexModal = 999999999;
        var zIndexBackdrop = 999999998;
        
        // Force modal positioning
        $modal.css({
            'z-index': zIndexModal,
            'position': 'fixed',
            'top': '0',
            'left': '0',
            'width': '100%',
            'height': '100%',
            'display': 'flex',
            'align-items': 'center',
            'justify-content': 'center'
        });
        
        // Force backdrop positioning
        setTimeout(function() {
            $('.modal-backdrop').css({
                'z-index': zIndexBackdrop,
                'position': 'fixed',
                'top': '0',
                'left': '0',
                'width': '100%',
                'height': '100%',
                'background-color': 'rgba(0, 0, 0, 0.3)'
            });
        }, 50);
        
        // Ensure modal content is clickable
        $modal.find('.modal-dialog').css({
            'z-index': zIndexModal,
            'position': 'relative',
            'pointer-events': 'auto'
        });
        
        $modal.find('.modal-content').css({
            'pointer-events': 'auto',
            'background-color': '#fff'
        });
    });
    
    // Fix modal close issues
    $(document).on('hidden.bs.modal', '.modal', function () {
        // Ensure body scroll is restored
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    });
    
    // Prevent modal from closing when clicking inside modal content
    $(document).on('click', '.modal-dialog', function(e) {
        e.stopPropagation();
    });
    
    // Ensure backdrop click closes modal
    $(document).on('click', '.modal', function(e) {
        if (e.target === this) {
            $(this).modal('hide');
        }
    });
    
    // Fix for multiple modals
    $(document).on('hidden.bs.modal', '.modal', function () {
        if ($('.modal:visible').length) {
            $('body').addClass('modal-open');
        }
    });
    
    // Ensure form elements work in modal
    $(document).on('shown.bs.modal', '.modal', function () {
        $(this).find('input, select, textarea').first().focus();
        
        // Additional aggressive positioning after modal is shown
        $(this).css({
            'z-index': '999999999',
            'display': 'flex',
            'align-items': 'center',
            'justify-content': 'center'
        });
    });
    
    // Force modal above everything on any Bootstrap modal event
    $(document).on('show.bs.modal shown.bs.modal', '.modal', function() {
        $(this).css('z-index', '999999999');
        $('.modal-backdrop').css('z-index', '999999998');
    });
    
    // Emergency modal fix - if modal appears but is not clickable
    $(document).on('click', '[data-bs-toggle="modal"]', function(e) {
        e.preventDefault();
        var target = $(this).attr('data-bs-target') || $(this).attr('href');
        if (target) {
            var $modal = $(target);
            $modal.css({
                'z-index': '999999999',
                'position': 'fixed',
                'top': '0',
                'left': '0',
                'width': '100%',
                'height': '100%'
            });
            $modal.modal('show');
        }
    });
});

// Function to manually open modal (fallback)
function openModal(modalId) {
    var $modal = $('#' + modalId);
    $modal.css({
        'z-index': '999999999',
        'position': 'fixed',
        'top': '0',
        'left': '0',
        'width': '100%',
        'height': '100%'
    });
    $modal.modal('show');
}

// Function to manually close modal (fallback)
function closeModal(modalId) {
    $('#' + modalId).modal('hide');
}

// Emergency fix: If user reports modal still not working, call this function
function forceFixModal() {
    $('.modal').each(function() {
        $(this).css({
            'z-index': '999999999',
            'position': 'fixed',
            'top': '0',
            'left': '0',
            'width': '100%',
            'height': '100%'
        });
    });
    $('.modal-backdrop').css('z-index', '999999998');
}
</script>

@stack('scripts')

</body>
</html>
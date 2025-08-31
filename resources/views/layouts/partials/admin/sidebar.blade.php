
<nav class="sidebar vertical-scroll  ps-container ps-theme-default ps-active-y">
    <div class="logo d-flex justify-content-between">
        <a href="{{ route('admin.dashboard') }}"><img src="{{ asset('img/logo.png') }}" alt=""></a>
        <div class="sidebar_close_icon d-lg-none">
            <i class="ti-close"></i>
        </div>
    </div>
    <ul id="sidebar_menu">
        <li class="{{ request()->routeIs('admin.dashboard') ? 'mm-active' : '' }}">
          <a href="{{ route('admin.dashboard') }}"  aria-expanded="false">
          <div class="icon_menu">
              <img src="{{ asset('img/menu-icon/dashboard.svg') }}" alt="">
        </div>
            <span>@lang('admin.dashboard')</span>
          </a>
        </li>
        <li class="{{ request()->routeIs('admin.clients.*') ? 'mm-active' : '' }}">
            <a class="has-arrow" href="#" aria-expanded="false">
              <div class="icon_menu">
                  <img src="{{ asset('img/menu-icon/2.svg') }}" alt="">
              </div>
              <span>@lang('admin.client_management')</span>
            </a>
            <ul>
              <li><a href="{{ route('admin.clients.index') }}">@lang('admin.all_clients')</a></li>
              <li><a href="{{ route('admin.clients.premium') }}">@lang('admin.premium_clients')</a></li>
              <li><a href="{{ route('admin.clients.create') }}">@lang('admin.add_new_client')</a></li>
            </ul>
        </li>
        <li class="{{ request()->routeIs('admin.upgrade-requests.*') ? 'mm-active' : '' }}">
            <a href="{{ route('admin.upgrade-requests.index') }}" aria-expanded="false">
              <div class="icon_menu">
                  <img src="{{ asset('img/menu-icon/3.svg') }}" alt="">
              </div>
              <span>@lang('admin.upgrade_requests')</span>
              @php
                  $pendingRequests = \App\Models\UpgradeRequest::where('status', 'pending')->count();
              @endphp
              @if($pendingRequests > 0)
                  <span class="badge bg-warning text-dark rounded-pill ms-2" style="font-size: 0.75rem;">{{ $pendingRequests }}</span>
              @endif
            </a>
        </li>
        <li class="{{ request()->routeIs('admin.orders.*') ? 'mm-active' : '' }}">
            <a class="has-arrow" href="#" aria-expanded="false">
              <div class="icon_menu">
                  <img src="{{ asset('img/menu-icon/4.svg') }}" alt="">
              </div>
              <span>@lang('admin.order_management')</span>
            </a>
            <ul>
              <li><a href="{{ route('admin.orders.index') }}">@lang('admin.all_orders')</a></li>
              <li><a href="{{ route('admin.orders.pending') }}">@lang('admin.pending_orders')</a></li>
              <li><a href="{{ route('admin.orders.delivered') }}">@lang('admin.delivered_orders')</a></li>
            </ul>
        </li>
        <li class="{{ request()->routeIs('admin.services.*') ? 'mm-active' : '' }}">
            <a class="has-arrow" href="#" aria-expanded="false">
              <div class="icon_menu">
                  <img src="{{ asset('img/menu-icon/5.svg') }}" alt="">
              </div>
              <span>@lang('admin.service_management')</span>
            </a>
            <ul>
              <li><a href="{{ route('admin.services.index') }}">@lang('admin.all_services')</a></li>
              <li><a href="{{ route('admin.services.calendar') }}">@lang('admin.service_calendar')</a></li>
            </ul>
        </li>
        <li class="{{ request()->routeIs('admin.reports.*') ? 'mm-active' : '' }}">
            <a class="has-arrow" href="#" aria-expanded="false">
              <div class="icon_menu">
                  <img src="{{ asset('img/menu-icon/6.svg') }}" alt="">
              </div>
              <span>@lang('admin.reports')</span>
            </a>
            <ul>
              <li><a href="{{ route('admin.reports.revenue') }}">@lang('admin.revenue_report')</a></li>
              <li><a href="{{ route('admin.reports.clients') }}">@lang('admin.client_report')</a></li>
              <li><a href="{{ route('admin.reports.orders') }}">@lang('admin.order_report')</a></li>
            </ul>
        </li>
        <li class="{{ request()->routeIs('admin.settings.*') ? 'mm-active' : '' }}">
            <a class="has-arrow" href="#" aria-expanded="false">
              <div class="icon_menu">
                  <img src="{{ asset('img/menu-icon/7.svg') }}" alt="">
              </div>
              <span>@lang('admin.settings')</span>
            </a>
            <ul>
              <li><a href="{{ route('admin.settings.general') }}">@lang('admin.general_settings')</a></li>
              <li><a href="{{ route('admin.settings.users') }}">@lang('admin.admin_users')</a></li>
              <li><a href="{{ route('admin.settings.payments') }}">@lang('admin.payment_settings')</a></li>
            </ul>
        </li>
        <li class="{{ request()->routeIs('admin.profile') ? 'mm-active' : '' }}">
            <a href="{{ route('admin.profile') }}" aria-expanded="false">
              <div class="icon_menu">
                  <img src="{{ asset('img/menu-icon/8.svg') }}" alt="">
              </div>
              <span>@lang('admin.profile')</span>
            </a>
        </li>
      </ul>
</nav>


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
      </ul>
</nav>

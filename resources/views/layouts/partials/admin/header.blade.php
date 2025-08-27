
<div class="container-fluid g-0">
    <div class="row">
        <div class="col-lg-12 p-0">
            <div class="header_iner d-flex justify-content-between align-items-center">
                <div class="sidebar_icon d-lg-none">
                    <i class="ti-menu"></i>
                </div>
                <div class="serach_field-area d-flex align-items-center">
                        <div class="search_inner">
                            <form action="#">
                                <div class="search_field">
                                    <input type="text" placeholder="Search here..." >
                                </div>
                                <button type="submit"> <img src="{{ asset('img/icon/icon_search.svg') }}" alt=""> </button>
                            </form>
                        </div>
                        <span class="f_s_14 f_w_400 ml_25 white_text text_white" ></span>
                    </div>
                <div class="header_right d-flex justify-content-between align-items-center">
                    <div class="header_notification_warp d-flex align-items-center">
                        <li>
                            <div class="dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <img src="{{ asset('img/icon/bell.svg') }}" alt="">
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="#">@lang('admin.notifications')</a></li>
                                </ul>
                            </div>
                        </li>
                        <li>
                            <div class="dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <img src="{{ asset('img/icon/msg.svg') }}" alt="">
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="#">@lang('admin.messages')</a></li>
                                </ul>
                            </div>
                        </li>
                    </div>
                    <div class="profile_info">
                        <img src="{{ asset('img/client_img.png') }}" alt="#">
                        <div class="profile_info_iner">
                            <div class="profile_author_name">
                                <p>@lang('admin.administrator')</p>
                                <h5>{{ auth()->user()->name }}</h5>
                            </div>
                            <div class="profile_info_details">
                                <a href="#">@lang('admin.my_profile')</a>
                                <a href="#">@lang('admin.settings')</a>
                                <a href="{{ route('admin.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">@lang('admin.log_out')</a>
                                <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="language_option">
                        <div class="dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ strtoupper(app()->getLocale()) }}
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                                <li><a class="dropdown-item" href="{{ route('admin.language', 'en') }}">English</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.language', 'bn') }}">Bengali</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

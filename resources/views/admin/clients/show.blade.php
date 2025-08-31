
@extends('layouts.admin')

@section('title', __('admin.client_details'))

@section('content')
<div class="main_content_iner">
    <div class="container-fluid p-0">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="white_card card_height_100 mb_30">
                    <div class="white_card_header">
                        <div class="box_header m-0">
                            <div class="main-title">
                                <h3 class="m-0">@lang('admin.client_details')</h3>
                            </div>
                            <div class="add_button ms-2">
                                <a href="{{ route('admin.clients.index') }}" class="btn_1">@lang('admin.back')</a>
                            </div>
                        </div>
                    </div>
                    <div class="white_card_body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="profile-card-4">
                                    <div class="profile-img">
                                        <img src="{{ asset('img/card2.jpg') }}">
                                    </div>
                                    <div class="profile-content">
                                        <h2 class="title">{{ $client->name }}
                                        <span>{{ $client->plan_type }}</span>
                                        </h2>
                                        <ul class="social-link">
                                        <li><a href="#" class="fab fa-facebook"></a></li>
                                        <li><a href="#" class="fab fa-google"></a></li>
                                        <li><a href="#" class="fab fa-twitter"></a></li>
                                        <li><a href="#" class="fab fa-youtube"></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <th>@lang('admin.name')</th>
                                            <td>{{ $client->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>@lang('admin.email')</th>
                                            <td>{{ $client->email }}</td>
                                        </tr>
                                        <tr>
                                            <th>@lang('admin.phone')</th>
                                            <td>{{ $client->phone }}</td>
                                        </tr>
                                        <tr>
                                            <th>@lang('admin.plan')</th>
                                            <td>{{ $client->plan_type }}</td>
                                        </tr>
                                        <tr>
                                            <th>@lang('admin.status')</th>
                                            <td>
                                                <span class="badge bg-{{ $client->status == 'active' ? 'success' : 'danger' }}">
                                                    {{ __("admin.{$client->status}") }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>@lang('admin.total_orders')</th>
                                            <td>{{ $stats['total_orders'] }}</td>
                                        </tr>
                                        <tr>
                                            <th>@lang('admin.total_customers')</th>
                                            <td>{{ $stats['total_customers'] }}</td>
                                        </tr>
                                        <tr>
                                            <th>@lang('admin.facebook_pages')</th>
                                            <td>{{ $stats['facebook_pages'] }}</td>
                                        </tr>
                                        <tr>
                                            <th>@lang('admin.revenue')</th>
                                            <td>{{ $stats['revenue'] }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@extends('layouts.admin')

@section('title', __('admin.edit_client'))

@section('content')
<div class="main_content_iner">
    <div class="container-fluid p-0">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="white_card card_height_100 mb_30">
                    <div class="white_card_header">
                        <div class="box_header m-0">
                            <div class="main-title">
                                <h3 class="m-0">@lang('admin.edit_client')</h3>
                            </div>
                            <div class="add_button ms-2">
                                <a href="{{ route('admin.clients.index') }}" class="btn_1">@lang('admin.back')</a>
                            </div>
                        </div>
                    </div>
                    <div class="white_card_body">
                        <form action="{{ route('admin.clients.update', $client) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label" for="name">@lang('admin.name')</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $client->name) }}">
                                @error('name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="email">@lang('admin.email')</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $client->email) }}">
                                @error('email')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="phone">@lang('admin.phone')</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $client->phone) }}">
                                @error('phone')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="password">@lang('admin.password') (@lang('admin.leave_blank_to_keep_same'))</label>
                                <input type="password" class="form-control" id="password" name="password">
                                @error('password')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="password_confirmation">@lang('admin.confirm_password')</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="plan_type">@lang('admin.plan')</label>
                                <select class="form-control" id="plan_type" name="plan_type">
                                    <option value="free" {{ $client->plan_type == 'free' ? 'selected' : '' }}>@lang('admin.free')</option>
                                    <option value="premium" {{ $client->plan_type == 'premium' ? 'selected' : '' }}>@lang('admin.premium')</option>
                                    <option value="enterprise" {{ $client->plan_type == 'enterprise' ? 'selected' : '' }}>@lang('admin.enterprise')</option>
                                </select>
                                @error('plan_type')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="status">@lang('admin.status')</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="active" {{ $client->status == 'active' ? 'selected' : '' }}>@lang('admin.active')</option>
                                    <option value="inactive" {{ $client->status == 'inactive' ? 'selected' : '' }}>@lang('admin.inactive')</option>
                                    <option value="suspended" {{ $client->status == 'suspended' ? 'selected' : '' }}>@lang('admin.suspended')</option>
                                </select>
                                @error('status')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary">@lang('admin.update_client')</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

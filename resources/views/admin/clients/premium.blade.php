
@extends('layouts.admin')

@section('title', __('admin.premium_clients'))

@section('content')
<div class="main_content_iner">
    <div class="container-fluid p-0">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="white_card card_height_100 mb_30">
                    <div class="white_card_header">
                        <div class="box_header m-0">
                            <div class="main-title">
                                <h3 class="m-0">@lang('admin.premium_clients')</h3>
                            </div>
                        </div>
                    </div>
                    <div class="white_card_body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">@lang('admin.name')</th>
                                        <th scope="col">@lang('admin.email')</th>
                                        <th scope="col">@lang('admin.phone')</th>
                                        <th scope="col">@lang('admin.plan')</th>
                                        <th scope="col">@lang('admin.status')</th>
                                        <th scope="col">@lang('admin.actions')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($clients as $client)
                                    <tr>
                                        <th scope="row">{{ $client->id }}</th>
                                        <td>{{ $client->name }}</td>
                                        <td>{{ $client->email }}</td>
                                        <td>{{ $client->phone }}</td>
                                        <td>{{ $client->plan_type }}</td>
                                        <td>
                                            <span class="badge bg-{{ $client->status == 'active' ? 'success' : 'danger' }}">
                                                {{ __("admin.{$client->status}") }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-sm btn-info">@lang('admin.view')</a>
                                            <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-sm btn-primary">@lang('admin.edit')</a>
                                            <form action="{{ route('admin.clients.destroy', $client) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('@lang('admin.are_you_sure')')">@lang('admin.delete')</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

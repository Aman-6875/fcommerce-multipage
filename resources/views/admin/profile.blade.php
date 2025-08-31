@extends('layouts.admin')

@section('title', 'Profile')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>My Profile</h3>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Profile Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><strong>Name:</strong></label>
                                <p class="form-control-plaintext">{{ $admin->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><strong>Email:</strong></label>
                                <p class="form-control-plaintext">{{ $admin->email }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><strong>Role:</strong></label>
                                <p class="form-control-plaintext">
                                    <span class="badge badge-primary">{{ ucfirst($admin->role) }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><strong>Status:</strong></label>
                                <p class="form-control-plaintext">
                                    <span class="badge badge-{{ $admin->is_active ? 'success' : 'danger' }}">
                                        {{ $admin->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><strong>Last Login:</strong></label>
                                <p class="form-control-plaintext">
                                    {{ $admin->last_login ? $admin->last_login->format('M d, Y h:i A') : 'Never' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label"><strong>Joined:</strong></label>
                                <p class="form-control-plaintext">{{ $admin->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                    </div>

                    @if($admin->permissions && count($admin->permissions) > 0)
                        <div class="mt-4">
                            <label class="form-label"><strong>Permissions:</strong></label>
                            <div class="mt-2">
                                @foreach($admin->permissions as $permission)
                                    <span class="badge badge-secondary mr-2">{{ ucfirst(str_replace('_', ' ', $permission)) }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mt-4">
                        <button type="button" class="btn btn-primary" onclick="alert('Profile editing functionality coming soon!')">
                            Edit Profile
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
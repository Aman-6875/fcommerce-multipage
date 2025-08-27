@extends('layouts.client')

@section('title', __('client.facebook.manage_pages'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">{{ __('client.facebook.facebook_pages') }}</h4>
                    @if(auth('client')->user()->canAddNewPage())
                        <a href="{{ route('client.facebook.connect') }}" class="btn btn-primary">
                            <i class="fa fa-plus"></i> {{ __('client.facebook.connect_page') }}
                        </a>
                    @else
                        <button class="btn btn-secondary" disabled>
                            <i class="fa fa-lock"></i> {{ __('client.facebook.page_limit_reached') }}
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    <!-- Page Limit Info -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i>
                                {{ __('client.facebook.page_limit_info', [
                                    'current' => $facebookPages->count(),
                                    'limit' => auth('client')->user()->getFacebookPageLimit()
                                ]) }}
                                
                                @if(!auth('client')->user()->isPremium())
                                    <a href="#" class="alert-link">{{ __('client.facebook.upgrade_for_more') }}</a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Connected Pages -->
                    @if($facebookPages->count() > 0)
                        <div class="row">
                            @foreach($facebookPages as $page)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center mb-3">
                                                @if($page->getProfilePicture())
                                                    <img src="{{ $page->getProfilePicture() }}" 
                                                         alt="{{ $page->page_name }}" 
                                                         class="rounded-circle me-3"
                                                         style="width: 50px; height: 50px; object-fit: cover;">
                                                @else
                                                    <div class="bg-secondary rounded-circle me-3 d-flex align-items-center justify-content-center"
                                                         style="width: 50px; height: 50px;">
                                                        <i class="fa fa-facebook text-white"></i>
                                                    </div>
                                                @endif
                                                
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-0">{{ $page->page_name }}</h6>
                                                    @if($page->getCategory())
                                                        <small class="text-muted">{{ $page->getCategory() }}</small>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Connection Status -->
                                            <div class="mb-3">
                                                <span class="badge bg-{{ $page->getConnectionStatusColor() }}">
                                                    {{ __('client.facebook.status.' . $page->getConnectionStatus()) }}
                                                </span>
                                                
                                                @if($page->last_sync)
                                                    <small class="text-muted d-block mt-1">
                                                        {{ __('client.facebook.last_sync') }}: {{ $page->last_sync->diffForHumans() }}
                                                    </small>
                                                @endif
                                            </div>

                                            <!-- Page Info -->
                                            <div class="mb-3">
                                                <small class="text-muted">
                                                    <i class="fa fa-id-card"></i> {{ $page->page_id }}
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fa fa-clock"></i> 
                                                    {{ __('client.facebook.connected_on') }}: {{ $page->created_at->format('M d, Y') }}
                                                </small>
                                            </div>

                                            <!-- Messaging Status -->
                                            @if($page->canManageMessages())
                                                <div class="alert alert-success py-2 mb-3">
                                                    <i class="fa fa-check"></i> {{ __('client.facebook.messaging_active') }}
                                                </div>
                                            @else
                                                <div class="alert alert-warning py-2 mb-3">
                                                    <i class="fa fa-exclamation-triangle"></i> {{ __('client.facebook.messaging_inactive') }}
                                                </div>
                                            @endif

                                            <!-- Actions -->
                                            <div class="d-flex gap-2">
                                                <form method="POST" action="{{ route('client.facebook.test', $page) }}" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-primary btn-sm">
                                                        <i class="fa fa-sync"></i> {{ __('client.facebook.test_connection') }}
                                                    </button>
                                                </form>
                                                
                                                <form method="POST" action="{{ route('client.facebook.disconnect', $page) }}" 
                                                      onsubmit="return confirm('{{ __('client.facebook.confirm_disconnect') }}')"
                                                      style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                                        <i class="fa fa-unlink"></i> {{ __('client.facebook.disconnect') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <!-- No Pages Connected -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fa fa-facebook-square text-muted" style="font-size: 4rem;"></i>
                            </div>
                            <h5 class="text-muted">{{ __('client.facebook.no_pages_connected') }}</h5>
                            <p class="text-muted mb-4">{{ __('client.facebook.connect_first_page') }}</p>
                            
                            @if(auth('client')->user()->canAddNewPage())
                                <a href="{{ route('client.facebook.connect') }}" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> {{ __('client.facebook.connect_your_first_page') }}
                                </a>
                            @endif
                        </div>
                    @endif

                    <!-- Setup Instructions -->
                    @if($facebookPages->count() === 0)
                        <div class="row mt-5">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>{{ __('client.facebook.setup_instructions') }}</h6>
                                        <ol class="mb-0">
                                            <li>{{ __('client.facebook.step_1') }}</li>
                                            <li>{{ __('client.facebook.step_2') }}</li>
                                            <li>{{ __('client.facebook.step_3') }}</li>
                                            <li>{{ __('client.facebook.step_4') }}</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-refresh page status every 30 seconds for connected pages
    @if($facebookPages->count() > 0)
    setInterval(function() {
        // You can add AJAX calls here to check page status
        // This would be useful for real-time connection monitoring
    }, 30000);
    @endif
});
</script>
@endpush
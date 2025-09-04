<div class="row">
    <div class="col-md-6">
        <h6 class="text-muted mb-3">{{ __('client.inquiry_information') }}</h6>
        
        <div class="d-flex justify-content-between mb-2">
            <span class="fw-medium">{{ __('client.inquiry_number') }}:</span>
            <span>{{ $inquiry->inquiry_number }}</span>
        </div>
        
        <div class="d-flex justify-content-between mb-2">
            <span class="fw-medium">{{ __('client.type') }}:</span>
            <span class="badge bg-{{ $inquiry->getTypeBadgeColor() }}">{{ __(ucfirst($inquiry->type)) }}</span>
        </div>
        
        <div class="d-flex justify-content-between mb-2">
            <span class="fw-medium">{{ __('client.status') }}:</span>
            <span class="badge bg-{{ $inquiry->getStatusBadgeColor() }}">{{ $inquiry->getStatusBadge() }}</span>
        </div>
        
        <div class="d-flex justify-content-between mb-2">
            <span class="fw-medium">{{ __('client.priority') }}:</span>
            <span class="badge bg-{{ $inquiry->getPriorityBadgeColor() }}">{{ $inquiry->getPriorityBadge() }}</span>
        </div>
        
        <div class="d-flex justify-content-between mb-2">
            <span class="fw-medium">{{ __('client.language') }}:</span>
            <span class="badge bg-light text-dark">{{ strtoupper($inquiry->language) }}</span>
        </div>
        
        <div class="d-flex justify-content-between mb-2">
            <span class="fw-medium">{{ __('client.created_date') }}:</span>
            <span>{{ $inquiry->created_at->format('M d, Y h:i A') }}</span>
        </div>
    </div>
    
    <div class="col-md-6">
        <h6 class="text-muted mb-3">{{ __('client.customer_information') }}</h6>
        
        <div class="d-flex justify-content-between mb-2">
            <span class="fw-medium">{{ __('client.name') }}:</span>
            <span>{{ $inquiry->customer_name ?: __('client.unknown_customer') }}</span>
        </div>
        
        @if($inquiry->customer_phone)
        <div class="d-flex justify-content-between mb-2">
            <span class="fw-medium">{{ __('client.phone') }}:</span>
            <span>{{ $inquiry->customer_phone }}</span>
        </div>
        @endif
        
        @if($inquiry->customer_address)
        <div class="d-flex justify-content-between mb-2">
            <span class="fw-medium">{{ __('client.address') }}:</span>
            <span>{{ $inquiry->customer_address }}</span>
        </div>
        @endif
        
        @if($inquiry->budget_range)
        <div class="d-flex justify-content-between mb-2">
            <span class="fw-medium">{{ __('client.budget') }}:</span>
            <span class="badge bg-warning text-dark">{{ $inquiry->budget_range }}</span>
        </div>
        @endif
        
        @if($inquiry->customer && $inquiry->customer->facebook_id)
        <div class="d-flex justify-content-between mb-2">
            <span class="fw-medium">{{ __('client.facebook_profile') }}:</span>
            <a href="https://facebook.com/{{ $inquiry->customer->facebook_id }}" target="_blank" class="text-primary">
                <i class="ri-facebook-line me-1"></i>{{ __('client.view_profile') }}
            </a>
        </div>
        @endif
    </div>
</div>

@if($inquiry->requirements)
<div class="row mt-4">
    <div class="col-12">
        <h6 class="text-muted mb-3">{{ __('client.requirements_details') }}</h6>
        <div class="border rounded p-3 bg-light">
            <p class="mb-0">{{ $inquiry->requirements }}</p>
        </div>
    </div>
</div>
@endif

@if($inquiry->extra_fields && is_array($inquiry->extra_fields) && count($inquiry->extra_fields) > 0)
<div class="row mt-4">
    <div class="col-12">
        <h6 class="text-muted mb-3">{{ __('client.additional_information') }}</h6>
        <div class="border rounded p-3 bg-light">
            @foreach($inquiry->extra_fields as $key => $value)
                @if($value)
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-medium">{{ __(ucfirst(str_replace('_', ' ', $key))) }}:</span>
                    <span>{{ is_array($value) ? implode(', ', $value) : $value }}</span>
                </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
@endif

<div class="row mt-4">
    <div class="col-12">
        <h6 class="text-muted mb-3">{{ __('client.timeline') }}</h6>
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-marker bg-primary"></div>
                <div class="timeline-content">
                    <h6 class="mb-1">{{ __('client.inquiry_created') }}</h6>
                    <small class="text-muted">{{ $inquiry->created_at->format('M d, Y h:i A') }}</small>
                </div>
            </div>
            
            @if($inquiry->updated_at != $inquiry->created_at)
            <div class="timeline-item">
                <div class="timeline-marker bg-info"></div>
                <div class="timeline-content">
                    <h6 class="mb-1">{{ __('client.last_updated') }}</h6>
                    <small class="text-muted">{{ $inquiry->updated_at->format('M d, Y h:i A') }}</small>
                </div>
            </div>
            @endif
            
            @if($inquiry->status === 'completed')
            <div class="timeline-item">
                <div class="timeline-marker bg-success"></div>
                <div class="timeline-content">
                    <h6 class="mb-1">{{ __('client.inquiry_completed') }}</h6>
                    <small class="text-muted">{{ $inquiry->updated_at->format('M d, Y h:i A') }}</small>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 4px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    padding-left: 15px;
}
</style>
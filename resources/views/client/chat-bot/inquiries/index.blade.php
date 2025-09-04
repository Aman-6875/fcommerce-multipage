@extends('layouts.client')

@section('title', __('client.customer_inquiries'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">{{ __('client.customer_inquiries') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">{{ __('common.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('client.customer_inquiries') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">{{ __('client.total_inquiries') }}</p>
                            <div class="d-flex align-items-end justify-content-between mt-2">
                                <h4 class="ff-secondary fw-semibold mb-0">
                                    <span class="counter-value" data-target="{{ $stats['total'] }}">{{ $stats['total'] }}</span>
                                </h4>
                            </div>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-primary-subtle rounded fs-3">
                                <i class="ri-question-line text-primary"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">{{ __('client.pending_inquiries') }}</p>
                            <div class="d-flex align-items-end justify-content-between mt-2">
                                <h4 class="ff-secondary fw-semibold mb-0">
                                    <span class="counter-value text-warning" data-target="{{ $stats['pending'] }}">{{ $stats['pending'] }}</span>
                                </h4>
                            </div>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-warning-subtle rounded fs-3">
                                <i class="ri-time-line text-warning"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">{{ __('client.completed_inquiries') }}</p>
                            <div class="d-flex align-items-end justify-content-between mt-2">
                                <h4 class="ff-secondary fw-semibold mb-0">
                                    <span class="counter-value text-success" data-target="{{ $stats['completed'] }}">{{ $stats['completed'] }}</span>
                                </h4>
                            </div>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-success-subtle rounded fs-3">
                                <i class="ri-check-line text-success"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 overflow-hidden">
                            <p class="text-uppercase fw-medium text-muted text-truncate mb-0">{{ __('client.todays_inquiries') }}</p>
                            <div class="d-flex align-items-end justify-content-between mt-2">
                                <h4 class="ff-secondary fw-semibold mb-0">
                                    <span class="counter-value text-info" data-target="{{ $stats['today'] }}">{{ $stats['today'] }}</span>
                                </h4>
                            </div>
                        </div>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-info-subtle rounded fs-3">
                                <i class="ri-calendar-line text-info"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">{{ __('client.manage_inquiries') }}</h4>
                    <div class="d-flex gap-2">
                        <div class="search-box">
                            <input type="text" class="form-control search" id="searchInput" placeholder="{{ __('client.search_inquiries') }}">
                            <i class="ri-search-line search-icon"></i>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="ri-filter-line me-1"></i>{{ __('client.filter') }}
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item filter-option" href="#" data-filter="all">{{ __('client.all_inquiries') }}</a></li>
                                <li><a class="dropdown-item filter-option" href="#" data-filter="pending">{{ __('client.pending') }}</a></li>
                                <li><a class="dropdown-item filter-option" href="#" data-filter="in_progress">{{ __('client.in_progress') }}</a></li>
                                <li><a class="dropdown-item filter-option" href="#" data-filter="completed">{{ __('client.completed') }}</a></li>
                                <li><a class="dropdown-item filter-option" href="#" data-filter="cancelled">{{ __('client.cancelled') }}</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($inquiries->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="inquiriesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('client.inquiry_number') }}</th>
                                        <th>{{ __('client.customer') }}</th>
                                        <th>{{ __('client.type') }}</th>
                                        <th>{{ __('client.details') }}</th>
                                        <th>{{ __('client.priority') }}</th>
                                        <th>{{ __('client.status') }}</th>
                                        <th>{{ __('client.created_date') }}</th>
                                        <th>{{ __('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($inquiries as $inquiry)
                                        <tr data-status="{{ $inquiry->status }}" data-type="{{ $inquiry->type }}">
                                            <td>
                                                <span class="fw-medium">{{ $inquiry->inquiry_number }}</span>
                                                @if($inquiry->priority === 'high')
                                                    <i class="ri-arrow-up-line text-danger ms-1" title="{{ __('client.high_priority') }}"></i>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    <h6 class="mb-1">{{ $inquiry->customer_name ?: __('client.unknown_customer') }}</h6>
                                                    @if($inquiry->customer_phone)
                                                        <small class="text-muted">{{ $inquiry->customer_phone }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $inquiry->getTypeBadgeColor() }}">
                                                    {{ __(ucfirst($inquiry->type)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    @if($inquiry->requirements)
                                                        <p class="mb-1 text-truncate" style="max-width: 200px;">{{ $inquiry->requirements }}</p>
                                                    @endif
                                                    @if($inquiry->budget_range)
                                                        <small class="badge bg-light text-dark">{{ $inquiry->budget_range }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $inquiry->getPriorityBadgeColor() }}">
                                                    {{ $inquiry->getPriorityBadge() }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-{{ $inquiry->getStatusBadgeColor() }} dropdown-toggle" 
                                                            type="button" data-bs-toggle="dropdown">
                                                        {{ $inquiry->getStatusBadge() }}
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item update-status" href="#" data-id="{{ $inquiry->id }}" data-status="pending">{{ __('client.pending') }}</a></li>
                                                        <li><a class="dropdown-item update-status" href="#" data-id="{{ $inquiry->id }}" data-status="in_progress">{{ __('client.in_progress') }}</a></li>
                                                        <li><a class="dropdown-item update-status" href="#" data-id="{{ $inquiry->id }}" data-status="completed">{{ __('client.completed') }}</a></li>
                                                        <li><a class="dropdown-item update-status" href="#" data-id="{{ $inquiry->id }}" data-status="cancelled">{{ __('client.cancelled') }}</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <span>{{ $inquiry->created_at->format('M d, Y') }}</span>
                                                    <br><small class="text-muted">{{ $inquiry->created_at->format('h:i A') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-primary view-inquiry" 
                                                            data-id="{{ $inquiry->id }}" title="{{ __('common.view') }}">
                                                        <i class="ri-eye-line"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger delete-inquiry" 
                                                            data-id="{{ $inquiry->id }}" title="{{ __('common.delete') }}">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $inquiries->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="ri-inbox-line display-4 text-muted"></i>
                            </div>
                            <h5 class="text-muted">{{ __('client.no_inquiries_found') }}</h5>
                            <p class="text-muted mb-4">{{ __('client.inquiries_will_appear_desc') }}</p>
                            <a href="{{ route('client.chat-bot.faqs.index') }}" class="btn btn-primary">
                                {{ __('client.setup_faqs') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Inquiry Modal -->
<div class="modal fade" id="viewInquiryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('client.inquiry_details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="inquiryDetails">
                <!-- Content will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('common.close') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('client.confirm_delete') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{ __('client.delete_inquiry_confirmation') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">{{ __('common.delete') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const filterOptions = document.querySelectorAll('.filter-option');
    const tableRows = document.querySelectorAll('#inquiriesTable tbody tr');
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
    
    // Filter functionality
    filterOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            e.preventDefault();
            const filterValue = this.dataset.filter;
            
            tableRows.forEach(row => {
                if (filterValue === 'all') {
                    row.style.display = '';
                } else {
                    const status = row.dataset.status;
                    row.style.display = status === filterValue ? '' : 'none';
                }
            });
            
            // Update dropdown button text
            document.querySelector('.dropdown-toggle').innerHTML = 
                '<i class="ri-filter-line me-1"></i>' + this.textContent;
        });
    });
    
    // Update status functionality
    document.querySelectorAll('.update-status').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const inquiryId = this.dataset.id;
            const newStatus = this.dataset.status;
            
            fetch(`/client/chat-bot/inquiries/${inquiryId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        });
    });
    
    // View inquiry details
    document.querySelectorAll('.view-inquiry').forEach(btn => {
        btn.addEventListener('click', function() {
            const inquiryId = this.dataset.id;
            
            fetch(`/client/chat-bot/inquiries/${inquiryId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('inquiryDetails').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('viewInquiryModal')).show();
                });
        });
    });
    
    // Delete inquiry
    document.querySelectorAll('.delete-inquiry').forEach(btn => {
        btn.addEventListener('click', function() {
            const inquiryId = this.dataset.id;
            const deleteForm = document.getElementById('deleteForm');
            deleteForm.action = `/client/chat-bot/inquiries/${inquiryId}`;
            
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        });
    });
});
</script>
@endpush
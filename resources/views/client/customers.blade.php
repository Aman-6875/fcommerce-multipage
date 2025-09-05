@extends('layouts.client')

@section('title', __('client.customers'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">{{ __('client.customers') }}</h4>
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <span class="badge badge-primary">{{ $customers->count() ?? 0 }}</span> 
                            {{ __('client.total_customers') }}
                        </div>
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#importModal">
                            <i class="fas fa-plus"></i> {{ __('client.add_customer') }}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($customers && $customers->count() > 0)
                        <!-- Search and Filter -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <input type="text" class="form-control" placeholder="{{ __('common.search') }}..." id="customerSearch">
                            </div>
                            <div class="col-md-3">
                                <select class="form-control" id="statusFilter">
                                    <option value="">{{ __('common.all_status') }}</option>
                                    <option value="active">{{ __('common.active') }}</option>
                                    <option value="blocked">{{ __('common.blocked') }}</option>
                                    <option value="unsubscribed">{{ __('common.unsubscribed') }}</option>
                                </select>
                            </div>
                            <!-- <div class="col-md-5"></div> -->
                            <div class="col-md-2">
                                <button class="btn btn-outline-secondary" onclick="resetFilters()">
                                    <i class="fas fa-sync"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Customers Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="customersTable" style="font-size: 14px;">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 25%; color: black;">{{ __('common.customer') }}</th>
                                        <th style="width: 20%; color: black;">{{ __('common.contact') }}</th>
                                        <th style="width: 15%; color: black;">{{ __('common.page') }}</th>
                                        <th style="width: 20%; color: black;">{{ __('common.last_interaction') }}</th>
                                        <th style="width: 10%; color: black;">{{ __('common.orders') }}</th>
                                        <th style="width: 10%; color: black;">{{ __('common.status') }}</th>
                                        <th style="width: 10%; color: black;">{{ __('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customers as $customer)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($customer->profile_data['profile_picture'] ?? null)
                                                    <img src="{{ $customer->profile_data['profile_picture'] }}" alt="Profile" class="rounded-circle me-2" width="32" height="32">
                                                @else
                                                    <div class="bg-dark border rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                        <i class="fas fa-user text-light"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="font-weight-bold">{{ $customer->name }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($customer->phone)
                                                <div class="mb-1">
                                                    <i class="fas fa-phone text-success me-1"></i> 
                                                    <span>{{ $customer->phone }}</span>
                                                </div>
                                            @endif
                                            @if($customer->email)
                                                <div class="mb-1">
                                                    <i class="fas fa-envelope text-primary me-1"></i> 
                                                    <span style="font-size: 12px;">{{ $customer->email }}</span>
                                                </div>
                                            @endif
                                            @if(!$customer->phone && !$customer->email)
                                                <span class="text-muted">No contact info</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($customer->current_page_name) && $customer->current_page_name !== 'Unknown')
                                                <span class="badge badge-info" style="color: black;">{{ $customer->current_page_name }}</span>
                                            @else
                                                <span class="text-muted">No Page</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ $customer->last_interaction ? $customer->last_interaction->diffForHumans() : 'Never' }}</div>
                                            <small class="text-muted">Messages: {{ $customer->page_messages_count ?? 0 }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border px-2 py-1">{{ $customer->orders()->count() }}</span>
                                        </td>
                                        <td>
                                            @switch($customer->status)
                                                @case('active')
                                                    <span class="badge bg-success text-dark px-2 py-1">Active</span>
                                                    @break
                                                @case('blocked')
                                                    <span class="badge bg-danger text-dark px-2 py-1">Blocked</span>
                                                    @break
                                                @case('unsubscribed')
                                                    <span class="badge bg-warning text-dark px-2 py-1">Unsubscribed</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary text-dark px-2 py-1">{{ ucfirst($customer->status) }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-outline-primary" onclick="viewCustomer({{ $customer->id }})" title="View Messages">
                                                    <i class="fas fa-comments"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-secondary" onclick="editCustomer({{ $customer->id }})" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="blockCustomer({{ $customer->id }})" title="Block">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                    
                                    @if($customers->isEmpty())
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            No customers found
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    @else
                        <!-- No Customers -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-users text-muted" style="font-size: 4rem;"></i>
                            </div>
                            <h5 class="text-muted">{{ __('client.no_customers_yet') }}</h5>
                            <p class="text-muted mb-4">{{ __('client.customers_will_appear_here') }}</p>
                            
                            @if(auth('client')->user()->facebookPages()->where('is_connected', true)->count() === 0)
                                <a href="{{ route('client.facebook-pages') }}" class="btn btn-primary">
                                    <i class="fab fa-facebook"></i> {{ __('client.connect_facebook_page') }}
                                </a>
                            @else
                                <p class="text-info">
                                    <i class="fas fa-info-circle"></i> 
                                    {{ __('client.customers_auto_added') }}
                                </p>
                            @endif
                        </div>
                    @endif

                    <!-- Plan Limits -->
                    @if(auth('client')->user()->isFree())
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>{{ __('client.free_plan_limit') }}:</strong> 
                                    {{ auth('client')->user()->customers()->count() }}/20 {{ __('client.customers') }}
                                    @if(auth('client')->user()->customers()->count() >= 20)
                                        <a href="{{ route('client.upgrade.index') }}" class="alert-link">{{ __('client.upgrade_for_unlimited') }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">{{ __('client.add_customer_manually') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addCustomerForm">
                    @csrf
                    <div class="form-group">
                        <label for="customer_name">{{ __('common.name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="customer_name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_phone">{{ __('common.phone') }}</label>
                        <input type="tel" class="form-control" id="customer_phone" name="phone">
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_email">{{ __('common.email') }}</label>
                        <input type="email" class="form-control" id="customer_email" name="email">
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_address">{{ __('common.address') }}</label>
                        <textarea class="form-control" id="customer_address" name="address" rows="2"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="facebook_page">{{ __('client.source_page') }}</label>
                        <select class="form-control" id="facebook_page" name="source_page_id">
                            <option value="">{{ __('common.select') }}</option>
                            @if(auth('client')->user()->facebookPages->count() > 0)
                                @foreach(auth('client')->user()->facebookPages as $page)
                                    <option value="{{ $page->page_id }}">{{ $page->page_name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="submitCustomerBtn">
                    <i class="fas fa-save"></i> {{ __('client.add_customer') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    console.log('jQuery loaded:', typeof jQuery !== 'undefined');
    console.log('Bootstrap modal loaded:', typeof $.fn.modal !== 'undefined');
    
    // Load customers data
    loadCustomers();
    
    // Search functionality
    $('#customerSearch').on('keyup', function() {
        filterCustomers();
    });
    
    // Filter functionality
    $('#statusFilter').on('change', function() {
        filterCustomers();
    });
    
    // Form submission
    $('#submitCustomerBtn').on('click', function() {
        console.log('Submit button clicked');
        if (!validateCustomerForm()) {
            console.log('Validation failed');
            return;
        }
        addCustomer();
    });
    
    // Modal events
    $('#importModal').on('hidden.bs.modal', function() {
        $('#addCustomerForm')[0].reset();
        $('#addCustomerForm .is-invalid').removeClass('is-invalid');
        $('#addCustomerForm .invalid-feedback').remove();
    });
    
    // Test modal trigger
    $('[data-target="#importModal"]').on('click', function() {
        console.log('Modal trigger clicked');
        $('#importModal').modal('show');
    });
});

function validateCustomerForm() {
    var isValid = true;
    var nameField = $('#customer_name');
    
    // Clear previous validation
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    
    // Check required name field
    if (!nameField.val().trim()) {
        nameField.addClass('is-invalid');
        nameField.after('<div class="invalid-feedback d-block">Name is required</div>');
        isValid = false;
    }
    
    // Check email format if provided
    var email = $('#customer_email').val();
    if (email && !isValidEmail(email)) {
        $('#customer_email').addClass('is-invalid');
        $('#customer_email').after('<div class="invalid-feedback d-block">Please enter a valid email</div>');
        isValid = false;
    }
    
    return isValid;
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function loadCustomers() {
    // Data is already loaded from server, no AJAX needed
    console.log('Customers loaded from server');
}

function filterCustomers() {
    var searchTerm = $('#customerSearch').val().toLowerCase();
    var statusFilter = $('#statusFilter').val();
    
    $('#customersTable tbody tr').each(function() {
        var row = $(this);
        var customerName = row.find('td:first .font-weight-bold').text().toLowerCase();
        var customerStatus = row.find('td:nth-child(6) .badge').text().toLowerCase();
        
        var showRow = true;
        
        // Search filter
        if (searchTerm && customerName.indexOf(searchTerm) === -1) {
            showRow = false;
        }
        
        // Status filter
        if (statusFilter && customerStatus !== statusFilter.toLowerCase()) {
            showRow = false;
        }
        
        if (showRow) {
            row.show();
        } else {
            row.hide();
        }
    });
}

function resetFilters() {
    $('#customerSearch').val('');
    $('#statusFilter').val('');
    filterCustomers();
}

function addCustomer() {
    console.log('Adding customer...');
    
    // Simulate success for now
    $('#importModal').modal('hide');
    alert('Customer would be added here (API not implemented yet)');
    
    // When API is ready, use this:
    /*
    $.ajax({
        url: '/api/customers',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: $('#addCustomerForm').serialize(),
        success: function(response) {
            $('#importModal').modal('hide');
            alert('Customer added successfully!');
            location.reload();
        },
        error: function(xhr) {
            alert('Error adding customer');
        }
    });
    */
}

function viewCustomer(customerId) {
    window.location.href = '/client/messages/' + customerId;
}

function editCustomer(customerId) {
    // For now, just show alert - implement edit modal later
    alert('Edit customer functionality coming soon. Customer ID: ' + customerId);
}

function blockCustomer(customerId) {
    if (confirm('Are you sure you want to block this customer?')) {
        // Implement block functionality
        alert('Block customer functionality coming soon. Customer ID: ' + customerId);
    }
}
</script>
@endpush
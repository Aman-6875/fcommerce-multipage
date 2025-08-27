@extends('layouts.client')

@section('title', __('client.services'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">{{ __('client.services') }}</h4>
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <span class="badge bg-info">{{ auth('client')->user()->services()->where('booking_date', '>=', now()->format('Y-m-d'))->count() ?? 0 }}</span> 
                            {{ __('client.upcoming_services') }}
                        </div>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createServiceModal">
                            <i class="fas fa-plus"></i> {{ __('client.book_service') }}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Service Stats -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h4>{{ auth('client')->user()->services()->count() ?? 0 }}</h4>
                                    <small>{{ __('client.total_bookings') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h4>{{ auth('client')->user()->services()->where('status', 'pending')->count() ?? 0 }}</h4>
                                    <small>{{ __('common.pending') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h4>{{ auth('client')->user()->services()->where('status', 'confirmed')->count() ?? 0 }}</h4>
                                    <small>{{ __('common.confirmed') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4>{{ auth('client')->user()->services()->where('status', 'completed')->count() ?? 0 }}</h4>
                                    <small>{{ __('common.completed') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h4>{{ auth('client')->user()->services()->where('status', 'cancelled')->count() ?? 0 }}</h4>
                                    <small>{{ __('common.cancelled') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-secondary text-white">
                                <div class="card-body text-center">
                                    <h4>৳{{ number_format(auth('client')->user()->services()->where('status', 'completed')->sum('service_price') ?? 0) }}</h4>
                                    <small>{{ __('client.total_revenue') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Calendar View -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ __('client.todays_appointments') }} - {{ now()->format('l, F j, Y') }}</h6>
                                </div>
                                <div class="card-body">
                                    <div id="todaysAppointments">
                                        <div class="text-center text-muted py-3">
                                            <i class="fas fa-calendar-check"></i> {{ __('client.no_appointments_today') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($services->count() > 0)
                        <!-- Search and Filter -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <input type="text" class="form-control" placeholder="{{ __('common.search') }} {{ __('client.services') }}..." id="serviceSearch">
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" id="statusFilter">
                                    <option value="">{{ __('common.all_status') }}</option>
                                    <option value="pending">{{ __('common.pending') }}</option>
                                    <option value="confirmed">{{ __('common.confirmed') }}</option>
                                    <option value="in_progress">{{ __('common.in_progress') }}</option>
                                    <option value="completed">{{ __('common.completed') }}</option>
                                    <option value="cancelled">{{ __('common.cancelled') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" id="serviceTypeFilter">
                                    <option value="">{{ __('client.all_service_types') }}</option>
                                    <option value="consultation">{{ __('client.consultation') }}</option>
                                    <option value="delivery">{{ __('client.delivery') }}</option>
                                    <option value="installation">{{ __('client.installation') }}</option>
                                    <option value="maintenance">{{ __('client.maintenance') }}</option>
                                    <option value="other">{{ __('common.other') }}</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" class="form-control" id="dateFilter">
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-outline-secondary" onclick="resetFilters()">
                                    <i class="fas fa-sync"></i>
                                </button>
                                <button class="btn btn-outline-primary" onclick="viewCalendar()">
                                    <i class="fas fa-calendar"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Services Table -->
                        <div class="table-responsive">
                            <table class="table table-striped" id="servicesTable">
                                <thead>
                                    <tr>
                                        <th>{{ __('client.service_name') }}</th>
                                        <th>{{ __('common.customer') }}</th>
                                        <th>{{ __('client.service_type') }}</th>
                                        <th>{{ __('common.date_time') }}</th>
                                        <th>{{ __('client.duration') }}</th>
                                        <th>{{ __('common.amount') }}</th>
                                        <th>{{ __('common.status') }}</th>
                                        <th>{{ __('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Sample data - will be populated by AJAX -->
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-spinner fa-spin"></i> {{ __('common.loading') }}...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <!-- No Services -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-concierge-bell text-muted" style="font-size: 4rem;"></i>
                            </div>
                            <h5 class="text-muted">{{ __('client.no_services_yet') }}</h5>
                            <p class="text-muted mb-4">{{ __('client.services_will_appear_here') }}</p>
                            
                            <div class="d-flex justify-content-center gap-3">
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createServiceModal">
                                    <i class="fas fa-plus"></i> {{ __('client.book_first_service') }}
                                </button>
                                
                                @if(auth('client')->user()->facebookPages()->where('is_connected', true)->count() === 0)
                                    <a href="{{ route('client.facebook-pages') }}" class="btn btn-outline-primary">
                                        <i class="fab fa-facebook"></i> {{ __('client.connect_facebook_first') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Service Modal -->
<div class="modal fade" id="createServiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('client.book_new_service') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createServiceForm">
                    @csrf
                    <div class="row">
                        <!-- Customer Information -->
                        <div class="col-md-6">
                            <h6 class="mb-3">{{ __('client.customer_information') }}</h6>
                            
                            <div class="form-group mb-3">
                                <label for="customer_name">{{ __('common.customer_name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="customer_phone">{{ __('common.phone') }} <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="customer_phone" name="customer_phone" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="customer_email">{{ __('common.email') }}</label>
                                <input type="email" class="form-control" id="customer_email" name="customer_email">
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="service_location">{{ __('client.service_location') }} <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="service_location" name="service_location" rows="3" required placeholder="{{ __('client.service_location_placeholder') }}"></textarea>
                            </div>
                        </div>
                        
                        <!-- Service Information -->
                        <div class="col-md-6">
                            <h6 class="mb-3">{{ __('client.service_information') }}</h6>
                            
                            <div class="form-group mb-3">
                                <label for="service_type">{{ __('client.service_type') }} <span class="text-danger">*</span></label>
                                <select class="form-control" id="service_type" name="service_type" required>
                                    <option value="">{{ __('common.select') }}</option>
                                    <option value="consultation">{{ __('client.consultation') }}</option>
                                    <option value="delivery">{{ __('client.delivery') }}</option>
                                    <option value="installation">{{ __('client.installation') }}</option>
                                    <option value="maintenance">{{ __('client.maintenance') }}</option>
                                    <option value="repair">{{ __('client.repair') }}</option>
                                    <option value="other">{{ __('common.other') }}</option>
                                </select>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="service_name">{{ __('client.service_name') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="service_name" name="service_name" required placeholder="{{ __('client.service_name_placeholder') }}">
                            </div>
                            
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group mb-3">
                                        <label for="booking_date">{{ __('client.booking_date') }} <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="booking_date" name="booking_date" min="{{ now()->format('Y-m-d') }}" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group mb-3">
                                        <label for="booking_time">{{ __('client.booking_time') }} <span class="text-danger">*</span></label>
                                        <input type="time" class="form-control" id="booking_time" name="booking_time" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group mb-3">
                                        <label for="duration">{{ __('client.duration') }} ({{ __('client.minutes') }})</label>
                                        <input type="number" class="form-control" id="duration" name="duration" min="15" step="15" value="60">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group mb-3">
                                        <label for="service_price">{{ __('client.service_price') }} (৳)</label>
                                        <input type="number" class="form-control" id="service_price" name="service_price" min="0" step="0.01">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="notes">{{ __('client.special_instructions') }}</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="{{ __('client.service_notes_placeholder') }}"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                <button type="button" class="btn btn-primary" onclick="createService()">
                    <i class="fas fa-save"></i> {{ __('client.book_service') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Load services data
    loadServices();
    
    // Load today's appointments
    loadTodaysAppointments();
    
    // Set minimum date for booking
    $('#booking_date').attr('min', new Date().toISOString().split('T')[0]);
});

function loadServices() {
    // This will be implemented when we have the services API endpoint
    console.log('Loading services...');
}

function loadTodaysAppointments() {
    // This will be implemented to show today's appointments
    console.log('Loading today\'s appointments...');
}

function createService() {
    // This will be implemented when we have the create service endpoint
    console.log('Creating service booking...');
}

function resetFilters() {
    $('#serviceSearch').val('');
    $('#statusFilter').val('');
    $('#serviceTypeFilter').val('');
    $('#dateFilter').val('');
    loadServices();
}

function viewCalendar() {
    // This will be implemented to show calendar view
    console.log('Opening calendar view...');
}
</script>
@endpush
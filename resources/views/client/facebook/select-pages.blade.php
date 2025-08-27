@extends('layouts.client')

@section('title', __('client.facebook.select_pages'))

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ __('client.facebook.select_pages_to_connect') }}</h4>
                    <p class="card-text text-muted">{{ __('client.facebook.select_pages_description') }}</p>
                </div>
                <div class="card-body">
                    <!-- Page Limit Warning -->
                    @if($remainingSlots < count($pagesData))
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i>
                            {{ __('client.facebook.page_limit_warning', [
                                'remaining' => $remainingSlots,
                                'total' => count($pagesData)
                            ]) }}
                        </div>
                    @endif

                    <!-- Page Selection Form -->
                    <form method="POST" action="{{ route('client.facebook.connect-pages') }}" id="pageSelectionForm">
                        @csrf
                        
                        <div class="row">
                            @foreach($pagesData as $pageData)
                                <div class="col-md-6 mb-4">
                                    <div class="card border page-card" 
                                         data-page-id="{{ $pageData['id'] }}"
                                         style="cursor: pointer;">
                                        <div class="card-body">
                                            <div class="form-check position-absolute" style="top: 10px; right: 10px;">
                                                <input class="form-check-input page-checkbox" 
                                                       type="checkbox" 
                                                       name="selected_pages[]" 
                                                       value="{{ $pageData['id'] }}"
                                                       id="page_{{ $pageData['id'] }}">
                                            </div>

                                            <div class="d-flex align-items-center mb-3">
                                                @if(isset($pageData['picture']['data']['url']))
                                                    <img src="{{ $pageData['picture']['data']['url'] }}" 
                                                         alt="{{ $pageData['name'] }}" 
                                                         class="rounded-circle me-3"
                                                         style="width: 50px; height: 50px; object-fit: cover;">
                                                @else
                                                    <div class="bg-primary rounded-circle me-3 d-flex align-items-center justify-content-center"
                                                         style="width: 50px; height: 50px;">
                                                        <i class="fa fa-facebook text-white"></i>
                                                    </div>
                                                @endif
                                                
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-0">{{ $pageData['name'] }}</h6>
                                                    @if(isset($pageData['category']))
                                                        <small class="text-muted">{{ $pageData['category'] }}</small>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Page ID -->
                                            <div class="mb-2">
                                                <small class="text-muted">
                                                    <i class="fa fa-id-card"></i> {{ $pageData['id'] }}
                                                </small>
                                            </div>

                                            <!-- Permissions Check -->
                                            @if(isset($pageData['tasks']))
                                                <div class="mb-2">
                                                    <small class="text-muted">{{ __('client.facebook.permissions') }}:</small>
                                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                                        @foreach($pageData['tasks'] as $task)
                                                            <span class="badge bg-{{ $task === 'MESSAGING' ? 'success' : 'secondary' }} badge-sm">
                                                                {{ $task }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Messaging Status -->
                                            @if(in_array('MESSAGING', $pageData['tasks'] ?? []))
                                                <div class="alert alert-success py-2 mb-0">
                                                    <i class="fa fa-check"></i> {{ __('client.facebook.messaging_enabled') }}
                                                </div>
                                            @else
                                                <div class="alert alert-warning py-2 mb-0">
                                                    <i class="fa fa-exclamation-triangle"></i> {{ __('client.facebook.messaging_disabled') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Selected Count -->
                        <div class="mt-4 mb-3">
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i>
                                <span id="selectedCount">0</span> {{ __('client.facebook.pages_selected') }}
                                ({{ __('client.facebook.max_allowed') }}: {{ $remainingSlots }})
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('client.facebook.index') }}" class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> {{ __('common.cancel') }}
                            </a>
                            
                            <button type="submit" class="btn btn-primary" id="connectButton" disabled>
                                <i class="fa fa-link"></i> {{ __('client.facebook.connect_selected_pages') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Help Section -->
            <div class="card mt-4">
                <div class="card-body">
                    <h6>{{ __('client.facebook.help_title') }}</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-success">{{ __('client.facebook.recommended') }}</h6>
                            <ul class="text-muted">
                                <li>{{ __('client.facebook.help_messaging') }}</li>
                                <li>{{ __('client.facebook.help_active_page') }}</li>
                                <li>{{ __('client.facebook.help_business_page') }}</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-warning">{{ __('client.facebook.note') }}</h6>
                            <ul class="text-muted">
                                <li>{{ __('client.facebook.help_admin_required') }}</li>
                                <li>{{ __('client.facebook.help_messaging_permission') }}</li>
                                <li>{{ __('client.facebook.help_page_limit') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const maxPages = {{ $remainingSlots }};
    let selectedCount = 0;

    function updateSelectedCount() {
        selectedCount = $('.page-checkbox:checked').length;
        $('#selectedCount').text(selectedCount);
        
        // Enable/disable connect button
        $('#connectButton').prop('disabled', selectedCount === 0);
        
        // Disable checkboxes if max reached
        if (selectedCount >= maxPages) {
            $('.page-checkbox:not(:checked)').prop('disabled', true);
        } else {
            $('.page-checkbox').prop('disabled', false);
        }
        
        // Update card styling
        $('.page-card').each(function() {
            const pageId = $(this).data('page-id');
            const checkbox = $(`#page_${pageId}`);
            
            if (checkbox.is(':checked')) {
                $(this).addClass('border-primary');
            } else {
                $(this).removeClass('border-primary');
            }
        });
    }

    // Handle checkbox changes
    $('.page-checkbox').on('change', updateSelectedCount);

    // Handle card clicks
    $('.page-card').on('click', function(e) {
        if (e.target.type !== 'checkbox') {
            const pageId = $(this).data('page-id');
            const checkbox = $(`#page_${pageId}`);
            
            if (!checkbox.is(':disabled')) {
                checkbox.prop('checked', !checkbox.is(':checked'));
                updateSelectedCount();
            }
        }
    });

    // Initial update
    updateSelectedCount();

    // Form validation
    $('#pageSelectionForm').on('submit', function(e) {
        if (selectedCount === 0) {
            e.preventDefault();
            alert('{{ __('client.facebook.please_select_at_least_one_page') }}');
        }
    });
});
</script>
@endpush
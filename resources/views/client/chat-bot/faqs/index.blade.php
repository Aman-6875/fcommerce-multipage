@extends('layouts.client')

@section('title', __('client.faq_management'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">{{ __('client.faq_management') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">{{ __('common.dashboard') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('client.faq_management') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">{{ __('client.manage_faqs') }}</h4>
                    <div>
                        <a href="{{ route('client.chat-bot.faqs.create') }}" class="btn btn-primary btn-sm me-2">
                            <i class="ri-add-line me-1"></i>{{ __('client.add_new_faq') }}
                        </a>
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#quickSetupModal">
                            <i class="ri-magic-line me-1"></i>{{ __('client.quick_setup') }}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($faqs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('client.order') }}</th>
                                        <th>{{ __('client.question') }}</th>
                                        <th>{{ __('client.answer_preview') }}</th>
                                        <th>{{ __('client.action_type') }}</th>
                                        <th>{{ __('client.language') }}</th>
                                        <th>{{ __('client.status') }}</th>
                                        <th>{{ __('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="sortable-faqs">
                                    @foreach($faqs as $faq)
                                        <tr data-id="{{ $faq->id }}">
                                            <td>
                                                <i class="ri-drag-move-2-line text-muted cursor-move"></i>
                                                <span class="ms-2">{{ $faq->order }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ Str::limit($faq->getQuestion(), 50) }}</strong>
                                                @if($faq->quick_reply_text)
                                                    <br><small class="text-muted">{{ $faq->quick_reply_text }}</small>
                                                @endif
                                            </td>
                                            <td>{{ Str::limit($faq->getAnswer(), 60) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $faq->action_type === 'start_inquiry' ? 'primary' : ($faq->action_type === 'show_menu' ? 'info' : 'secondary') }}">
                                                    {{ ucfirst(str_replace('_', ' ', $faq->action_type)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    {{ strtoupper($faq->language) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input toggle-status" type="checkbox" 
                                                           data-id="{{ $faq->id }}" {{ $faq->is_active ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('client.chat-bot.faqs.edit', $faq) }}" 
                                                       class="btn btn-outline-primary" title="{{ __('common.edit') }}">
                                                        <i class="ri-edit-line"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger delete-faq" 
                                                            data-id="{{ $faq->id }}" title="{{ __('common.delete') }}">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="ri-question-line display-4 text-muted"></i>
                            </div>
                            <h5 class="text-muted">{{ __('client.no_faqs_found') }}</h5>
                            <p class="text-muted mb-4">{{ __('client.start_creating_faqs_desc') }}</p>
                            <a href="{{ route('client.chat-bot.faqs.create') }}" class="btn btn-primary me-2">
                                {{ __('client.create_first_faq') }}
                            </a>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#quickSetupModal">
                                {{ __('client.use_quick_setup') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Setup Modal -->
<div class="modal fade" id="quickSetupModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('client.quick_setup_faqs') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('client.chat-bot.faqs.quick-setup') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="business_type" class="form-label">{{ __('client.select_business_type') }}</label>
                        <select class="form-select" id="business_type" name="business_type" required>
                            <option value="">{{ __('client.choose_business_type') }}</option>
                            <option value="software">{{ __('client.software_company') }}</option>
                            <option value="restaurant">{{ __('client.restaurant') }}</option>
                            <option value="salon">{{ __('client.salon_spa') }}</option>
                            <option value="general">{{ __('client.general_business') }}</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="language" class="form-label">{{ __('client.primary_language') }}</label>
                        <select class="form-select" id="language" name="language" required>
                            <option value="en">{{ __('client.english') }}</option>
                            <option value="bn">{{ __('client.bengali') }}</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        {{ __('client.quick_setup_desc') }}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-success">{{ __('client.setup_faqs') }}</button>
                </div>
            </form>
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
                {{ __('client.delete_faq_confirmation') }}
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
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sortable
    const sortable = Sortable.create(document.getElementById('sortable-faqs'), {
        animation: 150,
        handle: '.ri-drag-move-2-line',
        onEnd: function(evt) {
            const itemIds = Array.from(evt.to.children).map(row => row.dataset.id);
            
            fetch('{{ route("client.chat-bot.faqs.reorder") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ order: itemIds })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update order numbers in UI
                    evt.to.querySelectorAll('tr').forEach((row, index) => {
                        row.querySelector('td span.ms-2').textContent = index + 1;
                    });
                }
            });
        }
    });

    // Toggle status
    document.querySelectorAll('.toggle-status').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const faqId = this.dataset.id;
            const isActive = this.checked;
            
            fetch(`/client/chat-bot/faqs/${faqId}/toggle-status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ is_active: isActive })
            });
        });
    });

    // Delete FAQ
    document.querySelectorAll('.delete-faq').forEach(btn => {
        btn.addEventListener('click', function() {
            const faqId = this.dataset.id;
            const deleteForm = document.getElementById('deleteForm');
            deleteForm.action = `/client/chat-bot/faqs/${faqId}`;
            
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        });
    });
});
</script>
@endpush
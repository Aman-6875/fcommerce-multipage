@extends('layouts.client')

@section('title', __('client.create_faq'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">{{ __('client.create_faq') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">{{ __('common.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('client.chat-bot.faqs.index') }}">{{ __('client.faq_management') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('client.create_faq') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">{{ __('client.faq_details') }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('client.chat-bot.faqs.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="language" class="form-label">{{ __('client.language') }}</label>
                                <select class="form-select @error('language') is-invalid @enderror" id="language" name="language" required>
                                    <option value="">{{ __('client.select_language') }}</option>
                                    <option value="en" {{ old('language') === 'en' ? 'selected' : '' }}>{{ __('client.english') }}</option>
                                    <option value="bn" {{ old('language') === 'bn' ? 'selected' : '' }}>{{ __('client.bengali') }}</option>
                                </select>
                                @error('language')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="action_type" class="form-label">{{ __('client.action_type') }}</label>
                                <select class="form-select @error('action_type') is-invalid @enderror" id="action_type" name="action_type" required>
                                    <option value="">{{ __('client.select_action_type') }}</option>
                                    <option value="answer_only" {{ old('action_type') === 'answer_only' ? 'selected' : '' }}>{{ __('client.answer_only') }}</option>
                                    <option value="start_inquiry" {{ old('action_type') === 'start_inquiry' ? 'selected' : '' }}>{{ __('client.start_inquiry') }}</option>
                                    <option value="show_menu" {{ old('action_type') === 'show_menu' ? 'selected' : '' }}>{{ __('client.show_menu') }}</option>
                                    <option value="custom" {{ old('action_type') === 'custom' ? 'selected' : '' }}>{{ __('client.custom') }}</option>
                                </select>
                                @error('action_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    <small class="text-muted">{{ __('client.action_type_help') }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="question_en" class="form-label">{{ __('client.question_english') }}</label>
                            <textarea class="form-control @error('question_en') is-invalid @enderror" 
                                      id="question_en" name="question_en" rows="2" required>{{ old('question_en') }}</textarea>
                            @error('question_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="question_bn" class="form-label">{{ __('client.question_bengali') }}</label>
                            <textarea class="form-control @error('question_bn') is-invalid @enderror" 
                                      id="question_bn" name="question_bn" rows="2">{{ old('question_bn') }}</textarea>
                            @error('question_bn')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <small class="text-muted">{{ __('client.bengali_question_optional') }}</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="answer_en" class="form-label">{{ __('client.answer_english') }}</label>
                            <textarea class="form-control @error('answer_en') is-invalid @enderror" 
                                      id="answer_en" name="answer_en" rows="4" required>{{ old('answer_en') }}</textarea>
                            @error('answer_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="answer_bn" class="form-label">{{ __('client.answer_bengali') }}</label>
                            <textarea class="form-control @error('answer_bn') is-invalid @enderror" 
                                      id="answer_bn" name="answer_bn" rows="4">{{ old('answer_bn') }}</textarea>
                            @error('answer_bn')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <small class="text-muted">{{ __('client.bengali_answer_optional') }}</small>
                            </div>
                        </div>

                        <div class="mb-3" id="quick_reply_section">
                            <label for="quick_reply_text" class="form-label">{{ __('client.quick_reply_text') }}</label>
                            <input type="text" class="form-control @error('quick_reply_text') is-invalid @enderror" 
                                   id="quick_reply_text" name="quick_reply_text" value="{{ old('quick_reply_text') }}" 
                                   placeholder="{{ __('client.quick_reply_placeholder') }}">
                            @error('quick_reply_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <small class="text-muted">{{ __('client.quick_reply_help') }}</small>
                            </div>
                        </div>

                        <div class="mb-3" id="custom_action_section" style="display: none;">
                            <label for="custom_action" class="form-label">{{ __('client.custom_action') }}</label>
                            <input type="text" class="form-control @error('custom_action') is-invalid @enderror" 
                                   id="custom_action" name="custom_action" value="{{ old('custom_action') }}" 
                                   placeholder="{{ __('client.custom_action_placeholder') }}">
                            @error('custom_action')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <small class="text-muted">{{ __('client.custom_action_help') }}</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">{{ __('client.active') }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('client.chat-bot.faqs.index') }}" class="btn btn-secondary">
                                <i class="ri-arrow-left-line me-1"></i>{{ __('common.back') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i>{{ __('client.create_faq') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('client.action_type_guide') }}</h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="actionGuide">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#answer-only">
                                    {{ __('client.answer_only') }}
                                </button>
                            </h2>
                            <div id="answer-only" class="accordion-collapse collapse" data-bs-parent="#actionGuide">
                                <div class="accordion-body">
                                    {{ __('client.answer_only_desc') }}
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#start-inquiry">
                                    {{ __('client.start_inquiry') }}
                                </button>
                            </h2>
                            <div id="start-inquiry" class="accordion-collapse collapse" data-bs-parent="#actionGuide">
                                <div class="accordion-body">
                                    {{ __('client.start_inquiry_desc') }}
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#show-menu">
                                    {{ __('client.show_menu') }}
                                </button>
                            </h2>
                            <div id="show-menu" class="accordion-collapse collapse" data-bs-parent="#actionGuide">
                                <div class="accordion-body">
                                    {{ __('client.show_menu_desc') }}
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#custom">
                                    {{ __('client.custom') }}
                                </button>
                            </h2>
                            <div id="custom" class="accordion-collapse collapse" data-bs-parent="#actionGuide">
                                <div class="accordion-body">
                                    {{ __('client.custom_desc') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('client.tips') }}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="ri-lightbulb-line text-warning me-2"></i>
                            {{ __('client.tip_1') }}
                        </li>
                        <li class="mb-2">
                            <i class="ri-lightbulb-line text-warning me-2"></i>
                            {{ __('client.tip_2') }}
                        </li>
                        <li class="mb-2">
                            <i class="ri-lightbulb-line text-warning me-2"></i>
                            {{ __('client.tip_3') }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const actionType = document.getElementById('action_type');
    const customActionSection = document.getElementById('custom_action_section');
    
    actionType.addEventListener('change', function() {
        if (this.value === 'custom') {
            customActionSection.style.display = 'block';
        } else {
            customActionSection.style.display = 'none';
        }
    });
    
    // Trigger on page load if custom is selected
    if (actionType.value === 'custom') {
        customActionSection.style.display = 'block';
    }
});
</script>
@endpush
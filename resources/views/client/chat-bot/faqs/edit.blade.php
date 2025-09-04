@extends('layouts.client')

@section('title', __('client.edit_faq'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">{{ __('client.edit_faq') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('client.dashboard') }}">{{ __('common.dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('client.chat-bot.faqs.index') }}">{{ __('client.faq_management') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('client.edit_faq') }}</li>
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
                    <form action="{{ route('client.chat-bot.faqs.update', $faq) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="language" class="form-label">{{ __('client.language') }}</label>
                                <select class="form-select @error('language') is-invalid @enderror" id="language" name="language" required>
                                    <option value="">{{ __('client.select_language') }}</option>
                                    <option value="en" {{ old('language', $faq->language) === 'en' ? 'selected' : '' }}>{{ __('client.english') }}</option>
                                    <option value="bn" {{ old('language', $faq->language) === 'bn' ? 'selected' : '' }}>{{ __('client.bengali') }}</option>
                                </select>
                                @error('language')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="action_type" class="form-label">{{ __('client.action_type') }}</label>
                                <select class="form-select @error('action_type') is-invalid @enderror" id="action_type" name="action_type" required>
                                    <option value="">{{ __('client.select_action_type') }}</option>
                                    <option value="answer_only" {{ old('action_type', $faq->action_type) === 'answer_only' ? 'selected' : '' }}>{{ __('client.answer_only') }}</option>
                                    <option value="start_inquiry" {{ old('action_type', $faq->action_type) === 'start_inquiry' ? 'selected' : '' }}>{{ __('client.start_inquiry') }}</option>
                                    <option value="show_menu" {{ old('action_type', $faq->action_type) === 'show_menu' ? 'selected' : '' }}>{{ __('client.show_menu') }}</option>
                                    <option value="custom" {{ old('action_type', $faq->action_type) === 'custom' ? 'selected' : '' }}>{{ __('client.custom') }}</option>
                                </select>
                                @error('action_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="question_en" class="form-label">{{ __('client.question_english') }}</label>
                            <textarea class="form-control @error('question_en') is-invalid @enderror" 
                                      id="question_en" name="question_en" rows="2" required>{{ old('question_en', $faq->question_en) }}</textarea>
                            @error('question_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="question_bn" class="form-label">{{ __('client.question_bengali') }}</label>
                            <textarea class="form-control @error('question_bn') is-invalid @enderror" 
                                      id="question_bn" name="question_bn" rows="2">{{ old('question_bn', $faq->question_bn) }}</textarea>
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
                                      id="answer_en" name="answer_en" rows="4" required>{{ old('answer_en', $faq->answer_en) }}</textarea>
                            @error('answer_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="answer_bn" class="form-label">{{ __('client.answer_bengali') }}</label>
                            <textarea class="form-control @error('answer_bn') is-invalid @enderror" 
                                      id="answer_bn" name="answer_bn" rows="4">{{ old('answer_bn', $faq->answer_bn) }}</textarea>
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
                                   id="quick_reply_text" name="quick_reply_text" value="{{ old('quick_reply_text', $faq->quick_reply_text) }}" 
                                   placeholder="{{ __('client.quick_reply_placeholder') }}">
                            @error('quick_reply_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <small class="text-muted">{{ __('client.quick_reply_help') }}</small>
                            </div>
                        </div>

                        <div class="mb-3" id="custom_action_section" style="{{ old('action_type', $faq->action_type) === 'custom' ? 'display: block;' : 'display: none;' }}">
                            <label for="custom_action" class="form-label">{{ __('client.custom_action') }}</label>
                            <input type="text" class="form-control @error('custom_action') is-invalid @enderror" 
                                   id="custom_action" name="custom_action" value="{{ old('custom_action', $faq->custom_action) }}" 
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
                                           {{ old('is_active', $faq->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">{{ __('client.active') }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('client.chat-bot.faqs.index') }}" class="btn btn-secondary">
                                <i class="ri-arrow-left-line me-1"></i>{{ __('common.back') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i>{{ __('client.update_faq') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('client.preview') }}</h5>
                </div>
                <div class="card-body">
                    <div class="border rounded p-3 mb-3">
                        <h6 class="text-muted mb-2">{{ __('client.question') }}:</h6>
                        <p class="mb-1" id="preview-question">{{ $faq->getQuestion() }}</p>
                        @if($faq->quick_reply_text)
                            <small class="badge bg-light text-dark">{{ $faq->quick_reply_text }}</small>
                        @endif
                    </div>
                    
                    <div class="border rounded p-3">
                        <h6 class="text-muted mb-2">{{ __('client.answer') }}:</h6>
                        <p class="mb-0" id="preview-answer">{{ $faq->getAnswer() }}</p>
                    </div>
                    
                    <div class="mt-3">
                        <span class="badge bg-{{ $faq->action_type === 'start_inquiry' ? 'primary' : ($faq->action_type === 'show_menu' ? 'info' : 'secondary') }}">
                            {{ ucfirst(str_replace('_', ' ', $faq->action_type)) }}
                        </span>
                        <span class="badge bg-light text-dark ms-2">{{ strtoupper($faq->language) }}</span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ __('client.faq_stats') }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ __('client.created') }}:</span>
                        <span class="text-muted">{{ $faq->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ __('client.last_updated') }}:</span>
                        <span class="text-muted">{{ $faq->updated_at->format('M d, Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>{{ __('client.order_position') }}:</span>
                        <span class="text-muted">#{{ $faq->order }}</span>
                    </div>
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
    
    // Live preview updates
    const questionEn = document.getElementById('question_en');
    const questionBn = document.getElementById('question_bn');
    const answerEn = document.getElementById('answer_en');
    const answerBn = document.getElementById('answer_bn');
    const language = document.getElementById('language');
    const previewQuestion = document.getElementById('preview-question');
    const previewAnswer = document.getElementById('preview-answer');
    
    function updatePreview() {
        const lang = language.value;
        const question = lang === 'bn' && questionBn.value ? questionBn.value : questionEn.value;
        const answer = lang === 'bn' && answerBn.value ? answerBn.value : answerEn.value;
        
        previewQuestion.textContent = question || '{{ __("client.question_preview") }}';
        previewAnswer.textContent = answer || '{{ __("client.answer_preview") }}';
    }
    
    [questionEn, questionBn, answerEn, answerBn, language].forEach(input => {
        input.addEventListener('input', updatePreview);
        input.addEventListener('change', updatePreview);
    });
});
</script>
@endpush
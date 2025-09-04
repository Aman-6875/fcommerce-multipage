@extends('layouts.client')

@section('title', 'Create Workflow')

@section('content')
<div class="container-fluid px-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Create Workflow</h1>
                    <p class="text-muted mb-0">Build a conversation flow for your customers</p>
                </div>
                <a href="{{ route('client.workflows.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Workflows
                </a>
            </div>

            <!-- Workflow Builder -->
            <form action="{{ route('client.workflows.store') }}" method="POST" id="workflowForm">
                @csrf
                
                <!-- Basic Information -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="m-0 fw-bold">Basic Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Workflow Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="facebook_page_id" class="form-label">Facebook Page <span class="text-danger">*</span></label>
                                    <select class="form-select @error('facebook_page_id') is-invalid @enderror" 
                                            id="facebook_page_id" name="facebook_page_id" required>
                                        @foreach($facebookPages as $page)
                                            <option value="{{ $page->id }}" 
                                                {{ (old('facebook_page_id', $selectedPageId) == $page->id) ? 'selected' : '' }}>
                                                {{ $page->page_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('facebook_page_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3" 
                                              placeholder="Describe what this workflow does...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Language Settings -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Supported Languages <span class="text-danger">*</span></label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="supported_languages[]" 
                                               value="en" id="lang_en" checked>
                                        <label class="form-check-label" for="lang_en">English</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="supported_languages[]" 
                                               value="bn" id="lang_bn" {{ in_array('bn', old('supported_languages', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="lang_bn">বাংলা (Bangla)</label>
                                    </div>
                                    @error('supported_languages')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="default_language" class="form-label">Default Language <span class="text-danger">*</span></label>
                                    <select class="form-select @error('default_language') is-invalid @enderror" 
                                            id="default_language" name="default_language" required>
                                        <option value="en" {{ old('default_language') == 'en' ? 'selected' : '' }}>English</option>
                                        <option value="bn" {{ old('default_language') == 'bn' ? 'selected' : '' }}>বাংলা (Bangla)</option>
                                    </select>
                                    @error('default_language')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Workflow Builder -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold">Workflow Steps</h6>
                        <button type="button" class="btn btn-light btn-sm" onclick="addStep()">
                            <i class="fas fa-plus me-1"></i>Add Step
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="workflowSteps" class="workflow-builder">
                            <!-- Steps will be added here dynamically -->
                        </div>
                        
                        <div id="emptyWorkflow" class="text-center py-5">
                            <i class="fas fa-project-diagram text-muted mb-3" style="font-size: 3rem;"></i>
                            <h5 class="text-muted">No steps yet</h5>
                            <p class="text-muted">Add your first step to start building the workflow</p>
                            <button type="button" class="btn btn-primary" onclick="addStep()">
                                <i class="fas fa-plus me-2"></i>Add First Step
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Hidden JSON field -->
                <input type="hidden" name="workflow_definition" id="workflow_definition" value="{{ old('workflow_definition', '{"steps":[]}') }}">
                
                <!-- Submit Buttons -->
                <div class="card shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="window.history.back()">Cancel</button>
                            <div>
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-save me-2"></i>Save as Draft
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Step Templates -->
<div id="stepTemplates" style="display: none;">
    <!-- Product Selector Template -->
    <div class="step-template" data-type="product_selector">
        <div class="step-card card mb-3" data-step-index="0">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fas fa-shopping-cart text-primary me-2"></i>
                    <strong>Product Selection</strong>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="editStep(this)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeStep(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="step-config">
                    <!-- Step configuration will be filled here -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Form Template -->
    <div class="step-template" data-type="form">
        <div class="step-card card mb-3" data-step-index="0">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fas fa-wpforms text-success me-2"></i>
                    <strong>Form Input</strong>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="editStep(this)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeStep(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="step-config">
                    <!-- Step configuration will be filled here -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Choice Template -->
    <div class="step-template" data-type="choice">
        <div class="step-card card mb-3" data-step-index="0">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fas fa-list-ul text-warning me-2"></i>
                    <strong>Multiple Choice</strong>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="editStep(this)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeStep(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="step-config">
                    <!-- Step configuration will be filled here -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Confirmation Template -->
    <div class="step-template" data-type="confirmation">
        <div class="step-card card mb-3" data-step-index="0">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle text-info me-2"></i>
                    <strong>Order Confirmation</strong>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="editStep(this)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeStep(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="step-config">
                    <!-- Step configuration will be filled here -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Info Display Template -->
    <div class="step-template" data-type="info_display">
        <div class="step-card card mb-3" data-step-index="0">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle text-secondary me-2"></i>
                    <strong>Welcome Message</strong>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="editStep(this)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeStep(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="step-config">
                    <!-- Step configuration will be filled here -->
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.workflow-builder {
    min-height: 200px;
}

.step-card {
    border-left: 4px solid #007bff;
    transition: all 0.3s ease;
}

.step-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.step-card[data-type="product_selector"] {
    border-left-color: #007bff;
}

.step-card[data-type="form"] {
    border-left-color: #28a745;
}

.step-card[data-type="choice"] {
    border-left-color: #ffc107;
}

.step-card[data-type="confirmation"] {
    border-left-color: #17a2b8;
}

.step-card[data-type="info_display"] {
    border-left-color: #6c757d;
}

.sortable-ghost {
    opacity: 0.5;
}

.sortable-chosen {
    transform: scale(1.02);
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
let workflowSteps = [];
let stepCounter = 0;

// Initialize sortable
document.addEventListener('DOMContentLoaded', function() {
    const workflowContainer = document.getElementById('workflowSteps');
    new Sortable(workflowContainer, {
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onEnd: function() {
            updateStepIndices();
            updateWorkflowDefinition();
        }
    });
    
    // Load existing workflow if editing
    const existingDefinition = document.getElementById('workflow_definition').value;
    if (existingDefinition && existingDefinition !== '{"steps":[]}') {
        loadWorkflowDefinition(JSON.parse(existingDefinition));
    }
    
    updateEmptyState();
});

function addStep() {
    // Show step type selector modal
    showStepTypeSelector();
}

function showStepTypeSelector() {
    const modal = `
        <div class="modal fade" id="stepTypeModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Choose Step Type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <button type="button" class="btn btn-outline-primary w-100 h-100 p-4" onclick="createStep('product_selector')">
                                    <i class="fas fa-shopping-cart mb-2" style="font-size: 2rem;"></i>
                                    <div><strong>Product Selection</strong></div>
                                    <small class="text-muted">Let customers choose products</small>
                                </button>
                            </div>
                            <div class="col-md-6 mb-3">
                                <button type="button" class="btn btn-outline-success w-100 h-100 p-4" onclick="createStep('form')">
                                    <i class="fas fa-wpforms mb-2" style="font-size: 2rem;"></i>
                                    <div><strong>Form Input</strong></div>
                                    <small class="text-muted">Collect customer information</small>
                                </button>
                            </div>
                            <div class="col-md-6 mb-3">
                                <button type="button" class="btn btn-outline-warning w-100 h-100 p-4" onclick="createStep('choice')">
                                    <i class="fas fa-list-ul mb-2" style="font-size: 2rem;"></i>
                                    <div><strong>Multiple Choice</strong></div>
                                    <small class="text-muted">Give customers options</small>
                                </button>
                            </div>
                            <div class="col-md-6 mb-3">
                                <button type="button" class="btn btn-outline-info w-100 h-100 p-4" onclick="createStep('confirmation')">
                                    <i class="fas fa-check-circle mb-2" style="font-size: 2rem;"></i>
                                    <div><strong>Confirmation</strong></div>
                                    <small class="text-muted">Final order confirmation</small>
                                </button>
                            </div>
                            <div class="col-md-6 mb-3">
                                <button type="button" class="btn btn-outline-secondary w-100 h-100 p-4" onclick="createStep('info_display')">
                                    <i class="fas fa-info-circle mb-2" style="font-size: 2rem;"></i>
                                    <div><strong>Welcome Message</strong></div>
                                    <small class="text-muted">Display information to customer</small>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modal);
    const modalEl = new bootstrap.Modal(document.getElementById('stepTypeModal'));
    modalEl.show();
    
    // Remove modal after hiding
    document.getElementById('stepTypeModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function createStep(type) {
    // Close modal
    bootstrap.Modal.getInstance(document.getElementById('stepTypeModal')).hide();
    
    const stepId = 'step_' + (++stepCounter);
    const stepData = {
        id: stepId,
        type: type,
        labels: {
            en: { title: getDefaultTitle(type, 'en'), description: '' },
            bn: { title: getDefaultTitle(type, 'bn'), description: '' }
        },
        config: getDefaultConfig(type)
    };
    
    workflowSteps.push(stepData);
    renderStep(stepData, workflowSteps.length - 1);
    updateEmptyState();
    updateWorkflowDefinition();
}

function getDefaultTitle(type, language) {
    const titles = {
        product_selector: {
            en: 'Select Products',
            bn: 'পণ্য নির্বাচন করুন'
        },
        form: {
            en: 'Customer Information',
            bn: 'গ্রাহকের তথ্য'
        },
        choice: {
            en: 'Choose Option',
            bn: 'অপশন বেছে নিন'
        },
        confirmation: {
            en: 'Confirm Order',
            bn: 'অর্ডার নিশ্চিত করুন'
        },
        info_display: {
            en: 'Welcome Message',
            bn: 'স্বাগতম বার্তা'
        }
    };
    
    return titles[type]?.[language] || type;
}

function getDefaultConfig(type) {
    const configs = {
        product_selector: {
            multiple: true,
            min_products: 1,
            max_products: 5,
            allow_quantity: true
        },
        form: {
            fields: [
                {
                    name: 'name',
                    type: 'text',
                    required: true,
                    labels: { en: 'Full Name', bn: 'পূর্ণ নাম' }
                }
            ]
        },
        choice: {
            choices: []
        },
        confirmation: {
            show_summary: true
        },
        info_display: {
            auto_continue: false
        }
    };
    
    return configs[type] || {};
}

function renderStep(stepData, index) {
    const template = document.querySelector(`[data-type="${stepData.type}"]`).cloneNode(true);
    const stepElement = template.querySelector('.step-card');
    stepElement.setAttribute('data-step-index', index);
    stepElement.setAttribute('data-step-id', stepData.id);
    
    // Update step title
    const title = stepElement.querySelector('strong');
    title.textContent = stepData.labels.en.title;
    
    // Add step configuration preview
    const configDiv = stepElement.querySelector('.step-config');
    configDiv.innerHTML = getStepConfigPreview(stepData);
    
    document.getElementById('workflowSteps').appendChild(stepElement);
}

function getStepConfigPreview(stepData) {
    const type = stepData.type;
    const config = stepData.config;
    
    switch (type) {
        case 'product_selector':
            return `
                <div class="small text-muted">
                    <i class="fas fa-cog me-1"></i>
                    ${config.multiple ? 'Multiple selection' : 'Single selection'} • 
                    ${config.allow_quantity ? 'With quantities' : 'No quantities'} • 
                    Max ${config.max_products || 'unlimited'} products
                </div>
            `;
        case 'form':
            const fieldCount = config.fields?.length || 0;
            return `
                <div class="small text-muted">
                    <i class="fas fa-cog me-1"></i>
                    ${fieldCount} field${fieldCount !== 1 ? 's' : ''}
                </div>
            `;
        case 'choice':
            const choiceCount = config.choices?.length || 0;
            return `
                <div class="small text-muted">
                    <i class="fas fa-cog me-1"></i>
                    ${choiceCount} choice${choiceCount !== 1 ? 's' : ''}
                </div>
            `;
        case 'confirmation':
            return `
                <div class="small text-muted">
                    <i class="fas fa-cog me-1"></i>
                    ${config.show_summary ? 'With order summary' : 'Simple confirmation'}
                </div>
            `;
        case 'info_display':
            return `
                <div class="small text-muted">
                    <i class="fas fa-cog me-1"></i>
                    ${config.auto_continue ? 'Auto-continue' : 'Wait for customer input'}
                </div>
            `;
        default:
            return '';
    }
}

function editStep(button) {
    const stepCard = button.closest('.step-card');
    const stepIndex = parseInt(stepCard.getAttribute('data-step-index'));
    const stepData = workflowSteps[stepIndex];
    
    // Show step editor modal (implement based on step type)
    showStepEditor(stepData, stepIndex);
}

function showStepEditor(stepData, stepIndex) {
    const modalId = 'stepEditorModal';
    const existingModal = document.getElementById(modalId);
    if (existingModal) {
        existingModal.remove();
    }
    
    let modalContent = '';
    
    switch (stepData.type) {
        case 'info_display':
            modalContent = createInfoDisplayEditor(stepData, stepIndex);
            break;
        case 'form':
            modalContent = createFormEditor(stepData, stepIndex);
            break;
        case 'choice':
            modalContent = createChoiceEditor(stepData, stepIndex);
            break;
        case 'product_selector':
            modalContent = createProductSelectorEditor(stepData, stepIndex);
            break;
        case 'confirmation':
            modalContent = createConfirmationEditor(stepData, stepIndex);
            break;
        default:
            modalContent = createGenericEditor(stepData, stepIndex);
    }
    
    const modal = `
        <div class="modal fade" id="${modalId}" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    ${modalContent}
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modal);
    const modalEl = new bootstrap.Modal(document.getElementById(modalId));
    modalEl.show();
    
    // Remove modal after hiding
    document.getElementById(modalId).addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function createInfoDisplayEditor(stepData, stepIndex) {
    return `
        <div class="modal-header">
            <h5 class="modal-title">
                <i class="fas fa-info-circle me-2"></i>Edit Welcome Message
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <form id="stepEditorForm">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Title (English)</label>
                        <input type="text" class="form-control" name="title_en" value="${stepData.labels.en.title || ''}" placeholder="Welcome to Our Store">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Title (বাংলা)</label>
                        <input type="text" class="form-control" name="title_bn" value="${stepData.labels.bn?.title || ''}" placeholder="আমাদের দোকানে স্বাগতম">
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Message (English)</label>
                        <textarea class="form-control" name="description_en" rows="4" placeholder="Hi! Welcome to our software company. How can we help you?">${stepData.labels.en.description || ''}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Message (বাংলা)</label>
                        <textarea class="form-control" name="description_bn" rows="4" placeholder="হাই! আমাদের সফটওয়্যার কোম্পানিতে স্বাগতম।">${stepData.labels.bn?.description || ''}</textarea>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="auto_continue" ${stepData.config?.auto_continue ? 'checked' : ''}>
                        <label class="form-check-label">Auto-continue to next step</label>
                    </div>
                    <small class="form-text text-muted">If unchecked, customer needs to type something to continue</small>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="saveStepChanges(${stepIndex})">Save Changes</button>
        </div>
    `;
}

function createChoiceEditor(stepData, stepIndex) {
    const choices = stepData.config?.choices || [];
    
    let choicesHtml = '';
    choices.forEach((choice, index) => {
        choicesHtml += `
            <div class="choice-item border p-3 mb-2" data-choice-index="${index}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>Choice ${index + 1}</strong>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeChoice(${index})">Remove</button>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="choice_${index}_en" value="${choice.labels?.en || ''}" placeholder="English label">
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="choice_${index}_bn" value="${choice.labels?.bn || ''}" placeholder="বাংলা label">
                    </div>
                </div>
            </div>
        `;
    });
    
    return `
        <div class="modal-header">
            <h5 class="modal-title">
                <i class="fas fa-list-ul me-2"></i>Edit Multiple Choice
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <form id="stepEditorForm">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Question (English)</label>
                        <input type="text" class="form-control" name="title_en" value="${stepData.labels.en.title || ''}" placeholder="What service interests you?">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Question (বাংলা)</label>
                        <input type="text" class="form-control" name="title_bn" value="${stepData.labels.bn?.title || ''}" placeholder="কোন সেবায় আগ্রহী?">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Choices</label>
                    <div id="choicesContainer">
                        ${choicesHtml}
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addChoice()">
                        <i class="fas fa-plus me-1"></i>Add Choice
                    </button>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="saveStepChanges(${stepIndex})">Save Changes</button>
        </div>
    `;
}

function createFormEditor(stepData, stepIndex) {
    const fields = stepData.config?.fields || [];
    
    let fieldsHtml = '';
    fields.forEach((field, index) => {
        fieldsHtml += `
            <div class="field-item border p-3 mb-2" data-field-index="${index}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>Field ${index + 1}</strong>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeField(${index})">Remove</button>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <select class="form-select" name="field_${index}_type">
                            <option value="text" ${field.type === 'text' ? 'selected' : ''}>Text</option>
                            <option value="email" ${field.type === 'email' ? 'selected' : ''}>Email</option>
                            <option value="tel" ${field.type === 'tel' ? 'selected' : ''}>Phone</option>
                            <option value="textarea" ${field.type === 'textarea' ? 'selected' : ''}>Textarea</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="field_${index}_name" value="${field.name || ''}" placeholder="Field name">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="field_${index}_label_en" value="${field.labels?.en || ''}" placeholder="English label">
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="field_${index}_required" ${field.required ? 'checked' : ''}>
                            <label class="form-check-label">Required</label>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    return `
        <div class="modal-header">
            <h5 class="modal-title">
                <i class="fas fa-wpforms me-2"></i>Edit Form
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <form id="stepEditorForm">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Form Title (English)</label>
                        <input type="text" class="form-control" name="title_en" value="${stepData.labels.en.title || ''}" placeholder="Customer Information">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Form Title (বাংলা)</label>
                        <input type="text" class="form-control" name="title_bn" value="${stepData.labels.bn?.title || ''}" placeholder="গ্রাহকের তথ্য">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Form Fields</label>
                    <div id="fieldsContainer">
                        ${fieldsHtml}
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addField()">
                        <i class="fas fa-plus me-1"></i>Add Field
                    </button>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="saveStepChanges(${stepIndex})">Save Changes</button>
        </div>
    `;
}

function createGenericEditor(stepData, stepIndex) {
    return createInfoDisplayEditor(stepData, stepIndex);
}

function createProductSelectorEditor(stepData, stepIndex) {
    return createGenericEditor(stepData, stepIndex);
}

function createConfirmationEditor(stepData, stepIndex) {
    return createGenericEditor(stepData, stepIndex);
}

function saveStepChanges(stepIndex) {
    const form = document.getElementById('stepEditorForm');
    const formData = new FormData(form);
    const stepData = workflowSteps[stepIndex];
    
    // Update step data based on step type
    if (stepData.type === 'info_display') {
        stepData.labels.en.title = formData.get('title_en') || '';
        stepData.labels.en.description = formData.get('description_en') || '';
        stepData.labels.bn = stepData.labels.bn || {};
        stepData.labels.bn.title = formData.get('title_bn') || '';
        stepData.labels.bn.description = formData.get('description_bn') || '';
        stepData.config.auto_continue = formData.get('auto_continue') === 'on';
    }
    else if (stepData.type === 'choice') {
        stepData.labels.en.title = formData.get('title_en') || '';
        stepData.labels.bn = stepData.labels.bn || {};
        stepData.labels.bn.title = formData.get('title_bn') || '';
        
        // Save choices
        const choices = [];
        let choiceIndex = 0;
        while (formData.get(`choice_${choiceIndex}_en`)) {
            choices.push({
                id: `choice_${choiceIndex + 1}`,
                labels: {
                    en: formData.get(`choice_${choiceIndex}_en`),
                    bn: formData.get(`choice_${choiceIndex}_bn`) || ''
                }
            });
            choiceIndex++;
        }
        stepData.config.choices = choices;
    }
    else if (stepData.type === 'form') {
        stepData.labels.en.title = formData.get('title_en') || '';
        stepData.labels.bn = stepData.labels.bn || {};
        stepData.labels.bn.title = formData.get('title_bn') || '';
        
        // Save fields
        const fields = [];
        let fieldIndex = 0;
        while (formData.get(`field_${fieldIndex}_name`)) {
            fields.push({
                name: formData.get(`field_${fieldIndex}_name`),
                type: formData.get(`field_${fieldIndex}_type`),
                required: formData.get(`field_${fieldIndex}_required`) === 'on',
                labels: {
                    en: formData.get(`field_${fieldIndex}_label_en`),
                    bn: formData.get(`field_${fieldIndex}_label_bn`) || ''
                }
            });
            fieldIndex++;
        }
        stepData.config.fields = fields;
    }
    
    // Re-render the step
    const stepCard = document.querySelector(`[data-step-index="${stepIndex}"]`);
    stepCard.querySelector('strong').textContent = stepData.labels.en.title;
    stepCard.querySelector('.step-config').innerHTML = getStepConfigPreview(stepData);
    
    // Update workflow definition
    updateWorkflowDefinition();
    
    // Close modal
    bootstrap.Modal.getInstance(document.getElementById('stepEditorModal')).hide();
}

function addChoice() {
    const container = document.getElementById('choicesContainer');
    const choiceIndex = container.children.length;
    
    const choiceHtml = `
        <div class="choice-item border p-3 mb-2" data-choice-index="${choiceIndex}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>Choice ${choiceIndex + 1}</strong>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeChoice(${choiceIndex})">Remove</button>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="choice_${choiceIndex}_en" placeholder="English label">
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control" name="choice_${choiceIndex}_bn" placeholder="বাংলা label">
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', choiceHtml);
}

function removeChoice(index) {
    document.querySelector(`[data-choice-index="${index}"]`).remove();
}

function addField() {
    const container = document.getElementById('fieldsContainer');
    const fieldIndex = container.children.length;
    
    const fieldHtml = `
        <div class="field-item border p-3 mb-2" data-field-index="${fieldIndex}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>Field ${fieldIndex + 1}</strong>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeField(${fieldIndex})">Remove</button>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <select class="form-select" name="field_${fieldIndex}_type">
                        <option value="text">Text</option>
                        <option value="email">Email</option>
                        <option value="tel">Phone</option>
                        <option value="textarea">Textarea</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" name="field_${fieldIndex}_name" placeholder="Field name">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" name="field_${fieldIndex}_label_en" placeholder="English label">
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="field_${fieldIndex}_required">
                        <label class="form-check-label">Required</label>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', fieldHtml);
}

function removeField(index) {
    document.querySelector(`[data-field-index="${index}"]`).remove();
}

function removeStep(button) {
    if (!confirm('Are you sure you want to remove this step?')) {
        return;
    }
    
    const stepCard = button.closest('.step-card');
    const stepIndex = parseInt(stepCard.getAttribute('data-step-index'));
    
    workflowSteps.splice(stepIndex, 1);
    stepCard.remove();
    
    updateStepIndices();
    updateEmptyState();
    updateWorkflowDefinition();
}

function updateStepIndices() {
    const stepCards = document.querySelectorAll('.step-card');
    stepCards.forEach((card, index) => {
        card.setAttribute('data-step-index', index);
    });
}

function updateEmptyState() {
    const emptyState = document.getElementById('emptyWorkflow');
    const workflowContainer = document.getElementById('workflowSteps');
    
    if (workflowSteps.length === 0) {
        emptyState.style.display = 'block';
        workflowContainer.style.display = 'none';
    } else {
        emptyState.style.display = 'none';
        workflowContainer.style.display = 'block';
    }
}

function updateWorkflowDefinition() {
    const definition = {
        steps: workflowSteps
    };
    
    document.getElementById('workflow_definition').value = JSON.stringify(definition);
}

function loadWorkflowDefinition(definition) {
    workflowSteps = definition.steps || [];
    workflowSteps.forEach((step, index) => {
        renderStep(step, index);
    });
    updateEmptyState();
}

// Form validation
document.getElementById('workflowForm').addEventListener('submit', function(e) {
    if (workflowSteps.length === 0) {
        e.preventDefault();
        alert('Please add at least one step to your workflow.');
        return;
    }
    
    updateWorkflowDefinition();
});
</script>
@endpush
@endsection
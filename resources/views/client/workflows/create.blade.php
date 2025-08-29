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
@extends('layouts.client')

@section('title', 'Edit Workflow')

@section('content')
<div class="container-fluid px-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Edit Workflow</h1>
                    <p class="text-muted mb-0">Modify your conversation flow</p>
                </div>
                <div class="d-flex gap-2">
                    @if(!$workflow->isPublished())
                        <form action="{{ route('client.workflows.publish', $workflow) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-play me-2"></i>Publish
                            </button>
                        </form>
                    @else
                        <form action="{{ route('client.workflows.unpublish', $workflow) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-pause me-2"></i>Unpublish
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('client.workflows.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Workflows
                    </a>
                </div>
            </div>

            <!-- Status Alert -->
            @if($workflow->isPublished())
                <div class="alert alert-success d-flex align-items-center" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>
                        This workflow is <strong>published</strong> and active for customers.
                        @if($workflow->version > 1)
                            <span class="badge bg-light text-dark ms-2">Version {{ $workflow->version }}</span>
                        @endif
                    </div>
                </div>
            @else
                <div class="alert alert-warning d-flex align-items-center" role="alert">
                    <i class="fas fa-pause-circle me-2"></i>
                    <div>This workflow is in <strong>draft mode</strong>. Customers cannot see it until published.</div>
                </div>
            @endif

            <!-- Workflow Builder -->
            <form action="{{ route('client.workflows.update', $workflow) }}" method="POST" id="workflowForm">
                @csrf
                @method('PUT')
                
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
                                           id="name" name="name" value="{{ old('name', $workflow->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="facebook_page_id" class="form-label">Facebook Page</label>
                                    <select class="form-select" id="facebook_page_id" name="facebook_page_id" disabled>
                                        @foreach($facebookPages as $page)
                                            <option value="{{ $page->id }}" 
                                                {{ $workflow->facebook_page_id == $page->id ? 'selected' : '' }}>
                                                {{ $page->page_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">Cannot change page for existing workflow</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3" 
                                              placeholder="Describe what this workflow does...">{{ old('description', $workflow->description) }}</textarea>
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
                                               value="en" id="lang_en" {{ in_array('en', old('supported_languages', $workflow->supported_languages)) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="lang_en">English</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="supported_languages[]" 
                                               value="bn" id="lang_bn" {{ in_array('bn', old('supported_languages', $workflow->supported_languages)) ? 'checked' : '' }}>
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
                                        <option value="en" {{ old('default_language', $workflow->default_language) == 'en' ? 'selected' : '' }}>English</option>
                                        <option value="bn" {{ old('default_language', $workflow->default_language) == 'bn' ? 'selected' : '' }}>বাংলা (Bangla)</option>
                                    </select>
                                    @error('default_language')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Workflow Steps Preview -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold">Workflow Steps ({{ $workflow->getTotalSteps() }} steps)</h6>
                        <button type="button" class="btn btn-light btn-sm" onclick="toggleEditMode()">
                            <i class="fas fa-edit me-1"></i>Edit Steps
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Read-only preview of steps -->
                        <div id="stepsPreview">
                            @if($workflow->getSteps())
                                @foreach($workflow->getSteps() as $index => $step)
                                    <div class="card mb-3 border-start border-4 border-primary">
                                        <div class="card-body py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <span class="badge bg-primary">{{ $index + 1 }}</span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">
                                                        @switch($step['type'])
                                                            @case('info_display')
                                                                <i class="fas fa-info-circle text-info me-2"></i>
                                                                @break
                                                            @case('product_selector')
                                                                <i class="fas fa-shopping-cart text-primary me-2"></i>
                                                                @break
                                                            @case('form')
                                                                <i class="fas fa-wpforms text-success me-2"></i>
                                                                @break
                                                            @case('choice')
                                                                <i class="fas fa-list-ul text-warning me-2"></i>
                                                                @break
                                                            @case('confirmation')
                                                                <i class="fas fa-check-circle text-info me-2"></i>
                                                                @break
                                                        @endswitch
                                                        {{ $step['labels']['en']['title'] ?? $step['id'] }}
                                                    </h6>
                                                    <small class="text-muted">
                                                        Type: {{ ucfirst(str_replace('_', ' ', $step['type'])) }}
                                                        @if(isset($step['labels']['en']['description']) && $step['labels']['en']['description'])
                                                            • {{ Str::limit($step['labels']['en']['description'], 50) }}
                                                        @endif
                                                    </small>
                                                </div>
                                                <div>
                                                    <span class="badge bg-light text-dark">{{ $step['type'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-project-diagram text-muted mb-3" style="font-size: 2rem;"></i>
                                    <p class="text-muted">No steps defined</p>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Editable workflow builder (initially hidden) -->
                        <div id="workflowBuilder" style="display: none;">
                            <div id="workflowSteps" class="workflow-builder">
                                <!-- Steps will be loaded here dynamically -->
                            </div>
                            
                            <div class="text-center mt-3">
                                <button type="button" class="btn btn-primary" onclick="addStep()">
                                    <i class="fas fa-plus me-2"></i>Add Step
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hidden JSON field -->
                <input type="hidden" name="workflow_definition" id="workflow_definition" value="{{ old('workflow_definition', json_encode($workflow->definition)) }}">
                
                <!-- Submit Buttons -->
                <div class="card shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="window.history.back()">Cancel</button>
                            <div>
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-save me-2"></i>Save Changes
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
    <!-- Info Display Template -->
    <div class="step-template" data-type="info_display">
        <div class="step-card card mb-3" data-step-index="0">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle text-info me-2"></i>
                    <strong>Information Display</strong>
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

.step-card[data-type="info_display"] {
    border-left-color: #17a2b8;
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
let editMode = false;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Load existing workflow definition
    const existingDefinition = document.getElementById('workflow_definition').value;
    if (existingDefinition) {
        const definition = JSON.parse(existingDefinition);
        workflowSteps = definition.steps || [];
    }
});

function toggleEditMode() {
    editMode = !editMode;
    const previewDiv = document.getElementById('stepsPreview');
    const builderDiv = document.getElementById('workflowBuilder');
    const toggleButton = document.querySelector('[onclick="toggleEditMode()"]');
    
    if (editMode) {
        previewDiv.style.display = 'none';
        builderDiv.style.display = 'block';
        toggleButton.innerHTML = '<i class="fas fa-eye me-1"></i>Preview Steps';
        
        // Initialize sortable and load steps
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
        
        // Load existing steps
        workflowContainer.innerHTML = '';
        workflowSteps.forEach((step, index) => {
            renderStep(step, index);
        });
        
    } else {
        previewDiv.style.display = 'block';
        builderDiv.style.display = 'none';
        toggleButton.innerHTML = '<i class="fas fa-edit me-1"></i>Edit Steps';
    }
}

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
                                <button type="button" class="btn btn-outline-info w-100 h-100 p-4" onclick="createStep('info_display')">
                                    <i class="fas fa-info-circle mb-2" style="font-size: 2rem;"></i>
                                    <div><strong>Info Display</strong></div>
                                    <small class="text-muted">Show information message</small>
                                </button>
                            </div>
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
    updateWorkflowDefinition();
}

function getDefaultTitle(type, language) {
    const titles = {
        info_display: {
            en: 'Information Message',
            bn: 'তথ্য বার্তা'
        },
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
        info_display: {
            auto_continue: true
        },
        product_selector: {
            multiple: true,
            min_products: 1,
            max_products: 5,
            allow_quantity: true,
            show_suggestions: true,
            retry_attempts: 3
        },
        form: {
            fields: [
                {
                    name: 'name',
                    type: 'text',
                    required: true,
                    labels: { en: 'Full Name', bn: 'পূর্ণ নাম' },
                    validation: { min_length: 2, max_length: 100 }
                }
            ]
        },
        choice: {
            choices: []
        },
        confirmation: {
            show_summary: true,
            show_total: true,
            show_customer_info: true,
            show_delivery_info: true,
            allow_edit: false
        }
    };
    
    return configs[type] || {};
}

function renderStep(stepData, index) {
    console.log('Rendering step:', stepData.type, stepData.id, stepData);
    
    const template = document.querySelector(`[data-type="${stepData.type}"]`);
    if (!template) {
        console.error('Template not found for step type:', stepData.type);
        return;
    }
    
    const clonedTemplate = template.cloneNode(true);
    const stepElement = clonedTemplate.querySelector('.step-card');
    stepElement.setAttribute('data-step-index', index);
    stepElement.setAttribute('data-step-id', stepData.id);
    
    // Update step title
    const title = stepElement.querySelector('strong');
    title.textContent = stepData.labels.en.title || stepData.id;
    
    // Add step configuration preview
    const configDiv = stepElement.querySelector('.step-config');
    configDiv.innerHTML = getStepConfigPreview(stepData);
    
    document.getElementById('workflowSteps').appendChild(stepElement);
}

function getStepConfigPreview(stepData) {
    const type = stepData.type;
    const config = stepData.config || {};
    
    switch (type) {
        case 'info_display':
            return `
                <div class="small text-muted">
                    <i class="fas fa-cog me-1"></i>
                    ${config.auto_continue ? 'Auto continue' : 'Manual continue'}
                </div>
            `;
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
            const fields = stepData.fields || config.fields || [];
            const fieldCount = fields.length;
            return `
                <div class="small text-muted">
                    <i class="fas fa-cog me-1"></i>
                    ${fieldCount} field${fieldCount !== 1 ? 's' : ''}
                </div>
            `;
        case 'choice':
            const choices = stepData.choices || config.choices || [];
            const choiceCount = choices.length;
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
    
    showStepEditor(stepData, stepIndex);
}

function showStepEditor(stepData, stepIndex) {
    console.log('Editing step:', stepData.type, stepData);
    let modalContent = '';
    
    switch (stepData.type) {
        case 'info_display':
            modalContent = getInfoDisplayEditor(stepData, stepIndex);
            break;
        case 'product_selector':
            modalContent = getProductSelectorEditor(stepData, stepIndex);
            break;
        case 'form':
            modalContent = getFormEditor(stepData, stepIndex);
            break;
        case 'choice':
            modalContent = getChoiceEditor(stepData, stepIndex);
            break;
        case 'confirmation':
            modalContent = getConfirmationEditor(stepData, stepIndex);
            break;
        default:
            console.log('Unknown step type:', stepData.type);
            alert('Editor for step type "' + stepData.type + '" is not available yet.');
            return;
    }
    
    const modal = `
        <div class="modal fade" id="stepEditorModal" tabindex="-1" data-bs-backdrop="static">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-edit me-2"></i>
                            Edit Step: ${stepData.labels.en.title}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        ${modalContent}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="saveStepChanges(${stepIndex})">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modal);
    const modalEl = new bootstrap.Modal(document.getElementById('stepEditorModal'));
    modalEl.show();
    
    // Remove modal after hiding
    document.getElementById('stepEditorModal').addEventListener('hidden.bs.modal', function() {
        this.remove();
    });
}

function getInfoDisplayEditor(stepData, stepIndex) {
    // Safely access nested properties
    const enLabels = stepData.labels?.en || {};
    const bnLabels = stepData.labels?.bn || {};
    const config = stepData.config || {};
    
    return `
        <div class="row">
            <div class="col-md-6">
                <h6 class="mb-3">English Content</h6>
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" class="form-control" id="en_title" 
                           value="${(enLabels.title || '').replace(/"/g, '&quot;')}" placeholder="Welcome message title">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="en_description" rows="4" 
                              placeholder="Welcome message for customers">${enLabels.description || ''}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Continue Message</label>
                    <input type="text" class="form-control" id="en_continue_message" 
                           value="${(enLabels.continue_message || 'Continue').replace(/"/g, '&quot;')}" placeholder="Button text">
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="mb-3">বাংলা Content</h6>
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" class="form-control" id="bn_title" 
                           value="${(bnLabels.title || '').replace(/"/g, '&quot;')}" placeholder="স্বাগত বার্তার শিরোনাম">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="bn_description" rows="4" 
                              placeholder="গ্রাহকদের জন্য স্বাগত বার্তা">${bnLabels.description || ''}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Continue Message</label>
                    <input type="text" class="form-control" id="bn_continue_message" 
                           value="${(bnLabels.continue_message || 'এগিয়ে যান').replace(/"/g, '&quot;')}" placeholder="বাটনের টেক্সট">
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-12">
                <h6 class="mb-3">Settings</h6>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="auto_continue" 
                           ${config.auto_continue ? 'checked' : ''}>
                    <label class="form-check-label" for="auto_continue">
                        Auto Continue (automatically proceed to next step)
                    </label>
                </div>
            </div>
        </div>
    `;
}

function getProductSelectorEditor(stepData, stepIndex) {
    return `
        <div class="row">
            <div class="col-md-6">
                <h6 class="mb-3">English Labels</h6>
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" class="form-control" id="en_title" 
                           value="${stepData.labels.en.title || ''}" placeholder="Select Products">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="en_description" rows="3" 
                              placeholder="What would you like to order?">${stepData.labels.en.description || ''}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Format Help Text</label>
                    <input type="text" class="form-control" id="en_format_help" 
                           value="${stepData.labels.en.format_help || ''}" 
                           placeholder="Type product names or 'Product1, Product2'">
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="mb-3">বাংলা Labels</h6>
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" class="form-control" id="bn_title" 
                           value="${stepData.labels.bn.title || ''}" placeholder="পণ্য নির্বাচন করুন">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="bn_description" rows="3" 
                              placeholder="আজ আপনি কী অর্ডার করতে চান?">${stepData.labels.bn.description || ''}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Format Help Text</label>
                    <input type="text" class="form-control" id="bn_format_help" 
                           value="${stepData.labels.bn.format_help || ''}" 
                           placeholder="পণ্যের নাম টাইপ করুন">
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <h6 class="mb-3">Product Selection Settings</h6>
                <div class="mb-3">
                    <label class="form-label">Minimum Products</label>
                    <input type="number" class="form-control" id="min_products" 
                           value="${stepData.config.min_products || 1}" min="1">
                </div>
                <div class="mb-3">
                    <label class="form-label">Maximum Products</label>
                    <input type="number" class="form-control" id="max_products" 
                           value="${stepData.config.max_products || 5}" min="1">
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="allow_quantity" 
                           ${stepData.config.allow_quantity ? 'checked' : ''}>
                    <label class="form-check-label" for="allow_quantity">
                        Allow quantity selection (e.g., "2 shirts", "shirt x3")
                    </label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="show_suggestions" 
                           ${stepData.config.show_suggestions ? 'checked' : ''}>
                    <label class="form-check-label" for="show_suggestions">
                        Show product suggestions on errors
                    </label>
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="mb-3">Available Products</h6>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> All your active products will be available for selection. 
                    Customers can search by name, and the system will handle typos automatically.
                </div>
                <div class="mb-3">
                    <label class="form-label">Error Messages</label>
                    <div class="row">
                        <div class="col-12 mb-2">
                            <input type="text" class="form-control form-control-sm" id="en_error_not_found" 
                                   value="${stepData.labels.en.error_not_found || ''}" 
                                   placeholder="Product not found message (EN)">
                        </div>
                        <div class="col-12">
                            <input type="text" class="form-control form-control-sm" id="bn_error_not_found" 
                                   value="${stepData.labels.bn.error_not_found || ''}" 
                                   placeholder="Product not found message (BN)">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function getFormEditor(stepData, stepIndex) {
    const fields = stepData.fields || stepData.config?.fields || [];
    let fieldsHtml = '';
    
    fields.forEach((field, idx) => {
        fieldsHtml += `
            <div class="card mb-3 field-item" data-field-index="${idx}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h6 class="mb-0">Field ${idx + 1}: ${field.labels?.en || field.name}</h6>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeField(${idx})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label">Field Name</label>
                            <input type="text" class="form-control form-control-sm" 
                                   data-field="name" value="${field.name || ''}" placeholder="field_name">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Field Type</label>
                            <select class="form-select form-select-sm" data-field="type">
                                <option value="text" ${field.type === 'text' ? 'selected' : ''}>Text</option>
                                <option value="email" ${field.type === 'email' ? 'selected' : ''}>Email</option>
                                <option value="tel" ${field.type === 'tel' ? 'selected' : ''}>Phone</option>
                                <option value="textarea" ${field.type === 'textarea' ? 'selected' : ''}>Textarea</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Required</label>
                            <select class="form-select form-select-sm" data-field="required">
                                <option value="true" ${field.required ? 'selected' : ''}>Yes</option>
                                <option value="false" ${!field.required ? 'selected' : ''}>No</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <label class="form-label">Label (English)</label>
                            <input type="text" class="form-control form-control-sm" 
                                   data-field="label_en" value="${field.labels?.en || ''}" placeholder="Full Name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Label (বাংলা)</label>
                            <input type="text" class="form-control form-control-sm" 
                                   data-field="label_bn" value="${field.labels?.bn || ''}" placeholder="পূর্ণ নাম">
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    return `
        <div class="row">
            <div class="col-md-6">
                <h6 class="mb-3">English Content</h6>
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" class="form-control" id="en_title" 
                           value="${stepData.labels.en.title || ''}" placeholder="Customer Information">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="en_description" rows="3" 
                              placeholder="Please provide your contact details">${stepData.labels.en.description || ''}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Success Message</label>
                    <input type="text" class="form-control" id="en_success" 
                           value="${stepData.labels.en.success || ''}" placeholder="Information saved successfully!">
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="mb-3">বাংলা Content</h6>
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" class="form-control" id="bn_title" 
                           value="${stepData.labels.bn.title || ''}" placeholder="গ্রাহকের তথ্য">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="bn_description" rows="3" 
                              placeholder="আপনার যোগাযোগের তথ্য দিন">${stepData.labels.bn.description || ''}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Success Message</label>
                    <input type="text" class="form-control" id="bn_success" 
                           value="${stepData.labels.bn.success || ''}" placeholder="তথ্য সফলভাবে সংরক্ষিত!">
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Form Fields</h6>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addFormField()">
                        <i class="fas fa-plus me-1"></i>Add Field
                    </button>
                </div>
                <div id="formFields">
                    ${fieldsHtml || '<p class="text-muted">No fields added yet. Click "Add Field" to create form fields.</p>'}
                </div>
            </div>
        </div>
    `;
}

function getChoiceEditor(stepData, stepIndex) {
    const choices = stepData.choices || stepData.config?.choices || [];
    let choicesHtml = '';
    
    choices.forEach((choice, idx) => {
        choicesHtml += `
            <div class="card mb-3 choice-item" data-choice-index="${idx}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h6 class="mb-0">Option ${idx + 1}</h6>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeChoice(${idx})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">Choice ID</label>
                            <input type="text" class="form-control form-control-sm" 
                                   data-choice="id" value="${choice.id || ''}" placeholder="choice_id">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Label (English)</label>
                            <input type="text" class="form-control form-control-sm" 
                                   data-choice="label_en" value="${choice.labels?.en || ''}" placeholder="Inside Dhaka">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Label (বাংলা)</label>
                            <input type="text" class="form-control form-control-sm" 
                                   data-choice="label_bn" value="${choice.labels?.bn || ''}" placeholder="ঢাকার ভিতরে">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Extra Charge (৳)</label>
                            <input type="number" class="form-control form-control-sm" 
                                   data-choice="shipping_charge" value="${choice.shipping_charge || 0}" min="0" step="0.01">
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       data-choice="is_default" ${choice.is_default ? 'checked' : ''}>
                                <label class="form-check-label">Default selection</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    return `
        <div class="row">
            <div class="col-md-6">
                <h6 class="mb-3">English Content</h6>
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" class="form-control" id="en_title" 
                           value="${stepData.labels.en.title || ''}" placeholder="Choose an option">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="en_description" rows="3" 
                              placeholder="Please select from the options below">${stepData.labels.en.description || ''}</textarea>
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="mb-3">বাংলা Content</h6>
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" class="form-control" id="bn_title" 
                           value="${stepData.labels.bn.title || ''}" placeholder="একটি অপশন বেছে নিন">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="bn_description" rows="3" 
                              placeholder="নিচের অপশনগুলো থেকে নির্বাচন করুন">${stepData.labels.bn.description || ''}</textarea>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Choice Options</h6>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addChoice()">
                        <i class="fas fa-plus me-1"></i>Add Option
                    </button>
                </div>
                <div id="choiceOptions">
                    ${choicesHtml || '<p class="text-muted">No options added yet. Click "Add Option" to create choices.</p>'}
                </div>
            </div>
        </div>
    `;
}

function getConfirmationEditor(stepData, stepIndex) {
    return `
        <div class="row">
            <div class="col-md-6">
                <h6 class="mb-3">English Content</h6>
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" class="form-control" id="en_title" 
                           value="${stepData.labels.en.title || ''}" placeholder="Confirm Your Order">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="en_description" rows="3" 
                              placeholder="Please review your order details">${stepData.labels.en.description || ''}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Button</label>
                    <input type="text" class="form-control" id="en_confirm_button" 
                           value="${stepData.labels.en.confirm_button || ''}" placeholder="Yes, Place Order">
                </div>
                <div class="mb-3">
                    <label class="form-label">Cancel Button</label>
                    <input type="text" class="form-control" id="en_cancel_button" 
                           value="${stepData.labels.en.cancel_button || ''}" placeholder="No, Edit Order">
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="mb-3">বাংলা Content</h6>
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" class="form-control" id="bn_title" 
                           value="${stepData.labels.bn.title || ''}" placeholder="আপনার অর্ডার নিশ্চিত করুন">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="bn_description" rows="3" 
                              placeholder="আপনার অর্ডারের বিবরণ দেখুন">${stepData.labels.bn.description || ''}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Button</label>
                    <input type="text" class="form-control" id="bn_confirm_button" 
                           value="${stepData.labels.bn.confirm_button || ''}" placeholder="হ্যাঁ, অর্ডার দিন">
                </div>
                <div class="mb-3">
                    <label class="form-label">Cancel Button</label>
                    <input type="text" class="form-control" id="bn_cancel_button" 
                           value="${stepData.labels.bn.cancel_button || ''}" placeholder="না, এডিট করুন">
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-12">
                <h6 class="mb-3">Display Settings</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="show_summary" 
                                   ${stepData.config.show_summary ? 'checked' : ''}>
                            <label class="form-check-label" for="show_summary">Show order summary</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="show_total" 
                                   ${stepData.config.show_total ? 'checked' : ''}>
                            <label class="form-check-label" for="show_total">Show total amount</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="show_customer_info" 
                                   ${stepData.config.show_customer_info ? 'checked' : ''}>
                            <label class="form-check-label" for="show_customer_info">Show customer information</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="show_delivery_info" 
                                   ${stepData.config.show_delivery_info ? 'checked' : ''}>
                            <label class="form-check-label" for="show_delivery_info">Show delivery information</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function saveStepChanges(stepIndex) {
    const stepData = workflowSteps[stepIndex];
    const stepType = stepData.type;
    
    // Update labels
    stepData.labels.en.title = document.getElementById('en_title').value;
    stepData.labels.en.description = document.getElementById('en_description').value;
    stepData.labels.bn.title = document.getElementById('bn_title').value;
    stepData.labels.bn.description = document.getElementById('bn_description').value;
    
    // Update type-specific data
    switch (stepType) {
        case 'info_display':
            stepData.labels.en.continue_message = document.getElementById('en_continue_message').value;
            stepData.labels.bn.continue_message = document.getElementById('bn_continue_message').value;
            stepData.config.auto_continue = document.getElementById('auto_continue').checked;
            break;
            
        case 'product_selector':
            stepData.labels.en.format_help = document.getElementById('en_format_help').value;
            stepData.labels.bn.format_help = document.getElementById('bn_format_help').value;
            stepData.labels.en.error_not_found = document.getElementById('en_error_not_found').value;
            stepData.labels.bn.error_not_found = document.getElementById('bn_error_not_found').value;
            stepData.config.min_products = parseInt(document.getElementById('min_products').value);
            stepData.config.max_products = parseInt(document.getElementById('max_products').value);
            stepData.config.allow_quantity = document.getElementById('allow_quantity').checked;
            stepData.config.show_suggestions = document.getElementById('show_suggestions').checked;
            break;
            
        case 'form':
            stepData.labels.en.success = document.getElementById('en_success').value;
            stepData.labels.bn.success = document.getElementById('bn_success').value;
            
            // Collect form fields
            const fieldElements = document.querySelectorAll('.field-item');
            const fields = [];
            fieldElements.forEach(fieldEl => {
                const field = {
                    name: fieldEl.querySelector('[data-field="name"]').value,
                    type: fieldEl.querySelector('[data-field="type"]').value,
                    required: fieldEl.querySelector('[data-field="required"]').value === 'true',
                    labels: {
                        en: fieldEl.querySelector('[data-field="label_en"]').value,
                        bn: fieldEl.querySelector('[data-field="label_bn"]').value
                    }
                };
                fields.push(field);
            });
            // Store fields in both locations for compatibility
            stepData.fields = fields;
            if (!stepData.config) stepData.config = {};
            stepData.config.fields = fields;
            break;
            
        case 'choice':
            // Collect choices
            const choiceElements = document.querySelectorAll('.choice-item');
            const choices = [];
            choiceElements.forEach(choiceEl => {
                const choice = {
                    id: choiceEl.querySelector('[data-choice="id"]').value,
                    labels: {
                        en: choiceEl.querySelector('[data-choice="label_en"]').value,
                        bn: choiceEl.querySelector('[data-choice="label_bn"]').value
                    },
                    shipping_charge: parseFloat(choiceEl.querySelector('[data-choice="shipping_charge"]').value) || 0,
                    is_default: choiceEl.querySelector('[data-choice="is_default"]').checked
                };
                choices.push(choice);
            });
            // Store choices in both locations for compatibility
            stepData.choices = choices;
            if (!stepData.config) stepData.config = {};
            stepData.config.choices = choices;
            break;
            
        case 'confirmation':
            stepData.labels.en.confirm_button = document.getElementById('en_confirm_button').value;
            stepData.labels.en.cancel_button = document.getElementById('en_cancel_button').value;
            stepData.labels.bn.confirm_button = document.getElementById('bn_confirm_button').value;
            stepData.labels.bn.cancel_button = document.getElementById('bn_cancel_button').value;
            stepData.config.show_summary = document.getElementById('show_summary').checked;
            stepData.config.show_total = document.getElementById('show_total').checked;
            stepData.config.show_customer_info = document.getElementById('show_customer_info').checked;
            stepData.config.show_delivery_info = document.getElementById('show_delivery_info').checked;
            break;
    }
    
    // Update the workflow definition
    updateWorkflowDefinition();
    
    // Close modal
    bootstrap.Modal.getInstance(document.getElementById('stepEditorModal')).hide();
    
    // Re-render the step in builder if in edit mode
    if (editMode) {
        const stepCard = document.querySelector(`[data-step-index="${stepIndex}"]`);
        if (stepCard) {
            // Update the step title in the card
            const titleElement = stepCard.querySelector('strong');
            titleElement.textContent = stepData.labels.en.title;
            
            // Update the config preview
            const configDiv = stepCard.querySelector('.step-config');
            configDiv.innerHTML = getStepConfigPreview(stepData);
        }
    }
    
    // Show success message
    alert('Step changes saved successfully!');
}

// Helper functions for dynamic field/choice management
function addFormField() {
    const fieldsContainer = document.getElementById('formFields');
    const fieldIndex = document.querySelectorAll('.field-item').length;
    
    const fieldHtml = `
        <div class="card mb-3 field-item" data-field-index="${fieldIndex}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h6 class="mb-0">Field ${fieldIndex + 1}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeField(${fieldIndex})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Field Name</label>
                        <input type="text" class="form-control form-control-sm" 
                               data-field="name" value="" placeholder="field_name">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Field Type</label>
                        <select class="form-select form-select-sm" data-field="type">
                            <option value="text">Text</option>
                            <option value="email">Email</option>
                            <option value="tel">Phone</option>
                            <option value="textarea">Textarea</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Required</label>
                        <select class="form-select form-select-sm" data-field="required">
                            <option value="true">Yes</option>
                            <option value="false">No</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <label class="form-label">Label (English)</label>
                        <input type="text" class="form-control form-control-sm" 
                               data-field="label_en" value="" placeholder="Full Name">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Label (বাংলা)</label>
                        <input type="text" class="form-control form-control-sm" 
                               data-field="label_bn" value="" placeholder="পূর্ণ নাম">
                    </div>
                </div>
            </div>
        </div>
    `;
    
    if (fieldsContainer.innerHTML.includes('No fields added yet')) {
        fieldsContainer.innerHTML = '';
    }
    fieldsContainer.insertAdjacentHTML('beforeend', fieldHtml);
}

function addChoice() {
    const choicesContainer = document.getElementById('choiceOptions');
    const choiceIndex = document.querySelectorAll('.choice-item').length;
    
    const choiceHtml = `
        <div class="card mb-3 choice-item" data-choice-index="${choiceIndex}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h6 class="mb-0">Option ${choiceIndex + 1}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeChoice(${choiceIndex})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Choice ID</label>
                        <input type="text" class="form-control form-control-sm" 
                               data-choice="id" value="" placeholder="choice_id">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Label (English)</label>
                        <input type="text" class="form-control form-control-sm" 
                               data-choice="label_en" value="" placeholder="Inside Dhaka">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Label (বাংলা)</label>
                        <input type="text" class="form-control form-control-sm" 
                               data-choice="label_bn" value="" placeholder="ঢাকার ভিতরে">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Extra Charge (৳)</label>
                        <input type="number" class="form-control form-control-sm" 
                               data-choice="shipping_charge" value="0" min="0" step="0.01">
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" 
                                   data-choice="is_default">
                            <label class="form-check-label">Default selection</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    if (choicesContainer.innerHTML.includes('No options added yet')) {
        choicesContainer.innerHTML = '';
    }
    choicesContainer.insertAdjacentHTML('beforeend', choiceHtml);
}

function removeField(index) {
    document.querySelector(`[data-field-index="${index}"]`).remove();
}

function removeChoice(index) {
    document.querySelector(`[data-choice-index="${index}"]`).remove();
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
    updateWorkflowDefinition();
}

function updateStepIndices() {
    const stepCards = document.querySelectorAll('.step-card');
    stepCards.forEach((card, index) => {
        card.setAttribute('data-step-index', index);
    });
}

function updateWorkflowDefinition() {
    const definition = {
        steps: workflowSteps
    };
    
    document.getElementById('workflow_definition').value = JSON.stringify(definition);
}

// Form validation
document.getElementById('workflowForm').addEventListener('submit', function(e) {
    updateWorkflowDefinition();
});
</script>
@endpush
@endsection
@extends('layouts.client')

@section('title', 'Workflows')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Workflows</h1>
                    <p class="text-muted mb-0">Create and manage customer conversation workflows</p>
                </div>
                <div class="d-flex gap-2">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-plus me-2"></i>Create Workflow
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <form action="{{ route('client.workflows.create-from-template') }}" method="POST" class="m-0">
                                    @csrf
                                    <input type="hidden" name="facebook_page_id" value="{{ $selectedPage->id }}">
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-magic me-2"></i>Use Default Template
                                    </button>
                                </form>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('client.workflows.create', ['page_id' => $selectedPage->id]) }}">
                                    <i class="fas fa-plus me-2"></i>Create Custom Workflow
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Page Selector -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body py-3">
                    <form method="GET" id="pageSelector">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <label for="page_id" class="col-form-label">Facebook Page:</label>
                            </div>
                            <div class="col">
                                <select name="page_id" id="page_id" class="form-select" onchange="document.getElementById('pageSelector').submit()">
                                    @foreach($facebookPages as $page)
                                        <option value="{{ $page->id }}" {{ $selectedPage->id == $page->id ? 'selected' : '' }}>
                                            {{ $page->page_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Workflows List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-white border-bottom">
                    <h6 class="m-0 fw-bold text-primary">Workflows for {{ $selectedPage->page_name }}</h6>
                </div>
                <div class="card-body p-0">
                    @if($workflows->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Languages</th>
                                        <th>Steps</th>
                                        <th>Conversations</th>
                                        <th>Success Rate</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($workflows as $workflow)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h6 class="mb-0">{{ $workflow->name }}</h6>
                                                        @if($workflow->description)
                                                            <small class="text-muted">{{ Str::limit($workflow->description, 50) }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($workflow->is_active)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-play me-1"></i>Active
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-pause me-1"></i>Draft
                                                    </span>
                                                @endif
                                                @if($workflow->version > 1)
                                                    <small class="text-muted ms-1">v{{ $workflow->version }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    @foreach($workflow->supported_languages as $lang)
                                                        <span class="badge bg-light text-dark">
                                                            {{ strtoupper($lang) }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $workflow->getTotalSteps() }} steps</span>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <div class="text-success">
                                                        <i class="fas fa-check-circle me-1"></i>{{ $workflow->completed_conversations }} completed
                                                    </div>
                                                    <div class="text-primary">
                                                        <i class="fas fa-clock me-1"></i>{{ $workflow->active_conversations }} active
                                                    </div>
                                                    <div class="text-muted">
                                                        <i class="fas fa-chart-line me-1"></i>{{ $workflow->total_conversations }} total
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $successRate = $workflow->total_conversations > 0 
                                                        ? round(($workflow->completed_conversations / $workflow->total_conversations) * 100, 1)
                                                        : 0;
                                                @endphp
                                                <div class="d-flex align-items-center">
                                                    <div class="progress me-2" style="width: 60px; height: 8px;">
                                                        <div class="progress-bar 
                                                            @if($successRate >= 70) bg-success 
                                                            @elseif($successRate >= 40) bg-warning 
                                                            @else bg-danger 
                                                            @endif" 
                                                            style="width: {{ $successRate }}%"></div>
                                                    </div>
                                                    <small>{{ $successRate }}%</small>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $workflow->created_at->format('M d, Y') }}</small>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('client.workflows.show', $workflow) }}">
                                                                <i class="fas fa-eye me-2"></i>View Details
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('client.workflows.edit', $workflow) }}">
                                                                <i class="fas fa-edit me-2"></i>Edit
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        @if($workflow->is_active)
                                                            <li>
                                                                <form action="{{ route('client.workflows.unpublish', $workflow) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="submit" class="dropdown-item text-warning">
                                                                        <i class="fas fa-pause me-2"></i>Unpublish
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @else
                                                            <li>
                                                                <form action="{{ route('client.workflows.publish', $workflow) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="submit" class="dropdown-item text-success">
                                                                        <i class="fas fa-play me-2"></i>Publish
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                        @if(!$workflow->is_active)
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <form action="{{ route('client.workflows.destroy', $workflow) }}" method="POST" class="d-inline" 
                                                                      onsubmit="return confirm('Are you sure you want to delete this workflow?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="dropdown-item text-danger">
                                                                        <i class="fas fa-trash me-2"></i>Delete
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        @if($workflows->hasPages())
                            <div class="card-footer bg-white">
                                {{ $workflows->appends(request()->query())->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-project-diagram text-muted" style="font-size: 4rem;"></i>
                            </div>
                            <h5 class="text-muted">No workflows yet</h5>
                            <p class="text-muted mb-4">Create your first workflow to start automating customer conversations.</p>
                            
                            <div class="d-flex gap-3 justify-content-center">
                                <!-- Create from Default Template -->
                                <form action="{{ route('client.workflows.create-from-template') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="facebook_page_id" value="{{ $selectedPage->id }}">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-magic me-2"></i>Use Default Template
                                    </button>
                                </form>
                                
                                <!-- Custom Workflow -->
                                <a href="{{ route('client.workflows.create', ['page_id' => $selectedPage->id]) }}" 
                                   class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Create Custom Workflow
                                </a>
                            </div>
                            
                            <div class="mt-4">
                                <small class="text-muted">
                                    <strong>Default Template:</strong> Complete 8-step e-commerce workflow with product selection, customer info, delivery, payment & confirmation.
                                </small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Auto-refresh active conversations count every 30 seconds
setInterval(function() {
    // Only refresh if page is visible
    if (!document.hidden) {
        location.reload();
    }
}, 30000);
</script>
@endpush
@endsection
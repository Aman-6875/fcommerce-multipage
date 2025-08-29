@extends('layouts.client')

@section('title', 'Workflow Details')

@section('content')
<div class="container-fluid px-4">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">{{ $workflow->name }}</h1>
                    <p class="text-muted mb-0">
                        {{ $workflow->description ?: 'No description provided' }}
                        @if($workflow->version > 1)
                            <span class="badge bg-light text-dark ms-2">Version {{ $workflow->version }}</span>
                        @endif
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('client.workflows.edit', $workflow) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit
                    </a>
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
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>

            <!-- Status Card -->
            <div class="row mb-4">
                <div class="col-12">
                    @if($workflow->isPublished())
                        <div class="alert alert-success d-flex align-items-center" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <div>
                                This workflow is <strong>published</strong> and active for customers on 
                                <strong>{{ $workflow->facebookPage->page_name }}</strong>.
                                Published on {{ $workflow->published_at->format('M d, Y \a\t g:i A') }}.
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning d-flex align-items-center" role="alert">
                            <i class="fas fa-pause-circle me-2"></i>
                            <div>This workflow is in <strong>draft mode</strong>. Customers cannot see it until published.</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Conversations</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $workflow->total_conversations }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-comments fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $workflow->completed_conversations }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Active</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $workflow->active_conversations }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Success Rate</div>
                                    @php
                                        $successRate = $workflow->total_conversations > 0 
                                            ? round(($workflow->completed_conversations / $workflow->total_conversations) * 100, 1)
                                            : 0;
                                    @endphp
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $successRate }}%</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Workflow Steps -->
                <div class="col-lg-8 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h6 class="m-0 fw-bold">Workflow Steps ({{ $workflow->getTotalSteps() }} steps)</h6>
                        </div>
                        <div class="card-body">
                            @if($workflow->getSteps())
                                @foreach($workflow->getSteps() as $index => $step)
                                    <div class="card mb-3 border-start border-4 
                                        @switch($step['type'])
                                            @case('info_display') border-info @break
                                            @case('product_selector') border-primary @break
                                            @case('form') border-success @break
                                            @case('choice') border-warning @break
                                            @case('confirmation') border-info @break
                                            @default border-secondary
                                        @endswitch">
                                        <div class="card-body py-3">
                                            <div class="d-flex align-items-start">
                                                <div class="me-3">
                                                    <span class="badge bg-primary rounded-circle" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                                        {{ $index + 1 }}
                                                    </span>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 d-flex align-items-center">
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
                                                    
                                                    @if(isset($step['labels']['en']['description']) && $step['labels']['en']['description'])
                                                        <p class="text-muted mb-2 small">{{ $step['labels']['en']['description'] }}</p>
                                                    @endif
                                                    
                                                    <div class="d-flex gap-3 small text-muted">
                                                        <span><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $step['type'])) }}</span>
                                                        
                                                        @if($step['type'] === 'product_selector' && isset($step['config']))
                                                            <span>
                                                                <strong>Selection:</strong> 
                                                                {{ $step['config']['multiple'] ? 'Multiple' : 'Single' }}
                                                            </span>
                                                        @endif
                                                        
                                                        @if($step['type'] === 'form' && isset($step['fields']))
                                                            <span>
                                                                <strong>Fields:</strong> {{ count($step['fields']) }}
                                                            </span>
                                                        @endif
                                                        
                                                        @if($step['type'] === 'choice' && isset($step['choices']))
                                                            <span>
                                                                <strong>Options:</strong> {{ count($step['choices']) }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="badge bg-light text-dark">{{ $step['id'] }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-project-diagram text-muted mb-3" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted">No steps defined</h5>
                                    <p class="text-muted mb-4">This workflow doesn't have any steps configured yet.</p>
                                    <a href="{{ route('client.workflows.edit', $workflow) }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Add Steps
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar Information -->
                <div class="col-lg-4">
                    <!-- Workflow Info -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="m-0 fw-bold">Workflow Information</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td><strong>Facebook Page:</strong></td>
                                    <td>{{ $workflow->facebookPage->page_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Languages:</strong></td>
                                    <td>
                                        @foreach($workflow->supported_languages as $lang)
                                            <span class="badge bg-light text-dark me-1">{{ strtoupper($lang) }}</span>
                                        @endforeach
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Default Language:</strong></td>
                                    <td><span class="badge bg-primary">{{ strtoupper($workflow->default_language) }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if($workflow->isPublished())
                                            <span class="badge bg-success">Published</span>
                                        @else
                                            <span class="badge bg-secondary">Draft</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Created:</strong></td>
                                    <td>{{ $workflow->created_at->format('M d, Y') }}</td>
                                </tr>
                                @if($workflow->published_at)
                                <tr>
                                    <td><strong>Published:</strong></td>
                                    <td>{{ $workflow->published_at->format('M d, Y') }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Last Updated:</strong></td>
                                    <td>{{ $workflow->updated_at->format('M d, Y') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Recent Conversations -->
                    <div class="card shadow">
                        <div class="card-header bg-success text-white">
                            <h6 class="m-0 fw-bold">Recent Conversations</h6>
                        </div>
                        <div class="card-body">
                            @if($recentConversations->count() > 0)
                                @foreach($recentConversations as $conversation)
                                    <div class="d-flex align-items-center py-2 border-bottom">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold">{{ $conversation->customer->name ?? 'Unknown Customer' }}</div>
                                            <small class="text-muted">
                                                Step {{ $conversation->current_step_index + 1 }} • 
                                                {{ ucfirst($conversation->status) }} •
                                                {{ $conversation->last_activity_at->diffForHumans() }}
                                            </small>
                                        </div>
                                        <div>
                                            @switch($conversation->status)
                                                @case('active')
                                                    <span class="badge bg-info">Active</span>
                                                    @break
                                                @case('completed')
                                                    <span class="badge bg-success">Completed</span>
                                                    @break
                                                @case('abandoned')
                                                    <span class="badge bg-danger">Abandoned</span>
                                                    @break
                                                @case('paused')
                                                    <span class="badge bg-warning">Paused</span>
                                                    @break
                                            @endswitch
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-3">
                                    <i class="fas fa-comment text-muted mb-2" style="font-size: 2rem;"></i>
                                    <p class="text-muted mb-0">No conversations yet</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts.user')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('user.tasks.index') }}">My Tasks</a></li>
            <li class="breadcrumb-item active">Task Details</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-tasks"></i> Task Details</h2>
            <p class="text-muted mb-0">View and manage task information</p>
        </div>
        <a href="{{ route('user.tasks.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Tasks
        </a>
    </div>

    <div class="row">
        <!-- Task Information -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Task Information
                    </h5>
                    
                    <!-- Status Badge -->
                    @if($job->status === 'pending')
                        <span class="badge bg-warning text-dark">Pending</span>
                    @elseif($job->status === 'in_progress')
                        <span class="badge bg-info">In Progress</span>
                    @else
                        <span class="badge bg-success">Completed</span>
                    @endif
                </div>
                <div class="card-body">
                    <!-- Machine Info -->
                    @if($job->machine)
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-cogs text-primary"></i> Equipment
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> {{ $job->machine->name }}</p>
                                <p><strong>Code:</strong> {{ $job->machine->code }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Location:</strong> {{ $job->machine->location ?? '-' }}</p>
                                <p><strong>Department:</strong> {{ $job->machine->department->name ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Task Details -->
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-clipboard-list text-primary"></i> Task Details
                        </h5>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong>Priority:</strong>
                                    @if($job->priority === 'urgent')
                                        <span class="badge bg-danger">URGENT</span>
                                    @elseif($job->priority === 'high')
                                        <span class="badge bg-warning text-dark">HIGH</span>
                                    @elseif($job->priority === 'normal')
                                        <span class="badge bg-info">NORMAL</span>
                                    @else
                                        <span class="badge bg-secondary">LOW</span>
                                    @endif
                                </p>
                                <p class="mb-2">
                                    <strong>Estimated Time:</strong> {{ $job->estimated_time ?? '-' }} hours
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong>Assigned By:</strong> {{ $job->assignedBy->name ?? 'System' }}
                                </p>
                                <p class="mb-2">
                                    <strong>Assigned Date:</strong> {{ $job->created_at->format('d M Y, H:i') }}
                                </p>
                            </div>
                        </div>

                        @if($job->due_date)
                        <div class="alert {{ $job->due_date->isPast() && $job->status !== 'completed' ? 'alert-danger' : 'alert-info' }}">
                            <i class="fas fa-clock"></i>
                            <strong>Due Date:</strong> {{ $job->due_date->format('d M Y, H:i') }}
                            @if($job->due_date->isPast() && $job->status !== 'completed')
                                <span class="badge bg-danger ms-2">OVERDUE</span>
                            @endif
                        </div>
                        @endif

                        <div class="mb-3">
                            <strong>Description:</strong>
                            <p class="mt-2">{{ $job->description }}</p>
                        </div>

                        @if($job->notes)
                        <div>
                            <strong>Additional Notes:</strong>
                            <p class="mt-2">{{ $job->notes }}</p>
                        </div>
                        @endif
                    </div>

                    <!-- Timeline -->
                    <div>
                        <h5 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-history text-primary"></i> Timeline
                        </h5>
                        <div class="timeline">
                            <div class="mb-3">
                                <i class="fas fa-plus-circle text-info"></i>
                                <strong>Created:</strong> {{ $job->created_at->format('d M Y, H:i') }}
                            </div>
                            @if($job->started_at)
                            <div class="mb-3">
                                <i class="fas fa-play-circle text-primary"></i>
                                <strong>Started:</strong> {{ $job->started_at->format('d M Y, H:i') }}
                            </div>
                            @endif
                            @if($job->completed_at)
                            <div class="mb-3">
                                <i class="fas fa-check-circle text-success"></i>
                                <strong>Completed:</strong> {{ $job->completed_at->format('d M Y, H:i') }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Work Reports -->
            @if($job->workReports->count() > 0)
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt"></i> Work Reports
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($job->workReports as $report)
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6>Report #{{ $report->id }}</h6>
                                <p class="text-muted small mb-1">
                                    By: {{ $report->user->name }} | 
                                    {{ $report->created_at->format('d M Y, H:i') }}
                                </p>
                            </div>
                            <span class="badge {{ $report->status === 'completed' ? 'bg-success' : 'bg-warning' }}">
                                {{ ucfirst($report->status) }}
                            </span>
                        </div>
                        <a href="{{ route('user.reports.show', $report) }}" class="btn btn-sm btn-outline-primary mt-2">
                            <i class="fas fa-eye"></i> View Report
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Actions Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    @if($job->status === 'pending')
                        <form method="POST" action="{{ route('user.tasks.update-status', $job) }}" class="mb-3">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="in_progress">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-play"></i> Start Task
                            </button>
                        </form>
                    @endif

                    @if($job->status === 'in_progress')
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Task is in progress. Submit a work report to complete.
                        </div>
                    @endif

                    @if($job->status !== 'completed')
                        <a href="{{ route('user.reports.create', ['job_id' => $job->id]) }}" class="btn btn-success w-100 mb-2">
                            <i class="fas fa-file-alt"></i> Submit Work Report
                        </a>
                    @endif

                    @if($job->status === 'completed')
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            Task completed successfully!
                        </div>
                    @endif

                    <a href="{{ route('user.tasks.index') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-arrow-left"></i> Back to Tasks
                    </a>
                </div>
            </div>

            <!-- Task Stats -->
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Task Stats
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Status:</strong>
                        <span class="float-end">{{ ucfirst(str_replace('_', ' ', $job->status)) }}</span>
                    </div>
                    <div class="mb-2">
                        <strong>Priority:</strong>
                        <span class="float-end">{{ ucfirst($job->priority) }}</span>
                    </div>
                    @if($job->started_at && $job->completed_at)
                    <div class="mb-2">
                        <strong>Duration:</strong>
                        <span class="float-end">
                            {{ $job->started_at->diffInHours($job->completed_at) }} hours
                        </span>
                    </div>
                    @endif
                    <div>
                        <strong>Reports:</strong>
                        <span class="float-end">{{ $job->workReports->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
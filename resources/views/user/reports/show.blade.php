@extends('layouts.user')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('user.reports.index') }}">My Work Reports</a></li>
            <li class="breadcrumb-item active">{{ $report->report_code }}</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-file-alt"></i> Work Report Details</h2>
            <p class="text-muted mb-0">Report Code: <strong>{{ $report->report_code }}</strong></p>
        </div>
        <div>
            @if($report->status === 'pending')
                <a href="{{ route('user.reports.edit', $report) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit Report
                </a>
            @endif
            <a href="{{ route('user.reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Reports
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Report Details -->
        <div class="col-lg-8">
            <!-- Status Alert -->
            @if($report->status === 'draft')
                <div class="alert alert-secondary">
                    <i class="fas fa-file"></i>
                    <strong>Status: Draft</strong>
                    <p class="mb-0 mt-1">This report is still in draft status.</p>
                </div>
            @elseif($report->status === 'submitted')
                <div class="alert alert-warning">
                    <i class="fas fa-clock"></i>
                    <strong>Status: Submitted</strong>
                    <p class="mb-0 mt-1">Your report is waiting for supervisor validation.</p>
                </div>
            @elseif($report->status === 'approved')
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <strong>Status: Approved</strong>
                    <p class="mb-0 mt-1">
                        Report approved by {{ $report->validator->name ?? 'Supervisor' }} 
                        on {{ $report->validated_at ? $report->validated_at->format('d M Y, H:i') : '-' }}
                    </p>
                </div>
            @elseif($report->status === 'revision_needed')
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Status: Needs Revision</strong>
                    <p class="mb-0 mt-1">Please review and update your report based on supervisor comments.</p>
                </div>
            @endif

            <!-- Job Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-tasks"></i> Related Job
                    </h5>
                </div>
                <div class="card-body">
                    @if($report->job)
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong>Machine:</strong> 
                                    {{ $report->job->machine->name ?? '-' }}
                                </p>
                                <p class="mb-2">
                                    <strong>Job Code:</strong> 
                                    {{ $report->job->job_code ?? '-' }}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong>Job Status:</strong>
                                    <span class="badge bg-{{ $report->job->status_badge }}">
                                        {{ ucfirst($report->job->status) }}
                                    </span>
                                </p>
                                <p class="mb-2">
                                    <strong>Priority:</strong>
                                    <span class="badge bg-{{ $report->job->priority_badge }}">
                                        {{ ucfirst($report->job->priority) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <p class="mb-0">
                            <strong>Description:</strong> {{ $report->job->description }}
                        </p>
                    @else
                        <p class="text-muted mb-0">No job information available</p>
                    @endif
                </div>
            </div>

            <!-- Work Details -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-wrench"></i> Work Details
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Work Period -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong><i class="fas fa-play text-success"></i> Work Start:</strong><br>
                                {{ \Carbon\Carbon::parse($report->work_start)->format('d M Y, H:i') }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong><i class="fas fa-stop text-danger"></i> Work End:</strong><br>
                                {{ \Carbon\Carbon::parse($report->work_end)->format('d M Y, H:i') }}
                            </p>
                        </div>
                    </div>

                    <!-- Downtime & Condition -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong><i class="fas fa-clock"></i> Downtime:</strong>
                                <span class="badge bg-secondary">{{ $report->downtime_minutes }} minutes</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong><i class="fas fa-stethoscope"></i> Machine Condition:</strong>
                                @if($report->machine_condition === 'good')
                                    <span class="badge bg-success">Good</span>
                                @elseif($report->machine_condition === 'fair')
                                    <span class="badge bg-warning text-dark">Fair</span>
                                @else
                                    <span class="badge bg-danger">Poor</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Work Performed -->
                    <div class="mb-3">
                        <strong class="d-block mb-2">
                            <i class="fas fa-list"></i> Work Performed:
                        </strong>
                        <div class="border-start border-3 border-primary ps-3">
                            <p class="mb-0">{{ $report->work_performed }}</p>
                        </div>
                    </div>

                    <!-- Issues Found -->
                    @if($report->issues_found)
                    <div class="mb-3">
                        <strong class="d-block mb-2">
                            <i class="fas fa-exclamation-triangle text-warning"></i> Issues Found:
                        </strong>
                        <div class="border-start border-3 border-warning ps-3">
                            <p class="mb-0">{{ $report->issues_found }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Recommendations -->
                    @if($report->recommendations)
                    <div>
                        <strong class="d-block mb-2">
                            <i class="fas fa-lightbulb text-info"></i> Recommendations:
                        </strong>
                        <div class="border-start border-3 border-info ps-3">
                            <p class="mb-0">{{ $report->recommendations }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Attachments -->
            @if($report->attachments && count($report->attachments) > 0)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-paperclip"></i> Attachments
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($report->attachments as $attachment)
                            <div class="col-md-4">
                                @if(str_ends_with($attachment, '.pdf'))
                                    <div class="border rounded p-3 text-center">
                                        <i class="fas fa-file-pdf fa-3x text-danger mb-2"></i>
                                        <p class="small mb-2">PDF Document</p>
                                        <a href="{{ Storage::url($attachment) }}" 
                                           target="_blank" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                @else
                                    <a href="{{ Storage::url($attachment) }}" target="_blank">
                                        <img src="{{ Storage::url($attachment) }}" 
                                             alt="Attachment" 
                                             class="img-fluid rounded shadow-sm">
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Admin Comments -->
            @if($report->admin_comments)
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-comment"></i> Supervisor Comments
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $report->admin_comments }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Report Info -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle"></i> Report Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Report Code:</strong>
                        <span class="float-end">{{ $report->report_code }}</span>
                    </div>
                    <div class="mb-2">
                        <strong>Status:</strong>
                        <span class="float-end">
                            @if($report->status === 'draft')
                                <span class="badge bg-secondary">Draft</span>
                            @elseif($report->status === 'submitted')
                                <span class="badge bg-warning text-dark">Submitted</span>
                            @elseif($report->status === 'approved')
                                <span class="badge bg-success">Approved</span>
                            @elseif($report->status === 'revision_needed')
                                <span class="badge bg-danger">Needs Revision</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $report->status)) }}</span>
                            @endif
                        </span>
                    </div>
                    <div class="mb-2">
                        <strong>Submitted By:</strong>
                        <span class="float-end">{{ $report->user->name }}</span>
                    </div>
                    <div class="mb-2">
                        <strong>Submitted At:</strong>
                        <span class="float-end">{{ $report->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    @if($report->validated_at)
                    <div>
                        <strong>Validated At:</strong>
                        <span class="float-end">{{ $report->validated_at->format('d M Y, H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    @if(in_array($report->status, ['draft', 'submitted', 'revision_needed']))
                        <a href="{{ route('user.reports.edit', $report) }}" class="btn btn-warning w-100 mb-2">
                            <i class="fas fa-edit"></i> Edit Report
                        </a>
                        
                        <form action="{{ route('user.reports.destroy', $report) }}" 
                            method="POST" 
                            onsubmit="return confirm('Are you sure you want to delete this report?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100 mb-2">
                                <i class="fas fa-trash"></i> Delete Report
                            </button>
                        </form>
                    @endif
                    
                    <a href="{{ route('user.reports.index') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-arrow-left"></i> Back to Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
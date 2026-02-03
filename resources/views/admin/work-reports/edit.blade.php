@extends('layouts.admin')

@section('page-title', 'Edit Work Report')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <h2><i class="fas fa-edit"></i> Edit Work Report</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.work-reports.my-reports') }}">My Reports</a></li>
                <li class="breadcrumb-item active">Edit: {{ $workReport->report_code }}</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-file-alt"></i> Edit Report Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.work-reports.update', $workReport) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">Basic Information</h6>
                            </div>

                            <!-- Report Code -->
                            <div class="col-md-6 mb-3">
                                <label for="report_code" class="form-label">Report Code <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('report_code') is-invalid @enderror" 
                                       id="report_code" 
                                       name="report_code" 
                                       value="{{ old('report_code', $workReport->report_code) }}" 
                                       readonly>
                                @error('report_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                           <!-- Status Alerts -->
                            @if($workReport->status === 'submitted')
                                <div class="alert alert-warning">
                                    <i class="fas fa-clock"></i>
                                    <strong>Status: Submitted - Awaiting Validation</strong>
                                    <p class="mb-0 mt-1">This report needs to be reviewed and validated.</p>
                                </div>
                            @elseif($workReport->status === 'approved')
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i>
                                    <strong>Status: Approved</strong>
                                    <p class="mb-0 mt-1">
                                        Report validated by {{ $workReport->validator->name ?? 'Supervisor' }} 
                                        on {{ $workReport->validated_at ? $workReport->validated_at->format('d M Y, H:i') : '-' }}
                                    </p>
                                </div>
                            @elseif($workReport->status === 'revision_needed')
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Status: Needs Revision</strong>
                                    <p class="mb-0 mt-1">
                                        Sent back for revision by {{ $workReport->validator->name ?? 'Supervisor' }}
                                    </p>
                                </div>
                            @elseif($workReport->status === 'draft')
                                <div class="alert alert-secondary">
                                    <i class="fas fa-file"></i>
                                    <strong>Status: Draft</strong>
                                    <p class="mb-0 mt-1">This report is still in draft status.</p>
                                </div>
                            @endif

                            <!-- Job -->
                            <div class="col-md-12 mb-3">
                                <label for="job_id" class="form-label">Maintenance Job <span class="text-danger">*</span></label>
                                <select class="form-select @error('job_id') is-invalid @enderror" 
                                        id="job_id" 
                                        name="job_id" 
                                        required>
                                    <option value="">Select Job</option>
                                    @foreach($jobs as $job)
                                        <option value="{{ $job->id }}" 
                                                {{ old('job_id', $workReport->job_id) == $job->id ? 'selected' : '' }}>
                                            {{ $job->job_code }} - {{ $job->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('job_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Work Schedule -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">Work Schedule</h6>
                            </div>

                            <!-- Work Start -->
                            <div class="col-md-4 mb-3">
                                <label for="work_start" class="form-label">Work Started <span class="text-danger">*</span></label>
                                <input type="datetime-local" 
                                       class="form-control @error('work_start') is-invalid @enderror" 
                                       id="work_start" 
                                       name="work_start" 
                                       value="{{ old('work_start', $workReport->work_start->format('Y-m-d\TH:i')) }}"
                                       required>
                                @error('work_start')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Work End -->
                            <div class="col-md-4 mb-3">
                                <label for="work_end" class="form-label">Work Completed <span class="text-danger">*</span></label>
                                <input type="datetime-local" 
                                       class="form-control @error('work_end') is-invalid @enderror" 
                                       id="work_end" 
                                       name="work_end" 
                                       value="{{ old('work_end', $workReport->work_end->format('Y-m-d\TH:i')) }}"
                                       required>
                                @error('work_end')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Downtime -->
                            <div class="col-md-4 mb-3">
                                <label for="downtime_minutes" class="form-label">Machine Downtime (minutes)</label>
                                <input type="number" 
                                       class="form-control @error('downtime_minutes') is-invalid @enderror" 
                                       id="downtime_minutes" 
                                       name="downtime_minutes" 
                                       value="{{ old('downtime_minutes', $workReport->downtime_minutes) }}" 
                                       min="0">
                                @error('downtime_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Work Details -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">Work Details</h6>
                            </div>

                            <!-- Work Performed -->
                            <div class="col-md-12 mb-3">
                                <label for="work_performed" class="form-label">Work Performed <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('work_performed') is-invalid @enderror" 
                                          id="work_performed" 
                                          name="work_performed" 
                                          rows="4" 
                                          required>{{ old('work_performed', $workReport->work_performed) }}</textarea>
                                @error('work_performed')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Issues Found -->
                            <div class="col-md-12 mb-3">
                                <label for="issues_found" class="form-label">Issues Found</label>
                                <textarea class="form-control @error('issues_found') is-invalid @enderror" 
                                          id="issues_found" 
                                          name="issues_found" 
                                          rows="3">{{ old('issues_found', $workReport->issues_found) }}</textarea>
                                @error('issues_found')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Recommendations -->
                            <div class="col-md-12 mb-3">
                                <label for="recommendations" class="form-label">Recommendations</label>
                                <textarea class="form-control @error('recommendations') is-invalid @enderror" 
                                          id="recommendations" 
                                          name="recommendations" 
                                          rows="3">{{ old('recommendations', $workReport->recommendations) }}</textarea>
                                @error('recommendations')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Machine Condition -->
                            <div class="col-md-12 mb-3">
                                <label for="machine_condition" class="form-label">Machine Condition After Work <span class="text-danger">*</span></label>
                                <select class="form-select @error('machine_condition') is-invalid @enderror" 
                                        id="machine_condition" 
                                        name="machine_condition" 
                                        required>
                                    <option value="excellent" {{ old('machine_condition', $workReport->machine_condition) == 'excellent' ? 'selected' : '' }}>
                                        Excellent - Like new, no issues
                                    </option>
                                    <option value="good" {{ old('machine_condition', $workReport->machine_condition) == 'good' ? 'selected' : '' }}>
                                        Good - Working well, minor wear
                                    </option>
                                    <option value="fair" {{ old('machine_condition', $workReport->machine_condition) == 'fair' ? 'selected' : '' }}>
                                        Fair - Operational but needs attention
                                    </option>
                                    <option value="poor" {{ old('machine_condition', $workReport->machine_condition) == 'poor' ? 'selected' : '' }}>
                                        Poor - Frequent issues, needs major work
                                    </option>
                                    <option value="critical" {{ old('machine_condition', $workReport->machine_condition) == 'critical' ? 'selected' : '' }}>
                                        Critical - Unsafe or not operational
                                    </option>
                                </select>
                                @error('machine_condition')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Existing Attachments -->
                        @if($workReport->attachments && count($workReport->attachments) > 0)
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">Existing Attachments</h6>
                            </div>
                            <div class="col-md-12">
                                <div class="row g-2">
                                    @foreach($workReport->attachments as $index => $attachment)
                                    <div class="col-md-3">
                                        <div class="card">
                                            <img src="{{ Storage::url($attachment['path']) }}" 
                                                 class="card-img-top" 
                                                 alt="Attachment" 
                                                 style="height: 150px; object-fit: cover;">
                                            <div class="card-body p-2">
                                                <small class="text-muted d-block">{{ $attachment['original_name'] }}</small>
                                                <form action="{{ route('admin.work-reports.delete-attachment', [$workReport, $index]) }}" 
                                                      method="POST" 
                                                      onsubmit="return confirm('Delete this attachment?')"
                                                      class="mt-1">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm w-100">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- New Attachments -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">Add New Attachments</h6>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="attachments" class="form-label">Upload Additional Photos</label>
                                <input type="file" 
                                       class="form-control @error('attachments.*') is-invalid @enderror" 
                                       id="attachments" 
                                       name="attachments[]" 
                                       accept="image/*"
                                       multiple>
                                @error('attachments.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">You can upload multiple photos. Max 5MB per image.</small>
                            </div>

                            <div id="preview-container" class="col-md-12">
                                <!-- Image previews will appear here -->
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.work-reports.my-reports') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-warning text-dark">
                                <i class="fas fa-save"></i> Update Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Sidebar -->
        <div class="col-lg-4">
            <!-- Current Report Info -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Current Report Info</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <strong>Submitted:</strong>
                            <small class="d-block text-muted">{{ $workReport->created_at->format('d M Y, H:i') }}</small>
                        </li>
                        <li class="mb-2">
                            <strong>Last Updated:</strong>
                            <small class="d-block text-muted">{{ $workReport->updated_at->format('d M Y, H:i') }}</small>
                        </li>
                        <li class="mb-2">
                            <strong>Current Status:</strong>
                            <span class="badge bg-{{ $workReport->statusBadge }}">
                                {{ ucfirst($workReport->status) }}
                            </span>
                        </li>
                        <li class="mb-2">
                            <strong>Work Duration:</strong>
                            <small class="d-block text-muted">{{ $workReport->workDurationFormatted }}</small>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Admin Comments -->
            @if($workReport->admin_comments)
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-comment"></i> Admin Feedback</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $workReport->admin_comments }}</p>
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.work-reports.show', $workReport) }}" class="btn btn-info btn-sm w-100 mb-2">
                        <i class="fas fa-eye"></i> View Full Details
                    </a>
                    
                    @if($workReport->status === 'draft')
                    <form action="{{ route('admin.work-reports.destroy', $workReport) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="btn btn-danger btn-sm w-100"
                                onclick="return confirm('Are you sure you want to delete this draft?')">
                            <i class="fas fa-trash"></i> Delete Draft
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Image preview for new uploads
document.getElementById('attachments').addEventListener('change', function(e) {
    const previewContainer = document.getElementById('preview-container');
    previewContainer.innerHTML = '';
    
    const files = e.target.files;
    
    if (files.length > 0) {
        previewContainer.innerHTML = '<div class="row g-2">';
        
        Array.from(files).forEach((file, index) => {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const col = document.createElement('div');
                col.className = 'col-md-3';
                col.innerHTML = `
                    <div class="card">
                        <img src="${e.target.result}" class="card-img-top" alt="Preview ${index + 1}" style="height: 150px; object-fit: cover;">
                        <div class="card-body p-2">
                            <small class="text-muted">${file.name}</small>
                        </div>
                    </div>
                `;
                previewContainer.querySelector('.row').appendChild(col);
            };
            
            reader.readAsDataURL(file);
        });
    }
});
</script>
@endsection
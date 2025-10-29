@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <h2><i class="fas fa-plus-circle"></i> Submit Work Report</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.work-reports.my-reports') }}">My Reports</a></li>
                <li class="breadcrumb-item active">Submit Report</li>
            </ol>
        </nav>
    </div>

    @if($jobs->isEmpty())
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>No jobs available!</strong> You don't have any assigned jobs that are in progress or completed.
            Please contact your supervisor.
        </div>
    @else
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-file-alt"></i> Work Report Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.work-reports.store') }}" enctype="multipart/form-data">
                        @csrf

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
                                       value="{{ old('report_code', $reportCode) }}" 
                                       readonly>
                                @error('report_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Auto-generated report code</small>
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" 
                                        name="status" 
                                        required>
                                    <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>
                                        Submit for Review (Pending)
                                    </option>
                                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>
                                        Save as Draft
                                    </option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

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
                                                {{ old('job_id', $selectedJobId) == $job->id ? 'selected' : '' }}
                                                data-machine="{{ $job->machine ? $job->machine->name : 'N/A' }}">
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
                                       value="{{ old('work_start') }}"
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
                                       value="{{ old('work_end') }}"
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
                                       value="{{ old('downtime_minutes') }}" 
                                       min="0"
                                       placeholder="e.g., 120">
                                @error('downtime_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Total time machine was not operational</small>
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
                                          placeholder="Describe in detail what maintenance work was performed..."
                                          required>{{ old('work_performed') }}</textarea>
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
                                          rows="3" 
                                          placeholder="Any problems or issues discovered during maintenance...">{{ old('issues_found') }}</textarea>
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
                                          rows="3" 
                                          placeholder="Future maintenance recommendations or improvements...">{{ old('recommendations') }}</textarea>
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
                                    <option value="">Select Condition</option>
                                    <option value="excellent" {{ old('machine_condition') == 'excellent' ? 'selected' : '' }}>
                                        Excellent - Like new, no issues
                                    </option>
                                    <option value="good" {{ old('machine_condition', 'good') == 'good' ? 'selected' : '' }}>
                                        Good - Working well, minor wear
                                    </option>
                                    <option value="fair" {{ old('machine_condition') == 'fair' ? 'selected' : '' }}>
                                        Fair - Operational but needs attention
                                    </option>
                                    <option value="poor" {{ old('machine_condition') == 'poor' ? 'selected' : '' }}>
                                        Poor - Frequent issues, needs major work
                                    </option>
                                    <option value="critical" {{ old('machine_condition') == 'critical' ? 'selected' : '' }}>
                                        Critical - Unsafe or not operational
                                    </option>
                                </select>
                                @error('machine_condition')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Attachments -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">Attachments (Photos)</h6>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="attachments" class="form-label">Upload Photos</label>
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
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Sidebar -->
        <div class="col-lg-4">
            <!-- Instructions -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Instructions</h6>
                </div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li>Select the maintenance job you worked on</li>
                        <li>Enter accurate start and end times</li>
                        <li>Provide detailed description of work performed</li>
                        <li>Document any issues discovered</li>
                        <li>Add recommendations for future maintenance</li>
                        <li>Upload photos of completed work (before/after)</li>
                        <li>Submit for review or save as draft</li>
                    </ul>
                </div>
            </div>

            <!-- Machine Condition Guide -->
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-cogs"></i> Condition Rating Guide</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <span class="badge bg-success">Excellent:</span>
                            <small class="d-block text-muted">Like new condition, no issues</small>
                        </li>
                        <li class="mb-2">
                            <span class="badge bg-info">Good:</span>
                            <small class="d-block text-muted">Working well, minimal wear</small>
                        </li>
                        <li class="mb-2">
                            <span class="badge bg-warning text-dark">Fair:</span>
                            <small class="d-block text-muted">Operational but needs attention</small>
                        </li>
                        <li class="mb-2">
                            <span class="badge bg-danger">Poor:</span>
                            <small class="d-block text-muted">Frequent issues, major work needed</small>
                        </li>
                        <li class="mb-2">
                            <span class="badge bg-danger">Critical:</span>
                            <small class="d-block text-muted">Unsafe or not operational</small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
// Image preview
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

// Calculate duration automatically
document.getElementById('work_start').addEventListener('change', calculateDuration);
document.getElementById('work_end').addEventListener('change', calculateDuration);

function calculateDuration() {
    const start = document.getElementById('work_start').value;
    const end = document.getElementById('work_end').value;
    
    if (start && end) {
        const startDate = new Date(start);
        const endDate = new Date(end);
        const diff = (endDate - startDate) / 1000 / 60; // minutes
        
        if (diff > 0) {
            const hours = Math.floor(diff / 60);
            const minutes = Math.floor(diff % 60);
            console.log(`Duration: ${hours}h ${minutes}m`);
        }
    }
}
</script>
@endsection
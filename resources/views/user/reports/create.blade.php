@extends('layouts.user')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('user.reports.index') }}">My Work Reports</a></li>
            <li class="breadcrumb-item active">Submit New Report</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-file-alt"></i> Submit Work Report</h2>
            <p class="text-muted mb-0">Document your maintenance work</p>
        </div>
        <a href="{{ route('user.reports.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Reports
        </a>
    </div>

    <form method="POST" action="{{ route('user.reports.store') }}" enctype="multipart/form-data">
        @csrf
        
        <div class="row">
            <!-- Main Form -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard"></i> Report Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Select Job -->
                        <div class="mb-3">
                            <label class="form-label">
                                Select Job/Task <span class="text-danger">*</span>
                            </label>
                            <select name="job_id" class="form-select @error('job_id') is-invalid @enderror" required>
                                <option value="">-- Select a job --</option>
                                @foreach($jobs as $job)
                                    <option value="{{ $job->id }}" 
                                            {{ (old('job_id') == $job->id || ($selectedJob && $selectedJob->id == $job->id)) ? 'selected' : '' }}>
                                        {{ $job->machine ? $job->machine->name : 'General Task' }} 
                                        - {{ Str::limit($job->description, 50) }}
                                        ({{ ucfirst($job->status) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('job_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Select the maintenance job this report is for</small>
                        </div>

                        <!-- Work Period -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    Work Start <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" 
                                       name="work_start" 
                                       class="form-control @error('work_start') is-invalid @enderror" 
                                       value="{{ old('work_start') }}" 
                                       required>
                                @error('work_start')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    Work End <span class="text-danger">*</span>
                                </label>
                                <input type="datetime-local" 
                                       name="work_end" 
                                       class="form-control @error('work_end') is-invalid @enderror" 
                                       value="{{ old('work_end') }}" 
                                       required>
                                @error('work_end')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Work Performed -->
                        <div class="mb-3">
                            <label class="form-label">
                                Work Performed <span class="text-danger">*</span>
                            </label>
                            <textarea name="work_performed" 
                                      rows="4" 
                                      class="form-control @error('work_performed') is-invalid @enderror" 
                                      placeholder="Describe the maintenance work you performed..."
                                      required>{{ old('work_performed') }}</textarea>
                            @error('work_performed')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Be specific about what you did</small>
                        </div>

                        <!-- Issues Found -->
                        <div class="mb-3">
                            <label class="form-label">Issues Found</label>
                            <textarea name="issues_found" 
                                      rows="3" 
                                      class="form-control @error('issues_found') is-invalid @enderror" 
                                      placeholder="Document any issues or problems you discovered...">{{ old('issues_found') }}</textarea>
                            @error('issues_found')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Recommendations -->
                        <div class="mb-3">
                            <label class="form-label">Recommendations</label>
                            <textarea name="recommendations" 
                                      rows="3" 
                                      class="form-control @error('recommendations') is-invalid @enderror" 
                                      placeholder="Suggest improvements or follow-up actions...">{{ old('recommendations') }}</textarea>
                            @error('recommendations')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Machine Condition -->
                        <div class="mb-3">
                            <label class="form-label">
                                Machine Condition After Work <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="radio" 
                                           name="machine_condition" 
                                           id="condition_good" 
                                           value="good" 
                                           {{ old('machine_condition') === 'good' ? 'checked' : '' }} 
                                           required>
                                    <label class="form-check-label" for="condition_good">
                                        <span class="badge bg-success">Good</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="radio" 
                                           name="machine_condition" 
                                           id="condition_fair" 
                                           value="fair" 
                                           {{ old('machine_condition') === 'fair' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="condition_fair">
                                        <span class="badge bg-warning text-dark">Fair</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="radio" 
                                           name="machine_condition" 
                                           id="condition_poor" 
                                           value="poor" 
                                           {{ old('machine_condition') === 'poor' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="condition_poor">
                                        <span class="badge bg-danger">Poor</span>
                                    </label>
                                </div>
                            </div>
                            @error('machine_condition')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Attachments -->
                        <div class="mb-3">
                            <label class="form-label">
                                Attachments (Photos/Documents)
                            </label>
                            <input type="file" 
                                   name="attachments[]" 
                                   class="form-control @error('attachments.*') is-invalid @enderror" 
                                   multiple 
                                   accept="image/*,.pdf">
                            @error('attachments.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Upload photos or documents (Max 2MB per file, JPG, PNG, or PDF)
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Guidelines -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle"></i> Report Guidelines
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="small mb-0">
                            <li class="mb-2">Select the job you worked on</li>
                            <li class="mb-2">Enter accurate work start and end times</li>
                            <li class="mb-2">Describe work performed in detail</li>
                            <li class="mb-2">Document any issues found</li>
                            <li class="mb-2">Provide recommendations if needed</li>
                            <li class="mb-2">Assess final machine condition</li>
                            <li class="mb-2">Attach before/after photos</li>
                        </ul>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-check"></i> Submit Report
                        </h6>
                    </div>
                    <div class="card-body">
                        <button type="submit" class="btn btn-success w-100 mb-2">
                            <i class="fas fa-paper-plane"></i> Submit Report
                        </button>
                        <a href="{{ route('user.reports.index') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        
                        <div class="alert alert-warning mt-3 mb-0">
                            <small>
                                <i class="fas fa-exclamation-triangle"></i>
                                Report will be sent for validation
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
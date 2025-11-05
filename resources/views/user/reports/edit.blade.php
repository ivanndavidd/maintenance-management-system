@extends('layouts.user')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('user.reports.index') }}">My Work Reports</a></li>
            <li class="breadcrumb-item"><a href="{{ route('user.reports.show', $report) }}">{{ $report->report_code }}</a></li>
            <li class="breadcrumb-item active">Edit</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-edit"></i> Edit Work Report</h2>
            <p class="text-muted mb-0">Report Code: <strong>{{ $report->report_code }}</strong></p>
        </div>
        <a href="{{ route('user.reports.show', $report) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Report
        </a>
    </div>

    <form method="POST" action="{{ route('user.reports.update', $report) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
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
                        <!-- Job Info (Read Only) -->
                        <div class="alert alert-info">
                            <strong>Job:</strong> 
                            {{ $report->job->machine->name ?? 'General Task' }} - 
                            {{ Str::limit($report->job->description, 50) }}
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
                                       value="{{ old('work_start', \Carbon\Carbon::parse($report->work_start)->format('Y-m-d\TH:i')) }}" 
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
                                       value="{{ old('work_end', \Carbon\Carbon::parse($report->work_end)->format('Y-m-d\TH:i')) }}" 
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
                                      required>{{ old('work_performed', $report->work_performed) }}</textarea>
                            @error('work_performed')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Issues Found -->
                        <div class="mb-3">
                            <label class="form-label">Issues Found</label>
                            <textarea name="issues_found" 
                                      rows="3" 
                                      class="form-control @error('issues_found') is-invalid @enderror">{{ old('issues_found', $report->issues_found) }}</textarea>
                            @error('issues_found')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Recommendations -->
                        <div class="mb-3">
                            <label class="form-label">Recommendations</label>
                            <textarea name="recommendations" 
                                      rows="3" 
                                      class="form-control @error('recommendations') is-invalid @enderror">{{ old('recommendations', $report->recommendations) }}</textarea>
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
                                           {{ old('machine_condition', $report->machine_condition) === 'good' ? 'checked' : '' }} 
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
                                           {{ old('machine_condition', $report->machine_condition) === 'fair' ? 'checked' : '' }}>
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
                                           {{ old('machine_condition', $report->machine_condition) === 'poor' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="condition_poor">
                                        <span class="badge bg-danger">Poor</span>
                                    </label>
                                </div>
                            </div>
                            @error('machine_condition')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Existing Attachments -->
                        @if($report->attachments && count($report->attachments) > 0)
                        <div class="mb-3">
                            <label class="form-label">Existing Attachments</label>
                            <div class="row g-2">
                                @foreach($report->attachments as $attachment)
                                    <div class="col-md-3">
                                        @if(str_ends_with($attachment, '.pdf'))
                                            <div class="border rounded p-2 text-center">
                                                <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                                <p class="small mb-0">PDF</p>
                                            </div>
                                        @else
                                            <img src="{{ Storage::url($attachment) }}" 
                                                 alt="Attachment" 
                                                 class="img-fluid rounded">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- New Attachments -->
                        <div class="mb-3">
                            <label class="form-label">
                                Add New Attachments
                            </label>
                            <input type="file" 
                                   name="attachments[]" 
                                   class="form-control @error('attachments.*') is-invalid @enderror" 
                                   multiple 
                                   accept="image/*,.pdf">
                            @error('attachments.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Upload additional photos or documents</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Actions -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-save"></i> Save Changes
                        </h6>
                    </div>
                    <div class="card-body">
                        <button type="submit" class="btn btn-success w-100 mb-2">
                            <i class="fas fa-save"></i> Update Report
                        </button>
                        <a href="{{ route('user.reports.show', $report) }}" class="btn btn-secondary w-100">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </div>

                <!-- Info -->
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle"></i> Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="small mb-0">
                            <i class="fas fa-exclamation-circle text-warning"></i>
                            You can edit reports in draft, submitted, or revision needed status.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
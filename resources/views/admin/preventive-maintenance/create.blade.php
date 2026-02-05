@extends('layouts.admin')

@section('page-title', 'Create PM Schedule')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Create Preventive Maintenance Schedule</h4>
            <p class="text-muted mb-0">Create a monthly PM schedule, then add dates and tasks</p>
        </div>
        <a href="{{ route($routePrefix.'.preventive-maintenance.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <form action="{{ route($routePrefix.'.preventive-maintenance.store') }}" method="POST">
        @csrf

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Schedule Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Schedule Month <span class="text-danger">*</span></label>
                        <input type="month" name="scheduled_month" class="form-control" required
                               value="{{ old('scheduled_month', date('Y-m')) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control"
                               placeholder="e.g., PM January 2026"
                               value="{{ old('title') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control"
                               placeholder="Optional description"
                               value="{{ old('description') }}">
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ route($routePrefix.'.preventive-maintenance.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Create Schedule
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

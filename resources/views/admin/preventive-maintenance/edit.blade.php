@extends('layouts.admin')

@section('page-title', 'Edit PM Schedule')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Edit Preventive Maintenance Schedule</h4>
            <p class="text-muted mb-0">{{ $schedule->scheduled_month->format('F Y') }}</p>
        </div>
        <a href="{{ route($routePrefix.'.preventive-maintenance.show', $schedule) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <form action="{{ route($routePrefix.'.preventive-maintenance.update', $schedule) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Schedule Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Schedule Month <span class="text-danger">*</span></label>
                        <input type="month" name="scheduled_month" class="form-control" required
                               value="{{ old('scheduled_month', $schedule->scheduled_month->format('Y-m')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control"
                               value="{{ old('title', $schedule->title) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            @foreach(\App\Models\PmSchedule::getStatuses() as $key => $label)
                                <option value="{{ $key }}" {{ $schedule->status == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control"
                               value="{{ old('description', $schedule->description) }}">
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route($routePrefix.'.preventive-maintenance.show', $schedule) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save Changes
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

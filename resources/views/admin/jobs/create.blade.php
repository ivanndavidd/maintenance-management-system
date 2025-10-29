@extends('layouts.admin')

@section('page-title', 'Create Maintenance Job')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Create New Maintenance Job</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.jobs.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Title
                            <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            name="title"
                            class="form-control @error('title') is-invalid @enderror"
                            value="{{ old('title') }}"
                            required
                        />
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Equipment
                            <span class="text-danger">*</span>
                        </label>
                        <select
                            name="machine_id"
                            class="form-control @error('machine_id') is-invalid @enderror"
                            required
                        >
                            <option value="">Select Equipment</option>
                            @foreach ($machines as $machine))
                                <option
                                    value="{{ $machine->id }}"
                                    {{ old('machine_id') == $machine->id ? 'selected' : '' }}
                                >
                                    {{ $machine->code }} - {{ $machine->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('machine_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Type
                            <span class="text-danger">*</span>
                        </label>
                        <select
                            name="type"
                            class="form-control @error('type') is-invalid @enderror"
                            required
                        >
                            <option value="">Select Type</option>
                            <option
                                value="preventive"
                                {{ old('type') == 'preventive' ? 'selected' : '' }}
                            >
                                Preventive
                            </option>
                            <option
                                value="corrective"
                                {{ old('type') == 'corrective' ? 'selected' : '' }}
                            >
                                Corrective
                            </option>
                            <option
                                value="predictive"
                                {{ old('type') == 'predictive' ? 'selected' : '' }}
                            >
                                Predictive
                            </option>
                            <option
                                value="breakdown"
                                {{ old('type') == 'breakdown' ? 'selected' : '' }}
                            >
                                Breakdown
                            </option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Priority
                            <span class="text-danger">*</span>
                        </label>
                        <select
                            name="priority"
                            class="form-control @error('priority') is-invalid @enderror"
                            required
                        >
                            <option value="">Select Priority</option>
                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>
                                Low
                            </option>
                            <option
                                value="medium"
                                {{ old('priority') == 'medium' ? 'selected' : '' }}
                            >
                                Medium
                            </option>
                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>
                                High
                            </option>
                            <option
                                value="urgent"
                                {{ old('priority') == 'urgent' ? 'selected' : '' }}
                            >
                                Urgent
                            </option>
                        </select>
                        @error('priority')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Assign To</label>
                        <select
                            name="assigned_to"
                            class="form-control @error('assigned_to') is-invalid @enderror"
                        >
                            <option value="">Select User (Optional)</option>
                            @foreach ($users as $user))
                                <option
                                    value="{{ $user->id }}"
                                    {{ old('assigned_to') == $user->id ? 'selected' : '' }}
                                >
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_to')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Scheduled Date
                            <span class="text-danger">*</span>
                        </label>
                        <input
                            type="datetime-local"
                            name="scheduled_date"
                            class="form-control @error('scheduled_date') is-invalid @enderror"
                            value="{{ old('scheduled_date') }}"
                            required
                        />
                        @error('scheduled_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Estimated Duration (minutes)</label>
                        <input
                            type="number"
                            name="estimated_duration"
                            class="form-control @error('estimated_duration') is-invalid @enderror"
                            value="{{ old('estimated_duration') }}"
                            min="1"
                        />
                        @error('estimated_duration')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Description
                        <span class="text-danger">*</span>
                    </label>
                    <textarea
                        name="description"
                        rows="4"
                        class="form-control @error('description') is-invalid @enderror"
                        required
                    >
{{ old('description') }}</textarea
                    >
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.jobs.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Job</button>
                </div>
            </form>
        </div>
    </div>
@endsection

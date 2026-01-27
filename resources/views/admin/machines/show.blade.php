@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-cogs"></i> Machine Details</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.machines.index') }}">Machines</a></li>
                    <li class="breadcrumb-item active">{{ $machine->name }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.machines.edit', $machine) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit Machine
            </a>
            <a href="{{ route('admin.machines.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Success Messages -->
    <div class="row">
        <!-- Machine Profile Card -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="machine-icon-large bg-primary text-white mx-auto mb-3">
                        <i class="fas fa-cogs fa-3x"></i>
                    </div>
                    <h4 class="mb-1">{{ $machine->name }}</h4>
                    <p class="text-muted mb-3">{{ $machine->code }}</p>
                    
                    <span class="badge bg-{{ $machine->statusBadge }} mb-3 px-3 py-2">
                        <i class="fas fa-{{ $machine->status == 'operational' ? 'check-circle' : ($machine->status == 'maintenance' ? 'wrench' : ($machine->status == 'breakdown' ? 'exclamation-triangle' : 'archive')) }}"></i>
                        {{ ucfirst($machine->status) }}
                    </span>

                    <hr>

                    <div class="text-start">
                        <p class="mb-2">
                            <i class="fas fa-tag text-primary"></i>
                            <strong class="ms-2">Category:</strong>
                            <span class="d-block ms-4">{{ $machine->category->name ?? 'N/A' }}</span>
                        </p>
                        @if($machine->model)
                        <p class="mb-2">
                            <i class="fas fa-cube text-success"></i>
                            <strong class="ms-2">Model:</strong>
                            <span class="d-block ms-4">{{ $machine->model }}</span>
                        </p>
                        @endif
                        @if($machine->brand)
                        <p class="mb-2">
                            <i class="fas fa-industry text-info"></i>
                            <strong class="ms-2">Brand:</strong>
                            <span class="d-block ms-4">{{ $machine->brand }}</span>
                        </p>
                        @endif
                        @if($machine->serial_number)
                        <p class="mb-2">
                            <i class="fas fa-barcode text-warning"></i>
                            <strong class="ms-2">Serial Number:</strong>
                            <span class="d-block ms-4">{{ $machine->serial_number }}</span>
                        </p>
                        @endif
                        <p class="mb-2">
                            <i class="fas fa-building text-secondary"></i>
                            <strong class="ms-2">Department:</strong>
                            <span class="d-block ms-4">{{ $machine->department->name ?? '-' }}</span>
                        </p>
                        @if($machine->location)
                        <p class="mb-2">
                            <i class="fas fa-map-marker-alt text-danger"></i>
                            <strong class="ms-2">Location:</strong>
                            <span class="d-block ms-4">{{ $machine->location }}</span>
                        </p>
                        @endif
                        @if($machine->purchase_cost)
                        <p class="mb-2">
                            <i class="fas fa-dollar-sign text-success"></i>
                            <strong class="ms-2">Purchase Cost:</strong>
                            <span class="d-block ms-4">Rp {{ number_format($machine->purchase_cost, 0, ',', '.') }}</span>
                        </p>
                        @endif
                    </div>

                    <hr>

                    <div class="d-grid gap-2">
                        <button type="button" 
                                class="btn btn-warning btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#statusModal">
                            <i class="fas fa-exchange-alt"></i> Update Status
                        </button>
                    </div>
                </div>
            </div>

            <!-- Maintenance Schedule Card -->
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-calendar-alt"></i> Maintenance Schedule</h6>
                </div>
                <div class="card-body">
                    @if($machine->maintenance_interval_days)
                    <div class="mb-3">
                        <strong>Maintenance Interval:</strong>
                        <p class="mb-0 text-primary">Every {{ $machine->maintenance_interval_days }} days</p>
                    </div>
                    @endif
                    
                    <div class="mb-3">
                        <strong>Last Maintenance:</strong>
                        <p class="mb-0">
                            @if($machine->last_maintenance_date)
                                {{ $machine->last_maintenance_date->format('d M Y') }}
                                <small class="text-muted">({{ $machine->last_maintenance_date->diffForHumans() }})</small>
                            @else
                                <span class="text-muted">Not recorded</span>
                            @endif
                        </p>
                    </div>
                    <div class="mb-3">
                        <strong>Next Maintenance:</strong>
                        <p class="mb-0">
                            @if($machine->next_maintenance_date)
                                <span class="text-{{ $machine->isMaintenanceDue() ? 'danger' : 'success' }}">
                                    {{ $machine->next_maintenance_date->format('d M Y') }}
                                    @if($machine->isMaintenanceDue())
                                        <i class="fas fa-exclamation-circle" title="Overdue"></i>
                                    @endif
                                </span>
                                <small class="text-muted d-block">
                                    ({{ $machine->next_maintenance_date->diffForHumans() }})
                                </small>
                            @else
                                <span class="text-muted">Not scheduled</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <strong>Uptime Since Last Breakdown:</strong>
                        <p class="mb-0">
                            <span class="text-success">{{ $uptimeDays }} {{ is_numeric($uptimeDays) ? 'days' : '' }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics & History -->
        <div class="col-lg-8">
            <!-- Statistics Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Maintenance Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h3 class="text-primary mb-0">{{ $stats['total_jobs'] }}</h3>
                                <small class="text-muted">Total Jobs</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h3 class="text-success mb-0">{{ $stats['completed_jobs'] }}</h3>
                                <small class="text-muted">Completed</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h3 class="text-warning mb-0">{{ $stats['pending_jobs'] }}</h3>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h3 class="text-info mb-0">{{ $stats['in_progress_jobs'] }}</h3>
                                <small class="text-muted">In Progress</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row text-center">
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h4 class="text-danger mb-0">{{ $stats['breakdown_count'] }}</h4>
                                <small class="text-muted">Breakdown Repairs</small>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="p-3 bg-light rounded">
                                <h4 class="text-success mb-0">{{ $stats['preventive_count'] }}</h4>
                                <small class="text-muted">Preventive Maintenance</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Machine Details Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Machine Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Purchase Information</h6>
                            <p class="mb-2">
                                <strong>Purchase Date:</strong>
                                <span class="d-block">{{ $machine->purchase_date ? $machine->purchase_date->format('d M Y') : '-' }}</span>
                            </p>
                            <p class="mb-2">
                                <strong>Purchase Cost:</strong>
                                <span class="d-block">{{ $machine->purchase_cost ? 'Rp ' . number_format($machine->purchase_cost, 0, ',', '.') : '-' }}</span>
                            </p>
                            <p class="mb-2">
                                <strong>Warranty Expiry:</strong>
                                <span class="d-block">{{ $machine->warranty_expiry ? $machine->warranty_expiry->format('d M Y') : '-' }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Record Information</h6>
                            <p class="mb-2">
                                <strong>Created:</strong>
                                <span class="d-block">{{ $machine->created_at->format('d M Y, H:i') }}</span>
                            </p>
                            <p class="mb-2">
                                <strong>Last Updated:</strong>
                                <span class="d-block">{{ $machine->updated_at->format('d M Y, H:i') }}</span>
                            </p>
                            <p class="mb-2">
                                <strong>Maintenance Interval:</strong>
                                <span class="d-block">{{ $machine->maintenance_interval_days ? $machine->maintenance_interval_days . ' days' : '-' }}</span>
                            </p>
                        </div>
                    </div>

                    @if($machine->specifications)
                    <hr>
                    <h6 class="text-muted mb-2">Specifications</h6>
                    <p class="mb-0">{{ $machine->specifications }}</p>
                    @endif

                    @if($machine->notes)
                    <hr>
                    <h6 class="text-muted mb-2">Notes</h6>
                    <p class="mb-0">{{ $machine->notes }}</p>
                    @endif
                </div>
            </div>

            <!-- Maintenance History -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Maintenance History</h5>
                </div>
                <div class="card-body p-0">
                    @if($recentJobs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Job Title</th>
                                    <th>Type</th>
                                    <th>Assigned To</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentJobs as $job)
                                <tr>
                                    <td>
                                        <small>{{ $job->created_at->format('d M Y') }}</small>
                                    </td>
                                    <td>{{ Str::limit($job->title, 40) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $job->type == 'breakdown' ? 'danger' : 'info' }}">
                                            {{ ucfirst($job->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $job->assignedUser->name ?? 'Unassigned' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $job->priority == 'high' ? 'danger' : ($job->priority == 'medium' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($job->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $job->status == 'completed' ? 'success' : ($job->status == 'in_progress' ? 'primary' : 'warning') }}">
                                            {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No maintenance history yet</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.machines.update-status', $machine) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-exchange-alt"></i> Update Machine Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Updating status for: <strong>{{ $machine->name }}</strong>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">New Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="operational" {{ $machine->status == 'operational' ? 'selected' : '' }}>
                                Operational
                            </option>
                            <option value="maintenance" {{ $machine->status == 'maintenance' ? 'selected' : '' }}>
                                Maintenance
                            </option>
                            <option value="breakdown" {{ $machine->status == 'breakdown' ? 'selected' : '' }}>
                                Breakdown
                            </option>
                            <option value="retired" {{ $machine->status == 'retired' ? 'selected' : '' }}>
                                Retired
                            </option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark">
                        <i class="fas fa-save"></i> Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.machine-icon-large {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endsection
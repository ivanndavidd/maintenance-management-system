@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <h2><i class="fas fa-edit"></i> Edit Machine</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.machines.index') }}">Machines</a></li>
                <li class="breadcrumb-item active">Edit: {{ $machine->name }}</li>
            </ol>
        </nav>
    </div>

    <!-- Success Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-cogs"></i> Edit Machine Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.machines.update', $machine) }}">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">Basic Information</h6>
                            </div>

                            <!-- Code -->
                            <div class="col-md-6 mb-3">
                                <label for="code" class="form-label">Machine Code <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('code') is-invalid @enderror" 
                                       id="code" 
                                       name="code" 
                                       value="{{ old('code', $machine->code) }}" 
                                       required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Name -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Machine Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $machine->name) }}" 
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Category -->
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select @error('category_id') is-invalid @enderror" 
                                        id="category_id" 
                                        name="category_id" 
                                        required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" 
                                                {{ old('category_id', $machine->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Model -->
                            <div class="col-md-6 mb-3">
                                <label for="model" class="form-label">Model</label>
                                <input type="text" 
                                       class="form-control @error('model') is-invalid @enderror" 
                                       id="model" 
                                       name="model" 
                                       value="{{ old('model', $machine->model) }}">
                                @error('model')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Brand -->
                            <div class="col-md-6 mb-3">
                                <label for="brand" class="form-label">Brand</label>
                                <input type="text" 
                                       class="form-control @error('brand') is-invalid @enderror" 
                                       id="brand" 
                                       name="brand" 
                                       value="{{ old('brand', $machine->brand) }}">
                                @error('brand')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Serial Number -->
                            <div class="col-md-6 mb-3">
                                <label for="serial_number" class="form-label">Serial Number</label>
                                <input type="text" 
                                       class="form-control @error('serial_number') is-invalid @enderror" 
                                       id="serial_number" 
                                       name="serial_number" 
                                       value="{{ old('serial_number', $machine->serial_number) }}">
                                @error('serial_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Location & Assignment -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">Location & Assignment</h6>
                            </div>

                            <!-- Department -->
                            <div class="col-md-6 mb-3">
                                <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                                <select class="form-select @error('department_id') is-invalid @enderror" 
                                        id="department_id" 
                                        name="department_id" 
                                        required>
                                    <option value="">Select Department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" 
                                                {{ old('department_id', $machine->department_id) == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Location -->
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" 
                                       class="form-control @error('location') is-invalid @enderror" 
                                       id="location" 
                                       name="location" 
                                       value="{{ old('location', $machine->location) }}">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" 
                                        name="status" 
                                        required>
                                    <option value="operational" {{ old('status', $machine->status) == 'operational' ? 'selected' : '' }}>
                                        Operational
                                    </option>
                                    <option value="maintenance" {{ old('status', $machine->status) == 'maintenance' ? 'selected' : '' }}>
                                        Maintenance
                                    </option>
                                    <option value="breakdown" {{ old('status', $machine->status) == 'breakdown' ? 'selected' : '' }}>
                                        Breakdown
                                    </option>
                                    <option value="retired" {{ old('status', $machine->status) == 'retired' ? 'selected' : '' }}>
                                        Retired
                                    </option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Maintenance Interval -->
                            <div class="col-md-6 mb-3">
                                <label for="maintenance_interval_days" class="form-label">Maintenance Interval (Days)</label>
                                <input type="number" 
                                       class="form-control @error('maintenance_interval_days') is-invalid @enderror" 
                                       id="maintenance_interval_days" 
                                       name="maintenance_interval_days" 
                                       value="{{ old('maintenance_interval_days', $machine->maintenance_interval_days) }}" 
                                       min="1">
                                @error('maintenance_interval_days')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">How often maintenance should be performed</small>
                            </div>
                        </div>

                        <!-- Maintenance Schedule -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">Maintenance Schedule</h6>
                            </div>

                            <!-- Last Maintenance -->
                            <div class="col-md-6 mb-3">
                                <label for="last_maintenance_date" class="form-label">Last Maintenance Date</label>
                                <input type="date" 
                                       class="form-control @error('last_maintenance_date') is-invalid @enderror" 
                                       id="last_maintenance_date" 
                                       name="last_maintenance_date" 
                                       value="{{ old('last_maintenance_date', $machine->last_maintenance_date?->format('Y-m-d')) }}">
                                @error('last_maintenance_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Next Maintenance -->
                            <div class="col-md-6 mb-3">
                                <label for="next_maintenance_date" class="form-label">Next Maintenance Date</label>
                                <input type="date" 
                                       class="form-control @error('next_maintenance_date') is-invalid @enderror" 
                                       id="next_maintenance_date" 
                                       name="next_maintenance_date" 
                                       value="{{ old('next_maintenance_date', $machine->next_maintenance_date?->format('Y-m-d')) }}">
                                @error('next_maintenance_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @if($machine->isMaintenanceDue())
                                    <small class="text-danger">
                                        <i class="fas fa-exclamation-triangle"></i> Maintenance is overdue!
                                    </small>
                                @endif
                            </div>
                        </div>

                        <!-- Purchase & Warranty -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">Purchase & Warranty</h6>
                            </div>

                            <!-- Purchase Date -->
                            <div class="col-md-4 mb-3">
                                <label for="purchase_date" class="form-label">Purchase Date</label>
                                <input type="date" 
                                       class="form-control @error('purchase_date') is-invalid @enderror" 
                                       id="purchase_date" 
                                       name="purchase_date" 
                                       value="{{ old('purchase_date', $machine->purchase_date?->format('Y-m-d')) }}">
                                @error('purchase_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Purchase Cost -->
                            <div class="col-md-4 mb-3">
                                <label for="purchase_cost" class="form-label">Purchase Cost (Rp)</label>
                                <input type="number" 
                                       class="form-control @error('purchase_cost') is-invalid @enderror" 
                                       id="purchase_cost" 
                                       name="purchase_cost" 
                                       value="{{ old('purchase_cost', $machine->purchase_cost) }}" 
                                       min="0"
                                       step="0.01">
                                @error('purchase_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Warranty Expiry -->
                            <div class="col-md-4 mb-3">
                                <label for="warranty_expiry" class="form-label">Warranty Expiry</label>
                                <input type="date" 
                                       class="form-control @error('warranty_expiry') is-invalid @enderror" 
                                       id="warranty_expiry" 
                                       name="warranty_expiry" 
                                       value="{{ old('warranty_expiry', $machine->warranty_expiry?->format('Y-m-d')) }}">
                                @error('warranty_expiry')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">Additional Information</h6>
                            </div>

                            <!-- Specifications -->
                            <div class="col-md-12 mb-3">
                                <label for="specifications" class="form-label">Specifications</label>
                                <textarea class="form-control @error('specifications') is-invalid @enderror" 
                                          id="specifications" 
                                          name="specifications" 
                                          rows="3">{{ old('specifications', $machine->specifications) }}</textarea>
                                @error('specifications')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div class="col-md-12 mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" 
                                          name="notes" 
                                          rows="3">{{ old('notes', $machine->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.machines.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-warning text-dark">
                                <i class="fas fa-save"></i> Update Machine
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Sidebar -->
        <div class="col-lg-4">
            <!-- Machine Info Card -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Current Machine Info</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <strong>Created:</strong>
                            <small class="d-block text-muted">{{ $machine->created_at->format('d M Y, H:i') }}</small>
                        </li>
                        <li class="mb-2">
                            <strong>Last Updated:</strong>
                            <small class="d-block text-muted">{{ $machine->updated_at->format('d M Y, H:i') }}</small>
                        </li>
                        <li class="mb-2">
                            <strong>Current Status:</strong>
                            <span class="badge bg-{{ $machine->statusBadge }}">
                                {{ ucfirst($machine->status) }}
                            </span>
                        </li>
                        <li class="mb-2">
                            <strong>Total Jobs:</strong>
                            <small class="d-block text-muted">{{ $machine->maintenanceJobs()->count() }} maintenance jobs</small>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.machines.show', $machine) }}" class="btn btn-info btn-sm w-100 mb-2">
                        <i class="fas fa-eye"></i> View Full Details
                    </a>
                    
                    <button type="button" 
                            class="btn btn-warning btn-sm w-100 mb-2" 
                            data-bs-toggle="modal" 
                            data-bs-target="#statusModal">
                        <i class="fas fa-exchange-alt"></i> Quick Status Change
                    </button>
                    
                    <form action="{{ route('admin.machines.destroy', $machine) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="btn btn-danger btn-sm w-100"
                                onclick="return confirm('Are you sure you want to delete this machine?')">
                            <i class="fas fa-trash"></i> Delete Machine
                        </button>
                    </form>
                </div>
            </div>

            <!-- Warning Notes -->
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Important</h6>
                </div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li>Machine code must be unique</li>
                        <li>Changing status affects maintenance scheduling</li>
                        <li>Set next maintenance date for preventive maintenance</li>
                        <li>Cannot delete machine with active jobs</li>
                        <li>Purchase cost is used for asset tracking</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Status Change Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.machines.update-status', $machine) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-exchange-alt"></i> Update Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Quick status update for: <strong>{{ $machine->name }}</strong>
                    </div>

                    <div class="mb-3">
                        <label for="modal_status" class="form-label">New Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="modal_status" name="status" required>
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
@endsection
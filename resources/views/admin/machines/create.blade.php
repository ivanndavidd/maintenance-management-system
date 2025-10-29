@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <h2><i class="fas fa-plus-circle"></i> Add New Machine</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.machines.index') }}">Machines</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-cogs"></i> Machine Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.machines.store') }}">
                        @csrf

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
                                       value="{{ old('code') }}" 
                                       placeholder="e.g., FLT-001"
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
                                       value="{{ old('name') }}" 
                                       placeholder="e.g., Toyota Electric Forklift #1"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Category (instead of Type) -->
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select @error('category_id') is-invalid @enderror" 
                                        id="category_id" 
                                        name="category_id" 
                                        required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                                       value="{{ old('model') }}" 
                                       placeholder="e.g., 8FBCU25">
                                @error('model')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Brand (instead of Manufacturer) -->
                            <div class="col-md-6 mb-3">
                                <label for="brand" class="form-label">Brand</label>
                                <input type="text" 
                                       class="form-control @error('brand') is-invalid @enderror" 
                                       id="brand" 
                                       name="brand" 
                                       value="{{ old('brand') }}" 
                                       placeholder="e.g., Toyota">
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
                                       value="{{ old('serial_number') }}" 
                                       placeholder="e.g., TY2023001">
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
                                        <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
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
                                       value="{{ old('location') }}" 
                                       placeholder="e.g., Warehouse A - Bay 3">
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
                                    <option value="operational" {{ old('status') == 'operational' ? 'selected' : '' }}>
                                        Operational
                                    </option>
                                    <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>
                                        Maintenance
                                    </option>
                                    <option value="breakdown" {{ old('status') == 'breakdown' ? 'selected' : '' }}>
                                        Breakdown
                                    </option>
                                    <option value="retired" {{ old('status') == 'retired' ? 'selected' : '' }}>
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
                                       value="{{ old('maintenance_interval_days', 30) }}" 
                                       min="1"
                                       placeholder="e.g., 30">
                                @error('maintenance_interval_days')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">How often maintenance should be performed</small>
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
                                       value="{{ old('purchase_date') }}">
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
                                       value="{{ old('purchase_cost') }}" 
                                       min="0"
                                       step="0.01"
                                       placeholder="e.g., 45000000">
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
                                       value="{{ old('warranty_expiry') }}">
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
                                          rows="3" 
                                          placeholder="Capacity: 2.5 ton, Lift Height: 6m, Battery: 48V">{{ old('specifications') }}</textarea>
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
                                          rows="3" 
                                          placeholder="Additional notes or remarks">{{ old('notes') }}</textarea>
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
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Machine
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Information</h6>
                </div>
                <div class="card-body">
                    <h6>Machine Status:</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <span class="badge bg-success">Operational:</span>
                            <small class="d-block text-muted">Machine is working normally</small>
                        </li>
                        <li class="mb-2">
                            <span class="badge bg-warning text-dark">Maintenance:</span>
                            <small class="d-block text-muted">Under scheduled maintenance</small>
                        </li>
                        <li class="mb-2">
                            <span class="badge bg-danger">Breakdown:</span>
                            <small class="d-block text-muted">Machine is broken, needs repair</small>
                        </li>
                        <li class="mb-2">
                            <span class="badge bg-secondary">Retired:</span>
                            <small class="d-block text-muted">No longer in service</small>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Important Notes</h6>
                </div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li>Machine code must be unique</li>
                        <li>All fields marked with * are required</li>
                        <li>Serial number helps in warranty tracking</li>
                        <li>Department assignment is mandatory</li>
                        <li>Maintenance interval determines next maintenance date</li>
                        <li>Purchase cost used for depreciation calculation</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
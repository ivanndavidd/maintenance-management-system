@extends('layouts.admin')

@section('page-title', 'Edit Asset')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Edit Asset</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.assets.index') }}">Assets</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Asset Information - {{ $asset->asset_name }}</h5>
        </div>
        <div class="card-body">
            <form action="{{ route($routePrefix.'.assets.update', $asset) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="equipment_id" class="form-label">Equipment ID</label>
                            <input type="text" name="equipment_id" id="equipment_id"
                                class="form-control @error('equipment_id') is-invalid @enderror"
                                value="{{ old('equipment_id', $asset->equipment_id) }}"
                                placeholder="Optional">
                            @error('equipment_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="asset_name" class="form-label">Asset Name <span class="text-danger">*</span></label>
                            <input type="text" name="asset_name" id="asset_name"
                                class="form-control @error('asset_name') is-invalid @enderror"
                                value="{{ old('asset_name', $asset->asset_name) }}"
                                placeholder="Enter asset name"
                                required>
                            @error('asset_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="equipment_type" class="form-label">Equipment Type <span class="text-danger">*</span></label>
                            <input type="text" name="equipment_type" id="equipment_type"
                                class="form-control @error('equipment_type') is-invalid @enderror"
                                value="{{ old('equipment_type', $asset->equipment_type) }}"
                                placeholder="e.g., Compressor, Conveyor"
                                required>
                            @error('equipment_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                            <input type="text" name="location" id="location"
                                class="form-control @error('location') is-invalid @enderror"
                                value="{{ old('location', $asset->location) }}"
                                placeholder="e.g., Warehouse A"
                                required>
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status"
                                class="form-select @error('status') is-invalid @enderror"
                                required>
                                <option value="active" {{ old('status', $asset->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $asset->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="maintenance" {{ old('status', $asset->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="disposed" {{ old('status', $asset->status) == 'disposed' ? 'selected' : '' }}>Disposed</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea name="notes" id="notes" rows="3"
                        class="form-control @error('notes') is-invalid @enderror"
                        placeholder="Optional notes">{{ old('notes', $asset->notes) }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route($routePrefix.'.assets.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Asset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

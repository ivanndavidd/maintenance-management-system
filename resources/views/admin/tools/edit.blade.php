@extends('layouts.admin')

@section('page-title', 'Edit Tool')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h5>Edit Tool</h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.tools.index') }}">Tools</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Tool Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route($routePrefix.'.tools.update', $tool) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="material_code" class="form-label">Material Code <span class="text-danger">*</span></label>
                                <input type="text" name="material_code" id="material_code"
                                    class="form-control @error('material_code') is-invalid @enderror"
                                    value="{{ old('material_code', $tool->material_code) }}"
                                    placeholder="e.g., T-00001"
                                    required>
                                <small class="text-muted">Kode material dari ERP/SAP (wajib diisi, harus unik)</small>
                                @error('material_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="equipment_type" class="form-label">Equipment Type</label>
                                <input type="text" name="equipment_type" id="equipment_type"
                                    class="form-control @error('equipment_type') is-invalid @enderror"
                                    value="{{ old('equipment_type', $tool->equipment_type) }}"
                                    placeholder="e.g., Tools, Hand Tools, Power Tools">
                                @error('equipment_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="sparepart_name" class="form-label">Tool Name <span class="text-danger">*</span></label>
                                <input type="text" name="sparepart_name" id="sparepart_name"
                                    class="form-control @error('sparepart_name') is-invalid @enderror"
                                    value="{{ old('sparepart_name', $tool->sparepart_name) }}" required>
                                @error('sparepart_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="brand" class="form-label">Brand</label>
                                <input type="text" name="brand" id="brand"
                                    class="form-control @error('brand') is-invalid @enderror"
                                    value="{{ old('brand', $tool->brand) }}">
                                @error('brand')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="model" class="form-label">Model</label>
                                <input type="text" name="model" id="model"
                                    class="form-control @error('model') is-invalid @enderror"
                                    value="{{ old('model', $tool->model) }}">
                                @error('model')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" id="quantity"
                                    class="form-control @error('quantity') is-invalid @enderror"
                                    value="{{ old('quantity', $tool->quantity) }}" min="0" required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="unit" class="form-label">Unit <span class="text-danger">*</span></label>
                                <select name="unit" id="unit" class="form-select @error('unit') is-invalid @enderror" required>
                                    <option value="">Select Unit</option>
                                    @foreach(['pcs', 'unit', 'set', 'box', 'pack'] as $u)
                                        <option value="{{ $u }}" {{ old('unit', $tool->unit) == $u ? 'selected' : '' }}>{{ ucfirst($u) }}</option>
                                    @endforeach
                                </select>
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="minimum_stock" class="form-label">Minimum Stock <span class="text-danger">*</span></label>
                                <input type="number" name="minimum_stock" id="minimum_stock"
                                    class="form-control @error('minimum_stock') is-invalid @enderror"
                                    value="{{ old('minimum_stock', $tool->minimum_stock) }}" min="0" required>
                                @error('minimum_stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="parts_price" class="form-label">Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="parts_price" id="parts_price"
                                        class="form-control @error('parts_price') is-invalid @enderror"
                                        value="{{ old('parts_price', (int) $tool->parts_price) }}" min="0" step="1" required>
                                </div>
                                @error('parts_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="vulnerability" class="form-label">Vulnerability</label>
                                <select name="vulnerability" id="vulnerability" class="form-select @error('vulnerability') is-invalid @enderror">
                                    <option value="">Select Vulnerability</option>
                                    @foreach(['low', 'medium', 'high', 'critical'] as $v)
                                        <option value="{{ $v }}" {{ old('vulnerability', $tool->vulnerability) == $v ? 'selected' : '' }}>{{ ucfirst($v) }}</option>
                                    @endforeach
                                </select>
                                @error('vulnerability')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" name="location" id="location"
                                class="form-control @error('location') is-invalid @enderror"
                                value="{{ old('location', $tool->location) }}" placeholder="e.g., Warehouse A, Shelf 1">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route($routePrefix.'.tools.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i><span class="btn-text"> Back</span>
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i><span class="btn-text"> Update Tool</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Tool Info</h5>
                </div>
                <div class="card-body">
                    <p><strong>Tool ID:</strong> <code>{{ $tool->tool_id }}</code></p>
                    <p><strong>Item Type:</strong> {{ ucfirst($tool->item_type ?? 'tool') }}</p>
                    <p><strong>Added By:</strong> {{ $tool->addedBy->name ?? '-' }}</p>
                    <p><strong>Created:</strong> {{ $tool->created_at?->format('d M Y') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

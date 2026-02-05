@extends('layouts.admin')

@section('page-title', 'Edit Sparepart')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Edit Sparepart</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.spareparts.index') }}">Spareparts</a></li>
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.spareparts.show', $sparepart) }}">{{ $sparepart->sparepart_name }}</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Sparepart Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route($routePrefix.'.spareparts.update', $sparepart) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="alert alert-secondary">
                            <strong>Material Code:</strong> {{ $sparepart->material_code ?? '-' }}
                            <span class="text-muted">(From import data)</span>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="equipment_type" class="form-label">Equipment Type <span class="text-danger">*</span></label>
                                <input type="text" name="equipment_type" id="equipment_type"
                                    class="form-control @error('equipment_type') is-invalid @enderror"
                                    value="{{ old('equipment_type', $sparepart->equipment_type) }}"
                                    placeholder="e.g., CBS, Singulator, Belt Conveyor, Tools" required>
                                <small class="text-muted">Enter equipment type (e.g., CBS, Belt Conveyor, Panel, Tools)</small>
                                @error('equipment_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="sparepart_name" class="form-label">Sparepart Name <span class="text-danger">*</span></label>
                                <input type="text" name="sparepart_name" id="sparepart_name"
                                    class="form-control @error('sparepart_name') is-invalid @enderror"
                                    value="{{ old('sparepart_name', $sparepart->sparepart_name) }}" required>
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
                                    value="{{ old('brand', $sparepart->brand) }}">
                                @error('brand')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="model" class="form-label">Model</label>
                                <input type="text" name="model" id="model"
                                    class="form-control @error('model') is-invalid @enderror"
                                    value="{{ old('model', $sparepart->model) }}">
                                @error('model')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> <strong>Note:</strong> To change quantity, please use <a href="{{ route($routePrefix.'.spareparts.adjustments.create') }}">Stock Adjustment</a> feature for proper tracking.
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="quantity" class="form-label">Current Quantity</label>
                                <input type="number" name="quantity" id="quantity"
                                    class="form-control bg-light"
                                    value="{{ $sparepart->quantity }}" readonly>
                                <small class="text-muted">Use Stock Adjustment to modify</small>
                            </div>

                            <div class="col-md-4">
                                <label for="minimum_stock" class="form-label">Minimum Stock <span class="text-danger">*</span></label>
                                <input type="number" name="minimum_stock" id="minimum_stock"
                                    class="form-control @error('minimum_stock') is-invalid @enderror"
                                    value="{{ old('minimum_stock', $sparepart->minimum_stock) }}" min="0" required>
                                @error('minimum_stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="unit" class="form-label">Unit <span class="text-danger">*</span></label>
                                <select name="unit" id="unit" class="form-select @error('unit') is-invalid @enderror" required>
                                    <option value="">Select Unit</option>
                                    <option value="pcs" {{ old('unit', $sparepart->unit) == 'pcs' ? 'selected' : '' }}>Pcs</option>
                                    <option value="unit" {{ old('unit', $sparepart->unit) == 'unit' ? 'selected' : '' }}>Unit</option>
                                    <option value="set" {{ old('unit', $sparepart->unit) == 'set' ? 'selected' : '' }}>Set</option>
                                    <option value="box" {{ old('unit', $sparepart->unit) == 'box' ? 'selected' : '' }}>Box</option>
                                    <option value="pack" {{ old('unit', $sparepart->unit) == 'pack' ? 'selected' : '' }}>Pack</option>
                                    <option value="kg" {{ old('unit', $sparepart->unit) == 'kg' ? 'selected' : '' }}>Kg</option>
                                    <option value="liter" {{ old('unit', $sparepart->unit) == 'liter' ? 'selected' : '' }}>Liter</option>
                                    <option value="meter" {{ old('unit', $sparepart->unit) == 'meter' ? 'selected' : '' }}>Meter</option>
                                </select>
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="parts_price" class="form-label">Unit Price (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="parts_price" id="parts_price"
                                    class="form-control @error('parts_price') is-invalid @enderror"
                                    value="{{ old('parts_price', $sparepart->parts_price) }}" min="0" step="0.01" required>
                                @error('parts_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="vulnerability" class="form-label">Vulnerability Level</label>
                                <select name="vulnerability" id="vulnerability" class="form-select @error('vulnerability') is-invalid @enderror">
                                    <option value="">Not Specified</option>
                                    <option value="low" {{ old('vulnerability', $sparepart->vulnerability) == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('vulnerability', $sparepart->vulnerability) == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('vulnerability', $sparepart->vulnerability) == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="critical" {{ old('vulnerability', $sparepart->vulnerability) == 'critical' ? 'selected' : '' }}>Critical</option>
                                </select>
                                @error('vulnerability')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label">Storage Location</label>
                            <input type="text" name="location" id="location"
                                class="form-control @error('location') is-invalid @enderror"
                                value="{{ old('location', $sparepart->location) }}"
                                placeholder="e.g., Warehouse A - Rack 3 - Shelf 2">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="path" class="form-label">Image/Document</label>
                            @if($sparepart->path)
                                <div class="mb-2">
                                    <small class="text-muted">Current file: {{ basename($sparepart->path) }}</small>
                                    <a href="{{ asset('storage/' . $sparepart->path) }}" target="_blank" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </div>
                            @endif
                            <input type="file" name="path" id="path"
                                class="form-control @error('path') is-invalid @enderror"
                                accept="image/*,.pdf">
                            <small class="text-muted">Leave empty to keep existing file. Accepted: Images (JPG, PNG) or PDF. Max 2MB</small>
                            @error('path')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route($routePrefix.'.spareparts.show', $sparepart) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Sparepart
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Last Opname Info</h5>
                </div>
                <div class="card-body">
                    @if($sparepart->last_opname_at)
                        <p><strong>Last Opname:</strong> {{ $sparepart->last_opname_at->format('d M Y H:i') }}</p>
                        <p><strong>Physical Qty:</strong> {{ $sparepart->physical_quantity ?? '-' }}</p>
                        <p><strong>Discrepancy:</strong>
                            @if($sparepart->discrepancy_qty > 0)
                                <span class="text-success">+{{ $sparepart->discrepancy_qty }}</span>
                            @elseif($sparepart->discrepancy_qty < 0)
                                <span class="text-danger">{{ $sparepart->discrepancy_qty }}</span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </p>
                        <p><strong>Status:</strong>
                            <span class="badge bg-{{ $sparepart->opname_status === 'completed' ? 'success' : 'secondary' }}">
                                {{ ucfirst($sparepart->opname_status ?? 'pending') }}
                            </span>
                        </p>
                    @else
                        <p class="text-muted">No opname recorded yet</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route($routePrefix.'.spareparts.adjustments.create') }}?sparepart_id={{ $sparepart->id }}" class="btn btn-warning w-100 mb-2">
                        <i class="fas fa-edit"></i> Adjust Stock
                    </a>
                    <a href="{{ route($routePrefix.'.spareparts.purchase-orders.create') }}?sparepart_id={{ $sparepart->id }}" class="btn btn-success w-100 mb-2">
                        <i class="fas fa-shopping-cart"></i> Create Purchase Order
                    </a>
                    <a href="{{ route($routePrefix.'.spareparts.opname.executions.create') }}?sparepart_id={{ $sparepart->id }}" class="btn btn-info w-100">
                        <i class="fas fa-clipboard-check"></i> Record Opname
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

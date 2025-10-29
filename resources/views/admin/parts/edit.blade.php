@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>✏️ Edit Part</h2>
        <a href="{{ route('admin.parts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Edit Part: <strong>{{ $part->code }}</strong></h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.parts.update', $part) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Part Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" 
                               class="form-control @error('code') is-invalid @enderror" 
                               value="{{ old('code', $part->code) }}" required>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-8 mb-3">
                        <label class="form-label">Part Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" 
                               class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $part->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="2" 
                              class="form-control @error('description') is-invalid @enderror">{{ old('description', $part->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Unit <span class="text-danger">*</span></label>
                        <select name="unit" class="form-control @error('unit') is-invalid @enderror" required>
                            <option value="pcs" {{ old('unit', $part->unit) == 'pcs' ? 'selected' : '' }}>Pieces (pcs)</option>
                            <option value="unit" {{ old('unit', $part->unit) == 'unit' ? 'selected' : '' }}>Unit</option>
                            <option value="box" {{ old('unit', $part->unit) == 'box' ? 'selected' : '' }}>Box</option>
                            <option value="set" {{ old('unit', $part->unit) == 'set' ? 'selected' : '' }}>Set</option>
                            <option value="meter" {{ old('unit', $part->unit) == 'meter' ? 'selected' : '' }}>Meter (m)</option>
                            <option value="liter" {{ old('unit', $part->unit) == 'liter' ? 'selected' : '' }}>Liter (L)</option>
                            <option value="kg" {{ old('unit', $part->unit) == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                            <option value="roll" {{ old('unit', $part->unit) == 'roll' ? 'selected' : '' }}>Roll</option>
                        </select>
                        @error('unit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="stock_quantity" 
                               class="form-control @error('stock_quantity') is-invalid @enderror" 
                               value="{{ old('stock_quantity', $part->stock_quantity) }}" min="0" required>
                        @error('stock_quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Minimum Stock <span class="text-danger">*</span></label>
                        <input type="number" name="minimum_stock" 
                               class="form-control @error('minimum_stock') is-invalid @enderror" 
                               value="{{ old('minimum_stock', $part->minimum_stock) }}" min="0" required>
                        <small class="text-muted">Alert threshold</small>
                        @error('minimum_stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Unit Cost (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="unit_cost" 
                               class="form-control @error('unit_cost') is-invalid @enderror" 
                               value="{{ old('unit_cost', $part->unit_cost) }}" min="0" step="0.01" required>
                        @error('unit_cost')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Supplier</label>
                        <input type="text" name="supplier" 
                               class="form-control @error('supplier') is-invalid @enderror" 
                               value="{{ old('supplier', $part->supplier) }}">
                        @error('supplier')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Storage Location</label>
                        <input type="text" name="location" 
                               class="form-control @error('location') is-invalid @enderror" 
                               value="{{ old('location', $part->location) }}">
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.parts.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Part
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
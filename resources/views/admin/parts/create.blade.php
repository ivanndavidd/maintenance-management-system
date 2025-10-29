@extends('layouts.admin')

@section('page-title', 'Add Part')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Add New Part</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.parts.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Part Code
                            <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            name="code"
                            class="form-control @error('code') is-invalid @enderror"
                            value="{{ old('code') }}"
                            required
                        />
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-8 mb-3">
                        <label class="form-label">
                            Part Name
                            <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}"
                            required
                        />
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">
                            Unit
                            <span class="text-danger">*</span>
                        </label>
                        <select
                            name="unit"
                            class="form-control @error('unit') is-invalid @enderror"
                            required
                        >
                            <option value="pcs" {{ old('unit') == 'pcs' ? 'selected' : '' }}>
                                Pieces
                            </option>
                            <option value="unit" {{ old('unit') == 'unit' ? 'selected' : '' }}>
                                Unit
                            </option>
                            <option value="box" {{ old('unit') == 'box' ? 'selected' : '' }}>
                                Box
                            </option>
                            <option value="meter" {{ old('unit') == 'meter' ? 'selected' : '' }}>
                                Meter
                            </option>
                            <option value="liter" {{ old('unit') == 'liter' ? 'selected' : '' }}>
                                Liter
                            </option>
                            <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>
                                Kilogram
                            </option>
                        </select>
                        @error('unit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">
                            Stock Quantity
                            <span class="text-danger">*</span>
                        </label>
                        <input
                            type="number"
                            name="stock_quantity"
                            class="form-control @error('stock_quantity') is-invalid @enderror"
                            value="{{ old('stock_quantity', 0) }}"
                            min="0"
                            required
                        />
                        @error('stock_quantity')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">
                            Minimum Stock
                            <span class="text-danger">*</span>
                        </label>
                        <input
                            type="number"
                            name="minimum_stock"
                            class="form-control @error('minimum_stock') is-invalid @enderror"
                            value="{{ old('minimum_stock', 5) }}"
                            min="0"
                            required
                        />
                        @error('minimum_stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">
                            Unit Cost ($)
                            <span class="text-danger">*</span>
                        </label>
                        <input
                            type="number"
                            name="unit_cost"
                            class="form-control @error('unit_cost') is-invalid @enderror"
                            value="{{ old('unit_cost') }}"
                            min="0"
                            step="0.01"
                            required
                        />
                        @error('unit_cost')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Supplier</label>
                        <input
                            type="text"
                            name="supplier"
                            class="form-control"
                            value="{{ old('supplier') }}"
                        />
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Storage Location</label>
                        <input
                            type="text"
                            name="location"
                            class="form-control"
                            value="{{ old('location') }}"
                            placeholder="e.g., Warehouse Rack A-1"
                        />
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="3" class="form-control">
{{ old('description') }}</textarea
                    >
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.parts.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Add Part</button>
                </div>
            </form>
        </div>
    </div>
@endsection

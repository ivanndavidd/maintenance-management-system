@extends('layouts.admin')

@section('page-title', 'Create Asset')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Create New Asset</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.assets.index') }}">Assets</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>
    </div>

    <div class="card" style="max-width:600px;">
        <div class="card-header"><h5 class="mb-0">Asset Information</h5></div>
        <div class="card-body">
            <form action="{{ route($routePrefix.'.assets.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="equipment_id" class="form-label">Equipment ID</label>
                    <input type="text" name="equipment_id" id="equipment_id"
                        class="form-control @error('equipment_id') is-invalid @enderror"
                        value="{{ old('equipment_id') }}" placeholder="Optional">
                    @error('equipment_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="asset_name" class="form-label">Asset Name <span class="text-danger">*</span></label>
                    <input type="text" name="asset_name" id="asset_name"
                        class="form-control @error('asset_name') is-invalid @enderror"
                        value="{{ old('asset_name') }}" required>
                    @error('asset_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status','active') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="disposed" {{ old('status') == 'disposed' ? 'selected' : '' }}>Disposed</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea name="notes" id="notes" rows="3"
                        class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create Asset</button>
                    <a href="{{ route($routePrefix.'.assets.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

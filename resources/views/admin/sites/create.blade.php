@extends('layouts.admin')

@section('page-title', 'Create New Site')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Create New Site</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.sites.index') }}">Site Management</a></li>
                <li class="breadcrumb-item active">Create Site</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Site Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.sites.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <h6 class="text-muted">Site Details</h6>
                            <hr>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Site Code <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('code') is-invalid @enderror"
                                           id="code"
                                           name="code"
                                           value="{{ old('code') }}"
                                           placeholder="e.g., site_jakarta"
                                           pattern="[a-z0-9_]+"
                                           required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Lowercase letters, numbers, and underscores only. This will be used for database name.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Site Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name"
                                           name="name"
                                           value="{{ old('name') }}"
                                           placeholder="e.g., Warehouse Jakarta"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description"
                                      name="description"
                                      rows="2"
                                      placeholder="Brief description of this site...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Note:</strong> Admin users from the central database can automatically access all sites without needing separate accounts.
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.sites.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Site
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">What Happens Next</h5>
                </div>
                <div class="card-body">
                    <ol class="ps-3">
                        <li class="mb-2">A new database will be created with name <code>warehouse_[code]</code></li>
                        <li class="mb-2">All migrations will be run on the new database</li>
                        <li class="mb-2">Roles (admin, supervisor, staff) will be created</li>
                        <li class="mb-2">Site will be available for selection on the site selection page</li>
                        <li class="mb-2">Central admin users can access this site immediately</li>
                    </ol>

                    <div class="alert alert-warning mb-0 mt-3">
                        <small>
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Note:</strong> This process may take a few moments as it creates the database and runs all migrations.
                        </small>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Database Preview</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">Database name will be:</p>
                    <code id="db-preview">warehouse_[your_code]</code>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('code').addEventListener('input', function(e) {
    // Convert to lowercase and replace invalid characters
    let value = e.target.value.toLowerCase().replace(/[^a-z0-9_]/g, '');
    e.target.value = value;

    // Update preview
    document.getElementById('db-preview').textContent = value ? 'warehouse_' + value : 'warehouse_[your_code]';
});
</script>
@endpush
@endsection

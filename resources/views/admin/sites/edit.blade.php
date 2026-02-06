@extends('layouts.admin')

@section('page-title', 'Edit Site')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Edit Site: {{ $site->name }}</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.sites.index') }}">Site Management</a></li>
                <li class="breadcrumb-item active">Edit Site</li>
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
                    <form action="{{ route('admin.sites.update', $site) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Site Code</label>
                                    <input type="text"
                                           class="form-control"
                                           id="code"
                                           value="{{ $site->code }}"
                                           disabled>
                                    <small class="text-muted">Site code cannot be changed.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="database_name" class="form-label">Database Name</label>
                                    <input type="text"
                                           class="form-control"
                                           id="database_name"
                                           value="{{ $site->database_name }}"
                                           disabled>
                                    <small class="text-muted">Database name cannot be changed.</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Site Name <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name', $site->name) }}"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description"
                                      name="description"
                                      rows="3">{{ old('description', $site->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="is_active"
                                       name="is_active"
                                       value="1"
                                       {{ old('is_active', $site->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Site is Active
                                </label>
                            </div>
                            <small class="text-muted">Inactive sites won't appear in the site selection page.</small>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('admin.sites.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Site
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Site Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td class="text-muted">Created:</td>
                            <td>{{ $site->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Updated:</td>
                            <td>{{ $site->updated_at->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status:</td>
                            <td>
                                @if($site->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Database Actions</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.sites.migrate', $site) }}"
                          method="POST"
                          onsubmit="return confirm('Run all pending migrations for this site?')">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="fas fa-database"></i> Run Migrations
                        </button>
                    </form>
                    <small class="text-muted d-block mt-2">
                        Run this after updating the application to apply database changes to this site.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('page-title', 'Add New User')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <h2><i class="fas fa-user-plus"></i> Add New User</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user"></i> User Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

                        <!-- Personal Information -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">Personal Information</h6>
                            </div>

                            <!-- Name -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}" 
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Employee ID -->
                            <div class="col-md-6 mb-3">
                                <label for="employee_id" class="form-label">Employee ID <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('employee_id') is-invalid @enderror" 
                                       id="employee_id" 
                                       name="employee_id" 
                                       value="{{ old('employee_id') }}" 
                                       required>
                                @error('employee_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone') }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- System Access -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">System Access</h6>
                            </div>

                            <!-- Department -->
                            <div class="col-md-6 mb-3">
                                <label for="department_id" class="form-label">Department</label>
                                <select class="form-select @error('department_id') is-invalid @enderror" 
                                        id="department_id" 
                                        name="department_id">
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

                            <!-- Role -->
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select @error('role') is-invalid @enderror" 
                                        id="role" 
                                        name="role" 
                                        required>
                                    <option value="">Select Role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                            {{ ucfirst($role->name) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    User roles determine access levels to system features
                                </small>
                            </div>

                            <!-- Active Status -->
                            <div class="col-md-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_active" 
                                           name="is_active" 
                                           value="1" 
                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        <strong>Active Account</strong>
                                        <small class="d-block text-muted">User can login to the system</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">Security</h6>
                            </div>

                            <!-- Password -->
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Minimum 8 characters</small>
                            </div>

                            <!-- Confirm Password -->
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password_confirmation" 
                                       name="password_confirmation" 
                                       required>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create User
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
                    <h6>Role Descriptions:</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <strong class="text-danger">Super Admin:</strong>
                            <small class="d-block text-muted">Full system access, cannot be deleted</small>
                        </li>
                        <li class="mb-2">
                            <strong class="text-warning">Admin:</strong>
                            <small class="d-block text-muted">Manage users, jobs, machines, and reports</small>
                        </li>
                        <li class="mb-2">
                            <strong class="text-primary">User/Operator:</strong>
                            <small class="d-block text-muted">View assigned tasks and submit work reports</small>
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
                        <li>Employee ID must be unique</li>
                        <li>Email will be used for login</li>
                        <li>Users will receive credentials via email (if configured)</li>
                        <li>Inactive users cannot login to the system</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@extends('layouts.admin')

@section('page-title', 'Edit User')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <h2><i class="fas fa-user-edit"></i> Edit User</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('supervisor.users.index') }}">Users</a></li>
                <li class="breadcrumb-item active">Edit: {{ $user->name }}</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-user"></i> Edit User Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('supervisor.users.update', $user) }}">
                        @csrf
                        @method('PUT')

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
                                       value="{{ old('name', $user->name) }}"
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
                                       value="{{ old('employee_id', $user->employee_id) }}"
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
                                       value="{{ old('email', $user->email) }}"
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
                                       value="{{ old('phone', $user->phone) }}">
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
                                        <option value="{{ $department->id }}"
                                                {{ old('department_id', $user->department_id) == $department->id ? 'selected' : '' }}>
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
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}"
                                                {{ old('role', $userRole ? $userRole->name : '') == $role->name ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Active Status -->
                            <div class="col-md-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="is_active"
                                           name="is_active"
                                           value="1"
                                           {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        <strong>Active Account</strong>
                                        <small class="d-block text-muted">User can login to the system</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Password (Optional) -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">Change Password (Optional)</h6>
                                <p class="text-muted small">Leave blank if you don't want to change the password</p>
                            </div>

                            <!-- Password -->
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       id="password"
                                       name="password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Minimum 8 characters</small>
                            </div>

                            <!-- Confirm Password -->
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <input type="password"
                                       class="form-control"
                                       id="password_confirmation"
                                       name="password_confirmation">
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('supervisor.users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-warning text-dark">
                                <i class="fas fa-save"></i> Update User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Sidebar -->
        <div class="col-lg-4">
            <!-- User Info Card -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Current User Info</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <strong>Created:</strong>
                            <small class="d-block text-muted">{{ $user->created_at->format('d M Y, H:i') }}</small>
                        </li>
                        <li class="mb-2">
                            <strong>Last Updated:</strong>
                            <small class="d-block text-muted">{{ $user->updated_at->format('d M Y, H:i') }}</small>
                        </li>
                        <li class="mb-2">
                            <strong>Last Login:</strong>
                            <small class="d-block text-muted">
                                {{ $user->last_login_at ? $user->last_login_at->format('d M Y, H:i') : 'Never' }}
                            </small>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('supervisor.users.show', $user) }}" class="btn btn-info btn-sm w-100 mb-2">
                        <i class="fas fa-eye"></i> View Full Profile
                    </a>

                    @if($user->id !== auth()->id())
                        <form action="{{ route('supervisor.users.toggle-status', $user) }}" method="POST" class="mb-2">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="btn btn-{{ $user->is_active ? 'secondary' : 'success' }} btn-sm w-100"
                                    onclick="return confirm('Are you sure?')">
                                <i class="fas fa-power-off"></i>
                                {{ $user->is_active ? 'Deactivate User' : 'Activate User' }}
                            </button>
                        </form>

                        <button type="button"
                                class="btn btn-warning btn-sm w-100 mb-2"
                                data-bs-toggle="modal"
                                data-bs-target="#resetPasswordModal">
                            <i class="fas fa-key"></i> Reset Password
                        </button>

                        <form action="{{ route('supervisor.users.destroy', $user) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="btn btn-danger btn-sm w-100"
                                    onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                <i class="fas fa-trash"></i> Delete User
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Warning Notes -->
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Important</h6>
                </div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li>Email must be unique in the system</li>
                        <li>Employee ID must be unique</li>
                        <li>Changing role affects user permissions</li>
                        <li>Inactive users cannot login</li>
                        <li class="text-info"><strong>You can only assign Supervisor Maintenance or Staff Maintenance roles</strong></li>
                        @if($user->id === auth()->id())
                            <li class="text-danger"><strong>You cannot delete your own account</strong></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('supervisor.users.reset-password', $user) }}" method="POST">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-key"></i> Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        You are resetting password for: <strong>{{ $user->name }}</strong>
                    </div>

                    <div class="mb-3">
                        <label for="modal_password" class="form-label">New Password <span class="text-danger">*</span></label>
                        <input type="password"
                               class="form-control"
                               id="modal_password"
                               name="password"
                               required>
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>

                    <div class="mb-3">
                        <label for="modal_password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password"
                               class="form-control"
                               id="modal_password_confirmation"
                               name="password_confirmation"
                               required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark">
                        <i class="fas fa-key"></i> Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

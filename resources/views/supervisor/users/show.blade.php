@extends('layouts.admin')

@section('page-title', 'User Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-user"></i> User Details</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('supervisor.users.index') }}">Users</a></li>
                    <li class="breadcrumb-item active">{{ $user->name }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('supervisor.users.edit', $user) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit User
            </a>
            <a href="{{ route('supervisor.users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- User Profile Card -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-id-card"></i> Profile</h5>
                </div>
                <div class="card-body text-center">
                    <div class="avatar-lg bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 32px;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <h4>{{ $user->name }}</h4>
                    <p class="text-muted mb-2">{{ $user->email }}</p>

                    @if($user->roles->isNotEmpty())
                        @foreach($user->roles as $role)
                            <span class="badge bg-info mb-2">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</span>
                        @endforeach
                    @else
                        <span class="badge bg-secondary mb-2">No Role</span>
                    @endif

                    <hr>

                    <ul class="list-unstyled text-start small">
                        <li class="mb-2">
                            <i class="fas fa-id-badge text-primary me-2"></i>
                            <strong>Employee ID:</strong> {{ $user->employee_id ?? 'N/A' }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-phone text-primary me-2"></i>
                            <strong>Phone:</strong> {{ $user->phone ?? 'N/A' }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-building text-primary me-2"></i>
                            <strong>Department:</strong> {{ $user->department->name ?? 'N/A' }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-circle {{ $user->is_active ? 'text-success' : 'text-danger' }} me-2"></i>
                            <strong>Status:</strong>
                            <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h6>
                </div>
                <div class="card-body">
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
                                    onclick="return confirm('Are you sure you want to delete this user?')">
                                <i class="fas fa-trash"></i> Delete User
                            </button>
                        </form>
                    @else
                        <p class="text-muted small mb-0">
                            <i class="fas fa-info-circle"></i> This is your own account
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistics & Activity -->
        <div class="col-lg-8">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h3>{{ $stats['total_assigned'] }}</h3>
                            <small>Total Assigned</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3>{{ $stats['completed'] }}</h3>
                            <small>Completed</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h3>{{ $stats['in_progress'] }}</h3>
                            <small>In Progress</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h3>{{ $stats['completion_rate'] }}%</h3>
                            <small>Completion Rate</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent CMR -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Recent CMR Tickets</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Ticket #</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentCmr as $cmr)
                                    <tr>
                                        <td>
                                            <a href="{{ route('supervisor.corrective-maintenance.index') }}">
                                                {{ $cmr->ticket_number ?? 'CMR-'.$cmr->id }}
                                            </a>
                                        </td>
                                        <td>{{ Str::limit($cmr->title ?? $cmr->description, 40) }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'in_progress' => 'info',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$cmr->status] ?? 'secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $cmr->status)) }}
                                            </span>
                                        </td>
                                        <td>{{ $cmr->created_at->format('d M Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            No CMR tickets found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Account Information -->
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Created At:</strong>
                            <p class="text-muted">{{ $user->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong>Last Updated:</strong>
                            <p class="text-muted">{{ $user->updated_at->format('d M Y, H:i') }}</p>
                        </div>
                        <div class="col-md-4">
                            <strong>Last Login:</strong>
                            <p class="text-muted">
                                {{ $user->last_login_at ? $user->last_login_at->format('d M Y, H:i') : 'Never' }}
                            </p>
                        </div>
                    </div>
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

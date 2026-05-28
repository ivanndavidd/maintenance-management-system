@extends('layouts.admin')

@section('page-title', 'User Profile')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5><i class="fas fa-user"></i> User Profile</h5>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.users.index') }}">Users</a></li>
                    <li class="breadcrumb-item active">{{ $user->name }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route($routePrefix.'.users.edit', $user) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i><span class="btn-text"> Edit User</span>
            </a>
            <a href="{{ route($routePrefix.'.users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i><span class="btn-text"> Back to List</span>
            </a>
        </div>
    </div>

    <!-- Success Messages -->
    <div class="row">
        <!-- User Profile Card -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    @if($user->profile_photo)
                        <img src="{{ asset('storage/' . $user->profile_photo) }}"
                             alt="{{ $user->name }}"
                             class="mx-auto mb-3 d-block"
                             style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid #667eea;">
                    @else
                        <div class="avatar-circle-large bg-primary text-white mx-auto mb-3">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                    @endif
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-3">{{ $user->employee_id }}</p>
                    
                    @foreach($user->roles as $role)
                        <span class="badge bg-{{ $role->name == 'admin' ? 'warning' : ($role->name == 'supervisor_maintenance' ? 'info' : 'primary') }} mb-3">
                            {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                        </span>
                    @endforeach

                    @if($user->is_active)
                        <span class="badge bg-success mb-3">
                            <i class="fas fa-check"></i> Active
                        </span>
                    @else
                        <span class="badge bg-danger mb-3">
                            <i class="fas fa-times"></i> Inactive
                        </span>
                    @endif

                    <hr>

                    <div class="text-start">
                        <p class="mb-2">
                            <i class="fas fa-envelope text-primary"></i>
                            <strong class="ms-2">Email:</strong>
                            <span class="d-block ms-4">{{ $user->email }}</span>
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-phone text-success"></i>
                            <strong class="ms-2">Phone:</strong>
                            <span class="d-block ms-4">{{ $user->phone ?? '-' }}</span>
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-calendar text-warning"></i>
                            <strong class="ms-2">Joined:</strong>
                            <span class="d-block ms-4">{{ $user->created_at->format('d M Y') }}</span>
                        </p>
                    </div>

                    @if($user->id !== auth()->id())
                    <hr>
                    <div class="d-grid gap-2">
                        <button type="button" 
                                class="btn btn-warning btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#resetPasswordModal">
                            <i class="fas fa-key"></i> Reset Password
                        </button>
                        
                        <form action="{{ route($routePrefix.'.users.toggle-status', $user) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="btn btn-{{ $user->is_active ? 'secondary' : 'success' }} btn-sm w-100"
                                    onclick="return confirm('Are you sure?')">
                                <i class="fas fa-power-off"></i> 
                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Performance & Statistics -->
        <div class="col-lg-8">
            <!-- Performance Statistics -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Performance Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center g-3">
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-light rounded">
                                <h5 class="text-warning mb-0">{{ $stats['cm_total'] }}</h5>
                                <small class="text-muted">CM Assigned</small>
                                <div style="font-size:11px;" class="text-success">{{ $stats['cm_completed'] }} done</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-light rounded">
                                <h5 class="text-success mb-0">{{ $stats['pm_total'] }}</h5>
                                <small class="text-muted">PM Assigned</small>
                                <div style="font-size:11px;" class="text-success">{{ $stats['pm_completed'] }} done</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-light rounded">
                                <h5 class="text-info mb-0">{{ $stats['so_total'] }}</h5>
                                <small class="text-muted">Stock Opname</small>
                                <div style="font-size:11px;" class="text-success">{{ $stats['so_completed'] }} done</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-3 bg-light rounded">
                                <h5 class="text-primary mb-0">{{ $stats['completion_rate'] }}%</h5>
                                <small class="text-muted">Completion Rate</small>
                                <div style="font-size:11px;" class="text-muted">{{ $stats['total_all'] }} total</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent CM Tickets -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-wrench"></i> Recent CM Tickets</h5>
                </div>
                <div class="card-body p-0">
                    @if($recentCmr->count() > 0)
                    <div style="max-height:340px;overflow-y:auto;">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Ticket</th>
                                    <th>Asset</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentCmr as $cmr)
                                <tr>
                                    <td><strong style="font-size:12px;">{{ $cmr->ticket_number }}</strong></td>
                                    <td style="font-size:12px;">{{ $cmr->report?->asset?->asset_name ?? $cmr->equipment_name ?? '-' }}</td>
                                    <td><span class="badge {{ $cmr->getPriorityBadgeClass() }}">{{ ucfirst($cmr->priority) }}</span></td>
                                    <td><span class="badge {{ $cmr->getStatusBadgeClass() }}">{{ ucfirst(str_replace('_', ' ', $cmr->status)) }}</span></td>
                                    <td><small>{{ $cmr->created_at->format('d M Y') }}</small></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-wrench fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No CM tickets assigned yet</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route($routePrefix.'.users.reset-password', $user) }}" method="POST">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-key"></i> Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Resetting password for: <strong>{{ $user->name }}</strong>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark">
            <i class="fas fa-key"></i><span class="btn-text"> Reset Password</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.avatar-circle-large {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 36px;
}
</style>
@endsection
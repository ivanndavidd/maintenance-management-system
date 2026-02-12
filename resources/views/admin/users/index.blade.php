@extends('layouts.admin')

@section('page-title', 'User Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-users"></i> User Management</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Users</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route($routePrefix.'.users.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New User
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <!-- Filters Card -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route($routePrefix.'.users.index') }}" id="filterForm">
                <div class="row g-3">
                    <!-- Search -->
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Name, email, employee ID..." 
                               value="{{ request('search') }}">
                    </div>

                    <!-- Role Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="">All Roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Department Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Department</label>
                        <select name="department" class="form-select">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ request('department') == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                    <a href="{{ route($routePrefix.'.users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-list"></i> Users List ({{ $users->total() }} users)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>
                                <strong>{{ $user->employee_id }}</strong>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-primary text-white me-2">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $user->name }}</strong>
                                        @if($user->id === auth()->id())
                                            <span class="badge bg-info ms-1">You</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->department)
                                    <span class="badge bg-secondary">{{ $user->department->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="badge bg-{{ $role->name == 'admin' ? 'warning' : ($role->name == 'supervisor_maintenance' ? 'info' : 'primary') }}">
                                        {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                    </span>
                                @endforeach
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check"></i> Active
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times"></i> Inactive
                                    </span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $user->created_at->format('d M Y') }}</small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route($routePrefix.'.users.show', $user) }}" 
                                       class="btn btn-sm btn-info" 
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route($routePrefix.'.users.edit', $user) }}" 
                                       class="btn btn-sm btn-warning" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    @if($user->id !== auth()->id())
                                        <!-- Access Site -->
                                        @php
                                            $userRoleName = $user->roles->first()?->name;
                                            $isSupervisor = $userRoleName === 'supervisor_maintenance';
                                        @endphp
                                        <button type="button"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Access Site"
                                                data-bs-toggle="modal"
                                                data-bs-target="#siteAccessModal{{ $user->id }}"
                                                {{ $isSupervisor ? 'disabled' : '' }}>
                                            <i class="fas fa-globe"></i>
                                        </button>

                                        <!-- Toggle Status -->
                                        <form action="{{ route($routePrefix.'.users.toggle-status', $user) }}"
                                              method="POST"
                                              class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="btn btn-sm btn-{{ $user->is_active ? 'secondary' : 'success' }}"
                                                    title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}"
                                                    onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-power-off"></i>
                                            </button>
                                        </form>

                                        <!-- Delete -->
                                        <form action="{{ route($routePrefix.'.users.destroy', $user) }}"
                                              method="POST"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-sm btn-danger"
                                                    title="Delete"
                                                    onclick="return confirm('Are you sure you want to delete this user?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">No users found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($users->hasPages())
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} users
                </div>
                <div>
                    {{ $users->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Site Access Modals -->
@foreach($users as $user)
    @if($user->id !== auth()->id())
    <div class="modal fade" id="siteAccessModal{{ $user->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route($routePrefix.'.users.site-access', $user) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-globe"></i> Site Access: {{ $user->name }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">Select which sites this user can access:</p>
                        @forelse($sites as $site)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox"
                                       name="site_ids[]"
                                       value="{{ $site->id }}"
                                       id="site_{{ $user->id }}_{{ $site->id }}"
                                       {{ in_array($site->id, $userSiteAccess[$user->id] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="site_{{ $user->id }}_{{ $site->id }}">
                                    {{ $site->name }}
                                    <small class="text-muted">({{ $site->code }})</small>
                                </label>
                            </div>
                        @empty
                            <p class="text-muted">No sites available.</p>
                        @endforelse
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Site Access
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}
</style>
@endsection
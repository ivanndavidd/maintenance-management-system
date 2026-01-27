@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-cogs"></i> Machines / Equipment</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Machines</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.machines.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Machine
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
            <form method="GET" action="{{ route('admin.machines.index') }}">
                <div class="row g-3">
                    <!-- Search -->
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Name, code, model..." 
                               value="{{ request('search') }}">
                    </div>

                    <!-- Status Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
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

                    <!-- Category Filter (instead of Type) -->
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                    <a href="{{ route('admin.machines.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Machines Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-success shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Operational</h6>
                            <h3 class="mb-0 text-success">
                                {{ $machines->where('status', 'operational')->count() }}
                            </h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x text-success opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-warning shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Maintenance</h6>
                            <h3 class="mb-0 text-warning">
                                {{ $machines->where('status', 'maintenance')->count() }}
                            </h3>
                        </div>
                        <i class="fas fa-wrench fa-2x text-warning opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-danger shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Breakdown</h6>
                            <h3 class="mb-0 text-danger">
                                {{ $machines->where('status', 'breakdown')->count() }}
                            </h3>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x text-danger opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-secondary shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Retired</h6>
                            <h3 class="mb-0 text-secondary">
                                {{ $machines->where('status', 'retired')->count() }}
                            </h3>
                        </div>
                        <i class="fas fa-archive fa-2x text-secondary opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Machines Table Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-list"></i> Machines List ({{ $machines->total() }} machines)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <a href="{{ route('admin.machines.index', array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
                                    'sort_by' => 'code',
                                    'sort_order' => request('sort_by') == 'code' && request('sort_order') == 'asc' ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Code
                                    @if(request('sort_by') == 'code')
                                        <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.machines.index', array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
                                    'sort_by' => 'name',
                                    'sort_order' => request('sort_by') == 'name' && request('sort_order') == 'asc' ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Name
                                    @if(request('sort_by') == 'name')
                                        <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Category</th>
                            <th>Department</th>
                            <th>
                                <a href="{{ route('admin.machines.index', array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
                                    'sort_by' => 'location',
                                    'sort_order' => request('sort_by') == 'location' && request('sort_order') == 'asc' ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Location
                                    @if(request('sort_by') == 'location')
                                        <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.machines.index', array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
                                    'sort_by' => 'status',
                                    'sort_order' => request('sort_by') == 'status' && request('sort_order') == 'asc' ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Status
                                    @if(request('sort_by') == 'status')
                                        <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.machines.index', array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
                                    'sort_by' => 'next_maintenance_date',
                                    'sort_order' => request('sort_by') == 'next_maintenance_date' && request('sort_order') == 'asc' ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Next Maintenance
                                    @if(request('sort_by') == 'next_maintenance_date')
                                        <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($machines as $machine)
                        <tr>
                            <td><strong class="text-primary">{{ $machine->code }}</strong></td>
                            <td>
                                <strong>{{ $machine->name }}</strong>
                                @if($machine->model)
                                    <br><small class="text-muted">{{ $machine->model }}</small>
                                @endif
                            </td>
                            <td>
                                @if($machine->category)
                                    <span class="badge bg-info">{{ $machine->category->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($machine->department)
                                    <span class="badge bg-secondary">{{ $machine->department->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $machine->location ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $machine->statusBadge }}">
                                    <i class="fas fa-{{ $machine->status == 'operational' ? 'check-circle' : ($machine->status == 'maintenance' ? 'wrench' : ($machine->status == 'breakdown' ? 'exclamation-triangle' : 'archive')) }}"></i>
                                    {{ ucfirst($machine->status) }}
                                </span>
                            </td>
                            <td>
                                @if($machine->next_maintenance_date)
                                    <small class="text-{{ $machine->isMaintenanceDue() ? 'danger' : 'muted' }}">
                                        {{ $machine->next_maintenance_date->format('d M Y') }}
                                        @if($machine->isMaintenanceDue())
                                            <i class="fas fa-exclamation-circle text-danger" title="Maintenance Overdue"></i>
                                        @endif
                                    </small>
                                @else
                                    <small class="text-muted">Not scheduled</small>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.machines.show', $machine) }}" 
                                       class="btn btn-sm btn-info" 
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.machines.edit', $machine) }}" 
                                       class="btn btn-sm btn-warning" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <div class="btn-group" role="group">
                                        <button type="button" 
                                                class="btn btn-sm btn-secondary dropdown-toggle" 
                                                data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <button type="button" 
                                                        class="dropdown-item" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#statusModal{{ $machine->id }}">
                                                    <i class="fas fa-exchange-alt"></i> Change Status
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('admin.machines.destroy', $machine) }}" 
                                                      method="POST" 
                                                      onsubmit="return confirm('Are you sure you want to delete this machine?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- Status Update Modal -->
                        <div class="modal fade" id="statusModal{{ $machine->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.machines.update-status', $machine) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Update Machine Status</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Update status for: <strong>{{ $machine->name }}</strong></p>
                                            <div class="mb-3">
                                                <label class="form-label">New Status</label>
                                                <select name="status" class="form-select" required>
                                                    <option value="operational" {{ $machine->status == 'operational' ? 'selected' : '' }}>
                                                        Operational
                                                    </option>
                                                    <option value="maintenance" {{ $machine->status == 'maintenance' ? 'selected' : '' }}>
                                                        Maintenance
                                                    </option>
                                                    <option value="breakdown" {{ $machine->status == 'breakdown' ? 'selected' : '' }}>
                                                        Breakdown
                                                    </option>
                                                    <option value="retired" {{ $machine->status == 'retired' ? 'selected' : '' }}>
                                                        Retired
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Update Status</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">No machines found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($machines->hasPages())
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    Showing {{ $machines->firstItem() }} to {{ $machines->lastItem() }} of {{ $machines->total() }} machines
                </div>
                <div>
                    {{ $machines->appends(request()->except('page'))->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
.opacity-25 {
    opacity: 0.25;
}

/* Sortable table headers */
.table thead th a {
    display: block;
    width: 100%;
    white-space: nowrap;
    transition: all 0.2s ease;
}

.table thead th a:hover {
    color: #0d6efd !important;
}

.table thead th a i.fa-sort {
    opacity: 0.3;
    transition: opacity 0.2s ease;
}

.table thead th a:hover i.fa-sort {
    opacity: 0.6;
}

.table thead th a i.fa-sort-up,
.table thead th a i.fa-sort-down {
    color: #0d6efd;
}
</style>
@endsection
@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-cogs"></i> Parts & Inventory</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Parts</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.parts.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Part
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters Card -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.parts.index') }}">
                <div class="row g-3">
                    <!-- Search -->
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control"
                               placeholder="Name, code, supplier..."
                               value="{{ request('search') }}">
                    </div>

                    <!-- Stock Status Filter -->
                    <div class="col-md-4">
                        <label class="form-label">Stock Status</label>
                        <select name="stock_status" class="form-select">
                            <option value="">All Status</option>
                            <option value="available" {{ request('stock_status') == 'available' ? 'selected' : '' }}>
                                Available
                            </option>
                            <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>
                                Low Stock
                            </option>
                            <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>
                                Out of Stock
                            </option>
                        </select>
                    </div>

                    <!-- Location Filter -->
                    <div class="col-md-4">
                        <label class="form-label">Location</label>
                        <select name="location" class="form-select">
                            <option value="">All Locations</option>
                            @foreach($locations as $location)
                                <option value="{{ $location }}" {{ request('location') == $location ? 'selected' : '' }}>
                                    {{ $location }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                    <a href="{{ route('admin.parts.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-primary shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Parts</h6>
                            <h3 class="mb-0 text-primary">{{ $stats['total_parts'] }}</h3>
                        </div>
                        <i class="fas fa-boxes fa-2x text-primary opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-success shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Value</h6>
                            <h3 class="mb-0 text-success">Rp {{ number_format($stats['total_value'], 0, ',', '.') }}</h3>
                        </div>
                        <i class="fas fa-dollar-sign fa-2x text-success opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-warning shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Low Stock</h6>
                            <h3 class="mb-0 text-warning">{{ $stats['low_stock'] }}</h3>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x text-warning opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-danger shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Out of Stock</h6>
                            <h3 class="mb-0 text-danger">{{ $stats['out_of_stock'] }}</h3>
                        </div>
                        <i class="fas fa-times-circle fa-2x text-danger opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert (jika ada) -->
    @if($lowStockParts->count() > 0)
    <div class="alert alert-warning">
        <h5><i class="fas fa-exclamation-triangle"></i> Low Stock Alert!</h5>
        <p class="mb-0">{{ $lowStockParts->count() }} part(s) are running low on stock. Please reorder soon.</p>
    </div>
    @endif

    <!-- Parts Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-list"></i> Parts List ({{ $parts->total() }} parts)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <a href="{{ route('admin.parts.index', array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
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
                                <a href="{{ route('admin.parts.index', array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
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
                            <th>
                                <a href="{{ route('admin.parts.index', array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
                                    'sort_by' => 'unit',
                                    'sort_order' => request('sort_by') == 'unit' && request('sort_order') == 'asc' ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Unit
                                    @if(request('sort_by') == 'unit')
                                        <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ route('admin.parts.index', array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
                                    'sort_by' => 'stock_quantity',
                                    'sort_order' => request('sort_by') == 'stock_quantity' && request('sort_order') == 'asc' ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Stock
                                    @if(request('sort_by') == 'stock_quantity')
                                        <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Status</th>
                            <th>
                                <a href="{{ route('admin.parts.index', array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
                                    'sort_by' => 'unit_cost',
                                    'sort_order' => request('sort_by') == 'unit_cost' && request('sort_order') == 'asc' ? 'desc' : 'asc'
                                ])) }}" class="text-decoration-none text-dark">
                                    Unit Cost
                                    @if(request('sort_by') == 'unit_cost')
                                        <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Total Value</th>
                            <th>
                                <a href="{{ route('admin.parts.index', array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($parts as $part)
                        <tr>
                            <td><strong>{{ $part->code }}</strong></td>
                            <td>
                                {{ $part->name }}
                                @if($part->description)
                                    <br><small class="text-muted">{{ Str::limit($part->description, 50) }}</small>
                                @endif
                            </td>
                            <td>{{ $part->unit }}</td>
                            <td>
                                <strong>{{ number_format($part->stock_quantity) }}</strong>
                                <br>
                                <small class="text-muted">Min: {{ number_format($part->minimum_stock) }}</small>
                            </td>
                            <td>
                                @if($part->stock_quantity == 0)
                                    <span class="badge bg-danger">Out of Stock</span>
                                @elseif($part->stock_quantity <= $part->minimum_stock)
                                    <span class="badge bg-warning text-dark">Low Stock</span>
                                @else
                                    <span class="badge bg-success">Available</span>
                                @endif
                            </td>
                            <td>Rp {{ number_format($part->unit_cost, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($part->stock_quantity * $part->unit_cost, 0, ',', '.') }}</td>
                            <td>
                                @if($part->location)
                                    <small>{{ $part->location }}</small>
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.parts.edit', $part) }}" 
                                   class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.parts.destroy', $part) }}" 
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this part?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted">No parts found. Add your first part!</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($parts->hasPages())
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    Showing {{ $parts->firstItem() }} to {{ $parts->lastItem() }} of {{ $parts->total() }} parts
                </div>
                <div>
                    {{ $parts->appends(request()->except('page'))->links() }}
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
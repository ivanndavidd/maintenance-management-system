@extends('layouts.admin')

@section('page-title', 'Spareparts Management')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Spareparts Management</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Spareparts</li>
            </ol>
        </nav>
    </div>

    <!-- Quick Access Cards -->
    <div class="row mb-3 g-2">
        <div class="col-6 col-md-3">
            <div class="card border-primary h-100">
                <div class="card-body text-center p-3">
                    <i class="fas fa-boxes text-primary mb-2" style="font-size: 2rem;"></i>
                    <h6 class="mb-2">Stock Management</h6>
                    <a href="#master-data" class="btn btn-primary btn-sm">View</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-success h-100">
                <div class="card-body text-center p-3">
                    <i class="fas fa-shopping-cart text-success mb-2" style="font-size: 2rem;"></i>
                    <h6 class="mb-2">Purchase Orders</h6>
                    <a href="{{ route('admin.spareparts.purchase-orders') }}" class="btn btn-success btn-sm">View</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-info h-100">
                <div class="card-body text-center p-3">
                    <i class="fas fa-clipboard-check text-info mb-2" style="font-size: 2rem;"></i>
                    <h6 class="mb-2">Stock Opname</h6>
                    <a href="{{ route('admin.spareparts.opname.dashboard') }}" class="btn btn-info btn-sm">View</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-warning h-100">
                <div class="card-body text-center p-3">
                    <i class="fas fa-edit text-warning mb-2" style="font-size: 2rem;"></i>
                    <h6 class="mb-2">Stock Adjustments</h6>
                    <a href="{{ route('admin.spareparts.adjustments') }}" class="btn btn-warning btn-sm">View</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Master Data Section -->
    <div id="master-data" class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Master Data Spareparts</h5>
            <a href="{{ route('admin.spareparts.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Sparepart
            </a>
        </div>
        <div class="card-body">
            <!-- Filter Section -->
            <form method="GET" action="{{ route('admin.spareparts.index') }}">
                <div class="row mb-3 g-2">
                    <div class="col-12 col-md-3">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('search') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <select name="equipment_type" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            @foreach($equipmentTypes as $type)
                                <option value="{{ $type }}" {{ request('equipment_type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <select name="location" class="form-select form-select-sm">
                            <option value="">All Locations</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc }}" {{ request('location') == $loc ? 'selected' : '' }}>
                                    {{ $loc }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <select name="stock_status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="normal" {{ request('stock_status') == 'normal' ? 'selected' : '' }}>Normal</option>
                            <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Out</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </form>

            <!-- Active Filters Display -->
            @if(request('search') || request('equipment_type') || request('location') || request('stock_status'))
                <div class="mb-3">
                    <small class="text-muted">Active filters:</small>
                    <div class="d-inline-flex gap-2 ms-2 flex-wrap">
                        @if(request('search'))
                            <span class="badge bg-info">
                                Search: {{ request('search') }}
                                <a href="{{ route('admin.spareparts.index', array_filter(request()->except('search'))) }}" class="text-white text-decoration-none ms-1">×</a>
                            </span>
                        @endif
                        @if(request('equipment_type'))
                            <span class="badge bg-info">
                                Type: {{ ucfirst(request('equipment_type')) }}
                                <a href="{{ route('admin.spareparts.index', array_filter(request()->except('equipment_type'))) }}" class="text-white text-decoration-none ms-1">×</a>
                            </span>
                        @endif
                        @if(request('location'))
                            <span class="badge bg-info">
                                Location: {{ request('location') }}
                                <a href="{{ route('admin.spareparts.index', array_filter(request()->except('location'))) }}" class="text-white text-decoration-none ms-1">×</a>
                            </span>
                        @endif
                        @if(request('stock_status'))
                            <span class="badge bg-info">
                                Status: {{ ucfirst(request('stock_status')) }}
                                <a href="{{ route('admin.spareparts.index', array_filter(request()->except('stock_status'))) }}" class="text-white text-decoration-none ms-1">×</a>
                            </span>
                        @endif
                        <a href="{{ route('admin.spareparts.index') }}" class="badge bg-secondary text-decoration-none">
                            Clear All
                        </a>
                    </div>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Material Code</th>
                            <th>Name</th>
                            <th>Equipment Type</th>
                            <th>Brand</th>
                            <th>Model</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-center">Min Stock</th>
                            <th>Unit</th>
                            <th>Location</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($spareparts as $sparepart)
                        <tr>
                            <td><span class="badge bg-info">{{ $sparepart->material_code ?? '-' }}</span></td>
                            <td>
                                {{ $sparepart->sparepart_name }}
                                @if($sparepart->quantity <= 0)
                                    <span class="badge bg-danger ms-1">Out of Stock</span>
                                @elseif($sparepart->quantity <= $sparepart->minimum_stock)
                                    <span class="badge bg-warning text-dark ms-1">Low Stock</span>
                                @endif
                            </td>
                            <td><span class="badge bg-secondary">{{ $sparepart->equipment_type ?? '-' }}</span></td>
                            <td>{{ $sparepart->brand ?? '-' }}</td>
                            <td>{{ $sparepart->model ?? '-' }}</td>
                            <td class="text-center">
                                @if($sparepart->quantity <= 0)
                                    <span class="text-danger fw-bold">{{ $sparepart->quantity }}</span>
                                @elseif($sparepart->quantity <= $sparepart->minimum_stock)
                                    <span class="text-warning fw-bold">{{ $sparepart->quantity }}</span>
                                @else
                                    <span class="text-success fw-bold">{{ $sparepart->quantity }}</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $sparepart->minimum_stock }}</td>
                            <td>{{ $sparepart->unit }}</td>
                            <td>{{ $sparepart->location ?? '-' }}</td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.spareparts.show', $sparepart) }}" class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.spareparts.edit', $sparepart) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.spareparts.destroy', $sparepart) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this sparepart?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">No spareparts found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $spareparts->links() }}
            </div>
        </div>
    </div>

    <!-- Statistics Section -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Inventory Statistics</h6>
                </div>
                <div class="card-body p-2">
                    <div class="row g-2">
                        <div class="col-6 col-lg-3">
                            <div class="text-center p-2">
                                <h5 class="text-primary mb-0">{{ $stats['total'] }}</h5>
                                <small class="text-muted">Total</small>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="text-center p-2">
                                <h5 class="text-warning mb-0">{{ $stats['low_stock'] }}</h5>
                                <small class="text-muted">Low Stock</small>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="text-center p-2">
                                <h5 class="text-danger mb-0">{{ $stats['out_of_stock'] }}</h5>
                                <small class="text-muted">Out of Stock</small>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="text-center p-2">
                                <h6 class="text-success mb-0">Rp {{ number_format($stats['total_value'], 0, ',', '.') }}</h6>
                                <small class="text-muted">Total Value</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

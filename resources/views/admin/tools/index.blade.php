@extends('layouts.admin')

@section('page-title', 'Tools Management')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Tools Management</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Tools</li>
            </ol>
        </nav>
    </div>

    <!-- Inventory Statistics -->
    <div class="card mb-3">
        <div class="card-header bg-white">
            <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Inventory Statistics</h6>
        </div>
        <div class="card-body p-2">
            <div class="row text-center g-2">
                <div class="col-6 col-lg-3">
                    <div class="p-2">
                        <h5 class="text-primary mb-0">{{ $totalTools }}</h5>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="p-2">
                        <h5 class="text-warning mb-0">{{ $lowStock }}</h5>
                        <small class="text-muted">Low Stock</small>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="p-2">
                        <h5 class="text-danger mb-0">{{ $outOfStock }}</h5>
                        <small class="text-muted">Out of Stock</small>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="p-2">
                        <h6 class="text-success mb-0">Rp {{ number_format($totalValue, 0, ',', '.') }}</h6>
                        <small class="text-muted">Total Value</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-3 g-2">
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.tools.index') }}" class="text-decoration-none">
                <div class="card bg-primary text-white h-100 {{ !request('status') ? 'border-3 border-white' : '' }}" style="cursor: pointer;">
                    <div class="card-body text-center p-3">
                        <i class="fas fa-tools fa-3x mb-2"></i>
                        <h6 class="mb-1">Total Tools</h6>
                        <h4 class="mb-0">{{ \App\Models\Tool::count() }}</h4>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.tools.index', ['status' => 'in_stock']) }}" class="text-decoration-none">
                <div class="card bg-success text-white h-100 {{ request('status') == 'in_stock' ? 'border-3 border-white' : '' }}" style="cursor: pointer;">
                    <div class="card-body text-center p-3">
                        <i class="fas fa-check-circle fa-3x mb-2"></i>
                        <h6 class="mb-1">In Stock</h6>
                        <h4 class="mb-0">{{ \App\Models\Tool::where('quantity', '>', 0)->whereColumn('quantity', '>', 'minimum_stock')->count() }}</h4>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.tools.index', ['status' => 'low_stock']) }}" class="text-decoration-none">
                <div class="card bg-warning text-white h-100 {{ request('status') == 'low_stock' ? 'border-3 border-white' : '' }}" style="cursor: pointer;">
                    <div class="card-body text-center p-3">
                        <i class="fas fa-exclamation-triangle fa-3x mb-2"></i>
                        <h6 class="mb-1">Low Stock</h6>
                        <h4 class="mb-0">{{ \App\Models\Tool::where('quantity', '>', 0)->whereColumn('quantity', '<=', 'minimum_stock')->count() }}</h4>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.tools.index', ['status' => 'out_of_stock']) }}" class="text-decoration-none">
                <div class="card bg-danger text-white h-100 {{ request('status') == 'out_of_stock' ? 'border-3 border-white' : '' }}" style="cursor: pointer;">
                    <div class="card-body text-center p-3">
                        <i class="fas fa-times-circle fa-3x mb-2"></i>
                        <h6 class="mb-1">Out of Stock</h6>
                        <h4 class="mb-0">{{ \App\Models\Tool::where('quantity', '<=', 0)->count() }}</h4>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Tools Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-tools"></i> Tools List</h5>
            <div>
                <a href="{{ route('admin.tools.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Tool
                </a>
            </div>
        </div>
        <div class="card-body">
            <!-- Search & Filter -->
            <form method="GET" action="{{ route('admin.tools.index') }}" class="mb-3">
                <!-- Preserve status filter -->
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif

                <div class="row g-2">
                    <div class="col-12 col-md-5">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('search') }}">
                    </div>
                    <div class="col-8 col-md-4">
                        <select name="location" class="form-select form-select-sm">
                            <option value="">All Locations</option>
                            @foreach(\App\Models\Tool::select('location')->distinct()->whereNotNull('location')->orderBy('location')->pluck('location') as $loc)
                                <option value="{{ $loc }}" {{ request('location') == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4 col-md-3">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
            </form>

            <!-- Active Filters Display -->
            @if(request('status') || request('search') || request('location'))
                <div class="mb-3">
                    <small class="text-muted">Active filters:</small>
                    <div class="d-inline-flex gap-2 ms-2">
                        @if(request('status'))
                            <span class="badge bg-info">
                                Status: {{ ucwords(str_replace('_', ' ', request('status'))) }}
                                <a href="{{ route('admin.tools.index', array_filter(request()->except('status'))) }}" class="text-white ms-1">×</a>
                            </span>
                        @endif
                        @if(request('search'))
                            <span class="badge bg-info">
                                Search: "{{ request('search') }}"
                                <a href="{{ route('admin.tools.index', array_filter(request()->except('search'))) }}" class="text-white ms-1">×</a>
                            </span>
                        @endif
                        @if(request('location'))
                            <span class="badge bg-info">
                                Location: {{ request('location') }}
                                <a href="{{ route('admin.tools.index', array_filter(request()->except('location'))) }}" class="text-white ms-1">×</a>
                            </span>
                        @endif
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
                        @forelse($tools as $tool)
                        <tr>
                            <td><span class="badge bg-info">{{ $tool->material_code ?? '-' }}</span></td>
                            <td>
                                {{ $tool->sparepart_name }}
                                @if($tool->quantity <= 0)
                                    <span class="badge bg-danger ms-1">Out of Stock</span>
                                @elseif($tool->quantity <= $tool->minimum_stock)
                                    <span class="badge bg-warning text-dark ms-1">Low Stock</span>
                                @endif
                            </td>
                            <td><span class="badge bg-secondary">{{ $tool->equipment_type ?? '-' }}</span></td>
                            <td>{{ $tool->brand ?? '-' }}</td>
                            <td>{{ $tool->model ?? '-' }}</td>
                            <td class="text-center">
                                @if($tool->quantity <= 0)
                                    <span class="text-danger fw-bold">{{ $tool->quantity }}</span>
                                @elseif($tool->quantity <= $tool->minimum_stock)
                                    <span class="text-warning fw-bold">{{ $tool->quantity }}</span>
                                @else
                                    <span class="text-success fw-bold">{{ $tool->quantity }}</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $tool->minimum_stock }}</td>
                            <td>{{ $tool->unit }}</td>
                            <td>{{ $tool->location ?? '-' }}</td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.tools.show', $tool) }}" class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.tools.edit', $tool) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.tools.destroy', $tool) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to delete this tool?')">
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
                            <td colspan="10" class="text-center">No tools found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $tools->firstItem() ?? 0 }} to {{ $tools->lastItem() ?? 0 }} of {{ $tools->total() }} tools
                </div>
                <div>
                    {{ $tools->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

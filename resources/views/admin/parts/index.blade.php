@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>🔩 Parts & Inventory</h2>
        <a href="{{ route('admin.parts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Part
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <h6 class="text-muted">Total Parts</h6>
                    <h3>{{ $stats['total_parts'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <h6 class="text-muted">Total Value</h6>
                    <h3>Rp {{ number_format($stats['total_value'], 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <h6 class="text-muted">Low Stock</h6>
                    <h3 class="text-warning">{{ $stats['low_stock'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body">
                    <h6 class="text-muted">Out of Stock</h6>
                    <h3 class="text-danger">{{ $stats['out_of_stock'] }}</h3>
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
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Parts List</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Unit</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Unit Cost</th>
                            <th>Total Value</th>
                            <th>Location</th>
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

            <!-- Pagination -->
            <div class="mt-3">
                {{ $parts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
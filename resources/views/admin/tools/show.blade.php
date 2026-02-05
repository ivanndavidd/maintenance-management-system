@extends('layouts.admin')

@section('page-title', 'Tool Details')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Tool Details</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.tools.index') }}">Tools</a></li>
                <li class="breadcrumb-item active">{{ $tool->sparepart_name }}</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Basic Information -->
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Basic Information</h5>
                    <div>
                        @if($tool->quantity <= 0)
                            <span class="badge bg-danger">Out of Stock</span>
                        @elseif($tool->quantity <= $tool->minimum_stock)
                            <span class="badge bg-warning">Low Stock</span>
                        @else
                            <span class="badge bg-success">Available</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Material Code:</strong><br>
                            <h4 class="text-info">{{ $tool->material_code ?? '-' }}</h4>
                        </div>
                        <div class="col-md-6">
                            <strong>Tool Name:</strong><br>
                            <h4>{{ $tool->sparepart_name }}</h4>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Equipment Type:</strong><br>
                            <span class="badge bg-secondary">{{ $tool->equipment_type ?? '-' }}</span>
                        </div>
                        <div class="col-md-4">
                            <strong>Brand:</strong><br>
                            {{ $tool->brand ?? '-' }}
                        </div>
                        <div class="col-md-4">
                            <strong>Model:</strong><br>
                            {{ $tool->model ?? '-' }}
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Current Stock:</strong><br>
                            <h3>
                                @if($tool->quantity <= 0)
                                    <span class="text-danger">{{ $tool->quantity }}</span>
                                @elseif($tool->quantity <= $tool->minimum_stock)
                                    <span class="text-warning">{{ $tool->quantity }}</span>
                                @else
                                    <span class="text-success">{{ $tool->quantity }}</span>
                                @endif
                                {{ $tool->unit }}
                            </h3>
                        </div>
                        <div class="col-md-3">
                            <strong>Minimum Stock:</strong><br>
                            <h3>{{ $tool->minimum_stock }} {{ $tool->unit }}</h3>
                        </div>
                        <div class="col-md-3">
                            <strong>Unit Price:</strong><br>
                            <h5 class="text-primary">Rp {{ number_format($tool->parts_price, 0, ',', '.') }}</h5>
                        </div>
                        <div class="col-md-3">
                            <strong>Total Value:</strong><br>
                            <h5 class="text-success">Rp {{ number_format($tool->quantity * $tool->parts_price, 0, ',', '.') }}</h5>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Vulnerability Level:</strong><br>
                            @if($tool->vulnerability === 'critical')
                                <span class="badge bg-danger">Critical</span>
                            @elseif($tool->vulnerability === 'high')
                                <span class="badge bg-warning">High</span>
                            @elseif($tool->vulnerability === 'medium')
                                <span class="badge bg-info">Medium</span>
                            @elseif($tool->vulnerability === 'low')
                                <span class="badge bg-success">Low</span>
                            @else
                                <span class="badge bg-secondary">Not Specified</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Storage Location:</strong><br>
                            {{ $tool->location ?? '-' }}
                        </div>
                    </div>

                    @if($tool->path)
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <strong>Attachment:</strong><br>
                            <a href="{{ asset('storage/' . $tool->path) }}" target="_blank" class="btn btn-sm btn-info mt-2">
                                <i class="fas fa-file"></i> View Attachment
                            </a>
                        </div>
                    </div>
                    @endif

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <strong>Added By:</strong><br>
                            {{ $tool->addedByUser->name ?? '-' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Added At:</strong><br>
                            {{ $tool->created_at->format('d M Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Opname Information -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Stock Opname Information</h5>
                </div>
                <div class="card-body">
                    @if($tool->last_opname_at)
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Last Opname Date:</strong><br>
                                {{ $tool->last_opname_at->format('d M Y H:i') }}
                                <span class="text-muted">({{ $tool->last_opname_at->diffForHumans() }})</span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Opname Status:</strong><br>
                                <span class="badge bg-{{ $tool->opname_status === 'completed' ? 'success' : 'warning' }}">
                                    {{ ucfirst($tool->opname_status ?? 'pending') }}
                                </span>
                                </p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <p><strong>System Quantity:</strong><br>
                                {{ $tool->quantity }} {{ $tool->unit }}
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Physical Quantity:</strong><br>
                                {{ $tool->physical_quantity ?? '-' }} {{ $tool->unit }}
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Discrepancy:</strong><br>
                                @if($tool->discrepancy_qty > 0)
                                    <span class="text-success">+{{ $tool->discrepancy_qty }} {{ $tool->unit }}</span>
                                @elseif($tool->discrepancy_qty < 0)
                                    <span class="text-danger">{{ $tool->discrepancy_qty }} {{ $tool->unit }}</span>
                                @else
                                    <span class="text-muted">0 {{ $tool->unit }}</span>
                                @endif
                                </p>
                            </div>
                        </div>

                        @if($tool->discrepancy_value != 0)
                        <div class="alert alert-{{ $tool->discrepancy_value > 0 ? 'success' : 'danger' }}">
                            <strong>Value Impact:</strong>
                            Rp {{ number_format(abs($tool->discrepancy_value), 0, ',', '.') }}
                            ({{ $tool->discrepancy_value > 0 ? 'Surplus' : 'Shortage' }})
                        </div>
                        @endif

                        <p><strong>Verified By:</strong><br>
                        {{ $tool->verifiedByUser->name ?? '-' }}
                        </p>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No stock opname has been performed yet.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Actions Card -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route($routePrefix.'.tools.edit', $tool) }}" class="btn btn-warning w-100 mb-2">
                        <i class="fas fa-edit"></i> Edit Tool
                    </a>
                    <a href="{{ route($routePrefix.'.adjustments.create') }}?tool_id={{ $tool->id }}" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-sliders-h"></i> Adjust Stock
                    </a>
                    <a href="{{ route($routePrefix.'.purchase-orders.create') }}?tool_id={{ $tool->id }}" class="btn btn-success w-100 mb-2">
                        <i class="fas fa-shopping-cart"></i> Create Purchase Order
                    </a>
                    <a href="{{ route($routePrefix.'.opname.executions.create') }}?tool_id={{ $tool->id }}" class="btn btn-info w-100 mb-2">
                        <i class="fas fa-clipboard-check"></i> Record Opname
                    </a>

                    <hr>

                    <a href="{{ route($routePrefix.'.tools.index') }}" class="btn btn-secondary w-100 mb-2">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>

                    <form action="{{ route($routePrefix.'.tools.destroy', $tool) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this tool?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-trash"></i> Delete Tool
                        </button>
                    </form>
                </div>
            </div>

            <!-- Stock Alert Card -->
            @if($tool->quantity <= $tool->minimum_stock)
            <div class="card mb-3 border-warning">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Stock Alert</h5>
                </div>
                <div class="card-body">
                    <p>This item is {{ $tool->quantity <= 0 ? 'out of stock' : 'below minimum stock level' }}.</p>
                    <p><strong>Current:</strong> {{ $tool->quantity }} {{ $tool->unit }}</p>
                    <p><strong>Minimum:</strong> {{ $tool->minimum_stock }} {{ $tool->unit }}</p>
                    <p><strong>Suggested Order:</strong> {{ max($tool->minimum_stock * 2 - $tool->quantity, 0) }} {{ $tool->unit }}</p>
                    <a href="{{ route($routePrefix.'.purchase-orders.create') }}?tool_id={{ $tool->id }}" class="btn btn-warning w-100">
                        <i class="fas fa-shopping-cart"></i> Order Now
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

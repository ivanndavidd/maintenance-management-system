@extends('layouts.admin')

@section('page-title', 'Tool Details')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h5>Tool Details</h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.tools.index') }}">Tools</a></li>
                <li class="breadcrumb-item active">{{ $tool->sparepart_name }}</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-12 col-md-8 order-2 order-md-1">
            <!-- Basic Information -->
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Basic Information</h6>
                    @if($tool->quantity <= 0)
                        <span class="badge bg-danger">Out of Stock</span>
                    @elseif($tool->quantity <= $tool->minimum_stock)
                        <span class="badge bg-warning">Low Stock</span>
                    @else
                        <span class="badge bg-success">Available</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <small class="text-muted d-block">Material Code</small>
                            <span class="fw-bold text-info fs-6">{{ $tool->material_code ?? '-' }}</span>
                        </div>
                        <div class="col-12 col-md-6">
                            <small class="text-muted d-block">Tool Name</small>
                            <span class="fw-bold fs-6">{{ $tool->sparepart_name }}</span>
                        </div>
                        <div class="col-6 col-md-4">
                            <small class="text-muted d-block">Equipment Type</small>
                            <span class="badge bg-secondary">{{ $tool->equipment_type ?? '-' }}</span>
                        </div>
                        <div class="col-6 col-md-4">
                            <small class="text-muted d-block">Brand</small>
                            <span>{{ $tool->brand ?? '-' }}</span>
                        </div>
                        <div class="col-6 col-md-4">
                            <small class="text-muted d-block">Model</small>
                            <span>{{ $tool->model ?? '-' }}</span>
                        </div>
                    </div>

                    <hr class="my-2">

                    <div class="row g-2 mb-2">
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Current Stock</small>
                            <span class="fw-bold fs-6
                                @if($tool->quantity <= 0) text-danger
                                @elseif($tool->quantity <= $tool->minimum_stock) text-warning
                                @else text-success @endif">
                                {{ $tool->quantity }} {{ $tool->unit }}
                            </span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Minimum Stock</small>
                            <span class="fw-bold">{{ $tool->minimum_stock }} {{ $tool->unit }}</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Unit Price</small>
                            <span class="fw-bold text-primary" style="font-size:13px;">Rp {{ number_format($tool->parts_price, 0, ',', '.') }}</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Total Value</small>
                            <span class="fw-bold text-success" style="font-size:13px;">Rp {{ number_format($tool->quantity * $tool->parts_price, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <hr class="my-2">

                    <div class="row g-2">
                        <div class="col-6 col-md-6">
                            <small class="text-muted d-block">Vulnerability Level</small>
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
                        <div class="col-6 col-md-6">
                            <small class="text-muted d-block">Storage Location</small>
                            <span>{{ $tool->location ?? '-' }}</span>
                        </div>
                        <div class="col-6 col-md-6">
                            <small class="text-muted d-block">Added By</small>
                            <span>{{ $tool->addedByUser->name ?? '-' }}</span>
                        </div>
                        <div class="col-6 col-md-6">
                            <small class="text-muted d-block">Added At</small>
                            <span>{{ $tool->created_at->format('d M Y H:i') }}</span>
                        </div>
                        @if($tool->path)
                        <div class="col-12">
                            <small class="text-muted d-block">Attachment</small>
                            <a href="{{ asset('storage/' . $tool->path) }}" target="_blank" class="btn btn-sm btn-outline-info mt-1">
                                <i class="fas fa-file"></i> View Attachment
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Stock Opname Information -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">Stock Opname Information</h6>
                </div>
                <div class="card-body">
                    @if($tool->last_opname_at)
                        <div class="row g-2 mb-2">
                            <div class="col-12 col-md-6">
                                <small class="text-muted d-block">Last Opname Date</small>
                                <span>{{ $tool->last_opname_at->format('d M Y H:i') }}</span>
                                <small class="text-muted">({{ $tool->last_opname_at->diffForHumans() }})</small>
                            </div>
                            <div class="col-12 col-md-6">
                                <small class="text-muted d-block">Opname Status</small>
                                <span class="badge bg-{{ $tool->opname_status === 'completed' ? 'success' : 'warning' }}">
                                    {{ ucfirst($tool->opname_status ?? 'pending') }}
                                </span>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">System Qty</small>
                                <span>{{ $tool->quantity }} {{ $tool->unit }}</span>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Physical Qty</small>
                                <span>{{ $tool->physical_quantity ?? '-' }} {{ $tool->unit }}</span>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Discrepancy</small>
                                @if($tool->discrepancy_qty > 0)
                                    <span class="text-success fw-bold">+{{ $tool->discrepancy_qty }}</span>
                                @elseif($tool->discrepancy_qty < 0)
                                    <span class="text-danger fw-bold">{{ $tool->discrepancy_qty }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                                <small>{{ $tool->unit }}</small>
                            </div>
                        </div>

                        @if($tool->discrepancy_value != 0)
                        <div class="alert alert-{{ $tool->discrepancy_value > 0 ? 'success' : 'danger' }} py-2">
                            <strong>Value Impact:</strong>
                            Rp {{ number_format(abs($tool->discrepancy_value), 0, ',', '.') }}
                            ({{ $tool->discrepancy_value > 0 ? 'Surplus' : 'Shortage' }})
                        </div>
                        @endif

                        <div class="row g-2">
                            <div class="col-12 col-md-6">
                                <small class="text-muted d-block">Verified By</small>
                                <span>{{ $tool->verifiedByUser->name ?? '-' }}</span>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i> No stock opname has been performed yet.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4 order-1 order-md-2">
            <!-- Actions Card -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">Actions</h6>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route($routePrefix.'.tools.edit', $tool) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Edit Tool
                    </a>
                    <a href="{{ route($routePrefix.'.adjustments.create') }}?tool_id={{ $tool->id }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-sliders-h"></i> Adjust Stock
                    </a>
                    <a href="{{ route($routePrefix.'.purchase-orders.create') }}?tool_id={{ $tool->id }}" class="btn btn-success btn-sm">
                        <i class="fas fa-shopping-cart"></i> Create Purchase Order
                    </a>
                    <a href="{{ route($routePrefix.'.opname.executions.create') }}?tool_id={{ $tool->id }}" class="btn btn-info btn-sm">
                        <i class="fas fa-clipboard-check"></i> Record Opname
                    </a>
                    <hr class="my-1">
                    <a href="{{ route($routePrefix.'.tools.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    <form action="{{ route($routePrefix.'.tools.destroy', $tool) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this tool?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm w-100">
                            <i class="fas fa-trash"></i> Delete Tool
                        </button>
                    </form>
                </div>
            </div>

            <!-- Stock Alert Card -->
            @if($tool->quantity <= $tool->minimum_stock)
            <div class="card mb-3 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-exclamation-triangle"></i> Stock Alert</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1">This item is {{ $tool->quantity <= 0 ? 'out of stock' : 'below minimum stock level' }}.</p>
                    <div class="row g-1 mb-2">
                        <div class="col-6"><small class="text-muted">Current:</small> <strong>{{ $tool->quantity }} {{ $tool->unit }}</strong></div>
                        <div class="col-6"><small class="text-muted">Minimum:</small> <strong>{{ $tool->minimum_stock }} {{ $tool->unit }}</strong></div>
                        <div class="col-12"><small class="text-muted">Suggested Order:</small> <strong>{{ max($tool->minimum_stock * 2 - $tool->quantity, 0) }} {{ $tool->unit }}</strong></div>
                    </div>
                    <a href="{{ route($routePrefix.'.purchase-orders.create') }}?tool_id={{ $tool->id }}" class="btn btn-warning btn-sm w-100">
                        <i class="fas fa-shopping-cart"></i> Order Now
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

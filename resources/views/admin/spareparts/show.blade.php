@extends('layouts.admin')

@section('page-title', 'Sparepart Details')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h5>Sparepart Details</h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.spareparts.index') }}">Spareparts</a></li>
                <li class="breadcrumb-item active">{{ $sparepart->sparepart_name }}</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-12 col-md-8 order-2 order-md-1">
            <!-- Basic Information -->
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Basic Information</h6>
                    @if($sparepart->quantity <= 0)
                        <span class="badge bg-danger">Out of Stock</span>
                    @elseif($sparepart->quantity <= $sparepart->minimum_stock)
                        <span class="badge bg-warning">Low Stock</span>
                    @else
                        <span class="badge bg-success">Available</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <small class="text-muted d-block">Material Code</small>
                            <span class="fw-bold text-info fs-6">{{ $sparepart->material_code ?? '-' }}</span>
                        </div>
                        <div class="col-12 col-md-6">
                            <small class="text-muted d-block">Sparepart Name</small>
                            <span class="fw-bold fs-6">{{ $sparepart->sparepart_name }}</span>
                        </div>
                        <div class="col-6 col-md-4">
                            <small class="text-muted d-block">Equipment Type</small>
                            <span class="badge bg-secondary">{{ $sparepart->equipment_type ?? '-' }}</span>
                        </div>
                        <div class="col-6 col-md-4">
                            <small class="text-muted d-block">Brand</small>
                            <span>{{ $sparepart->brand ?? '-' }}</span>
                        </div>
                        <div class="col-6 col-md-4">
                            <small class="text-muted d-block">Model</small>
                            <span>{{ $sparepart->model ?? '-' }}</span>
                        </div>
                    </div>

                    <hr class="my-2">

                    <div class="row g-2 mb-2">
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Current Stock</small>
                            <span class="fw-bold fs-6
                                @if($sparepart->quantity <= 0) text-danger
                                @elseif($sparepart->quantity <= $sparepart->minimum_stock) text-warning
                                @else text-success @endif">
                                {{ $sparepart->quantity }} {{ $sparepart->unit }}
                            </span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Minimum Stock</small>
                            <span class="fw-bold">{{ $sparepart->minimum_stock }} {{ $sparepart->unit }}</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Unit Price</small>
                            <span class="fw-bold text-primary" style="font-size:13px;">Rp {{ number_format($sparepart->parts_price, 0, ',', '.') }}</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Total Value</small>
                            <span class="fw-bold text-success" style="font-size:13px;">Rp {{ number_format($sparepart->quantity * $sparepart->parts_price, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <hr class="my-2">

                    <div class="row g-2">
                        <div class="col-6 col-md-6">
                            <small class="text-muted d-block">Vulnerability Level</small>
                            @if($sparepart->vulnerability === 'critical')
                                <span class="badge bg-danger">Critical</span>
                            @elseif($sparepart->vulnerability === 'high')
                                <span class="badge bg-warning">High</span>
                            @elseif($sparepart->vulnerability === 'medium')
                                <span class="badge bg-info">Medium</span>
                            @elseif($sparepart->vulnerability === 'low')
                                <span class="badge bg-success">Low</span>
                            @else
                                <span class="badge bg-secondary">Not Specified</span>
                            @endif
                        </div>
                        <div class="col-6 col-md-6">
                            <small class="text-muted d-block">Storage Location</small>
                            <span>{{ $sparepart->location ?? '-' }}</span>
                        </div>
                        <div class="col-6 col-md-6">
                            <small class="text-muted d-block">Added By</small>
                            <span>{{ $sparepart->addedByUser->name ?? '-' }}</span>
                        </div>
                        <div class="col-6 col-md-6">
                            <small class="text-muted d-block">Added At</small>
                            <span>{{ $sparepart->created_at->format('d M Y H:i') }}</span>
                        </div>
                        @if($sparepart->path)
                        <div class="col-12">
                            <small class="text-muted d-block">Attachment</small>
                            <a href="{{ asset('storage/' . $sparepart->path) }}" target="_blank" class="btn btn-sm btn-outline-info mt-1">
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
                    @if($sparepart->last_opname_at)
                        <div class="row g-2 mb-2">
                            <div class="col-12 col-md-6">
                                <small class="text-muted d-block">Last Opname Date</small>
                                <span>{{ $sparepart->last_opname_at->format('d M Y H:i') }}</span>
                                <small class="text-muted">({{ $sparepart->last_opname_at->diffForHumans() }})</small>
                            </div>
                            <div class="col-12 col-md-6">
                                <small class="text-muted d-block">Opname Status</small>
                                <span class="badge bg-{{ $sparepart->opname_status === 'completed' ? 'success' : 'warning' }}">
                                    {{ ucfirst($sparepart->opname_status ?? 'pending') }}
                                </span>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">System Qty</small>
                                <span>{{ $sparepart->quantity }} {{ $sparepart->unit }}</span>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Physical Qty</small>
                                <span>{{ $sparepart->physical_quantity ?? '-' }} {{ $sparepart->unit }}</span>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Discrepancy</small>
                                @if($sparepart->discrepancy_qty > 0)
                                    <span class="text-success fw-bold">+{{ $sparepart->discrepancy_qty }}</span>
                                @elseif($sparepart->discrepancy_qty < 0)
                                    <span class="text-danger fw-bold">{{ $sparepart->discrepancy_qty }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                                <small>{{ $sparepart->unit }}</small>
                            </div>
                            <div class="col-12 col-md-6">
                                <small class="text-muted d-block">Verified By</small>
                                <span>{{ $sparepart->verifiedByUser->name ?? '-' }}</span>
                            </div>
                        </div>
                        @if($sparepart->discrepancy_value != 0)
                        <div class="alert alert-{{ $sparepart->discrepancy_value > 0 ? 'success' : 'danger' }} py-2 mb-0">
                            <strong>Value Impact:</strong>
                            Rp {{ number_format(abs($sparepart->discrepancy_value), 0, ',', '.') }}
                            ({{ $sparepart->discrepancy_value > 0 ? 'Surplus' : 'Shortage' }})
                        </div>
                        @endif
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
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route($routePrefix.'.spareparts.edit', $sparepart) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit Sparepart
                        </a>
                        <a href="{{ route($routePrefix.'.spareparts.adjustments.create') }}?sparepart_id={{ $sparepart->id }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-sliders-h"></i> Adjust Stock
                        </a>
                        <a href="{{ route($routePrefix.'.spareparts.purchase-orders.create') }}?sparepart_id={{ $sparepart->id }}" class="btn btn-success btn-sm">
                            <i class="fas fa-shopping-cart"></i> Create Purchase Order
                        </a>
                        <a href="{{ route($routePrefix.'.spareparts.opname.executions.create') }}?sparepart_id={{ $sparepart->id }}" class="btn btn-info btn-sm">
                            <i class="fas fa-clipboard-check"></i> Record Opname
                        </a>
                        <a href="{{ route($routePrefix.'.spareparts.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                        <form action="{{ route($routePrefix.'.spareparts.destroy', $sparepart) }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this sparepart?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm w-100">
                                <i class="fas fa-trash"></i> Delete Sparepart
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Stock Alert Card -->
            @if($sparepart->quantity <= $sparepart->minimum_stock)
            <div class="card mb-3 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-exclamation-triangle"></i> Stock Alert</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1">This item is {{ $sparepart->quantity <= 0 ? 'out of stock' : 'below minimum stock level' }}.</p>
                    <div class="row g-1 mb-2">
                        <div class="col-6"><small class="text-muted">Current:</small> <strong>{{ $sparepart->quantity }} {{ $sparepart->unit }}</strong></div>
                        <div class="col-6"><small class="text-muted">Minimum:</small> <strong>{{ $sparepart->minimum_stock }} {{ $sparepart->unit }}</strong></div>
                        <div class="col-12"><small class="text-muted">Suggested Order:</small> <strong>{{ max($sparepart->minimum_stock * 2 - $sparepart->quantity, 0) }} {{ $sparepart->unit }}</strong></div>
                    </div>
                    <a href="{{ route($routePrefix.'.spareparts.purchase-orders.create') }}?sparepart_id={{ $sparepart->id }}" class="btn btn-warning btn-sm w-100">
                        <i class="fas fa-shopping-cart"></i> Order Now
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Usage & Adjustment History --}}
    <div class="card mt-4">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="historyTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="usage-tab" data-bs-toggle="tab" data-bs-target="#usagePane" type="button">
                        <i class="fas fa-history me-1"></i> Usage History
                        <span class="badge bg-secondary ms-1">{{ $usageHistory->count() }}</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="adj-tab" data-bs-toggle="tab" data-bs-target="#adjPane" type="button">
                        <i class="fas fa-sliders-h me-1"></i> Adjustment History
                        <span class="badge bg-secondary ms-1">{{ $adjustmentHistory->count() }}</span>
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-0">
            <div class="tab-content" id="historyTabContent">

                {{-- Usage Tab --}}
                <div class="tab-pane fade show active" id="usagePane" role="tabpanel">
                    @if($usageHistory->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Used By</th>
                                        <th class="text-center">Qty</th>
                                        <th class="d-none d-md-table-cell">Source</th>
                                        <th class="d-none d-md-table-cell">Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($usageHistory as $usage)
                                    <tr>
                                        <td>
                                            <small>{{ $usage->used_at?->format('d M Y') ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $usage->usedByUser?->name ?? '-' }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger">-{{ $usage->quantity_used }}</span>
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            @if($usage->ticket_number)
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-wrench me-1"></i>{{ $usage->ticket_number }}
                                                </span>
                                            @elseif($usage->pm_report_id)
                                                <span class="badge bg-primary">
                                                    <i class="fas fa-calendar-check me-1"></i>PM Report
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            <small class="text-muted">{{ $usage->notes ?? '-' }}</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            No usage history found.
                        </div>
                    @endif
                </div>

                {{-- Adjustment Tab --}}
                <div class="tab-pane fade" id="adjPane" role="tabpanel">
                    @if($adjustmentHistory->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th class="d-none d-md-table-cell">Code</th>
                                        <th>Type</th>
                                        <th class="text-center">Qty Change</th>
                                        <th class="d-none d-md-table-cell">Adjusted By</th>
                                        <th class="d-none d-md-table-cell">Status</th>
                                        <th class="d-none d-lg-table-cell">Reason</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($adjustmentHistory as $adj)
                                    @php
                                        $isAdd = $adj->adjustment_type === 'add' || ($adj->adjustment_type === 'correction' && $adj->adjustment_qty > 0);
                                    @endphp
                                    <tr>
                                        <td>
                                            <small>{{ $adj->created_at->format('d M Y') }}</small>
                                            <div class="d-md-none">
                                                <small class="text-muted">{{ $adj->adjustedByUser?->name ?? '-' }}</small>
                                            </div>
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            <small class="text-muted">{{ $adj->adjustment_code ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $isAdd ? 'success' : 'danger' }}">
                                                {{ ucfirst(str_replace('_', ' ', $adj->adjustment_type)) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="fw-bold {{ $isAdd ? 'text-success' : 'text-danger' }}">
                                                {{ $isAdd ? '+' : '-' }}{{ abs($adj->adjustment_qty) }}
                                            </span>
                                            <br><small class="text-muted">{{ $adj->quantity_before }} → {{ $adj->quantity_after }}</small>
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            <small>{{ $adj->adjustedByUser?->name ?? '-' }}</small>
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            @php
                                                $statusColor = match($adj->status ?? '') {
                                                    'approved' => 'success',
                                                    'pending'  => 'warning',
                                                    'rejected' => 'danger',
                                                    default    => 'secondary',
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $statusColor }}">{{ ucfirst($adj->status ?? '-') }}</span>
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            <small class="text-muted">{{ $adj->reason ?? '-' }}</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            No adjustment history found.
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

</div>
@endsection

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
        <div class="col-md-8">
            <!-- Basic Information -->
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Basic Information</h5>
                    <div>
                        @if($sparepart->quantity <= 0)
                            <span class="badge bg-danger">Out of Stock</span>
                        @elseif($sparepart->quantity <= $sparepart->minimum_stock)
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
                            <h4 class="text-info">{{ $sparepart->material_code ?? '-' }}</h4>
                        </div>
                        <div class="col-md-6">
                            <strong>Sparepart Name:</strong><br>
                            <h4>{{ $sparepart->sparepart_name }}</h4>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Equipment Type:</strong><br>
                            <span class="badge bg-secondary">{{ $sparepart->equipment_type ?? '-' }}</span>
                        </div>
                        <div class="col-md-4">
                            <strong>Brand:</strong><br>
                            {{ $sparepart->brand ?? '-' }}
                        </div>
                        <div class="col-md-4">
                            <strong>Model:</strong><br>
                            {{ $sparepart->model ?? '-' }}
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Current Stock:</strong><br>
                            <h5>
                                @if($sparepart->quantity <= 0)
                                    <span class="text-danger">{{ $sparepart->quantity }}</span>
                                @elseif($sparepart->quantity <= $sparepart->minimum_stock)
                                    <span class="text-warning">{{ $sparepart->quantity }}</span>
                                @else
                                    <span class="text-success">{{ $sparepart->quantity }}</span>
                                @endif
                                {{ $sparepart->unit }}
                            </h5>
                        </div>
                        <div class="col-md-3">
                            <strong>Minimum Stock:</strong><br>
                            <h5>{{ $sparepart->minimum_stock }} {{ $sparepart->unit }}</h5>
                        </div>
                        <div class="col-md-3">
                            <strong>Unit Price:</strong><br>
                            <h5 class="text-primary">Rp {{ number_format($sparepart->parts_price, 0, ',', '.') }}</h5>
                        </div>
                        <div class="col-md-3">
                            <strong>Total Value:</strong><br>
                            <h5 class="text-success">Rp {{ number_format($sparepart->quantity * $sparepart->parts_price, 0, ',', '.') }}</h5>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Vulnerability Level:</strong><br>
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
                        <div class="col-md-6">
                            <strong>Storage Location:</strong><br>
                            {{ $sparepart->location ?? '-' }}
                        </div>
                    </div>

                    @if($sparepart->path)
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <strong>Attachment:</strong><br>
                            <a href="{{ asset('storage/' . $sparepart->path) }}" target="_blank" class="btn btn-sm btn-info mt-2">
                                <i class="fas fa-file"></i> View Attachment
                            </a>
                        </div>
                    </div>
                    @endif

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <strong>Added By:</strong><br>
                            {{ $sparepart->addedByUser->name ?? '-' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Added At:</strong><br>
                            {{ $sparepart->created_at->format('d M Y H:i') }}
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
                    @if($sparepart->last_opname_at)
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Last Opname Date:</strong><br>
                                {{ $sparepart->last_opname_at->format('d M Y H:i') }}
                                <span class="text-muted">({{ $sparepart->last_opname_at->diffForHumans() }})</span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Opname Status:</strong><br>
                                <span class="badge bg-{{ $sparepart->opname_status === 'completed' ? 'success' : 'warning' }}">
                                    {{ ucfirst($sparepart->opname_status ?? 'pending') }}
                                </span>
                                </p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <p><strong>System Quantity:</strong><br>
                                {{ $sparepart->quantity }} {{ $sparepart->unit }}
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Physical Quantity:</strong><br>
                                {{ $sparepart->physical_quantity ?? '-' }} {{ $sparepart->unit }}
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Discrepancy:</strong><br>
                                @if($sparepart->discrepancy_qty > 0)
                                    <span class="text-success">+{{ $sparepart->discrepancy_qty }} {{ $sparepart->unit }}</span>
                                @elseif($sparepart->discrepancy_qty < 0)
                                    <span class="text-danger">{{ $sparepart->discrepancy_qty }} {{ $sparepart->unit }}</span>
                                @else
                                    <span class="text-muted">0 {{ $sparepart->unit }}</span>
                                @endif
                                </p>
                            </div>
                        </div>

                        @if($sparepart->discrepancy_value != 0)
                        <div class="alert alert-{{ $sparepart->discrepancy_value > 0 ? 'success' : 'danger' }}">
                            <strong>Value Impact:</strong>
                            Rp {{ number_format(abs($sparepart->discrepancy_value), 0, ',', '.') }}
                            ({{ $sparepart->discrepancy_value > 0 ? 'Surplus' : 'Shortage' }})
                        </div>
                        @endif

                        <p><strong>Verified By:</strong><br>
                        {{ $sparepart->verifiedByUser->name ?? '-' }}
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
                    <a href="{{ route($routePrefix.'.spareparts.edit', $sparepart) }}" class="btn btn-warning w-100 mb-2">
            <i class="fas fa-edit"></i><span class="btn-text"> Edit Sparepart</span>
                    </a>
                    <a href="{{ route($routePrefix.'.spareparts.adjustments.create') }}?sparepart_id={{ $sparepart->id }}" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-sliders-h"></i> Adjust Stock
                    </a>
                    <a href="{{ route($routePrefix.'.spareparts.purchase-orders.create') }}?sparepart_id={{ $sparepart->id }}" class="btn btn-success w-100 mb-2">
                        <i class="fas fa-shopping-cart"></i> Create Purchase Order
                    </a>
                    <a href="{{ route($routePrefix.'.spareparts.opname.executions.create') }}?sparepart_id={{ $sparepart->id }}" class="btn btn-info w-100 mb-2">
                        <i class="fas fa-clipboard-check"></i> Record Opname
                    </a>

                    <hr>

                    <a href="{{ route($routePrefix.'.spareparts.index') }}" class="btn btn-secondary w-100 mb-2">
            <i class="fas fa-arrow-left"></i><span class="btn-text"> Back to List</span>
                    </a>

                    <form action="{{ route($routePrefix.'.spareparts.destroy', $sparepart) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to delete this sparepart?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
            <i class="fas fa-trash"></i><span class="btn-text"> Delete Sparepart</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Stock Alert Card -->
            @if($sparepart->quantity <= $sparepart->minimum_stock)
            <div class="card mb-3 border-warning">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Stock Alert</h5>
                </div>
                <div class="card-body">
                    <p>This item is {{ $sparepart->quantity <= 0 ? 'out of stock' : 'below minimum stock level' }}.</p>
                    <p><strong>Current:</strong> {{ $sparepart->quantity }} {{ $sparepart->unit }}</p>
                    <p><strong>Minimum:</strong> {{ $sparepart->minimum_stock }} {{ $sparepart->unit }}</p>
                    <p><strong>Suggested Order:</strong> {{ max($sparepart->minimum_stock * 2 - $sparepart->quantity, 0) }} {{ $sparepart->unit }}</p>
                    <a href="{{ route($routePrefix.'.spareparts.purchase-orders.create') }}?sparepart_id={{ $sparepart->id }}" class="btn btn-warning w-100">
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

@extends('layouts.admin')

@section('page-title', 'Stock Adjustment Details')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Stock Adjustment Details</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.adjustments.index') }}">Stock Adjustments</a></li>
                <li class="breadcrumb-item active">{{ $adjustment->adjustment_code }}</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Adjustment Information --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Adjustment Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Adjustment Code:</strong><br>
                            <span class="fs-5">{{ $adjustment->adjustment_code }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong><br>
                            @if($adjustment->status === 'approved')
                                <span class="badge bg-success fs-6">Approved</span>
                            @elseif($adjustment->status === 'pending')
                                <span class="badge bg-warning fs-6">Pending Approval</span>
                            @else
                                <span class="badge bg-danger fs-6">Rejected</span>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Item Type:</strong><br>
                            @if($adjustment->item_type === 'sparepart')
                                <span class="badge bg-primary">Sparepart</span>
                            @else
                                <span class="badge bg-info">Tool</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Item Name:</strong><br>
                            {{ $item->sparepart_name ?? 'N/A' }}<br>
                            <small class="text-muted">{{ $item->sparepart_id ?? 'N/A' }}</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Quantity Before:</strong><br>
                            <span class="fs-5">{{ $adjustment->quantity_before }}</span>
                        </div>
                        <div class="col-md-4">
                            <strong>Adjustment:</strong><br>
                            @if($adjustment->adjustment_qty > 0)
                                <span class="fs-5 text-success">+{{ $adjustment->adjustment_qty }}</span>
                            @else
                                <span class="fs-5 text-danger">{{ $adjustment->adjustment_qty }}</span>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <strong>Quantity After:</strong><br>
                            <span class="fs-5 text-primary">{{ $adjustment->quantity_after }}</span>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Adjustment Type:</strong><br>
                            @if($adjustment->adjustment_type === 'add')
                                <span class="badge bg-success">Add Stock</span>
                            @elseif($adjustment->adjustment_type === 'subtract')
                                <span class="badge bg-danger">Subtract Stock</span>
                            @else
                                <span class="badge bg-warning">Correction</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Reason Category:</strong><br>
                            <span class="badge bg-secondary">{{ ucfirst($adjustment->reason_category) }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Detailed Reason:</strong><br>
                        <div class="p-3 bg-light rounded">
                            {{ $adjustment->reason }}
                        </div>
                    </div>

                    @if($adjustment->value_impact)
                    <div class="mb-3">
                        <strong>Value Impact:</strong><br>
                        <span class="fs-5">Rp {{ number_format(abs($adjustment->value_impact), 0, ',', '.') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Approval Information --}}
            @if($adjustment->status !== 'pending')
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-check-circle"></i> Approval Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Approved/Rejected By:</strong><br>
                            {{ $adjustment->approvedByUser->name ?? 'N/A' }}
                        </div>
                        <div class="col-md-6">
                            <strong>Date:</strong><br>
                            {{ $adjustment->approved_at ? $adjustment->approved_at->format('d M Y H:i') : 'N/A' }}
                        </div>
                    </div>

                    @if($adjustment->approval_notes)
                    <div class="mb-3">
                        <strong>Approval Notes:</strong><br>
                        <div class="p-3 bg-light rounded">
                            {{ $adjustment->approval_notes }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            {{-- Actions --}}
            @if($adjustment->status === 'pending')
            <div class="card mb-3 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-tasks"></i> Actions Required</h5>
                </div>
                <div class="card-body">
                    <p>This adjustment is pending approval.</p>

                    <form action="{{ route('admin.adjustments.approve', $adjustment) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-check"></i> Approve Adjustment
                        </button>
                    </form>

                    <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="fas fa-times"></i> Reject Adjustment
                    </button>
                </div>
            </div>
            @endif

            {{-- Adjustment History --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Adjustment History</h5>
                </div>
                <div class="card-body">
                    <div class="small">
                        <strong>Created By:</strong><br>
                        {{ $adjustment->adjustedByUser->name ?? 'N/A' }}<br>
                        <small class="text-muted">{{ $adjustment->created_at->format('d M Y H:i') }}</small>
                    </div>

                    @if($adjustment->status === 'approved')
                    <hr>
                    <div class="small">
                        <strong>Approved By:</strong><br>
                        {{ $adjustment->approvedByUser->name ?? 'N/A' }}<br>
                        <small class="text-muted">{{ $adjustment->approved_at ? $adjustment->approved_at->format('d M Y H:i') : 'N/A' }}</small>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Related Information --}}
            @if($adjustment->related_opname_execution_id)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-link"></i> Related Opname</h5>
                </div>
                <div class="card-body">
                    <p class="small">
                        This adjustment is related to a stock opname execution.
                    </p>
                    <a href="#" class="btn btn-sm btn-info">
                        <i class="fas fa-eye"></i> View Opname
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.adjustments.reject', $adjustment) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Reject Adjustment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <strong>Warning:</strong> This will reject the adjustment and it will not be applied to stock.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="approval_notes" class="form-control" rows="4" required
                            placeholder="Please explain why this adjustment is being rejected..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Reject Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

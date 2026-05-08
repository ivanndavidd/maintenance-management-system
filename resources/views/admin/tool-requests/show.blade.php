@extends('layouts.admin')

@section('page-title', 'Tool Request Detail')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> {{ $toolRequest->request_number }}</h5>
            <small class="text-muted">Tool Usage Request Detail</small>
        </div>
        <a href="{{ route($routePrefix.'.tool-requests.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i><span class="btn-text"> Back</span>
        </a>
    </div>

    <div class="row">
        {{-- Detail --}}
        <div class="col-12 col-md-8 order-2 order-md-1">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Request Information</h6>
                    <span class="badge bg-{{ $toolRequest->getStatusBadgeClass() }}">{{ $toolRequest->getStatusLabel() }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <small class="text-muted d-block">Tool</small>
                            <span class="fw-bold fs-6">{{ $toolRequest->tool->sparepart_name ?? '-' }}</span>
                            @if($toolRequest->tool->material_code)
                                <small class="text-muted ms-2">{{ $toolRequest->tool->material_code }}</small>
                            @endif
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Qty Requested</small>
                            <span class="fw-bold text-primary">{{ $toolRequest->quantity_requested }} {{ $toolRequest->tool->unit ?? '' }}</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Stock Available</small>
                            <span class="fw-bold {{ ($toolRequest->tool->quantity ?? 0) >= $toolRequest->quantity_requested ? 'text-success' : 'text-danger' }}">
                                {{ $toolRequest->tool->quantity ?? '-' }} {{ $toolRequest->tool->unit ?? '' }}
                            </span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Usage Date</small>
                            <span>{{ $toolRequest->usage_date->format('d M Y') }}</span>
                        </div>
                        <div class="col-6 col-md-3">
                            <small class="text-muted d-block">Return Date</small>
                            <span>{{ $toolRequest->return_date ? $toolRequest->return_date->format('d M Y') : '-' }}</span>
                        </div>
                        <div class="col-12 col-md-6">
                            <small class="text-muted d-block">Requested By</small>
                            <span>{{ $toolRequest->requester->name ?? '-' }}</span>
                        </div>
                        <div class="col-12 col-md-6">
                            <small class="text-muted d-block">Submitted At</small>
                            <span>{{ $toolRequest->created_at->format('d M Y H:i') }}</span>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Purpose</small>
                            <span>{{ $toolRequest->purpose }}</span>
                        </div>
                        @if($toolRequest->location)
                        <div class="col-12">
                            <small class="text-muted d-block">Work Location</small>
                            <span>{{ $toolRequest->location }}</span>
                        </div>
                        @endif
                        @if($toolRequest->notes)
                        <div class="col-12">
                            <small class="text-muted d-block">Notes</small>
                            <span>{{ $toolRequest->notes }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="alert alert-primary py-2" style="font-size:13px;">
                <i class="fas fa-tools me-1"></i>
                <strong>Borrowing</strong> — stock deducted when marked In Use. Restored when returned.
            </div>

            {{-- Overdue warning --}}
            @if(in_array($toolRequest->status, ['approved','in_use']) && $toolRequest->return_date && $toolRequest->return_date->isPast())
            <div class="alert alert-danger py-2" style="font-size:13px;">
                <i class="fas fa-exclamation-triangle me-1"></i>
                <strong>Overdue!</strong> Expected return was {{ $toolRequest->return_date->format('d M Y') }}
                ({{ $toolRequest->return_date->diffForHumans() }}). Tool has not been returned yet.
            </div>
            @endif

            {{-- Review result --}}
            @if($toolRequest->reviewed_at)
            <div class="card mb-3 border-{{ in_array($toolRequest->status, ['approved','in_use','returned']) ? 'success' : 'danger' }}">
                <div class="card-header bg-{{ in_array($toolRequest->status, ['approved','in_use','returned']) ? 'success' : 'danger' }} text-white">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-{{ in_array($toolRequest->status, ['approved','in_use','returned']) ? 'check-circle' : 'times-circle' }}"></i>
                        Review Result
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <small class="text-muted d-block">Reviewed By</small>
                            <span>{{ $toolRequest->reviewer->name ?? '-' }}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Reviewed At</small>
                            <span>{{ $toolRequest->reviewed_at->format('d M Y H:i') }}</span>
                        </div>
                        @if($toolRequest->review_notes)
                        <div class="col-12">
                            <small class="text-muted d-block">Notes</small>
                            <span>{{ $toolRequest->review_notes }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Return/Used info --}}
            @if($toolRequest->returned_at)
            <div class="card mb-3 border-secondary">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-undo"></i> Return Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <small class="text-muted d-block">Returned At</small>
                            <span>{{ $toolRequest->returned_at->format('d M Y H:i') }}</span>
                        </div>
                        @if($toolRequest->return_notes)
                        <div class="col-12">
                            <small class="text-muted d-block">Notes</small>
                            <span>{{ $toolRequest->return_notes }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Actions sidebar --}}
        <div class="col-12 col-md-4 order-1 order-md-2">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">Actions</h6>
                </div>
                <div class="card-body d-grid gap-2">

                    @if($toolRequest->status === 'pending')
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal">
                        <i class="fas fa-check"></i> Approve
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="fas fa-times"></i> Reject
                    </button>
                    @endif

                    @if($toolRequest->status === 'approved')
                    <form action="{{ route($routePrefix.'.tool-requests.in-use', $toolRequest) }}" method="POST"
                          onsubmit="return confirm('Mark this tool as in use? Stock will be deducted.')">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-tools"></i> Mark as In Use
                        </button>
                    </form>
                    @endif

                    @if(in_array($toolRequest->status, ['approved', 'in_use']))
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#adminReturnModal">
                        <i class="fas fa-undo"></i> Mark as Returned
                    </button>
                    @endif

                    <a href="{{ route($routePrefix.'.tool-requests.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

            {{-- Stock info --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">Current Stock</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <small class="text-muted d-block">Available</small>
                            <span class="fw-bold {{ ($toolRequest->tool->quantity ?? 0) >= $toolRequest->quantity_requested ? 'text-success' : 'text-danger' }}">
                                {{ $toolRequest->tool->quantity ?? '-' }} {{ $toolRequest->tool->unit ?? '' }}
                            </span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Requested</small>
                            <span class="fw-bold text-primary">{{ $toolRequest->quantity_requested }} {{ $toolRequest->tool->unit ?? '' }}</span>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Location</small>
                            <span>{{ $toolRequest->tool->location ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Approve Modal --}}
@if($toolRequest->status === 'pending')
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route($routePrefix.'.tool-requests.approve', $toolRequest) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title fw-bold"><i class="fas fa-check-circle text-success"></i> Approve Request</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Approve <strong>{{ $toolRequest->request_number }}</strong> — {{ $toolRequest->tool->sparepart_name }} ({{ $toolRequest->quantity_requested }} {{ $toolRequest->tool->unit }})?</p>
                    <div class="mb-3">
                        <label class="form-label">Notes <small class="text-muted">(optional)</small></label>
                        <textarea name="review_notes" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route($routePrefix.'.tool-requests.reject', $toolRequest) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title fw-bold"><i class="fas fa-times-circle text-danger"></i> Reject Request</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Reject <strong>{{ $toolRequest->request_number }}</strong>?</p>
                    <div class="mb-3">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea name="review_notes" class="form-control form-control-sm" rows="3"
                                  placeholder="Reason for rejection..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Admin Mark Returned Modal --}}
@if(in_array($toolRequest->status, ['approved', 'in_use']))
<div class="modal fade" id="adminReturnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route($routePrefix.'.tool-requests.mark-returned', $toolRequest) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title fw-bold"><i class="fas fa-undo text-success"></i> Mark Tool as Returned</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Confirm that <strong>{{ $toolRequest->tool->sparepart_name }}</strong>
                    ({{ $toolRequest->quantity_requested }} {{ $toolRequest->tool->unit }}) has been returned by
                    <strong>{{ $toolRequest->requester->name }}</strong>.</p>
                    @if($toolRequest->status === 'in_use')
                    <div class="alert alert-info py-2" style="font-size:13px;">
                        <i class="fas fa-info-circle me-1"></i>
                        Stock will be restored by <strong>{{ $toolRequest->quantity_requested }} {{ $toolRequest->tool->unit }}</strong>.
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Notes <small class="text-muted">(optional)</small></label>
                        <textarea name="return_notes" class="form-control form-control-sm" rows="2"
                                  placeholder="Tool condition, remarks, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Confirm Returned</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@extends('layouts.admin')

@section('page-title', 'Tool Usage Requests')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0"><i class="fas fa-wrench"></i> Tool Usage Requests</h5>
            <small class="text-muted">Review and approve tool borrowing / consumption requests</small>
        </div>
        @if(auth()->user()->hasRole('supervisor_maintenance'))
        <a href="{{ route($routePrefix.'.tool-requests.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> New Request
        </a>
        @endif
    </div>

    {{-- Stats --}}
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <div class="card text-center py-2 border-warning">
                <div class="fw-bold fs-5 text-warning">{{ $stats['pending'] }}</div>
                <small class="text-muted">Pending</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center py-2 border-success">
                <div class="fw-bold fs-5 text-success">{{ $stats['approved'] }}</div>
                <small class="text-muted">Approved</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center py-2 border-primary">
                <div class="fw-bold fs-5 text-primary">{{ $stats['in_use'] }}</div>
                <small class="text-muted">In Use</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center py-2">
                <div class="fw-bold fs-5">{{ $stats['total'] }}</div>
                <small class="text-muted">Total</small>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" action="{{ route($routePrefix.'.tool-requests.index') }}">
                <div class="row g-2">
                    <div class="col-12 col-md-5">
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Search request no, tool, requester..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-7 col-md-4">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="pending"   {{ request('status') == 'pending'   ? 'selected' : '' }}>Pending</option>
                            <option value="approved"  {{ request('status') == 'approved'  ? 'selected' : '' }}>Approved</option>
                            <option value="rejected"  {{ request('status') == 'rejected'  ? 'selected' : '' }}>Rejected</option>
                            <option value="in_use"    {{ request('status') == 'in_use'    ? 'selected' : '' }}>In Use</option>
                            <option value="used"      {{ request('status') == 'used'      ? 'selected' : '' }}>Used</option>
                            <option value="returned"  {{ request('status') == 'returned'  ? 'selected' : '' }}>Returned</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-5 col-md-3">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- List --}}
    <div class="card">
        <div class="card-body p-0">
            @forelse($requests as $req)
            <div class="p-3 border-bottom {{ $req->status === 'pending' ? 'bg-warning bg-opacity-10' : '' }}">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <span class="fw-bold text-primary" style="font-size:13px;">{{ $req->request_number }}</span>
                            <span class="badge bg-{{ $req->getStatusBadgeClass() }}">{{ $req->getStatusLabel() }}</span>
                        </div>
                        <div class="fw-bold">{{ $req->tool->sparepart_name ?? '-' }}</div>
                        <div class="text-muted" style="font-size:12px;">
                            <i class="fas fa-user me-1"></i>{{ $req->requester->name ?? '-' }}
                            &nbsp;·&nbsp;
                            <i class="fas fa-boxes me-1"></i>{{ $req->quantity_requested }} {{ $req->tool->unit ?? '' }}
                            &nbsp;·&nbsp;
                            <i class="fas fa-calendar me-1"></i>{{ $req->usage_date->format('d M Y') }}
                        </div>
                        <div class="text-muted" style="font-size:12px; margin-top:2px;">
                            <i class="fas fa-bullseye me-1"></i>{{ Str::limit($req->purpose, 70) }}
                        </div>
                        @if(in_array($req->status, ['approved','in_use']) && $req->return_date && $req->return_date->isPast())
                        <div class="mt-1">
                            <span class="badge bg-danger" style="font-size:10px;">
                                <i class="fas fa-exclamation-triangle me-1"></i>Overdue — was due {{ $req->return_date->format('d M Y') }}
                            </span>
                        </div>
                        @elseif(in_array($req->status, ['approved','in_use']))
                        <div class="mt-1">
                            <span class="badge bg-warning text-dark" style="font-size:10px;">
                                <i class="fas fa-clock me-1"></i>Not yet returned
                                @if($req->return_date) · due {{ $req->return_date->format('d M Y') }} @endif
                            </span>
                        </div>
                        @endif
                    </div>
                    <div class="ms-2 d-flex flex-column gap-1">
                        <a href="{{ route($routePrefix.'.tool-requests.show', $req) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($req->status === 'pending')
                        <button type="button" class="btn btn-sm btn-success"
                                onclick="quickApprove({{ $req->id }}, '{{ $req->request_number }}')">
                            <i class="fas fa-check"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger"
                                onclick="quickReject({{ $req->id }}, '{{ $req->request_number }}')">
                            <i class="fas fa-times"></i>
                        </button>
                        @endif
                        @if($req->status === 'approved')
                        <form action="{{ route($routePrefix.'.tool-requests.in-use', $req) }}" method="POST"
                              onsubmit="return confirm('Mark {{ $req->request_number }} as in use? Stock will be deducted.')">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary w-100" title="Mark as In Use">
                                <i class="fas fa-tools"></i>
                            </button>
                        </form>
                        @endif
                        @if(in_array($req->status, ['approved', 'in_use']))
                        <button type="button" class="btn btn-sm btn-success"
                                onclick="quickReturn({{ $req->id }}, '{{ $req->request_number }}', {{ $req->status === 'in_use' ? 'true' : 'false' }})"
                                title="Mark as Returned">
                            <i class="fas fa-undo"></i>
                        </button>
                        @endif
                    </div>
                </div>
                <div class="text-muted mt-1" style="font-size:11px;">
                    Submitted {{ $req->created_at->diffForHumans() }}
                    @if($req->reviewed_at)
                        · Reviewed by {{ $req->reviewer->name ?? '-' }} {{ $req->reviewed_at->diffForHumans() }}
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-5 text-muted">
                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                No requests found.
            </div>
            @endforelse
        </div>
        @if($requests->hasPages())
        <div class="card-footer">
            {{ $requests->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Quick Approve Modal --}}
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="approveForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title fw-bold"><i class="fas fa-check-circle text-success"></i> Approve Request</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Approve request <strong id="approveReqNo"></strong>?</p>
                    <div class="mb-3">
                        <label class="form-label">Notes <small class="text-muted">(optional)</small></label>
                        <textarea name="review_notes" class="form-control form-control-sm" rows="2"
                                  placeholder="Optional approval notes..."></textarea>
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

{{-- Quick Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title fw-bold"><i class="fas fa-times-circle text-danger"></i> Reject Request</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Reject request <strong id="rejectReqNo"></strong>?</p>
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

{{-- Quick Return Modal --}}
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="returnForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title fw-bold"><i class="fas fa-undo text-success"></i> Mark as Returned</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Confirm tool for request <strong id="returnReqNo"></strong> has been returned?</p>
                    <div id="returnStockInfo" class="alert alert-info py-2 d-none" style="font-size:13px;">
                        <i class="fas fa-info-circle me-1"></i> Stock will be restored.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes <small class="text-muted">(optional)</small></label>
                        <textarea name="return_notes" class="form-control form-control-sm" rows="2"
                                  placeholder="Tool condition, remarks..."></textarea>
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
@endsection

@push('scripts')
<script>
function quickApprove(id, reqNo) {
    document.getElementById('approveReqNo').textContent = reqNo;
    document.getElementById('approveForm').action = '{{ url($routePrefix . '/tool-requests') }}/' + id + '/approve';
    new bootstrap.Modal(document.getElementById('approveModal')).show();
}

function quickReject(id, reqNo) {
    document.getElementById('rejectReqNo').textContent = reqNo;
    document.getElementById('rejectForm').action = '{{ url($routePrefix . '/tool-requests') }}/' + id + '/reject';
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function quickReturn(id, reqNo, isInUse) {
    document.getElementById('returnReqNo').textContent = reqNo;
    document.getElementById('returnForm').action = '{{ url($routePrefix . '/tool-requests') }}/' + id + '/mark-returned';
    const info = document.getElementById('returnStockInfo');
    if (isInUse) {
        info.classList.remove('d-none');
    } else {
        info.classList.add('d-none');
    }
    new bootstrap.Modal(document.getElementById('returnModal')).show();
}
</script>
@endpush

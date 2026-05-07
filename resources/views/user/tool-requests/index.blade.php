@extends('layouts.user')

@section('page-title', 'Tool Usage Requests')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0"><i class="fas fa-wrench"></i> Tool Usage Requests</h5>
            <small class="text-muted">Request tools for work usage</small>
        </div>
        <a href="{{ route('user.tool-requests.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i><span class="btn-text"> New Request</span>
        </a>
    </div>

    {{-- Stats --}}
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <div class="card text-center py-2">
                <div class="fw-bold fs-5 text-primary">{{ $stats['total'] }}</div>
                <small class="text-muted">Total</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center py-2">
                <div class="fw-bold fs-5 text-warning">{{ $stats['pending'] }}</div>
                <small class="text-muted">Pending</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center py-2">
                <div class="fw-bold fs-5 text-success">{{ $stats['approved'] }}</div>
                <small class="text-muted">Approved</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center py-2">
                <div class="fw-bold fs-5 text-info">{{ $stats['in_use'] }}</div>
                <small class="text-muted">In Use</small>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('user.tool-requests.index') }}">
                <div class="row g-2">
                    <div class="col-8 col-md-4">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="pending"   {{ request('status') == 'pending'   ? 'selected' : '' }}>Pending</option>
                            <option value="approved"  {{ request('status') == 'approved'  ? 'selected' : '' }}>Approved</option>
                            <option value="rejected"  {{ request('status') == 'rejected'  ? 'selected' : '' }}>Rejected</option>
                            <option value="in_use"    {{ request('status') == 'in_use'    ? 'selected' : '' }}>In Use</option>
                            <option value="returned"  {{ request('status') == 'returned'  ? 'selected' : '' }}>Returned</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-4 col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-filter"></i> Filter
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
            <div class="p-3 border-bottom">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <span class="fw-bold text-primary" style="font-size:13px;">{{ $req->request_number }}</span>
                            <span class="badge bg-{{ $req->getStatusBadgeClass() }}">{{ $req->getStatusLabel() }}</span>
                        </div>
                        <div class="fw-bold">{{ $req->tool->sparepart_name ?? '-' }}</div>
                        <div class="text-muted" style="font-size:12px;">
                            <i class="fas fa-boxes me-1"></i>Qty: {{ $req->quantity_requested }} {{ $req->tool->unit ?? '' }}
                            &nbsp;·&nbsp;
                            <i class="fas fa-calendar me-1"></i>{{ $req->usage_date->format('d M Y') }}
                            @if($req->return_date)
                                → {{ $req->return_date->format('d M Y') }}
                            @endif
                        </div>
                        <div class="text-muted" style="font-size:12px; margin-top:2px;">
                            <i class="fas fa-bullseye me-1"></i>{{ Str::limit($req->purpose, 60) }}
                        </div>
                    </div>
                    <a href="{{ route('user.tool-requests.show', $req) }}" class="btn btn-sm btn-outline-primary ms-2">
                        <i class="fas fa-eye"></i>
                    </a>
                </div>
                <div class="text-muted mt-1" style="font-size:11px;">
                    Submitted {{ $req->created_at->diffForHumans() }}
                    @if($req->reviewed_at)
                        · Reviewed {{ $req->reviewed_at->diffForHumans() }}
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-5 text-muted">
                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                No requests found.
                <div class="mt-2">
                    <a href="{{ route('user.tool-requests.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Create First Request
                    </a>
                </div>
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
@endsection

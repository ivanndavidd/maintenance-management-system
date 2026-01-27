@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-clipboard-list"></i> Inventory Requests</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Inventory Requests</li>
                </ol>
            </nav>
        </div>
        @if($pendingCount > 0)
        <div>
            <span class="badge bg-warning fs-5">
                <i class="fas fa-clock"></i> {{ $pendingCount }} Pending Request{{ $pendingCount > 1 ? 's' : '' }}
            </span>
        </div>
        @endif
    </div>

    <!-- Success/Error Messages -->
    <!-- Filters Card -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.inventory-requests.index') }}">
                <div class="row g-3">
                    <!-- Search -->
                    <div class="col-md-6">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control"
                               placeholder="Request code, part name, user name..."
                               value="{{ request('search') }}">
                    </div>

                    <!-- Status Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Apply
                        </button>
                        <a href="{{ route('admin.inventory-requests.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Requests Table -->
    @if($requests->count() > 0)
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Request Code</th>
                                <th>Requester</th>
                                <th>Part</th>
                                <th>Quantity</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Requested At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $request)
                            <tr class="{{ $request->isPending() ? 'table-warning' : '' }}">
                                <td>
                                    <strong>{{ $request->request_code }}</strong>
                                    @if($request->isPending())
                                        <br><span class="badge badge-sm bg-warning text-dark">NEW</span>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $request->user->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $request->user->employee_id ?? 'N/A' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $request->part->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $request->part->part_number }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $request->quantity_requested }} {{ $request->part->unit }}
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        Available: {{ $request->part->quantity }} {{ $request->part->unit }}
                                    </small>
                                </td>
                                <td>{{ Str::limit($request->reason, 30) }}</td>
                                <td>
                                    <span class="badge bg-{{ $request->status_badge }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                    @if($request->approver)
                                        <br>
                                        <small class="text-muted">by {{ $request->approver->name }}</small>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $request->created_at->format('d M Y') }}</small>
                                    <br>
                                    <small class="text-muted">{{ $request->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('admin.inventory-requests.show', $request) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                        @if($request->isPending())
                                            Review
                                        @else
                                            View
                                        @endif
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        Showing {{ $requests->firstItem() }} to {{ $requests->lastItem() }} of {{ $requests->total() }} entries
                    </div>
                    {{ $requests->appends(request()->except('page'))->links() }}
                </div>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No inventory requests found</h5>
                <p class="text-muted">
                    @if(request()->hasAny(['search', 'status']))
                        Try adjusting your filters
                    @else
                        There are no inventory requests at the moment
                    @endif
                </p>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.inventory-requests.index') }}" class="btn btn-primary">
                        <i class="fas fa-redo"></i> Clear Filters
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection

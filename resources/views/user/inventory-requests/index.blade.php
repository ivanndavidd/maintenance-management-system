@extends('layouts.user')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-box"></i> My Inventory Requests</h2>
                <p class="text-muted">Request parts and inventory for your work</p>
            </div>
            <a href="{{ route('user.inventory-requests.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Request
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('user.inventory-requests.index') }}" class="row g-3">
                <!-- Search -->
                <div class="col-md-6">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control"
                           placeholder="Search by request code, part name..."
                           value="{{ request('search') }}">
                </div>

                <!-- Status Filter -->
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('user.inventory-requests.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
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
                            <tr>
                                <td>
                                    <strong>{{ $request->request_code }}</strong>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $request->part->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $request->part->part_number }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $request->quantity_requested }}</span>
                                </td>
                                <td>{{ Str::limit($request->reason, 50) }}</td>
                                <td>
                                    <span class="badge bg-{{ $request->status_badge }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>
                                <td>
                                    <small>{{ $request->created_at->format('d M Y H:i') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('user.inventory-requests.show', $request) }}"
                                           class="btn btn-outline-primary"
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($request->isPending())
                                        <form action="{{ route('user.inventory-requests.destroy', $request) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to cancel this request?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Cancel Request">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
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
                <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No inventory requests found</h5>
                <p class="text-muted">
                    @if(request()->hasAny(['search', 'status']))
                        Try adjusting your filters
                    @else
                        You haven't submitted any inventory requests yet
                    @endif
                </p>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('user.inventory-requests.index') }}" class="btn btn-primary">
                        <i class="fas fa-redo"></i> Clear Filters
                    </a>
                @else
                    <a href="{{ route('user.inventory-requests.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create New Request
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection

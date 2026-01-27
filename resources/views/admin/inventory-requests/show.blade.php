@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.inventory-requests.index') }}">Inventory Requests</a>
                </li>
                <li class="breadcrumb-item active">{{ $inventoryRequest->request_code }}</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-file-alt"></i> Request Details</h2>
                <p class="text-muted">{{ $inventoryRequest->request_code }}</p>
            </div>
            <div>
                <a href="{{ route('admin.inventory-requests.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Request Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Request Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Request Code</label>
                            <p class="fw-bold">{{ $inventoryRequest->request_code }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Status</label>
                            <p>
                                <span class="badge bg-{{ $inventoryRequest->status_badge }} fs-6">
                                    {{ ucfirst($inventoryRequest->status) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Requested By</label>
                            <p class="fw-bold">
                                {{ $inventoryRequest->user->name }}
                                <br>
                                <small class="text-muted">{{ $inventoryRequest->user->email }}</small>
                                @if($inventoryRequest->user->employee_id)
                                    <br>
                                    <small class="text-muted">ID: {{ $inventoryRequest->user->employee_id }}</small>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Requested At</label>
                            <p class="fw-bold">
                                <i class="fas fa-calendar"></i>
                                {{ $inventoryRequest->created_at->format('d M Y H:i') }}
                                <br>
                                <small class="text-muted">{{ $inventoryRequest->created_at->diffForHumans() }}</small>
                            </p>
                        </div>
                    </div>

                    @if($inventoryRequest->approved_at)
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">
                                {{ $inventoryRequest->isApproved() ? 'Approved' : 'Rejected' }} By
                            </label>
                            <p class="fw-bold">
                                {{ $inventoryRequest->approver->name ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">
                                {{ $inventoryRequest->isApproved() ? 'Approved' : 'Rejected' }} At
                            </label>
                            <p class="fw-bold">
                                <i class="fas fa-calendar"></i>
                                {{ $inventoryRequest->approved_at->format('d M Y H:i') }}
                            </p>
                        </div>
                    </div>
                    @endif

                    <hr>

                    <!-- Part Details -->
                    <h6 class="text-primary mb-3"><i class="fas fa-box"></i> Part Details</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Part Name</label>
                            <p class="fw-bold">{{ $inventoryRequest->part->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Part Number</label>
                            <p class="fw-bold">{{ $inventoryRequest->part->part_number }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="text-muted small">Quantity Requested</label>
                            <p>
                                <span class="badge bg-info fs-6">
                                    {{ $inventoryRequest->quantity_requested }} {{ $inventoryRequest->part->unit }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">Current Stock</label>
                            <p>
                                <span class="badge bg-secondary fs-6">
                                    {{ $inventoryRequest->part->quantity }} {{ $inventoryRequest->part->unit }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">Stock After Approval</label>
                            <p>
                                @php
                                    $afterStock = $inventoryRequest->part->quantity - $inventoryRequest->quantity_requested;
                                    $badgeClass = $afterStock < 0 ? 'danger' : ($afterStock <= ($inventoryRequest->part->minimum_stock ?? 0) ? 'warning' : 'success');
                                @endphp
                                <span class="badge bg-{{ $badgeClass }} fs-6">
                                    {{ $afterStock }} {{ $inventoryRequest->part->unit }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Category</label>
                            <p>{{ $inventoryRequest->part->category }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Location</label>
                            <p>
                                @if($inventoryRequest->part->location)
                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                    {{ $inventoryRequest->part->location }}
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <hr>

                    <!-- Request Details -->
                    <h6 class="text-primary mb-3"><i class="fas fa-clipboard-list"></i> Request Details</h6>
                    <div class="mb-3">
                        <label class="text-muted small">Reason</label>
                        <p class="fw-bold">{{ $inventoryRequest->reason }}</p>
                    </div>

                    @if($inventoryRequest->usage_description)
                    <div class="mb-3">
                        <label class="text-muted small">Usage Description</label>
                        <div class="bg-light p-3 rounded">
                            <p class="mb-0 text-break">{{ $inventoryRequest->usage_description }}</p>
                        </div>
                    </div>
                    @endif

                    @if($inventoryRequest->admin_notes)
                    <hr>
                    <div class="mb-3">
                        <label class="text-muted small">Admin Notes</label>
                        <div class="alert alert-{{ $inventoryRequest->isApproved() ? 'success' : 'danger' }}">
                            <i class="fas fa-comment-dots"></i>
                            {{ $inventoryRequest->admin_notes }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Approval/Rejection Forms -->
            @if($inventoryRequest->isPending())
            <div class="row">
                <!-- Approve Form -->
                <div class="col-md-6">
                    <div class="card shadow-sm border-success">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-check"></i> Approve Request</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.inventory-requests.approve', $inventoryRequest) }}"
                                  method="POST"
                                  onsubmit="return confirm('Are you sure you want to approve this request? This will deduct {{ $inventoryRequest->quantity_requested }} {{ $inventoryRequest->part->unit }} from the inventory.')">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label">Admin Notes (Optional)</label>
                                    <textarea name="admin_notes" class="form-control" rows="3"
                                              placeholder="Add any notes for the requester...">{{ old('admin_notes') }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-check-circle"></i> Approve & Deduct Inventory
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Reject Form -->
                <div class="col-md-6">
                    <div class="card shadow-sm border-danger">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0"><i class="fas fa-times"></i> Reject Request</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.inventory-requests.reject', $inventoryRequest) }}"
                                  method="POST"
                                  onsubmit="return confirm('Are you sure you want to reject this request?')">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label">
                                        Rejection Reason <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="admin_notes" class="form-control @error('admin_notes') is-invalid @enderror"
                                              rows="3"
                                              placeholder="Please provide a reason for rejection..."
                                              required>{{ old('admin_notes') }}</textarea>
                                    @error('admin_notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-times-circle"></i> Reject Request
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status Timeline -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-history"></i> Request Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <!-- Submitted -->
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="me-3">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-paper-plane"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-1">Request Submitted</h6>
                                    <p class="text-muted small mb-0">
                                        {{ $inventoryRequest->created_at->format('d M Y H:i') }}
                                    </p>
                                    <p class="small mb-0">By: {{ $inventoryRequest->user->name }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Pending/Processing -->
                        @if($inventoryRequest->isPending())
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="me-3">
                                    <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-1">Awaiting Your Review</h6>
                                    <p class="text-muted small mb-0">Please review and take action</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Approved/Rejected -->
                        @if($inventoryRequest->isApproved() || $inventoryRequest->isRejected())
                        <div class="timeline-item">
                            <div class="d-flex">
                                <div class="me-3">
                                    <div class="bg-{{ $inventoryRequest->isApproved() ? 'success' : 'danger' }} text-white rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-{{ $inventoryRequest->isApproved() ? 'check' : 'times' }}"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-1">
                                        Request {{ $inventoryRequest->isApproved() ? 'Approved' : 'Rejected' }}
                                    </h6>
                                    <p class="text-muted small mb-0">
                                        {{ $inventoryRequest->approved_at->format('d M Y H:i') }}
                                    </p>
                                    @if($inventoryRequest->approver)
                                    <p class="small mb-0">By: {{ $inventoryRequest->approver->name }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Stock Warning -->
            @if($inventoryRequest->isPending())
                @php
                    $afterStock = $inventoryRequest->part->quantity - $inventoryRequest->quantity_requested;
                @endphp

                @if($afterStock < 0)
                <div class="card shadow-sm border-danger mb-4">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Insufficient Stock</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">
                            <strong>Warning:</strong> Approving this request will result in negative stock.
                            Current stock is insufficient to fulfill this request.
                        </p>
                    </div>
                </div>
                @elseif($inventoryRequest->part->minimum_stock && $afterStock <= $inventoryRequest->part->minimum_stock)
                <div class="card shadow-sm border-warning mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="fas fa-exclamation-circle"></i> Low Stock Warning</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">
                            <strong>Notice:</strong> Approving this request will bring stock below minimum level
                            ({{ $inventoryRequest->part->minimum_stock }} {{ $inventoryRequest->part->unit }}).
                            Consider reordering this part.
                        </p>
                    </div>
                </div>
                @endif
            @endif

            <!-- Requester Info -->
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-user"></i> Requester Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Name</small>
                        <p class="mb-0 fw-bold">{{ $inventoryRequest->user->name }}</p>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Email</small>
                        <p class="mb-0">{{ $inventoryRequest->user->email }}</p>
                    </div>
                    @if($inventoryRequest->user->employee_id)
                    <div class="mb-2">
                        <small class="text-muted">Employee ID</small>
                        <p class="mb-0">{{ $inventoryRequest->user->employee_id }}</p>
                    </div>
                    @endif
                    @if($inventoryRequest->user->department)
                    <div class="mb-2">
                        <small class="text-muted">Department</small>
                        <p class="mb-0">{{ $inventoryRequest->user->department->name ?? 'N/A' }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline-item::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 50px;
    width: 2px;
    height: calc(100% - 50px);
    background: #dee2e6;
}

.timeline-item:last-child::before {
    display: none;
}
</style>
@endsection

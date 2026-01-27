@extends('layouts.user')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('user.inventory-requests.index') }}">Inventory Requests</a>
                </li>
                <li class="breadcrumb-item active">{{ $inventoryRequest->request_code }}</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-file-alt"></i> Request Details</h2>
                <p class="text-muted">{{ $inventoryRequest->request_code }}</p>
            </div>
            <a href="{{ route('user.inventory-requests.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Request Information -->
        <div class="col-lg-8">
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
                            <label class="text-muted small">Requested At</label>
                            <p class="fw-bold">
                                <i class="fas fa-calendar"></i>
                                {{ $inventoryRequest->created_at->format('d M Y H:i') }}
                            </p>
                        </div>
                        @if($inventoryRequest->approved_at)
                        <div class="col-md-6">
                            <label class="text-muted small">
                                {{ $inventoryRequest->isApproved() ? 'Approved' : 'Rejected' }} At
                            </label>
                            <p class="fw-bold">
                                <i class="fas fa-calendar"></i>
                                {{ $inventoryRequest->approved_at->format('d M Y H:i') }}
                            </p>
                        </div>
                        @endif
                    </div>

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
                        <div class="col-md-6">
                            <label class="text-muted small">Quantity Requested</label>
                            <p>
                                <span class="badge bg-info fs-6">
                                    {{ $inventoryRequest->quantity_requested }} {{ $inventoryRequest->part->unit }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Current Available Stock</label>
                            <p>
                                <span class="badge bg-secondary fs-6">
                                    {{ $inventoryRequest->part->quantity }} {{ $inventoryRequest->part->unit }}
                                </span>
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
                        <p class="text-break">{{ $inventoryRequest->usage_description }}</p>
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

                <!-- Actions -->
                @if($inventoryRequest->isPending())
                <div class="card-footer bg-light">
                    <form action="{{ route('user.inventory-requests.destroy', $inventoryRequest) }}"
                          method="POST"
                          onsubmit="return confirm('Are you sure you want to cancel this request? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times"></i> Cancel Request
                        </button>
                    </form>
                </div>
                @endif
            </div>
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
                                    <h6 class="mb-1">Awaiting Approval</h6>
                                    <p class="text-muted small mb-0">Pending admin review</p>
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

            <!-- Part Information -->
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Part Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Category</small>
                        <p class="mb-0">{{ $inventoryRequest->part->category }}</p>
                    </div>
                    @if($inventoryRequest->part->location)
                    <div class="mb-2">
                        <small class="text-muted">Location</small>
                        <p class="mb-0">
                            <i class="fas fa-map-marker-alt text-danger"></i>
                            {{ $inventoryRequest->part->location }}
                        </p>
                    </div>
                    @endif
                    <div class="mb-2">
                        <small class="text-muted">Minimum Stock Level</small>
                        <p class="mb-0">
                            {{ $inventoryRequest->part->minimum_stock ?? 'Not set' }}
                            @if($inventoryRequest->part->minimum_stock)
                                {{ $inventoryRequest->part->unit }}
                            @endif
                        </p>
                    </div>
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

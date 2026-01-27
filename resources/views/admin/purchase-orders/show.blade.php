@extends('layouts.admin')

@section('page-title', 'Purchase Order Details')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2>Purchase Order: {{ $purchaseOrder->po_number }}</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.purchase-orders.index') }}">Purchase Orders</a></li>
                        <li class="breadcrumb-item active">{{ $purchaseOrder->po_number }}</li>
                    </ol>
                </nav>
            </div>
            <div>
                @if($purchaseOrder->approval_status === 'pending')
                    <span class="badge bg-warning text-dark">Pending Approval</span>
                @elseif($purchaseOrder->approval_status === 'approved')
                    <span class="badge bg-success">Approved</span>
                @elseif($purchaseOrder->approval_status === 'rejected')
                    <span class="badge bg-danger">Rejected</span>
                @endif

                @if($purchaseOrder->status === 'pending')
                    <span class="badge bg-secondary">Pending</span>
                @elseif($purchaseOrder->status === 'ordered')
                    <span class="badge bg-info">Ordered</span>
                @elseif($purchaseOrder->status === 'partial_received')
                    <span class="badge bg-warning">Partial Received</span>
                @elseif($purchaseOrder->status === 'received')
                    <span class="badge bg-success">Received</span>
                @elseif($purchaseOrder->status === 'cancelled')
                    <span class="badge bg-danger">Cancelled</span>
                @endif

                @if($purchaseOrder->has_unlisted_items)
                    <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle"></i> Has Unlisted</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Process Flow Visualization -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-chart-line"></i> Purchase Order Process Flow</h5>
        </div>
        <div class="card-body">
            <div class="process-flow">
                @php
                    $currentStep = 0;

                    // Determine current step based on status
                    if ($purchaseOrder->status === 'cancelled') {
                        $currentStep = -1; // Cancelled
                    } elseif ($purchaseOrder->approval_status === 'rejected') {
                        $currentStep = -1; // Rejected
                    } elseif ($purchaseOrder->approval_status === 'pending') {
                        $currentStep = 1; // Waiting for approval
                    } elseif ($purchaseOrder->status === 'ordered') {
                        $currentStep = 2; // Approved & Ordered
                    } elseif ($purchaseOrder->status === 'partial_received') {
                        $currentStep = 3; // Partially received
                    } elseif ($purchaseOrder->status === 'received') {
                        // Check if items need compliance check
                        $hasCompliantItems = $purchaseOrder->items->where('compliance_status', 'compliant')->count() > 0;
                        $allItemsAdded = $purchaseOrder->items->where('added_to_stock', true)->count() === $purchaseOrder->items->count();

                        if ($allItemsAdded) {
                            $currentStep = 6; // Completed - all items in stock
                        } elseif ($hasCompliantItems) {
                            $currentStep = 5; // Items ready to add to stock
                        } else {
                            $currentStep = 4; // Received - need quality check
                        }
                    } else {
                        $currentStep = 1; // Default to created
                    }
                @endphp

                <div class="timeline">
                    <!-- Step 1: PO Created -->
                    <div class="timeline-item {{ $currentStep >= 1 ? 'completed' : '' }} {{ $currentStep == 1 ? 'active' : '' }}">
                        <div class="timeline-marker">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="timeline-content">
                            <h6 class="mb-1">1. PO Created</h6>
                            <small class="text-muted">{{ $purchaseOrder->created_at->format('d M Y H:i') }}</small>
                            <p class="mb-0 small">By: {{ $purchaseOrder->orderedByUser->name ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Step 2: Waiting Approval -->
                    <div class="timeline-item {{ $currentStep >= 2 ? 'completed' : '' }} {{ $currentStep == 1 ? 'active' : '' }}">
                        <div class="timeline-marker">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="timeline-content">
                            <h6 class="mb-1">2. Waiting Approval</h6>
                            @if($purchaseOrder->approval_status === 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                                <p class="mb-0 small">Approver: {{ $purchaseOrder->approver->name ?? '-' }}</p>
                            @elseif($purchaseOrder->approval_status === 'approved')
                                <small class="text-success">Approved {{ $purchaseOrder->approved_at ? $purchaseOrder->approved_at->format('d M Y H:i') : '' }}</small>
                            @elseif($purchaseOrder->approval_status === 'rejected')
                                <small class="text-danger">Rejected</small>
                                <p class="mb-0 small text-danger">{{ $purchaseOrder->rejection_reason }}</p>
                            @endif
                        </div>
                    </div>

                    @if($purchaseOrder->approval_status !== 'rejected' && $purchaseOrder->status !== 'cancelled')
                        <!-- Step 3: Ordered -->
                        <div class="timeline-item {{ $currentStep >= 3 ? 'completed' : '' }} {{ $currentStep == 2 ? 'active' : '' }}">
                            <div class="timeline-marker">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">3. Ordered to Supplier</h6>
                                @if($currentStep >= 2)
                                    <small class="text-muted">Expected: {{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('d M Y') : '-' }}</small>
                                @else
                                    <span class="badge bg-secondary">Not yet</span>
                                @endif
                            </div>
                        </div>

                        <!-- Step 4: Receiving Goods -->
                        <div class="timeline-item {{ $currentStep >= 4 ? 'completed' : '' }} {{ $currentStep == 3 ? 'active' : '' }}">
                            <div class="timeline-marker">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">4. Receiving Goods</h6>
                                @if($currentStep >= 4)
                                    <small class="text-success">Received: {{ $purchaseOrder->actual_delivery_date ? $purchaseOrder->actual_delivery_date->format('d M Y') : '-' }}</small>
                                    <p class="mb-0 small">By: {{ $purchaseOrder->receivedByUser->name ?? '-' }}</p>
                                @elseif($currentStep == 3)
                                    <span class="badge bg-warning">Partially Received</span>
                                    <p class="mb-0 small">{{ $purchaseOrder->items->sum('quantity_received') }} / {{ $purchaseOrder->total_quantity }} items</p>
                                @else
                                    <span class="badge bg-secondary">Waiting</span>
                                @endif
                            </div>
                        </div>

                        <!-- Step 5: Quality & Compliance Check -->
                        <div class="timeline-item {{ $currentStep >= 5 ? 'completed' : '' }} {{ $currentStep == 4 ? 'active' : '' }}">
                            <div class="timeline-marker">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">5. Quality Check</h6>
                                @if($currentStep >= 5)
                                    @php
                                        $compliant = $purchaseOrder->items->where('compliance_status', 'compliant')->count();
                                        $nonCompliant = $purchaseOrder->items->where('compliance_status', 'non_compliant')->count();
                                    @endphp
                                    <small class="text-success">Compliant: {{ $compliant }}</small>
                                    @if($nonCompliant > 0)
                                        <br><small class="text-danger">Non-compliant: {{ $nonCompliant }}</small>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">Pending inspection</span>
                                @endif
                            </div>
                        </div>

                        <!-- Step 6: Add to Stock -->
                        <div class="timeline-item {{ $currentStep >= 6 ? 'completed' : '' }} {{ $currentStep == 5 ? 'active' : '' }}">
                            <div class="timeline-marker">
                                <i class="fas fa-warehouse"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">6. Add to Stock</h6>
                                @if($currentStep >= 6)
                                    <small class="text-success">All items added to stock</small>
                                @elseif($currentStep == 5)
                                    @php
                                        $addedCount = $purchaseOrder->items->where('added_to_stock', true)->count();
                                        $totalItems = $purchaseOrder->items->count();
                                    @endphp
                                    <span class="badge bg-info">{{ $addedCount }} / {{ $totalItems }} items in stock</span>
                                @else
                                    <span class="badge bg-secondary">Waiting</span>
                                @endif
                            </div>
                        </div>

                        <!-- Step 7: Completed -->
                        <div class="timeline-item {{ $currentStep >= 6 ? 'completed' : '' }}">
                            <div class="timeline-marker">
                                <i class="fas fa-flag-checkered"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">7. Process Complete</h6>
                                @if($currentStep >= 6)
                                    <span class="badge bg-success">Completed</span>
                                @else
                                    <span class="badge bg-secondary">In Progress</span>
                                @endif
                            </div>
                        </div>
                    @else
                        <!-- Cancelled/Rejected State -->
                        <div class="timeline-item completed">
                            <div class="timeline-marker bg-danger">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1 text-danger">{{ $purchaseOrder->status === 'cancelled' ? 'Cancelled' : 'Rejected' }}</h6>
                                @if($purchaseOrder->rejection_reason)
                                    <p class="mb-0 small text-danger">Reason: {{ $purchaseOrder->rejection_reason }}</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Cancellation History Section -->
    @if($purchaseOrder->status === 'cancelled' && $purchaseOrder->cancelled_at)
    <div class="card mb-4 border-danger">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="fas fa-ban"></i> Purchase Order Cancelled</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="40%">Cancellation Type:</th>
                            <td>{!! $purchaseOrder->getCancellationTypeBadge() !!}</td>
                        </tr>
                        <tr>
                            <th>Cancelled By:</th>
                            <td>{{ $purchaseOrder->cancelledByUser->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Cancelled At:</th>
                            <td>{{ $purchaseOrder->cancelled_at->format('d M Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted">Cancellation Reason:</h6>
                    <div class="alert alert-light border">
                        {{ $purchaseOrder->cancellation_reason ?? '-' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Next Actions Section -->
    @if($purchaseOrder->status !== 'cancelled' && $purchaseOrder->approval_status !== 'rejected')
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-tasks"></i> Next Actions</h5>
        </div>
        <div class="card-body">
            @if($purchaseOrder->approval_status === 'pending')
                <!-- Waiting for Approval -->
                <div class="alert alert-info">
                    <h6 class="alert-heading"><i class="fas fa-info-circle"></i> Waiting for Approval</h6>
                    <p class="mb-2">This PO is waiting for approval from: <strong>{{ $purchaseOrder->approver->name ?? '-' }}</strong></p>

                    @if(auth()->id() == $purchaseOrder->approver_id)
                        <p class="mb-3"><strong>You can approve or reject this PO:</strong></p>
                        <div class="d-flex gap-2">
                            <form action="{{ route('admin.purchase-orders.approve', $purchaseOrder) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Approve PO
                                </button>
                            </form>
                            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="fas fa-times"></i> Reject PO
                            </button>
                        </div>
                    @else
                        <p class="mb-0 text-muted"><em>You are not the designated approver for this PO.</em></p>
                    @endif
                </div>

                @if($purchaseOrder->canBeCancelled())
                <div class="mt-3">
                    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelPOModal">
                        <i class="fas fa-ban"></i> Cancel This Purchase Order
                    </button>
                </div>
                @endif

            @elseif($purchaseOrder->status === 'ordered')
                <!-- Ready to Receive Goods -->
                <div class="alert alert-primary">
                    <h6 class="alert-heading"><i class="fas fa-truck"></i> Ready to Receive Goods</h6>
                    <p class="mb-2">PO has been approved and ordered. Expected delivery: <strong>{{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('d M Y') : '-' }}</strong></p>
                    <p class="mb-3">When goods arrive, click the button below to start receiving process:</p>
                    <a href="{{ route('admin.purchase-orders.receive', $purchaseOrder) }}" class="btn btn-primary">
                        <i class="fas fa-box-open"></i> Receive Goods
                    </a>
                </div>

                @if($purchaseOrder->canBeCancelled())
                <div class="mt-3">
                    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelPOModal">
                        <i class="fas fa-ban"></i> Cancel This Purchase Order
                    </button>
                </div>
                @endif

            @elseif($purchaseOrder->status === 'partial_received')
                <!-- Partially Received -->
                <div class="alert alert-warning">
                    <h6 class="alert-heading"><i class="fas fa-box"></i> Partially Received</h6>
                    <p class="mb-2">Received: <strong>{{ $purchaseOrder->items->sum('quantity_received') }} / {{ $purchaseOrder->total_quantity }}</strong> items</p>
                    <p class="mb-3">Continue receiving remaining items:</p>
                    <a href="{{ route('admin.purchase-orders.receive', $purchaseOrder) }}" class="btn btn-warning">
                        <i class="fas fa-box-open"></i> Continue Receiving
                    </a>
                </div>

                @if($purchaseOrder->canBeCancelled())
                <div class="mt-3">
                    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelPOModal">
                        <i class="fas fa-ban"></i> Cancel This Purchase Order
                    </button>
                </div>
                @endif

            @elseif($purchaseOrder->status === 'received')
                @php
                    $needsQualityCheck = $purchaseOrder->items->where('compliance_status', null)->count() > 0;
                    $hasCompliant = $purchaseOrder->items->where('compliance_status', 'compliant')->count() > 0;
                    $canAddToStock = $purchaseOrder->items->where('compliance_status', 'compliant')->where('added_to_stock', false)->count() > 0;
                    $allItemsInStock = $purchaseOrder->items->where('added_to_stock', true)->count() === $purchaseOrder->items->count();
                @endphp

                @if($needsQualityCheck)
                    <!-- Need Quality Check -->
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="fas fa-check-circle"></i> Quality & Compliance Check Required</h6>
                        <p class="mb-2">All items have been received. Please perform quality and compliance check for each item.</p>
                        <p class="mb-0"><strong>Items needing inspection: {{ $needsQualityCheck }}</strong></p>
                    </div>
                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Items for Quality Check</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Qty Received</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchaseOrder->items->where('compliance_status', null) as $item)
                                        <tr>
                                            <td><strong>{{ $item->getItemName() }}</strong></td>
                                            <td>{{ $item->quantity_received }} {{ $item->unit }}</td>
                                            <td><span class="badge bg-secondary">Pending Check</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#complianceModal{{ $item->id }}">
                                                    <i class="fas fa-check"></i> Mark Compliant
                                                </button>
                                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#nonComplianceModal{{ $item->id }}">
                                                    <i class="fas fa-times"></i> Non-Compliant
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Non-Compliant Items Section -->
                    @php
                        $nonCompliantItems = $purchaseOrder->items->where('compliance_status', 'non_compliant');
                    @endphp
                    @if($nonCompliantItems->count() > 0)
                    <div class="card mt-3">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0"><i class="fas fa-times-circle"></i> Non-Compliant Items ({{ $nonCompliantItems->count() }})</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> These items failed quality check and cannot be added to stock. You can reverse the status if needed.
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Qty</th>
                                            <th>Reason</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($nonCompliantItems as $item)
                                        <tr>
                                            <td><strong>{{ $item->getItemName() }}</strong></td>
                                            <td>{{ $item->quantity_received }} {{ $item->unit }}</td>
                                            <td><small class="text-danger">{{ $item->compliance_notes ?? '-' }}</small></td>
                                            <td>
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#reverseComplianceModal{{ $item->id }}">
                                                    <i class="fas fa-undo"></i> Reverse to Pending
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                @elseif($canAddToStock)
                    <!-- Ready to Add to Stock -->
                    <div class="alert alert-success">
                        <h6 class="alert-heading"><i class="fas fa-warehouse"></i> Ready to Add to Stock</h6>
                        <p class="mb-2">Quality check completed. Items are ready to be added to inventory stock.</p>
                        <p class="mb-3"><strong>Items ready: {{ $purchaseOrder->items->where('compliance_status', 'compliant')->where('added_to_stock', false)->count() }}</strong></p>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAllToStockModal">
                            <i class="fas fa-plus-circle"></i> Add All Compliant Items to Stock
                        </button>
                    </div>
                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Items Ready for Stock</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Qty</th>
                                            <th>Compliance</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($purchaseOrder->items->where('compliance_status', 'compliant')->where('added_to_stock', false) as $item)
                                        <tr>
                                            <td><strong>{{ $item->getItemName() }}</strong></td>
                                            <td>{{ $item->quantity_received }} {{ $item->unit }}</td>
                                            <td><span class="badge bg-success">Compliant</span></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addItemToStockModal{{ $item->id }}">
                                                    <i class="fas fa-plus"></i> Add to Stock
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                @elseif($allItemsInStock)
                    <!-- Process Complete -->
                    <div class="alert alert-success">
                        <h6 class="alert-heading"><i class="fas fa-check-double"></i> Process Complete!</h6>
                        <p class="mb-0">All items from this PO have been successfully added to inventory stock.</p>
                    </div>

                @endif

                <!-- Cancel button for received stage (before stock added) -->
                @if($purchaseOrder->status === 'received' && $purchaseOrder->canBeCancelled())
                <div class="mt-3">
                    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelPOModal">
                        <i class="fas fa-ban"></i> Cancel This Purchase Order
                    </button>
                </div>
                @endif
            @endif
        </div>
    </div>
    @endif

    <div class="row">
        <!-- PO Summary -->
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>PO Number:</th>
                            <td><strong>{{ $purchaseOrder->po_number }}</strong></td>
                        </tr>
                        <tr>
                            <th>Total Items:</th>
                            <td><strong>{{ $purchaseOrder->total_items }}</strong> types</td>
                        </tr>
                        <tr>
                            <th>Total Quantity:</th>
                            <td><strong>{{ $purchaseOrder->total_quantity }}</strong> items</td>
                        </tr>
                        <tr>
                            <th>Grand Total:</th>
                            <td><strong class="text-success">Rp {{ number_format($purchaseOrder->total_price, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <th>Order Date:</th>
                            <td>{{ $purchaseOrder->order_date->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <th>Expected Delivery:</th>
                            <td>{{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('d M Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Ordered By:</th>
                            <td>{{ $purchaseOrder->orderedByUser->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Approver:</th>
                            <td>{{ $purchaseOrder->approver->name ?? '-' }}</td>
                        </tr>
                        @if($purchaseOrder->notes)
                        <tr>
                            <th>Notes:</th>
                            <td><small>{{ $purchaseOrder->notes }}</small></td>
                        </tr>
                        @endif
                    </table>

                    @if($purchaseOrder->approval_status === 'pending' && auth()->id() == $purchaseOrder->approver_id)
                        <div class="d-grid gap-2 mt-3">
                            <form action="{{ route('admin.purchase-orders.approve', $purchaseOrder) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                            </form>
                            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        </div>
                    @endif

                    <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-secondary w-100 mt-2">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>

                    @if(auth()->user()->hasRole('admin'))
                        <button type="button" class="btn btn-danger w-100 mt-2" data-bs-toggle="modal" data-bs-target="#deletePOModal">
                            <i class="fas fa-trash"></i> Delete PO
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Items List -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Items in This PO ({{ $purchaseOrder->items->count() }})</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="20%">Item Name</th>
                                    <th width="10%">Code</th>
                                    <th width="12%">Supplier</th>
                                    <th width="10%">Type</th>
                                    <th width="8%">Qty</th>
                                    <th width="8%">Unit</th>
                                    <th width="12%">Subtotal</th>
                                    <th width="15%">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseOrder->items as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $item->getItemName() }}</strong>
                                        @if($item->is_unlisted)
                                            <br><small class="text-muted">{{ $item->unlisted_description }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $item->getItemCode() }}</span>
                                    </td>
                                    <td>{{ $item->supplier ?? '-' }}</td>
                                    <td>
                                        @if($item->is_unlisted)
                                            <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle"></i> UNLISTED</span>
                                        @elseif($item->item_type === 'App\Models\Sparepart')
                                            <span class="badge bg-info">Sparepart</span>
                                        @elseif($item->item_type === 'App\Models\Tool')
                                            <span class="badge bg-success">Tool</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $item->quantity_ordered }}</td>
                                    <td>{{ $item->unit }}</td>
                                    <td class="text-end"><strong>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong></td>
                                    <td>
                                        @if($item->added_to_stock)
                                            <span class="badge bg-success"><i class="fas fa-check-double"></i> In Stock</span>
                                        @elseif($item->compliance_status === 'compliant')
                                            <span class="badge bg-success"><i class="fas fa-check"></i> Compliant</span>
                                        @elseif($item->compliance_status === 'non_compliant')
                                            <span class="badge bg-danger"><i class="fas fa-times"></i> Non-Compliant</span>
                                            <br>
                                            <button class="btn btn-xs btn-warning mt-1" data-bs-toggle="modal" data-bs-target="#reverseComplianceModal{{ $item->id }}" style="font-size: 10px; padding: 2px 6px;">
                                                <i class="fas fa-undo"></i> Reverse
                                            </button>
                                        @elseif($item->quantity_received > 0)
                                            <span class="badge bg-warning"><i class="fas fa-clock"></i> Pending Check</span>
                                        @elseif($purchaseOrder->approval_status === 'approved')
                                            <span class="badge bg-info"><i class="fas fa-truck"></i> Ready to Receive</span>
                                        @else
                                            <span class="badge bg-secondary"><i class="fas fa-hourglass-half"></i> Waiting</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="7" class="text-end">GRAND TOTAL:</th>
                                    <th class="text-end">Rp {{ number_format($purchaseOrder->total_price, 0, ',', '.') }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if($purchaseOrder->has_unlisted_items)
                        <div class="alert alert-warning mt-3">
                            <h6 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Warning: Unlisted Items</h6>
                            <p class="mb-0">This PO contains items that are <strong>NOT in master data</strong>. After receiving goods, admin must add these items to master data (Spareparts or Tools) before they can be added to stock.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.purchase-orders.reject', $purchaseOrder) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Reject Purchase Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Please provide reason for rejection"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Reject PO
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Compliance Modals for Each Item -->
@foreach($purchaseOrder->items as $item)
    <!-- Compliant Modal -->
    <div class="modal fade" id="complianceModal{{ $item->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.purchase-orders.mark-compliant', [$purchaseOrder, $item]) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Mark Item as Compliant</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Item:</strong> {{ $item->getItemName() }}</p>
                        <p><strong>Quantity:</strong> {{ $item->quantity_received }} {{ $item->unit }}</p>
                        <div class="mb-3">
                            <label class="form-label">Compliance Notes (Optional)</label>
                            <textarea name="compliance_notes" class="form-control" rows="3" placeholder="Add any notes about quality inspection..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Mark as Compliant
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Non-Compliant Modal -->
    <div class="modal fade" id="nonComplianceModal{{ $item->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.purchase-orders.mark-non-compliant', [$purchaseOrder, $item]) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Mark Item as Non-Compliant</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p><strong>Item:</strong> {{ $item->getItemName() }}</p>
                        <p><strong>Quantity:</strong> {{ $item->quantity_received }} {{ $item->unit }}</p>
                        <div class="mb-3">
                            <label class="form-label">Reason for Non-Compliance <span class="text-danger">*</span></label>
                            <textarea name="compliance_notes" class="form-control" rows="3" required placeholder="Describe quality issues, damages, or reasons for non-compliance..."></textarea>
                        </div>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> Non-compliant items will not be added to stock and may require return or reorder.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times"></i> Mark as Non-Compliant
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<!-- Reverse Compliance Modals -->
@foreach($purchaseOrder->items->where('compliance_status', 'non_compliant') as $item)
<div class="modal fade" id="reverseComplianceModal{{ $item->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.purchase-orders.reverse-compliance', [$purchaseOrder, $item]) }}" method="POST">
                @csrf
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Reverse Compliance Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Item:</strong> {{ $item->getItemName() }}</p>
                    <p><strong>Current Status:</strong> <span class="badge bg-danger">Non-Compliant</span></p>
                    <p><strong>Non-Compliance Reason:</strong></p>
                    <div class="alert alert-danger">
                        {{ $item->compliance_notes }}
                    </div>
                    <hr>
                    <p class="mb-0"><strong>This will reset the compliance status to "Pending Check"</strong>, allowing you to re-inspect and mark it as compliant or non-compliant again.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-undo"></i> Reverse to Pending Check
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Delete PO Modal (Admin Only) -->
@if(auth()->user()->hasRole('admin'))
<div class="modal fade" id="deletePOModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.purchase-orders.destroy', $purchaseOrder) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-trash"></i> Delete Purchase Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <h6 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Warning: Permanent Deletion</h6>
                        <p>You are about to <strong>permanently delete</strong> this Purchase Order:</p>
                        <ul>
                            <li><strong>PO Number:</strong> {{ $purchaseOrder->po_number }}</li>
                            <li><strong>Total Items:</strong> {{ $purchaseOrder->total_items }} items</li>
                            <li><strong>Status:</strong> {{ ucfirst($purchaseOrder->status) }}</li>
                        </ul>
                    </div>

                    @php
                        $hasStockItems = $purchaseOrder->items->where('added_to_stock', true)->count() > 0;
                    @endphp

                    @if($hasStockItems)
                        <div class="alert alert-warning">
                            <i class="fas fa-ban"></i> <strong>Cannot Delete:</strong> Some items have already been added to stock. Please reverse the process first before deleting this PO.
                        </div>
                    @else
                        <p><strong>This action cannot be undone!</strong></p>
                        <p>All items in this PO will also be deleted. Are you sure you want to proceed?</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    @if(!$hasStockItems)
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Yes, Delete Permanently
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Add All to Stock Modal -->
<div class="modal fade" id="addAllToStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.purchase-orders.add-all-to-stock', $purchaseOrder) }}" method="POST" data-loading-text="Adding items to stock...">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-warehouse"></i> Add All to Stock</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Confirmation Required</strong>
                    </div>
                    <p><strong>PO Number:</strong> {{ $purchaseOrder->po_number }}</p>
                    <p><strong>Items to Add:</strong> {{ $purchaseOrder->items->where('compliance_status', 'compliant')->where('added_to_stock', false)->count() }} compliant items</p>
                    <hr>
                    <p class="mb-0">This will add all compliant items to the inventory stock. Are you sure you want to continue?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Yes, Add All to Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Individual Item to Stock Modals -->
@foreach($purchaseOrder->items->where('compliance_status', 'compliant')->where('added_to_stock', false) as $item)
<div class="modal fade" id="addItemToStockModal{{ $item->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.purchase-orders.add-item-to-stock', [$purchaseOrder, $item]) }}" method="POST" data-loading-text="Adding item to stock...">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle"></i> Add Item to Stock</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Confirmation Required</strong>
                    </div>
                    <p><strong>Item:</strong> {{ $item->getItemName() }}</p>
                    <p><strong>Code:</strong> <span class="badge bg-info">{{ $item->getItemCode() }}</span></p>
                    <p><strong>Quantity:</strong> {{ $item->quantity_received }} {{ $item->unit }}</p>
                    <p><strong>Status:</strong> <span class="badge bg-success">Compliant</span></p>
                    <hr>
                    <p class="mb-0">This will add the item to the inventory stock. Are you sure?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Yes, Add to Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Cancel Purchase Order Modal -->
<div class="modal fade" id="cancelPOModal" tabindex="-1" aria-labelledby="cancelPOModalLabel" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content" style="z-index: 1060;">
            <form action="{{ route('admin.purchase-orders.cancel', $purchaseOrder) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-ban"></i> Cancel Purchase Order</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">Cancel PO: <strong>{{ $purchaseOrder->po_number }}</strong></p>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Cancellation Type <span class="text-danger">*</span></label>
                        <select name="cancellation_type" class="form-select form-select-sm" required>
                            <option value="">-- Select Type --</option>
                            <option value="cancelled_by_vendor">Cancelled by Vendor</option>
                            <option value="cancelled_by_user">Cancelled by User</option>
                            <option value="internal_issue">Internal Issue</option>
                            <option value="budget_constraint">Budget Constraint</option>
                            <option value="duplicate_order">Duplicate Order</option>
                            <option value="item_unavailable">Item Unavailable</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Cancellation Reason <span class="text-danger">*</span></label>
                        <textarea name="cancellation_reason" class="form-control form-control-sm" rows="3" required placeholder="Provide detailed reason for cancellation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban"></i> Yes, Cancel This PO
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Fix modal z-index and interaction issues - must be higher than sidebar (9999) and loading overlay (9999) */
.modal-backdrop {
    z-index: 10000 !important;
    pointer-events: none !important; /* Backdrop should not block clicks */
}

.modal-backdrop.show {
    z-index: 10000 !important;
    pointer-events: none !important;
}

#cancelPOModal {
    z-index: 10001 !important;
    pointer-events: auto !important;
}

#cancelPOModal .modal-dialog {
    z-index: 10002 !important;
    pointer-events: auto !important;
}

#cancelPOModal .modal-content {
    position: relative;
    pointer-events: auto !important;
    z-index: 10003 !important;
}

/* Ensure all form elements are clickable */
#cancelPOModal select,
#cancelPOModal textarea,
#cancelPOModal input,
#cancelPOModal button {
    pointer-events: auto !important;
    position: relative;
    z-index: 10004 !important;
}

/* Apply same z-index fix to all modals on this page */
.modal.show {
    z-index: 10001 !important;
    pointer-events: auto !important;
}

/* Timeline Process Flow Styles */
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    position: relative;
    padding-left: 60px;
    padding-bottom: 30px;
    opacity: 0.5;
}

.timeline-item.completed {
    opacity: 1;
}

.timeline-item.active {
    opacity: 1;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: 20px;
    top: 35px;
    bottom: -10px;
    width: 3px;
    background: #dee2e6;
}

.timeline-item.completed:before {
    background: #28a745;
}

.timeline-item.active:before {
    background: linear-gradient(to bottom, #28a745 0%, #ffc107 50%, #dee2e6 100%);
    animation: pulse 2s infinite;
}

.timeline-item:last-child:before {
    display: none;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 0;
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #e9ecef;
    border: 3px solid #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    font-size: 16px;
    z-index: 1;
}

.timeline-item.completed .timeline-marker {
    background: #28a745;
    border-color: #28a745;
    color: white;
}

.timeline-item.active .timeline-marker {
    background: #ffc107;
    border-color: #ffc107;
    color: #000;
    animation: glow 2s infinite;
    box-shadow: 0 0 10px rgba(255, 193, 7, 0.5);
}

.timeline-marker.bg-danger {
    background: #dc3545 !important;
    border-color: #dc3545 !important;
    color: white !important;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #dee2e6;
}

.timeline-item.completed .timeline-content {
    border-left-color: #28a745;
}

.timeline-item.active .timeline-content {
    border-left-color: #ffc107;
    background: #fff8e1;
}

.timeline-content h6 {
    color: #495057;
    font-weight: 600;
    margin-bottom: 5px;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

@keyframes glow {
    0%, 100% {
        box-shadow: 0 0 10px rgba(255, 193, 7, 0.5);
    }
    50% {
        box-shadow: 0 0 20px rgba(255, 193, 7, 0.8);
    }
}
</style>

<script>
// Fix modal z-index and clickability issues
document.addEventListener('DOMContentLoaded', function() {
    const cancelModal = document.getElementById('cancelPOModal');

    if (cancelModal) {
        // When modal is shown, ensure proper z-index
        cancelModal.addEventListener('show.bs.modal', function (event) {
            // Get the backdrop
            setTimeout(() => {
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.style.zIndex = '10000';
                }

                // Set modal z-index
                cancelModal.style.zIndex = '10001';

                // Ensure all form elements inside are clickable
                const formElements = cancelModal.querySelectorAll('select, textarea, input, button');
                formElements.forEach(element => {
                    element.style.pointerEvents = 'auto';
                    element.style.position = 'relative';
                    element.style.zIndex = '10003';
                });
            }, 50);
        });

        // Reset z-index when modal is hidden
        cancelModal.addEventListener('hidden.bs.modal', function (event) {
            cancelModal.style.zIndex = '';
        });
    }
});
</script>
@endsection

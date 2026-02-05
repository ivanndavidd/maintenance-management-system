@extends('layouts.admin')

@section('page-title', 'Receive Goods - ' . $purchaseOrder->po_number)

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2><i class="fas fa-truck"></i> Receive Goods</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.purchase-orders.index') }}">Purchase Orders</a></li>
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.purchase-orders.show', $purchaseOrder) }}">{{ $purchaseOrder->po_number }}</a></li>
                <li class="breadcrumb-item active">Receive Goods</li>
            </ol>
        </nav>
    </div>

    <!-- PO Summary -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-file-alt"></i> PO: {{ $purchaseOrder->po_number }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Total Items:</strong>
                    <p class="fs-5 mb-0">{{ $purchaseOrder->total_items }} types</p>
                </div>
                <div class="col-md-3">
                    <strong>Total Quantity:</strong>
                    <p class="fs-5 mb-0">{{ $purchaseOrder->total_quantity }} items</p>
                </div>
                <div class="col-md-3">
                    <strong>Already Received:</strong>
                    <p class="fs-5 mb-0 text-success">{{ $purchaseOrder->items->sum('quantity_received') }} items</p>
                </div>
                <div class="col-md-3">
                    <strong>Remaining:</strong>
                    <p class="fs-5 mb-0 text-warning">{{ $purchaseOrder->total_quantity - $purchaseOrder->items->sum('quantity_received') }} items</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Receive Form -->
    <div class="card">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-box-open"></i> Items to Receive</h5>
            <button type="button" class="btn btn-light btn-sm" onclick="fillAllRemaining()">
                <i class="fas fa-fill"></i> Fill All Remaining
            </button>
        </div>
        <div class="card-body">
            <form action="{{ route($routePrefix.'.purchase-orders.batch-receive') }}" method="POST" id="batchReceiveForm">
                @csrf
                <input type="hidden" name="purchase_order_id" value="{{ $purchaseOrder->id }}">

                <div class="mb-3">
                    <label class="form-label">Actual Delivery Date <span class="text-danger">*</span></label>
                    <input type="date"
                           name="actual_delivery_date"
                           class="form-control"
                           value="{{ date('Y-m-d') }}"
                           max="{{ date('Y-m-d') }}"
                           required>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="25%">Item Name</th>
                                <th width="12%">Code</th>
                                <th width="12%">Supplier</th>
                                <th width="10%">Ordered</th>
                                <th width="10%">Received</th>
                                <th width="10%">Remaining</th>
                                <th width="16%">Qty to Receive</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrder->items as $index => $item)
                            @php
                                $remaining = $item->quantity_ordered - $item->quantity_received;
                            @endphp
                            <tr class="{{ $remaining == 0 ? 'table-success' : '' }}">
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $item->getItemName() }}</strong>
                                    @if($item->is_unlisted)
                                        <br><span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle"></i> UNLISTED</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $item->getItemCode() }}</span>
                                </td>
                                <td><small>{{ $item->supplier ?? '-' }}</small></td>
                                <td class="text-center"><strong>{{ $item->quantity_ordered }}</strong> {{ $item->unit }}</td>
                                <td class="text-center text-success"><strong>{{ $item->quantity_received }}</strong> {{ $item->unit }}</td>
                                <td class="text-center">
                                    @if($remaining > 0)
                                        <span class="badge bg-warning">{{ $remaining }} {{ $item->unit }}</span>
                                    @else
                                        <span class="badge bg-success"><i class="fas fa-check"></i> Complete</span>
                                    @endif
                                </td>
                                <td>
                                    @if($remaining > 0)
                                        <input type="hidden" name="items[{{ $item->id }}][item_id]" value="{{ $item->id }}">
                                        <input type="hidden" name="items[{{ $item->id }}][max]" value="{{ $remaining }}">
                                        <input type="number"
                                               name="items[{{ $item->id }}][quantity]"
                                               class="form-control form-control-sm receive-qty"
                                               data-item-id="{{ $item->id }}"
                                               data-max="{{ $remaining }}"
                                               min="0"
                                               max="{{ $remaining }}"
                                               value="0"
                                               placeholder="0">
                                        <small class="text-muted">Max: {{ $remaining }}</small>
                                    @else
                                        <span class="text-success"><i class="fas fa-check-circle"></i> Done</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 d-flex justify-content-between">
                    <a href="{{ route($routePrefix.'.purchase-orders.show', $purchaseOrder) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to PO Details
                    </a>
                    <button type="submit" class="btn btn-success btn-lg" onclick="return confirmBatchReceive()">
                        <i class="fas fa-check-double"></i> Receive Selected Items
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Fill all remaining quantities
function fillAllRemaining() {
    const inputs = document.querySelectorAll('.receive-qty');
    inputs.forEach(input => {
        const max = input.getAttribute('data-max');
        input.value = max;
    });
}

// Confirm batch receive
function confirmBatchReceive() {
    const inputs = document.querySelectorAll('.receive-qty');
    let totalItems = 0;
    let itemsList = [];

    inputs.forEach(input => {
        const qty = parseInt(input.value) || 0;
        if (qty > 0) {
            totalItems++;
            // Get item name from the row
            const row = input.closest('tr');
            const itemName = row.querySelector('td:nth-child(2) strong').textContent.trim();
            itemsList.push(`- ${itemName}: ${qty} pcs`);
        }
    });

    if (totalItems === 0) {
        alert('Please enter quantity for at least one item!');
        return false;
    }

    const message = `You are about to receive ${totalItems} item(s):\n\n${itemsList.join('\n')}\n\nConfirm receipt?`;
    return confirm(message);
}

// Validate max quantity on input
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.receive-qty');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            const max = parseInt(this.getAttribute('data-max'));
            const value = parseInt(this.value) || 0;

            if (value > max) {
                this.value = max;
                alert(`Maximum quantity for this item is ${max}`);
            }

            if (value < 0) {
                this.value = 0;
            }
        });
    });
});
</script>
@endpush

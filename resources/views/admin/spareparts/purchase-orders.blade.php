@extends('layouts.admin')

@section('page-title', 'Purchase Orders - Spareparts')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Purchase Orders - Spareparts</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.spareparts.index') }}">Spareparts</a></li>
                <li class="breadcrumb-item active">Purchase Orders</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Purchase Orders List</h5>
            <a href="{{ route($routePrefix.'.spareparts.purchase-orders.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Purchase Order
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>PO Number</th>
                            <th>Sparepart</th>
                            <th>Supplier</th>
                            <th>Qty Ordered</th>
                            <th>Qty Received</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th>Order Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseOrders as $po)
                        <tr>
                            <td><strong>{{ $po->po_number }}</strong></td>
                            <td>
                                {{ $po->getItemName() }}<br>
                                <small class="text-muted">{{ $po->getItemCode() }}</small>
                            </td>
                            <td>{{ $po->supplier }}</td>
                            <td>{{ $po->quantity_ordered }} {{ $po->item ? $po->item->unit : '-' }}</td>
                            <td>
                                <span class="text-success">{{ $po->quantity_received }} {{ $po->item ? $po->item->unit : '-' }}</span>
                                @if($po->getRemainingQuantity() > 0)
                                    <br><small class="text-danger">Remaining: {{ $po->getRemainingQuantity() }}</small>
                                @endif
                            </td>
                            <td>Rp {{ number_format($po->total_price, 0, ',', '.') }}</td>
                            <td>
                                @if($po->status === 'pending')
                                    <span class="badge bg-secondary">Pending</span>
                                @elseif($po->status === 'ordered')
                                    <span class="badge bg-info">Ordered</span>
                                @elseif($po->status === 'partial_received')
                                    <span class="badge bg-warning">Partial Received</span>
                                @elseif($po->status === 'received')
                                    <span class="badge bg-success">Received</span>
                                @elseif($po->status === 'cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </td>
                            <td>{{ $po->order_date->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route($routePrefix.'.purchase-orders.show', $po) }}" class="btn btn-sm btn-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">No purchase orders found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $purchaseOrders->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

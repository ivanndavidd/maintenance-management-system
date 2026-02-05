@extends('layouts.admin')

@section('page-title', 'Purchase Orders')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Purchase Orders</h2>
        <a href="{{ route($routePrefix.'.purchase-orders.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Purchase Order
        </a>
    </div>

    <!-- Filter Section -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route($routePrefix.'.purchase-orders.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="ordered" {{ request('status') == 'ordered' ? 'selected' : '' }}>Ordered</option>
                            <option value="partial_received" {{ request('status') == 'partial_received' ? 'selected' : '' }}>Partial Received</option>
                            <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="approval_status" class="form-select">
                            <option value="">All Approval Status</option>
                            <option value="pending" {{ request('approval_status') == 'pending' ? 'selected' : '' }}>Pending Approval</option>
                            <option value="approved" {{ request('approval_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('approval_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route($routePrefix.'.purchase-orders.index') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>PO Number</th>
                            <th>Items</th>
                            <th>Total Qty</th>
                            <th>Total Price</th>
                            <th>Progress</th>
                            <th>Order Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseOrders as $po)
                        <tr>
                            <td>
                                <strong>{{ $po->po_number }}</strong>
                                @if($po->has_unlisted_items)
                                    <br><span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle"></i> Has Unlisted</span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $po->total_items }} items</strong>
                                @if($po->items->count() > 0)
                                    <br>
                                    <small class="text-muted">
                                        @foreach($po->items->take(2) as $item)
                                            @if($item->is_unlisted)
                                                <span class="badge bg-warning text-dark">{{ $item->unlisted_item_name }}</span>
                                            @else
                                                <span class="badge {{ $item->item_type === 'App\Models\Sparepart' ? 'bg-info' : 'bg-success' }}">
                                                    {{ $item->getItemName() }}
                                                </span>
                                            @endif
                                        @endforeach
                                        @if($po->items->count() > 2)
                                            <span class="badge bg-secondary">+{{ $po->items->count() - 2 }} more</span>
                                        @endif
                                    </small>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $po->total_quantity }}</strong> items
                                @if($po->items->sum('quantity_received') > 0)
                                    <br><small class="text-muted">Received: {{ $po->items->sum('quantity_received') }}</small>
                                @endif
                            </td>
                            <td><strong>Rp {{ number_format($po->total_price, 0, ',', '.') }}</strong></td>
                            <td>
                                @php
                                    // Calculate progress percentage
                                    $progress = 0;
                                    $statusText = 'Created';
                                    $progressColor = 'bg-secondary';

                                    if ($po->status === 'cancelled' || $po->approval_status === 'rejected') {
                                        $progress = 100;
                                        $statusText = $po->status === 'cancelled' ? 'Cancelled' : 'Rejected';
                                        $progressColor = 'bg-danger';
                                    } elseif ($po->approval_status === 'pending') {
                                        $progress = 15;
                                        $statusText = 'Waiting Approval';
                                        $progressColor = 'bg-warning';
                                    } elseif ($po->status === 'ordered') {
                                        $progress = 35;
                                        $statusText = 'Ordered';
                                        $progressColor = 'bg-info';
                                    } elseif ($po->status === 'partial_received') {
                                        $receivedPercent = $po->total_quantity > 0 ? ($po->items->sum('quantity_received') / $po->total_quantity) * 100 : 0;
                                        $progress = 50 + ($receivedPercent * 0.2);
                                        $statusText = 'Receiving (' . round($receivedPercent) . '%)';
                                        $progressColor = 'bg-warning';
                                    } elseif ($po->status === 'received') {
                                        $compliantCount = $po->items->where('compliance_status', 'compliant')->count();
                                        $addedToStockCount = $po->items->where('added_to_stock', true)->count();
                                        $totalItems = $po->items->count();

                                        if ($addedToStockCount === $totalItems) {
                                            $progress = 100;
                                            $statusText = 'Completed';
                                            $progressColor = 'bg-success';
                                        } elseif ($compliantCount > 0) {
                                            $progress = 85;
                                            $statusText = 'Ready for Stock';
                                            $progressColor = 'bg-info';
                                        } else {
                                            $progress = 70;
                                            $statusText = 'Quality Check';
                                            $progressColor = 'bg-warning';
                                        }
                                    }
                                @endphp
                                <div>
                                    <small class="text-muted d-block mb-1">{{ $statusText }}</small>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar {{ $progressColor }}"
                                             role="progressbar"
                                             style="width: {{ $progress }}%;"
                                             aria-valuenow="{{ $progress }}"
                                             aria-valuemin="0"
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ round($progress) }}%</small>
                                </div>
                            </td>
                            <td>{{ $po->order_date->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route($routePrefix.'.purchase-orders.show', $po) }}" class="btn btn-sm btn-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(auth()->user()->hasRole('admin'))
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $po->id }}" title="Delete PO">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p>No purchase orders found</p>
                            </td>
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

<!-- Delete Modals (Admin Only) -->
@if(auth()->user()->hasRole('admin'))
    @foreach($purchaseOrders as $po)
        <div class="modal fade" id="deleteModal{{ $po->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route($routePrefix.'.purchase-orders.destroy', $po) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title"><i class="fas fa-trash"></i> Delete PO</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            @php
                                $hasStockItems = $po->items->where('added_to_stock', true)->count() > 0;
                            @endphp

                            <p><strong>Delete Purchase Order:</strong> {{ $po->po_number }}</p>
                            <p><strong>Total Items:</strong> {{ $po->total_items }} items</p>

                            @if($hasStockItems)
                                <div class="alert alert-warning">
                                    <i class="fas fa-ban"></i> Cannot delete: Some items have been added to stock. Please reverse first.
                                </div>
                            @else
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle"></i> This action cannot be undone!
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            @if(!$hasStockItems)
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endif

@endsection

@extends('layouts.admin')

@section('page-title', 'Stock Adjustments')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Stock Adjustments</h2>
        <a href="{{ route($routePrefix.'.adjustments.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Adjustment
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list"></i> All Stock Adjustments</h5>
        </div>
        <div class="card-body">
            @if($adjustments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Adjustment Code</th>
                                <th>Item Type</th>
                                <th>Item Name</th>
                                <th>Type</th>
                                <th>Qty Before</th>
                                <th>Adjustment</th>
                                <th>Qty After</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Adjusted By</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($adjustments as $adjustment)
                            <tr>
                                <td>
                                    <strong>{{ $adjustment->adjustment_code }}</strong>
                                </td>
                                <td>
                                    @if($adjustment->item_type === 'sparepart')
                                        <span class="badge bg-primary">Sparepart</span>
                                    @else
                                        <span class="badge bg-info">Tool</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        if($adjustment->item_type === 'sparepart') {
                                            $item = \App\Models\Sparepart::find($adjustment->item_id);
                                        } else {
                                            $item = \App\Models\Tool::find($adjustment->item_id);
                                        }
                                    @endphp
                                    {{ $item->sparepart_name ?? 'N/A' }}
                                    <br>
                                    <small class="text-muted">{{ $item->sparepart_id ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    @if($adjustment->adjustment_type === 'add')
                                        <span class="badge bg-success">Add</span>
                                    @elseif($adjustment->adjustment_type === 'subtract')
                                        <span class="badge bg-danger">Subtract</span>
                                    @else
                                        <span class="badge bg-warning">Correction</span>
                                    @endif
                                </td>
                                <td>{{ $adjustment->quantity_before }}</td>
                                <td>
                                    @if($adjustment->adjustment_qty > 0)
                                        <span class="text-success">+{{ $adjustment->adjustment_qty }}</span>
                                    @else
                                        <span class="text-danger">{{ $adjustment->adjustment_qty }}</span>
                                    @endif
                                </td>
                                <td>{{ $adjustment->quantity_after }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst($adjustment->reason_category) }}</span>
                                    <br>
                                    <small class="text-muted">{{ Str::limit($adjustment->reason, 30) }}</small>
                                </td>
                                <td>
                                    @if($adjustment->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($adjustment->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @else
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>{{ $adjustment->adjustedByUser->name ?? 'N/A' }}</td>
                                <td>{{ $adjustment->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <a href="{{ route($routePrefix.'.adjustments.show', $adjustment) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $adjustments->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No stock adjustments found.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

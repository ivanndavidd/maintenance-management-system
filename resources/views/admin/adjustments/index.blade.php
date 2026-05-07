@extends('layouts.admin')

@section('page-title', 'Stock Adjustments')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5>Stock Adjustments</h5>
        <a href="{{ route($routePrefix.'.adjustments.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i><span class="btn-text"> New Adjustment</span>
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
                                <th>Code</th>
                                <th class="d-none d-md-table-cell">Item Type</th>
                                <th>Item Name</th>
                                <th class="d-none d-md-table-cell">Type</th>
                                <th class="d-none d-lg-table-cell">Qty Before</th>
                                <th class="d-none d-md-table-cell">Adjustment</th>
                                <th class="d-none d-lg-table-cell">Qty After</th>
                                <th class="d-none d-lg-table-cell">Reason</th>
                                <th>Status</th>
                                <th class="d-none d-lg-table-cell">Adjusted By</th>
                                <th class="d-none d-md-table-cell">Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($adjustments as $adjustment)
                            <tr>
                                <td>
                                    <strong>{{ $adjustment->adjustment_code }}</strong>
                                    <div class="d-md-none">
                                        @if($adjustment->item_type === 'sparepart')
                                            <span class="badge bg-primary" style="font-size:10px;">Sparepart</span>
                                        @else
                                            <span class="badge bg-info" style="font-size:10px;">Tool</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">
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
                                <td class="d-none d-md-table-cell">
                                    @if($adjustment->adjustment_type === 'add')
                                        <span class="badge bg-success">Add</span>
                                    @elseif($adjustment->adjustment_type === 'subtract')
                                        <span class="badge bg-danger">Subtract</span>
                                    @else
                                        <span class="badge bg-warning">Correction</span>
                                    @endif
                                </td>
                                <td class="d-none d-lg-table-cell">{{ $adjustment->quantity_before }}</td>
                                <td class="d-none d-md-table-cell">
                                    @if($adjustment->adjustment_qty > 0)
                                        <span class="text-success">+{{ $adjustment->adjustment_qty }}</span>
                                    @else
                                        <span class="text-danger">{{ $adjustment->adjustment_qty }}</span>
                                    @endif
                                </td>
                                <td class="d-none d-lg-table-cell">{{ $adjustment->quantity_after }}</td>
                                <td class="d-none d-lg-table-cell">
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
                                    <div class="d-md-none"><small class="text-muted">{{ $adjustment->created_at->format('d M Y') }}</small></div>
                                </td>
                                <td class="d-none d-lg-table-cell">{{ $adjustment->adjustedByUser->name ?? 'N/A' }}</td>
                                <td class="d-none d-md-table-cell">{{ $adjustment->created_at->format('d M Y H:i') }}</td>
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

@extends('layouts.admin')

@section('page-title', 'Stock Adjustments - Spareparts')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Stock Adjustments - Spareparts</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'Prefix.'.spareparts.index') }}">Spareparts</a></li>
                <li class="breadcrumb-item active">Stock Adjustments</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Adjustment History</h5>
            <a href="{{ route($routePrefix.'Prefix.'.spareparts.adjustments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Adjustment
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Adjustment Code</th>
                            <th>Sparepart</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Before</th>
                            <th>After</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Adjusted By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adjustments as $adjustment)
                        <tr>
                            <td><strong>{{ $adjustment->adjustment_code }}</strong></td>
                            <td>
                                @if($adjustment->sparepart)
                                    {{ $adjustment->sparepart->sparepart_name }}<br>
                                    <small class="text-muted">{{ $adjustment->sparepart->sparepart_id }}</small>
                                @else
                                    <span class="text-muted"><i class="fas fa-exclamation-triangle"></i> Sparepart Deleted</span><br>
                                    <small class="text-muted">ID: {{ $adjustment->item_id }}</small>
                                @endif
                            </td>
                            <td>
                                @if($adjustment->adjustment_type === 'add')
                                    <span class="badge bg-success">Add</span>
                                @elseif($adjustment->adjustment_type === 'subtract')
                                    <span class="badge bg-danger">Subtract</span>
                                @else
                                    <span class="badge bg-info">Correction</span>
                                @endif
                            </td>
                            <td>
                                @if($adjustment->adjustment_type === 'add')
                                    <span class="text-success">+{{ $adjustment->quantity_adjusted }}</span>
                                @elseif($adjustment->adjustment_type === 'subtract')
                                    <span class="text-danger">-{{ $adjustment->quantity_adjusted }}</span>
                                @else
                                    {{ $adjustment->quantity_adjusted }}
                                @endif
                            </td>
                            <td>{{ $adjustment->quantity_before }}</td>
                            <td>
                                <strong>{{ $adjustment->quantity_after }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $adjustment->reason_category)) }}</span>
                                @if($adjustment->notes)
                                    <br><small class="text-muted">{{ Str::limit($adjustment->notes, 30) }}</small>
                                @endif
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
                            <td>{{ $adjustment->adjustedByUser->name }}</td>
                            <td>{{ $adjustment->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <span class="text-muted">-</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center">No adjustments found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $adjustments->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

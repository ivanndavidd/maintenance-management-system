@extends('layouts.admin')

@section('page-title', 'Stock Opname Executions - Spareparts')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Stock Opname Executions - Spareparts</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.spareparts.index') }}">Spareparts</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.spareparts.opname.dashboard') }}">Opname Dashboard</a></li>
                <li class="breadcrumb-item active">Executions</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Execution History</h5>
            <a href="{{ route('admin.spareparts.opname.executions.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Record Execution
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Execution Code</th>
                            <th>Sparepart</th>
                            <th>Execution Date</th>
                            <th>System Qty</th>
                            <th>Physical Qty</th>
                            <th>Discrepancy</th>
                            <th>Accuracy</th>
                            <th>Status</th>
                            <th>Executed By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($executions as $execution)
                        <tr class="{{ $execution->is_missed ? 'table-danger' : '' }}">
                            <td>
                                <strong>{{ $execution->execution_code }}</strong>
                                @if($execution->is_missed)
                                    <br><span class="badge bg-danger">MISSED</span>
                                @endif
                                @if($execution->hasDiscrepancy())
                                    <br><span class="badge bg-warning">Has Discrepancy</span>
                                @endif
                            </td>
                            <td>
                                @if($execution->sparepart_id)
                                    {{ $execution->sparepart->sparepart_name }}<br>
                                    <small class="text-muted">{{ $execution->sparepart->sparepart_id }}</small>
                                @else
                                    <span class="text-muted">Bulk Opname</span>
                                @endif
                            </td>
                            <td>{{ $execution->execution_date->format('d M Y') }}</td>
                            <td>{{ $execution->system_quantity }}</td>
                            <td>{{ $execution->physical_quantity }}</td>
                            <td>
                                @if($execution->discrepancy_qty > 0)
                                    <span class="text-success">+{{ $execution->discrepancy_qty }}</span>
                                @elseif($execution->discrepancy_qty < 0)
                                    <span class="text-danger">{{ $execution->discrepancy_qty }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $accuracy = $execution->getAccuracyPercentage();
                                    $badgeClass = $accuracy >= 95 ? 'success' : ($accuracy >= 80 ? 'warning' : 'danger');
                                @endphp
                                <span class="badge bg-{{ $badgeClass }}">
                                    {{ number_format($accuracy, 2) }}%
                                </span>
                            </td>
                            <td>
                                @if($execution->status === 'on_time')
                                    <span class="badge bg-success">On Time</span>
                                @elseif($execution->status === 'late')
                                    <span class="badge bg-danger">Late</span>
                                @elseif($execution->status === 'early')
                                    <span class="badge bg-info">Early</span>
                                @endif
                            </td>
                            <td>{{ $execution->executedByUser->name }}</td>
                            <td>
                                <a href="{{ route('admin.spareparts.opname.executions.show', $execution) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">No executions found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $executions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

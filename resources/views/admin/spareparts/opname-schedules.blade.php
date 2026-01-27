@extends('layouts.admin')

@section('page-title', 'Stock Opname Schedules - Spareparts')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Stock Opname Schedules - Spareparts</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.spareparts.index') }}">Spareparts</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.spareparts.opname.dashboard') }}">Opname Dashboard</a></li>
                <li class="breadcrumb-item active">Schedules</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Opname Schedules</h5>
            <a href="{{ route('admin.spareparts.opname.schedules.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Schedule
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Schedule Code</th>
                            <th>Sparepart</th>
                            <th>Frequency</th>
                            <th>Scheduled Date</th>
                            <th>Assigned To</th>
                            <th>Executions</th>
                            <th>Missed Count</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $schedule)
                        <tr class="{{ $schedule->isOverdue() ? 'table-warning' : '' }}">
                            <td>
                                <strong>{{ $schedule->schedule_code }}</strong>
                                @if($schedule->isOverdue())
                                    <br><span class="badge bg-danger">Overdue</span>
                                @endif
                            </td>
                            <td>
                                @if($schedule->sparepart_id)
                                    {{ $schedule->sparepart->sparepart_name }}<br>
                                    <small class="text-muted">{{ $schedule->sparepart->sparepart_id }}</small>
                                @else
                                    <span class="text-muted">All Spareparts</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ ucfirst($schedule->frequency) }}</span>
                            </td>
                            <td>
                                {{ $schedule->scheduled_date->format('d M Y') }}
                                @if($schedule->isOverdue())
                                    <br><small class="text-danger">{{ $schedule->scheduled_date->diffForHumans() }}</small>
                                @endif
                            </td>
                            <td>{{ $schedule->assignedUser->name }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $schedule->execution_count }}</span>
                            </td>
                            <td>
                                @if($schedule->missed_count > 0)
                                    <span class="badge bg-danger">{{ $schedule->missed_count }}</span>
                                @else
                                    <span class="badge bg-success">0</span>
                                @endif
                            </td>
                            <td>
                                @if($schedule->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.spareparts.opname.schedules.show', $schedule) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">No schedules found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $schedules->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

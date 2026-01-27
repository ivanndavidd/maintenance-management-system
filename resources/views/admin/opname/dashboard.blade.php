@extends('layouts.admin')

@section('page-title', 'Stock Opname Dashboard')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Stock Opname Dashboard</h2>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Active Schedules</h6>
                            <h2 class="mb-0">{{ $stats['total_schedules'] }}</h2>
                        </div>
                        <i class="fas fa-calendar-alt fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Overdue Schedules</h6>
                            <h2 class="mb-0">{{ $stats['overdue_schedules'] }}</h2>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Total Executions</h6>
                            <h2 class="mb-0">{{ $stats['total_executions'] }}</h2>
                        </div>
                        <i class="fas fa-clipboard-check fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Average Accuracy</h6>
                            <h2 class="mb-0">{{ $stats['average_accuracy'] }}%</h2>
                        </div>
                        <i class="fas fa-chart-line fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                    <h5>Missed Executions</h5>
                    <h3 class="text-danger">{{ $stats['missed_executions'] }}</h3>
                    <small class="text-muted">More than 7 days late</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Executions -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Executions</h5>
                    <a href="{{ route('admin.opname.executions.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Accuracy</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentExecutions as $execution)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.opname.executions.show', $execution) }}">
                                            {{ $execution->execution_code }}
                                        </a>
                                        @if($execution->is_missed)
                                            <span class="badge bg-danger badge-sm">Missed</span>
                                        @endif
                                    </td>
                                    <td>{{ $execution->execution_date ? $execution->execution_date->format('d M Y') : '-' }}</td>
                                    <td>
                                        @if($execution->status === 'on_time')
                                            <span class="badge bg-success">On Time</span>
                                        @elseif($execution->status === 'late')
                                            <span class="badge bg-danger">Late</span>
                                        @elseif($execution->status === 'early')
                                            <span class="badge bg-info">Early</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ number_format($execution->getAccuracyPercentage(), 2) }}%</strong>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No executions found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Schedules -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Upcoming Schedules</h5>
                    <a href="{{ route('admin.opname.schedules.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Items</th>
                                    <th>End Date</th>
                                    <th>Assigned To</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingSchedules as $schedule)
                                <tr class="{{ $schedule->isOverdue() ? 'table-warning' : '' }}">
                                    <td>
                                        <a href="{{ route('admin.opname.schedules.show', $schedule) }}">
                                            {{ $schedule->schedule_code }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $schedule->total_items }} items</span>
                                    </td>
                                    <td>
                                        {{ $schedule->execution_date->format('d M Y') }}
                                        @if($schedule->isOverdue())
                                            <br><small class="text-danger">Overdue by {{ abs($schedule->getDaysRemaining()) }} days</small>
                                        @elseif($schedule->isDueSoon())
                                            <br><small class="text-warning">Due in {{ $schedule->getDaysRemaining() }} days</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($schedule->userAssignments->count() > 0)
                                            {{ $schedule->userAssignments->first()->user->name }}
                                            @if($schedule->userAssignments->count() > 1)
                                                <small class="text-muted">+{{ $schedule->userAssignments->count() - 1 }} more</small>
                                            @endif
                                        @else
                                            <span class="text-muted">No assignment</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No upcoming schedules</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('admin.opname.schedules.create') }}" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-calendar-plus"></i> Create Schedule
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.opname.executions.create') }}" class="btn btn-success w-100 mb-2">
                                <i class="fas fa-clipboard-check"></i> Record Execution
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.opname.reports.compliance') }}" class="btn btn-info w-100 mb-2">
                                <i class="fas fa-chart-bar"></i> Compliance Report
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.opname.reports.accuracy') }}" class="btn btn-warning w-100 mb-2">
                                <i class="fas fa-chart-pie"></i> Accuracy Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

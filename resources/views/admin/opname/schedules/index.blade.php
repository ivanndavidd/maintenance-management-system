@extends('layouts.admin')

@section('page-title', 'Stock Opname Schedules')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-calendar-alt"></i> Stock Opname Schedules</h2>
        <a href="{{ route($routePrefix.'.opname.schedules.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Schedule
        </a>
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route($routePrefix.'.opname.schedules.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Item Type</label>
                    <select name="item_type_filter" class="form-select">
                        <option value="">All Types</option>
                        <option value="spareparts" {{ request('item_type_filter') == 'spareparts' ? 'selected' : '' }}>Spareparts</option>
                        <option value="tools" {{ request('item_type_filter') == 'tools' ? 'selected' : '' }}>Tools</option>
                        <option value="assets" {{ request('item_type_filter') == 'assets' ? 'selected' : '' }}>Assets</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route($routePrefix.'.opname.schedules.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Schedule List --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list"></i> Schedule List</h5>
        </div>
        <div class="card-body">
            @if($schedules->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Schedule Code</th>
                                <th>Item Types</th>
                                <th>Execution Date</th>
                                <th>Total Items</th>
                                <th>Progress</th>
                                <th>Assigned Users</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schedules as $schedule)
                            <tr class="{{ $schedule->isOverdue() ? 'table-danger' : ($schedule->isDueSoon() ? 'table-warning' : '') }}">
                                <td>
                                    <strong>{{ $schedule->schedule_code }}</strong>
                                    @if($schedule->isOverdue())
                                        <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Overdue</small>
                                    @elseif($schedule->isDueSoon())
                                        <br><small class="text-warning"><i class="fas fa-clock"></i> Due Soon</small>
                                    @endif
                                </td>
                                <td>
                                    @if($schedule->include_spareparts)
                                        <span class="badge bg-info me-1">Spareparts</span>
                                    @endif
                                    @if($schedule->include_tools)
                                        <span class="badge bg-warning me-1">Tools</span>
                                    @endif
                                    @if($schedule->include_assets)
                                        <span class="badge bg-success me-1">Assets</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $executionDate = $schedule->getExecutionDate();
                                    @endphp
                                    @if($executionDate)
                                        <strong>{{ $executionDate->format('d M Y') }}</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $schedule->total_items }} items</span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar {{ $schedule->getProgressPercentage() == 100 ? 'bg-success' : 'bg-primary' }}"
                                            role="progressbar"
                                            style="width: {{ $schedule->getProgressPercentage() }}%">
                                            {{ $schedule->getProgressPercentage() }}%
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        {{ $schedule->completed_items }}/{{ $schedule->total_items }} completed
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        <i class="fas fa-users"></i> {{ $schedule->userAssignments->count() }} users
                                    </span>
                                </td>
                                <td>
                                    @if($schedule->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif($schedule->status === 'in_progress')
                                        <span class="badge bg-warning">In Progress</span>
                                    @elseif($schedule->status === 'completed')
                                        <span class="badge bg-primary">Completed</span>
                                    @else
                                        <span class="badge bg-secondary">Cancelled</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route($routePrefix.'.opname.schedules.show', $schedule) }}"
                                        class="btn btn-sm btn-info"
                                        title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($schedule->status !== 'completed')
                                    <a href="{{ route($routePrefix.'.opname.schedules.edit', $schedule) }}"
                                        class="btn btn-sm btn-warning"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-center mt-3">
                    {{ $schedules->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No schedules found.
                    <a href="{{ route($routePrefix.'.opname.schedules.create') }}">Create your first schedule</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

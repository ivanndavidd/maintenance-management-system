@extends('layouts.admin')

@section('page-title', 'My PM Tasks')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-calendar-check"></i> My PM Tasks</h4>
            <p class="text-muted mb-0">View your assigned preventive maintenance tasks</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Filter by Month</label>
                    <input type="month" name="scheduled_month" class="form-control" value="{{ request('scheduled_month') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('supervisor.my-tasks.preventive-maintenance') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Schedules Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Month</th>
                            <th>Title</th>
                            <th>Dates</th>
                            <th>Total Tasks</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $schedule)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($schedule->scheduled_month)->format('F Y') }}</td>
                                <td>{{ $schedule->title }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $schedule->scheduleDates->count() }} Dates</span>
                                </td>
                                <td>
                                    @php
                                        // Count all my tasks from this schedule
                                        $myTasks = collect();
                                        foreach ($schedule->scheduleDates as $date) {
                                            foreach ($date->cleaningGroups as $group) {
                                                foreach ($group->sprGroups as $spr) {
                                                    $myTasks = $myTasks->merge($spr->tasks);
                                                }
                                            }
                                            $myTasks = $myTasks->merge($date->standaloneTasks);
                                        }
                                        $totalTasks = $myTasks->count();
                                        $completedTasks = $myTasks->where('status', 'completed')->count();
                                        $progress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
                                    @endphp
                                    <span class="badge bg-secondary">{{ $totalTasks }} Tasks</span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%">
                                            {{ $progress }}%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $schedule->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($schedule->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('supervisor.my-tasks.preventive-maintenance.show', $schedule->id) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    No PM tasks assigned to you
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($schedules->hasPages())
            <div class="card-footer">
                {{ $schedules->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

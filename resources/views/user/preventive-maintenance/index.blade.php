@extends('layouts.user')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-calendar-check"></i> Preventive Maintenance</h4>
            <p class="text-muted mb-0">View preventive maintenance schedules and tasks</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Filter by Month</label>
                    <input type="month" name="month" class="form-control" value="{{ request('month') }}">
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
                    <a href="{{ route('user.preventive-maintenance.index') }}" class="btn btn-outline-secondary">
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
                            <th width="80">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $schedule)
                            @php $stats = $schedule->task_stats; @endphp
                            <tr>
                                <td><span class="fw-bold">{{ $schedule->scheduled_month->format('F Y') }}</span></td>
                                <td>
                                    {{ $schedule->title ?: '-' }}
                                    @if($schedule->description)
                                        <br><small class="text-muted">{{ Str::limit($schedule->description, 50) }}</small>
                                    @endif
                                </td>
                                <td><span class="badge bg-info">{{ $schedule->scheduleDates->count() }} Dates</span></td>
                                <td>
                                    <span class="badge bg-secondary">{{ $stats['total'] }} Tasks</span>
                                    @if($stats['completed'] > 0)
                                        <br><small class="text-success">{{ $stats['completed'] }} completed</small>
                                    @endif
                                </td>
                                <td style="min-width: 150px;">
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-success" style="width: {{ $stats['progress'] }}%">
                                            {{ $stats['progress'] }}%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $schedule->status_badge }}">{{ ucfirst($schedule->status) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('user.preventive-maintenance.show', $schedule) }}" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                        <p>No preventive maintenance schedules found.</p>
                                    </div>
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

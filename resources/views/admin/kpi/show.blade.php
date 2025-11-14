@extends('layouts.admin')

@section('title', 'KPI Details - ' . $user->name)

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.kpi.index') }}">KPI Management</a></li>
                    <li class="breadcrumb-item active">{{ $user->name }}</li>
                </ol>
            </nav>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">KPI Details: {{ $user->name }}</h1>
                    <p class="text-muted mb-0">{{ $user->employee_id }} | {{ $user->department->name ?? 'No Department' }}
                    </p>
                </div>
                <a href="{{ route('admin.kpi.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to KPI List
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-left-primary">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Jobs</div>
                        <div class="h5 mb-0 font-weight-bold">{{ $totalJobs }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-left-success">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">On Time Jobs</div>
                        <div class="h5 mb-0 font-weight-bold">{{ $onTimeJobs }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-left-info">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Early Jobs</div>
                        <div class="h5 mb-0 font-weight-bold">{{ $earlyJobs }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-left-danger">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Late Jobs</div>
                        <div class="h5 mb-0 font-weight-bold">{{ $lateJobs }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="font-weight-bold text-primary mb-3">On-Time Rate</h6>
                        <div class="d-flex align-items-center mb-2">
                            <div class="progress flex-grow-1 me-3" style="height: 25px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $onTimeRate }}%">
                                    {{ $onTimeRate }}%
                                </div>
                            </div>
                            <strong>{{ $onTimeRate }}%</strong>
                        </div>
                        <small class="text-muted">Percentage of jobs completed on time or early</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="font-weight-bold text-primary mb-3">Average Days Late</h6>
                        <div class="mb-2">
                            <h3 class="mb-0 {{ $avgDaysLate > 0 ? 'text-danger' : 'text-success' }}">
                                @if ($avgDaysLate > 0)
                                    {{ round($avgDaysLate, 1) }} days
                                @else
                                    No late jobs
                                @endif
                            </h3>
                        </div>
                        <small class="text-muted">Average delay for late completions</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Trend -->
        @if ($monthlyTrend->count() > 0)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Trend (Last 6 Months)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Month</th>
                                    <th>Total Jobs</th>
                                    <th>Late Jobs</th>
                                    <th>On-Time Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($monthlyTrend as $trend)
                                    @php
                                        $rate = $trend->total > 0 ? round((($trend->total - $trend->late_count) / $trend->total) * 100, 1) : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($trend->month . '-01')->format('F Y') }}</td>
                                        <td><span class="badge bg-secondary">{{ $trend->total }}</span></td>
                                        <td><span class="badge bg-danger">{{ $trend->late_count }}</span></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="me-2">{{ $rate }}%</span>
                                                <div class="progress" style="height: 8px; width: 100px;">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                        style="width: {{ $rate }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Filter Panel -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.kpi.show', $user->id) }}" class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Job Type</label>
                        <select name="job_type" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach ($types as $type)
                                <option value="{{ $type }}" {{ request('job_type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm"
                            value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm"
                            value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm me-2">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.kpi.show', $user->id) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Completion Logs -->
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary">Job Completion History</h6>
            </div>
            <div class="card-body">
                @if ($logs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Job Code</th>
                                    <th>Job Title</th>
                                    <th>Type</th>
                                    <th>Priority</th>
                                    <th>Scheduled Date</th>
                                    <th>Completed At</th>
                                    <th>Status</th>
                                    <th>Days Late</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($logs as $log)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.jobs.show', $log->job_id) }}"
                                                class="text-decoration-none">
                                                {{ $log->job_code }}
                                            </a>
                                        </td>
                                        <td>{{ $log->job_title }}</td>
                                        <td>
                                            @php
                                                $typeColor = match ($log->job_type) {
                                                    'preventive' => 'primary',
                                                    'corrective' => 'warning',
                                                    'predictive' => 'info',
                                                    'breakdown' => 'danger',
                                                    default => 'secondary',
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $typeColor }}">{{ ucfirst($log->job_type) }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $priorityColor = match ($log->priority) {
                                                    'urgent' => 'danger',
                                                    'high' => 'warning',
                                                    'medium' => 'info',
                                                    'low' => 'secondary',
                                                    default => 'secondary',
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $priorityColor }}">
                                                {{ ucfirst($log->priority) }}
                                            </span>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($log->scheduled_date)->format('d M Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($log->completed_at)->format('d M Y H:i') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $log->status_badge }}">
                                                {{ ucfirst(str_replace('_', ' ', $log->completion_status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if ($log->days_late > 0)
                                                <span class="text-danger font-weight-bold">+{{ $log->days_late }}
                                                    days</span>
                                            @elseif($log->days_late < 0)
                                                <span class="text-info font-weight-bold">{{ $log->days_late }} days</span>
                                            @else
                                                <span class="text-success font-weight-bold">On time</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted small">
                            Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }}
                            entries
                        </div>
                        {{ $logs->appends(request()->except('page'))->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No completion logs found for the selected filters</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .border-left-primary {
            border-left: 4px solid #4e73df !important;
        }

        .border-left-success {
            border-left: 4px solid #1cc88a !important;
        }

        .border-left-info {
            border-left: 4px solid #36b9cc !important;
        }

        .border-left-danger {
            border-left: 4px solid #dc3545 !important;
        }

        .text-xs {
            font-size: 0.7rem;
        }
    </style>
@endsection

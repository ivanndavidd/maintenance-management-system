@extends('layouts.admin')

@section('title', 'KPI Management')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">KPI Management</h1>
                <p class="text-muted mb-0">Monitor user performance and job completion metrics</p>
            </div>
        </div>

        <!-- Filter Panel -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.kpi.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                        <a href="{{ route('admin.kpi.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-left-primary">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Users</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $users->count() }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-left-success">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Jobs Completed</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $users->sum('total_jobs') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-left-warning">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Late Jobs</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $users->sum('late_jobs') }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-left-info">
                    <div class="card-body">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Avg On-Time Rate</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            @php
                                $totalJobs = $users->sum('total_jobs');
                                $totalOnTime = $users->sum('on_time_jobs') + $users->sum('early_jobs');
                                $avgRate = $totalJobs > 0 ? round(($totalOnTime / $totalJobs) * 100, 1) : 0;
                            @endphp
                            {{ $avgRate }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users KPI Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary">User Performance Metrics</h6>
            </div>
            <div class="card-body">
                @if ($users->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Department</th>
                                    <th>Total Jobs</th>
                                    <th>On Time</th>
                                    <th>Early</th>
                                    <th>Late</th>
                                    <th>On-Time Rate</th>
                                    <th>Late Rate</th>
                                    <th>Avg Days Late</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-primary text-white me-2">
                                                    {{ $item['user']->initials }}
                                                </div>
                                                <div>
                                                    <strong>{{ $item['user']->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $item['user']->employee_id }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $item['user']->department->name ?? '-' }}</td>
                                        <td><span class="badge bg-secondary">{{ $item['total_jobs'] }}</span></td>
                                        <td><span class="badge bg-success">{{ $item['on_time_jobs'] }}</span></td>
                                        <td><span class="badge bg-info">{{ $item['early_jobs'] }}</span></td>
                                        <td><span class="badge bg-danger">{{ $item['late_jobs'] }}</span></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="me-2">{{ $item['on_time_rate'] }}%</span>
                                                <div class="progress flex-grow-1" style="height: 8px; width: 100px;">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                        style="width: {{ $item['on_time_rate'] }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="me-2">{{ $item['late_rate'] }}%</span>
                                                <div class="progress flex-grow-1" style="height: 8px; width: 100px;">
                                                    <div class="progress-bar bg-danger" role="progressbar"
                                                        style="width: {{ $item['late_rate'] }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($item['avg_days_late'] > 0)
                                                <span class="text-danger">{{ $item['avg_days_late'] }} days</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.kpi.show', $item['user']->id) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> Details
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No KPI data available for the selected period</p>
                        <a href="{{ route('admin.kpi.index') }}" class="btn btn-sm btn-primary">Clear Filters</a>
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

        .border-left-warning {
            border-left: 4px solid #f6c23e !important;
        }

        .border-left-info {
            border-left: 4px solid #36b9cc !important;
        }

        .avatar-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        .text-xs {
            font-size: 0.7rem;
        }
    </style>
@endsection

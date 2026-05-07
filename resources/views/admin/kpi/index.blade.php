@extends('layouts.admin')

@section('title', 'KPI Management')
@section('page-title', 'KPI Management')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="h3 mb-0">KPI Management</h5>
                <p class="text-muted mb-0">Monitor user performance across PM, CM, and Stock Opname tasks</p>
            </div>
        </div>

        <!-- Filter Panel -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route($routePrefix.'.kpi.index') }}" class="row g-3">
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
            <i class="fas fa-filter"></i><span class="btn-text"> Apply Filter</span>
                        </button>
                        <a href="{{ route($routePrefix.'.kpi.index') }}" class="btn btn-secondary">
            <i class="fas fa-times"></i><span class="btn-text"> Clear</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4 g-2">
            <div class="col-6 col-md-3">
                <div class="card shadow-sm border-left-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-clipboard-check fa-2x text-primary opacity-50"></i>
                            </div>
                            <div>
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">PM Tasks</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalPm }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card shadow-sm border-left-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-wrench fa-2x text-warning opacity-50"></i>
                            </div>
                            <div>
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">CM Tickets</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalCm }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card shadow-sm border-left-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-boxes-stacked fa-2x text-info opacity-50"></i>
                            </div>
                            <div>
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Stock Opname</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalSo }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card shadow-sm border-left-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-check-double fa-2x text-success opacity-50"></i>
                            </div>
                            <div>
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Completed</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalAll }}</div>
                            </div>
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
                                    <th class="d-none d-md-table-cell">#</th>
                                    <th>User</th>
                                    <th class="d-none d-md-table-cell">Department</th>
                                    <th class="text-center">PM</th>
                                    <th class="text-center">CM</th>
                                    <th class="text-center d-none d-md-table-cell">SO</th>
                                    <th class="text-center d-none d-md-table-cell">Total</th>
                                    <th class="d-none d-lg-table-cell">Rate</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $index => $item)
                                    <tr>
                                        <td class="d-none d-md-table-cell">{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-primary text-white me-2 flex-shrink-0">
                                                    {{ $item['user']->initials }}
                                                </div>
                                                <div>
                                                    <strong>{{ $item['user']->name }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $item['user']->employee_id }}</small>
                                                    <div class="d-md-none">
                                                        <span class="badge bg-success" style="font-size:10px;">{{ $item['completion_rate'] }}%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="d-none d-md-table-cell">{{ $item['user']->department->name ?? '-' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ $item['pm_count'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning text-dark">{{ $item['cm_count'] }}</span>
                                        </td>
                                        <td class="text-center d-none d-md-table-cell">
                                            <span class="badge bg-info">{{ $item['so_count'] }}</span>
                                        </td>
                                        <td class="text-center d-none d-md-table-cell">
                                            <span class="badge bg-success">{{ $item['total_completed'] }}</span>
                                        </td>
                                        <td class="d-none d-lg-table-cell">
                                            <div class="d-flex align-items-center">
                                                <span class="me-2">{{ $item['completion_rate'] }}%</span>
                                                <div class="progress flex-grow-1" style="height: 8px; width: 80px;">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                        style="width: {{ $item['completion_rate'] }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route($routePrefix.'.kpi.show', $item['user']->id) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
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
                        <a href="{{ route($routePrefix.'.kpi.index') }}" class="btn btn-sm btn-primary">Clear Filters</a>
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

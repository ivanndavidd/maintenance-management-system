@extends('layouts.admin')

@section('title', 'KPI Details - ' . $user->name)
@section('page-title', 'KPI Details - ' . $user->name)

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
                    <p class="text-muted mb-0">{{ $user->employee_id }} | {{ $user->department->name ?? 'No Department' }}</p>
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
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-clipboard-check fa-2x text-primary opacity-50"></i>
                            </div>
                            <div>
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">PM Tasks</div>
                                <div class="h5 mb-0 font-weight-bold">{{ $pmCount }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-left-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-wrench fa-2x text-warning opacity-50"></i>
                            </div>
                            <div>
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">CM Tickets</div>
                                <div class="h5 mb-0 font-weight-bold">{{ $cmCount }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-left-info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-boxes-stacked fa-2x text-info opacity-50"></i>
                            </div>
                            <div>
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Stock Opname</div>
                                <div class="h5 mb-0 font-weight-bold">{{ $soCount }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-left-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-check-double fa-2x text-success opacity-50"></i>
                            </div>
                            <div>
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Completed</div>
                                <div class="h5 mb-0 font-weight-bold">{{ $totalCompleted }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Panel -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.kpi.show', $user->id) }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm me-2">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.kpi.show', $user->id) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Monthly Trend -->
        @if ($monthlyTrend->sum('total') > 0)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Trend (Last 6 Months)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Month</th>
                                    <th class="text-center">PM Tasks</th>
                                    <th class="text-center">CM Tickets</th>
                                    <th class="text-center">Stock Opname</th>
                                    <th class="text-center">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($monthlyTrend as $trend)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($trend['month'] . '-01')->format('F Y') }}</td>
                                        <td class="text-center"><span class="badge bg-primary">{{ $trend['pm'] }}</span></td>
                                        <td class="text-center"><span class="badge bg-warning text-dark">{{ $trend['cm'] }}</span></td>
                                        <td class="text-center"><span class="badge bg-info">{{ $trend['so'] }}</span></td>
                                        <td class="text-center"><span class="badge bg-success">{{ $trend['total'] }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Tabs for PM / CM / SO -->
        <ul class="nav nav-tabs mb-0" id="kpiTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pm-tab" data-bs-toggle="tab" data-bs-target="#pm" type="button" role="tab">
                    <i class="fas fa-clipboard-check text-primary"></i> PM Tasks <span class="badge bg-primary">{{ $pmCount }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="cm-tab" data-bs-toggle="tab" data-bs-target="#cm" type="button" role="tab">
                    <i class="fas fa-wrench text-warning"></i> CM Tickets <span class="badge bg-warning text-dark">{{ $cmCount }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="so-tab" data-bs-toggle="tab" data-bs-target="#so" type="button" role="tab">
                    <i class="fas fa-boxes-stacked text-info"></i> Stock Opname <span class="badge bg-info">{{ $soCount }}</span>
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- PM Tasks Tab -->
            <div class="tab-pane fade show active" id="pm" role="tabpanel">
                <div class="card shadow-sm border-top-0" style="border-top-left-radius: 0; border-top-right-radius: 0;">
                    <div class="card-body">
                        @if ($pmTasks->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Task Name</th>
                                            <th>Equipment</th>
                                            <th>Frequency</th>
                                            <th>Task Date</th>
                                            <th>Completed At</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pmTasks as $task)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td><strong>{{ $task->task_name }}</strong></td>
                                                <td>{{ $task->equipment_type ?? '-' }}</td>
                                                <td><span class="badge bg-secondary">{{ $task->frequency_label }}</span></td>
                                                <td>{{ $task->task_date ? $task->task_date->format('d M Y') : '-' }}</td>
                                                <td>{{ $task->completed_at ? $task->completed_at->format('d M Y H:i') : '-' }}</td>
                                                <td>{{ Str::limit($task->completion_notes, 50) ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-clipboard-check fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">No PM tasks completed</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- CM Tickets Tab -->
            <div class="tab-pane fade" id="cm" role="tabpanel">
                <div class="card shadow-sm border-top-0" style="border-top-left-radius: 0; border-top-right-radius: 0;">
                    <div class="card-body">
                        @if ($cmTickets->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Ticket Number</th>
                                            <th>Equipment</th>
                                            <th>Category</th>
                                            <th>Priority</th>
                                            <th>Completed At</th>
                                            <th>Resolution</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($cmTickets as $ticket)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td><strong>{{ $ticket->ticket_number }}</strong></td>
                                                <td>{{ $ticket->equipment_name ?? '-' }}</td>
                                                <td>
                                                    <span class="badge {{ $ticket->getProblemCategoryBadgeClass() }}">
                                                        {{ $ticket->getProblemCategoryLabel() }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $ticket->getPriorityBadgeClass() }}">
                                                        {{ ucfirst($ticket->priority) }}
                                                    </span>
                                                </td>
                                                <td>{{ $ticket->completed_at ? $ticket->completed_at->format('d M Y H:i') : '-' }}</td>
                                                <td>{{ Str::limit($ticket->resolution, 50) ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-wrench fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">No CM tickets completed</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Stock Opname Tab -->
            <div class="tab-pane fade" id="so" role="tabpanel">
                <div class="card shadow-sm border-top-0" style="border-top-left-radius: 0; border-top-right-radius: 0;">
                    <div class="card-body">
                        @if ($soItems->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Item Name</th>
                                            <th>Item Type</th>
                                            <th>System Qty</th>
                                            <th>Physical Qty</th>
                                            <th>Discrepancy</th>
                                            <th>Executed At</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($soItems as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td><strong>{{ $item->getItemName() }}</strong></td>
                                                <td><span class="badge bg-secondary">{{ ucfirst($item->item_type) }}</span></td>
                                                <td>{{ $item->system_quantity ?? '-' }}</td>
                                                <td>{{ $item->physical_quantity ?? '-' }}</td>
                                                <td>
                                                    @if ($item->discrepancy_qty != 0)
                                                        <span class="text-{{ $item->discrepancy_qty > 0 ? 'success' : 'danger' }} fw-bold">
                                                            {{ $item->discrepancy_qty > 0 ? '+' : '' }}{{ $item->discrepancy_qty }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">0</span>
                                                    @endif
                                                </td>
                                                <td>{{ $item->executed_at ? $item->executed_at->format('d M Y H:i') : '-' }}</td>
                                                <td>{{ Str::limit($item->notes, 50) ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-boxes-stacked fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">No stock opname items executed</p>
                            </div>
                        @endif
                    </div>
                </div>
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

        .text-xs {
            font-size: 0.7rem;
        }

        .nav-tabs .nav-link {
            color: #6c757d;
        }

        .nav-tabs .nav-link.active {
            font-weight: 600;
        }
    </style>
@endsection

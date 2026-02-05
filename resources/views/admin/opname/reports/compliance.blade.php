@extends('layouts.admin')

@section('page-title', 'Compliance Report')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Stock Opname Compliance Report</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.opname.dashboard') }}">Opname Dashboard</a></li>
                <li class="breadcrumb-item active">Compliance Report</li>
            </ol>
        </nav>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h6 class="text-uppercase">On Time</h6>
                    <h2>{{ $stats['on_time'] }}</h2>
                    <small>Executions</small>
                    <br>
                    <small class="opacity-75">
                        {{ $executions->total() > 0 ? round(($stats['on_time'] / $executions->total()) * 100, 1) : 0 }}%
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h6 class="text-uppercase">Late</h6>
                    <h2>{{ $stats['late'] }}</h2>
                    <small>Executions</small>
                    <br>
                    <small class="opacity-75">
                        {{ $executions->total() > 0 ? round(($stats['late'] / $executions->total()) * 100, 1) : 0 }}%
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6 class="text-uppercase">Early</h6>
                    <h2>{{ $stats['early'] }}</h2>
                    <small>Executions</small>
                    <br>
                    <small class="opacity-75">
                        {{ $executions->total() > 0 ? round(($stats['early'] / $executions->total()) * 100, 1) : 0 }}%
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h6 class="text-uppercase">Missed</h6>
                    <h2>{{ $stats['missed'] }}</h2>
                    <small>7+ Days Late</small>
                    <br>
                    <small class="opacity-75">
                        {{ $executions->total() > 0 ? round(($stats['missed'] / $executions->total()) * 100, 1) : 0 }}%
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Execution History</h5>
        </div>
        <div class="card-body">
            <!-- Filter Section -->
            <form method="GET" action="{{ route($routePrefix.'.opname.reports.compliance') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-2">
                        <label class="form-label">Item Type</label>
                        <select name="item_type" class="form-select">
                            <option value="">All Types</option>
                            <option value="sparepart" {{ request('item_type') == 'sparepart' ? 'selected' : '' }}>Spareparts</option>
                            <option value="tool" {{ request('item_type') == 'tool' ? 'selected' : '' }}>Tools</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="on_time" {{ request('status') == 'on_time' ? 'selected' : '' }}>On Time</option>
                            <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                            <option value="early" {{ request('status') == 'early' ? 'selected' : '' }}>Early</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">From Date</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">To Date</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <a href="{{ route($routePrefix.'.opname.reports.compliance') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            <!-- Active Filters Display -->
            @if(request('item_type') || request('status') || request('date_from') || request('date_to'))
                <div class="mb-3">
                    <small class="text-muted">Active filters:</small>
                    <div class="d-inline-flex gap-2 ms-2">
                        @if(request('item_type'))
                            <span class="badge bg-info">
                                Type: {{ ucfirst(request('item_type')) }}
                                <a href="{{ route($routePrefix.'.opname.reports.compliance', array_filter(request()->except('item_type'))) }}" class="text-white ms-1">×</a>
                            </span>
                        @endif
                        @if(request('status'))
                            <span class="badge bg-info">
                                Status: {{ ucwords(str_replace('_', ' ', request('status'))) }}
                                <a href="{{ route($routePrefix.'.opname.reports.compliance', array_filter(request()->except('status'))) }}" class="text-white ms-1">×</a>
                            </span>
                        @endif
                        @if(request('date_from'))
                            <span class="badge bg-info">
                                From: {{ request('date_from') }}
                                <a href="{{ route($routePrefix.'.opname.reports.compliance', array_filter(request()->except('date_from'))) }}" class="text-white ms-1">×</a>
                            </span>
                        @endif
                        @if(request('date_to'))
                            <span class="badge bg-info">
                                To: {{ request('date_to') }}
                                <a href="{{ route($routePrefix.'.opname.reports.compliance', array_filter(request()->except('date_to'))) }}" class="text-white ms-1">×</a>
                            </span>
                        @endif
                    </div>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Execution Code</th>
                            <th>Item Type</th>
                            <th>Item Name</th>
                            <th>Schedule Code</th>
                            <th>Execution Date</th>
                            <th>Scheduled Date</th>
                            <th>Days Difference</th>
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
                            </td>
                            <td>
                                @if($execution->item_type === 'sparepart')
                                    <span class="badge bg-primary">Sparepart</span>
                                @else
                                    <span class="badge bg-success">Tool</span>
                                @endif
                            </td>
                            <td>
                                {{ $execution->getItemName() }}<br>
                                <small class="text-muted">{{ $execution->getItemCode() }}</small>
                            </td>
                            <td>
                                @if($execution->schedule)
                                    <a href="{{ route($routePrefix.'.opname.schedules.show', $execution->schedule) }}">
                                        {{ $execution->schedule->schedule_code }}
                                    </a>
                                @else
                                    <span class="text-muted">Ad-hoc</span>
                                @endif
                            </td>
                            <td>{{ $execution->execution_date->format('d M Y') }}</td>
                            <td>
                                @if($execution->scheduled_date)
                                    {{ $execution->scheduled_date->format('d M Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($execution->scheduled_date)
                                    @if($execution->days_difference == 0)
                                        <span class="badge bg-success">Same day</span>
                                    @else
                                        {{ $execution->days_difference }} days
                                    @endif
                                @else
                                    -
                                @endif
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
                                <a href="{{ route($routePrefix.'.opname.executions.show', $execution) }}" class="btn btn-sm btn-info">
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

            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div>
                    Showing {{ $executions->firstItem() ?? 0 }} to {{ $executions->lastItem() ?? 0 }} of {{ $executions->total() }} executions
                </div>
                <div>
                    {{ $executions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('page-title', 'Compliance Report - Spareparts')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Stock Opname Compliance Report - Spareparts</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.spareparts.index') }}">Spareparts</a></li>
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.spareparts.opname.dashboard') }}">Opname Dashboard</a></li>
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
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <h6 class="text-uppercase">Late</h6>
                    <h2>{{ $stats['late'] }}</h2>
                    <small>Executions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6 class="text-uppercase">Early</h6>
                    <h2>{{ $stats['early'] }}</h2>
                    <small>Executions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h6 class="text-uppercase">Missed</h6>
                    <h2>{{ $stats['missed'] }}</h2>
                    <small>7+ Days Late</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Execution History</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Execution Code</th>
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
                                @if($execution->schedule)
                                    <a href="{{ route($routePrefix.'.spareparts.opname.schedules.show', $execution->schedule) }}">
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
                                <a href="{{ route($routePrefix.'.spareparts.opname.executions.show', $execution) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No executions found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $executions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

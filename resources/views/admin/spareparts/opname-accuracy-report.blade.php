@extends('layouts.admin')

@section('page-title', 'Accuracy Report - Spareparts')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Stock Opname Accuracy Report - Spareparts</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.spareparts.index') }}">Spareparts</a></li>
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.spareparts.opname.dashboard') }}">Opname Dashboard</a></li>
                <li class="breadcrumb-item active">Accuracy Report</li>
            </ol>
        </nav>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6 class="text-uppercase">Average Accuracy</h6>
                    <h1>{{ $stats['average_accuracy'] }}%</h1>
                    <small>Overall Performance</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h6 class="text-uppercase">Total Discrepancy Value</h6>
                    <h3>Rp {{ number_format($stats['total_discrepancy_value'], 0, ',', '.') }}</h3>
                    <small>Financial Impact</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6 class="text-uppercase">Items with Discrepancy</h6>
                    <h1>{{ $stats['items_with_discrepancy'] }}</h1>
                    <small>Total Items</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Accuracy Details</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Execution Code</th>
                            <th>Execution Date</th>
                            <th>System Qty</th>
                            <th>Physical Qty</th>
                            <th>Discrepancy</th>
                            <th>Value Impact</th>
                            <th>Accuracy</th>
                            <th>Executed By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($executions as $execution)
                        <tr>
                            <td>
                                <strong>{{ $execution->execution_code }}</strong>
                                @if($execution->hasDiscrepancy())
                                    <br><span class="badge bg-warning">Has Discrepancy</span>
                                @endif
                            </td>
                            <td>{{ $execution->execution_date->format('d M Y') }}</td>
                            <td>{{ $execution->system_quantity }}</td>
                            <td>{{ $execution->physical_quantity }}</td>
                            <td>
                                @if($execution->discrepancy_qty > 0)
                                    <span class="text-success">+{{ $execution->discrepancy_qty }}</span>
                                @elseif($execution->discrepancy_qty < 0)
                                    <span class="text-danger">{{ $execution->discrepancy_qty }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td>
                                @if($execution->discrepancy_value != 0)
                                    <span class="{{ $execution->discrepancy_value > 0 ? 'text-success' : 'text-danger' }}">
                                        Rp {{ number_format(abs($execution->discrepancy_value), 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $accuracy = $execution->getAccuracyPercentage();
                                    $badgeClass = $accuracy >= 95 ? 'success' : ($accuracy >= 80 ? 'warning' : 'danger');
                                @endphp
                                <span class="badge bg-{{ $badgeClass }}">
                                    {{ number_format($accuracy, 2) }}%
                                </span>
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
                            <td colspan="9" class="text-center">No executions found</td>
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

    <!-- Accuracy Distribution Chart -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Accuracy Distribution</h5>
                </div>
                <div class="card-body">
                    @php
                        $excellent = $executions->filter(function($e) { return $e->getAccuracyPercentage() >= 95; })->count();
                        $good = $executions->filter(function($e) { $a = $e->getAccuracyPercentage(); return $a >= 80 && $a < 95; })->count();
                        $poor = $executions->filter(function($e) { return $e->getAccuracyPercentage() < 80; })->count();
                    @endphp
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="alert alert-success">
                                <h2>{{ $excellent }}</h2>
                                <p class="mb-0">Excellent (≥ 95%)</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-warning">
                                <h2>{{ $good }}</h2>
                                <p class="mb-0">Good (80-94%)</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-danger">
                                <h2>{{ $poor }}</h2>
                                <p class="mb-0">Poor (< 80%)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('page-title', 'Accuracy Report')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Stock Opname Accuracy Report</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.opname.dashboard') }}">Opname Dashboard</a></li>
                <li class="breadcrumb-item active">Accuracy Report</li>
            </ol>
        </nav>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.opname.reports.accuracy') }}" class="mb-0">
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
                        <label class="form-label">Accuracy Level</label>
                        <select name="accuracy_level" class="form-select">
                            <option value="">All Levels</option>
                            <option value="excellent" {{ request('accuracy_level') == 'excellent' ? 'selected' : '' }}>Excellent (≥95%)</option>
                            <option value="good" {{ request('accuracy_level') == 'good' ? 'selected' : '' }}>Good (80-94%)</option>
                            <option value="poor" {{ request('accuracy_level') == 'poor' ? 'selected' : '' }}>Poor (<80%)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Has Discrepancy</label>
                        <select name="has_discrepancy" class="form-select">
                            <option value="">All</option>
                            <option value="yes" {{ request('has_discrepancy') == 'yes' ? 'selected' : '' }}>Yes</option>
                            <option value="no" {{ request('has_discrepancy') == 'no' ? 'selected' : '' }}>No</option>
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
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('admin.opname.reports.accuracy') }}" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Active Filters Display -->
    @if(request('item_type') || request('accuracy_level') || request('has_discrepancy') || request('date_from') || request('date_to'))
        <div class="mb-3">
            <small class="text-muted">Active filters:</small>
            <div class="d-inline-flex gap-2 ms-2 flex-wrap">
                @if(request('item_type'))
                    <span class="badge bg-info">
                        Type: {{ ucfirst(request('item_type')) }}
                        <a href="{{ route('admin.opname.reports.accuracy', array_filter(request()->except('item_type'))) }}" class="text-white text-decoration-none ms-1">×</a>
                    </span>
                @endif
                @if(request('accuracy_level'))
                    <span class="badge bg-info">
                        Level: {{ ucfirst(request('accuracy_level')) }}
                        <a href="{{ route('admin.opname.reports.accuracy', array_filter(request()->except('accuracy_level'))) }}" class="text-white text-decoration-none ms-1">×</a>
                    </span>
                @endif
                @if(request('has_discrepancy'))
                    <span class="badge bg-info">
                        Discrepancy: {{ ucfirst(request('has_discrepancy')) }}
                        <a href="{{ route('admin.opname.reports.accuracy', array_filter(request()->except('has_discrepancy'))) }}" class="text-white text-decoration-none ms-1">×</a>
                    </span>
                @endif
                @if(request('date_from'))
                    <span class="badge bg-info">
                        From: {{ request('date_from') }}
                        <a href="{{ route('admin.opname.reports.accuracy', array_filter(request()->except('date_from'))) }}" class="text-white text-decoration-none ms-1">×</a>
                    </span>
                @endif
                @if(request('date_to'))
                    <span class="badge bg-info">
                        To: {{ request('date_to') }}
                        <a href="{{ route('admin.opname.reports.accuracy', array_filter(request()->except('date_to'))) }}" class="text-white text-decoration-none ms-1">×</a>
                    </span>
                @endif
            </div>
        </div>
    @endif

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6 class="text-uppercase">Average Accuracy</h6>
                    <h1>{{ number_format($stats['average_accuracy'], 1) }}%</h1>
                    <small>Overall Performance</small>
                    <br>
                    <small class="opacity-75">Based on {{ $executions->total() }} executions</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body text-center">
                    <h6 class="text-uppercase">Total Discrepancy Value</h6>
                    <h3>Rp {{ number_format($stats['total_discrepancy_value'], 0, ',', '.') }}</h3>
                    <small>Financial Impact</small>
                    <br>
                    <small class="opacity-75">{{ $stats['items_with_discrepancy'] }} items affected</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h6 class="text-uppercase">Items with Discrepancy</h6>
                    <h1>{{ $stats['items_with_discrepancy'] }}</h1>
                    <small>Total Items</small>
                    <br>
                    <small class="opacity-75">
                        {{ $executions->total() > 0 ? round(($stats['items_with_discrepancy'] / $executions->total()) * 100, 1) : 0 }}% of total
                    </small>
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
                            <th>Item Type</th>
                            <th>Item Name</th>
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
                                    <br><span class="badge bg-warning text-dark">Has Discrepancy</span>
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
                                <strong>{{ $execution->getItemName() }}</strong><br>
                                <small class="text-muted">{{ $execution->getItemCode() }}</small>
                            </td>
                            <td>{{ $execution->execution_date->format('d M Y') }}</td>
                            <td>{{ $execution->system_quantity }}</td>
                            <td>{{ $execution->physical_quantity }}</td>
                            <td>
                                @if($execution->discrepancy_qty > 0)
                                    <span class="text-success fw-bold">+{{ $execution->discrepancy_qty }}</span>
                                @elseif($execution->discrepancy_qty < 0)
                                    <span class="text-danger fw-bold">{{ $execution->discrepancy_qty }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td>
                                @if($execution->discrepancy_value != 0)
                                    <span class="{{ $execution->discrepancy_value > 0 ? 'text-success' : 'text-danger' }} fw-bold">
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
                                <a href="{{ route('admin.opname.executions.show', $execution) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">No executions found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <small class="text-muted">
                        Showing {{ $executions->firstItem() ?? 0 }} to {{ $executions->lastItem() ?? 0 }} of {{ $executions->total() }} results
                    </small>
                </div>
                <div>
                    {{ $executions->links() }}
                </div>
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
                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="alert alert-success mb-0">
                                <h2 class="mb-2">{{ $stats['excellent'] ?? 0 }}</h2>
                                <p class="mb-1"><strong>Excellent (≥ 95%)</strong></p>
                                <small class="text-muted">
                                    {{ $executions->total() > 0 ? round((($stats['excellent'] ?? 0) / $executions->total()) * 100, 1) : 0 }}% of total
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-warning mb-0">
                                <h2 class="mb-2">{{ $stats['good'] ?? 0 }}</h2>
                                <p class="mb-1"><strong>Good (80-94%)</strong></p>
                                <small class="text-muted">
                                    {{ $executions->total() > 0 ? round((($stats['good'] ?? 0) / $executions->total()) * 100, 1) : 0 }}% of total
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-danger mb-0">
                                <h2 class="mb-2">{{ $stats['poor'] ?? 0 }}</h2>
                                <p class="mb-1"><strong>Poor (< 80%)</strong></p>
                                <small class="text-muted">
                                    {{ $executions->total() > 0 ? round((($stats['poor'] ?? 0) / $executions->total()) * 100, 1) : 0 }}% of total
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

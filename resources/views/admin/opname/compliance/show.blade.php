@extends('layouts.admin')

@section('page-title', 'Compliance Report Detail')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="fas fa-file-alt"></i> Compliance Report Detail</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.opname.compliance.index') }}">Compliance Report</a></li>
                        <li class="breadcrumb-item active">{{ $schedule->schedule_code }}</li>
                    </ol>
                </nav>
            </div>
            <div>
                <span class="badge bg-success fs-5">
                    <i class="fas fa-check-circle"></i> CLOSED
                </span>
            </div>
        </div>
    </div>

    <!-- Ticket Information -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Ticket Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th width="180">Ticket Number:</th>
                                    <td><strong>{{ $schedule->schedule_code }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Item Types:</th>
                                    <td>{{ $schedule->getItemTypes() }}</td>
                                </tr>
                                <tr>
                                    <th>Location:</th>
                                    <td>{{ $schedule->getAllLocations() }}</td>
                                </tr>
                                <tr>
                                    <th>Execution Date:</th>
                                    <td>{{ $schedule->execution_date->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Closed Date:</th>
                                    <td><strong>{{ $schedule->closed_at->format('d M Y H:i') }}</strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th width="180">Total Items:</th>
                                    <td><span class="badge bg-info">{{ $schedule->total_items }}</span></td>
                                </tr>
                                <tr>
                                    <th>Total Discrepancy:</th>
                                    <td>
                                        @php
                                            $discrepancy = $schedule->getTotalDiscrepancy();
                                        @endphp
                                        @if($discrepancy > 0)
                                            <span class="badge bg-warning">{{ $discrepancy }} items</span>
                                        @else
                                            <span class="badge bg-success">0 items</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Assigned Users:</th>
                                    <td>{{ $schedule->getAssignedUsersNames() }}</td>
                                </tr>
                                <tr>
                                    <th>Execution Type:</th>
                                    <td>
                                        @if($schedule->execution_type === 'early')
                                            <span class="badge bg-success">
                                                <i class="fas fa-arrow-up"></i> Early ({{ $schedule->days_difference }} days before deadline)
                                            </span>
                                        @elseif($schedule->execution_type === 'ontime')
                                            <span class="badge bg-info">
                                                <i class="fas fa-check"></i> On-Time
                                            </span>
                                        @elseif($schedule->execution_type === 'late')
                                            <span class="badge bg-danger">
                                                <i class="fas fa-arrow-down"></i> Late ({{ $schedule->days_difference }} days after deadline)
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Closed By:</th>
                                    <td>{{ $schedule->closedByUser->name ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Quick Stats</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Accuracy Rate</label>
                        <h3 class="mb-0 text-success">{{ $analytics['discrepancy']['accuracy_rate'] }}%</h3>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Items With Discrepancy</label>
                        <h3 class="mb-0 text-warning">{{ $analytics['discrepancy']['items_with_discrepancy'] }}</h3>
                    </div>
                    <div class="mb-0">
                        <label class="text-muted small">Synced to Stock</label>
                        <h3 class="mb-0">
                            {{ $analytics['sync']['synced'] }}/{{ $analytics['sync']['total_approved_with_discrepancy'] }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Item Accuracy Details -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-clipboard-check"></i> Item Accuracy Details</h5>
        </div>
        <div class="card-body">
            @if($items->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Item Code</th>
                                <th>Item Name</th>
                                <th>Type</th>
                                <th>System Qty</th>
                                <th>Physical Qty</th>
                                <th>Discrepancy</th>
                                <th>Accuracy</th>
                                <th>Executed By</th>
                                <th>Executed At</th>
                                <th>Review Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><code>{{ $item->getItemCode() }}</code></td>
                                <td>{{ $item->getItemName() }}</td>
                                <td>
                                    @if($item->item_type === 'sparepart')
                                        <span class="badge bg-warning">Sparepart</span>
                                    @elseif($item->item_type === 'tool')
                                        <span class="badge bg-info">Tool</span>
                                    @elseif($item->item_type === 'asset')
                                        <span class="badge bg-success">Asset</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $item->system_quantity }}</td>
                                <td class="text-center">{{ $item->physical_quantity }}</td>
                                <td class="text-center">
                                    @if($item->discrepancy_qty > 0)
                                        <span class="badge bg-success">+{{ $item->discrepancy_qty }}</span>
                                    @elseif($item->discrepancy_qty < 0)
                                        <span class="badge bg-danger">{{ $item->discrepancy_qty }}</span>
                                    @else
                                        <span class="badge bg-secondary">0</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->hasDiscrepancy())
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times"></i> Inaccurate
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> Accurate
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $item->executedByUser->name ?? '-' }}</strong>
                                </td>
                                <td>
                                    <small>{{ $item->executed_at ? $item->executed_at->format('d M Y H:i') : '-' }}</small>
                                </td>
                                <td>
                                    @if($item->review_status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($item->review_status === 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @elseif($item->review_status === 'pending_review')
                                        <span class="badge bg-warning">Pending Review</span>
                                    @else
                                        <span class="badge bg-secondary">No Review Needed</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No items found.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Back Button -->
    <div class="mt-4">
        <a href="{{ route('admin.opname.compliance.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Compliance Report List
        </a>
    </div>
</div>
@endsection

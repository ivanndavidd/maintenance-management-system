@extends('layouts.admin')

@section('page-title', 'Compliance Report - Closed Tickets')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="mb-4">
        <h2><i class="fas fa-file-alt"></i> Compliance Report - Closed Tickets</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.opname.dashboard') }}">Stock Opname</a></li>
                <li class="breadcrumb-item active">Compliance Report</li>
            </ol>
        </nav>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h3 class="text-primary mb-0">{{ $stats['total_closed'] }}</h3>
                    <p class="text-muted mb-0">Total Closed Tickets</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success mb-0">{{ $stats['early_completion'] }}</h3>
                    <p class="text-muted mb-0">Early Completion</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info mb-0">{{ $stats['ontime_completion'] }}</h3>
                    <p class="text-muted mb-0">On-Time Completion</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="text-danger mb-0">{{ $stats['late_completion'] }}</h3>
                    <p class="text-muted mb-0">Late Completion</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.opname.compliance.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Search Ticket</label>
                        <input type="text" name="search" class="form-control" placeholder="Ticket number..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Execution Type</label>
                        <select name="execution_type" class="form-select">
                            <option value="">All Types</option>
                            <option value="early" {{ request('execution_type') == 'early' ? 'selected' : '' }}>Early</option>
                            <option value="ontime" {{ request('execution_type') == 'ontime' ? 'selected' : '' }}>On-Time</option>
                            <option value="late" {{ request('execution_type') == 'late' ? 'selected' : '' }}>Late</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.opname.compliance.index') }}" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Closed Tickets Table -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-table"></i> Closed Tickets</h5>
        </div>
        <div class="card-body">
            @if($closedTickets->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Ticket Number</th>
                                <th>Item Types</th>
                                <th>Location</th>
                                <th>Closed Date</th>
                                <th>Total Items</th>
                                <th>Discrepancy</th>
                                <th>Assigned Users</th>
                                <th>Execution Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($closedTickets as $ticket)
                            <tr>
                                <td>{{ $closedTickets->firstItem() + $loop->index }}</td>
                                <td>
                                    <strong>{{ $ticket->schedule_code }}</strong>
                                    <br><small class="text-muted">{{ $ticket->closed_at->format('d M Y H:i') }}</small>
                                </td>
                                <td>{{ $ticket->getItemTypes() }}</td>
                                <td>{{ $ticket->getAllLocations() }}</td>
                                <td>{{ $ticket->closed_at->format('d M Y H:i') }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $ticket->total_items }} items</span>
                                </td>
                                <td>
                                    @php
                                        $discrepancy = $ticket->getTotalDiscrepancy();
                                    @endphp
                                    @if($discrepancy > 0)
                                        <span class="badge bg-warning">{{ $discrepancy }} items</span>
                                    @else
                                        <span class="badge bg-success">0 items</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $ticket->getAssignedUsersNames() }}</small>
                                </td>
                                <td>
                                    @if($ticket->execution_type === 'early')
                                        <span class="badge bg-success">
                                            <i class="fas fa-arrow-up"></i> Early ({{ $ticket->days_difference }} days)
                                        </span>
                                    @elseif($ticket->execution_type === 'ontime')
                                        <span class="badge bg-info">
                                            <i class="fas fa-check"></i> On-Time
                                        </span>
                                    @elseif($ticket->execution_type === 'late')
                                        <span class="badge bg-danger">
                                            <i class="fas fa-arrow-down"></i> Late ({{ $ticket->days_difference }} days)
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.opname.compliance.show', $ticket) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $closedTickets->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No closed tickets found.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

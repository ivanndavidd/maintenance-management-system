@extends('layouts.admin')

@section('title', 'Corrective Maintenance Tickets')
@section('page-title', 'Corrective Maintenance - Tickets')

@push('styles')
<style>
    .parent-has-child td {
        border-bottom: none !important;
    }
    .child-ticket td {
        border-top: none !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-tools me-2"></i>Corrective Maintenance Tickets</h2>
            <p class="text-muted mb-0">Manage maintenance request tickets from warehouse staff</p>
        </div>
        <a href="{{ route('maintenance-request.create') }}" target="_blank" class="btn btn-primary">
            <i class="fas fa-external-link-alt me-2"></i>Public Form
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4 g-2">
        <div class="col">
            <div class="card bg-secondary text-white h-100">
                <div class="card-body text-center py-2">
                    <h4 class="mb-0">{{ $stats['total'] }}</h4>
                    <small>Total</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body text-center py-2">
                    <h4 class="mb-0">{{ $stats['pending'] }}</h4>
                    <small>Pending</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card bg-info text-white h-100">
                <div class="card-body text-center py-2">
                    <h4 class="mb-0">{{ $stats['received'] }}</h4>
                    <small>Received</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card bg-primary text-white h-100">
                <div class="card-body text-center py-2">
                    <h4 class="mb-0">{{ $stats['in_progress'] }}</h4>
                    <small>In Progress</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card bg-success text-white h-100">
                <div class="card-body text-center py-2">
                    <h4 class="mb-0">{{ $stats['done'] }}</h4>
                    <small>Done</small>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card bg-orange text-white h-100" style="background-color: #fd7e14 !important;">
                <div class="card-body text-center py-2">
                    <h4 class="mb-0">{{ $stats['further_repair'] }}</h4>
                    <small>Further Repair</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.corrective-maintenance.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Ticket, name, email, location..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Done</option>
                        <option value="further_repair" {{ request('status') == 'further_repair' ? 'selected' : '' }}>Further Repair</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed (Legacy)</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed (Legacy)</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Problem Category</label>
                    <select name="problem_category" class="form-select">
                        <option value="">All Categories</option>
                        <option value="conveyor_totebox" {{ request('problem_category') == 'conveyor_totebox' ? 'selected' : '' }}>Conveyor Totebox</option>
                        <option value="conveyor_paket" {{ request('problem_category') == 'conveyor_paket' ? 'selected' : '' }}>Conveyor Paket</option>
                        <option value="lift_merah" {{ request('problem_category') == 'lift_merah' ? 'selected' : '' }}>Lift Merah</option>
                        <option value="lift_kuning" {{ request('problem_category') == 'lift_kuning' ? 'selected' : '' }}>Lift Kuning</option>
                        <option value="chute" {{ request('problem_category') == 'chute' ? 'selected' : '' }}>Chute</option>
                        <option value="others" {{ request('problem_category') == 'others' ? 'selected' : '' }}>Others</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Ticket #</th>
                            <th>Requestor</th>
                            <th>Problem Category</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                        {{-- Parent Ticket Row --}}
                        <tr @if($ticket->childTickets->count() > 0) class="parent-has-child" @endif>
                            <td>
                                <a href="{{ route('admin.corrective-maintenance.show', $ticket) }}" class="fw-bold text-decoration-none">
                                    {{ $ticket->ticket_number }}
                                </a>
                                @if($ticket->childTickets->count() > 0)
                                    <span class="badge bg-secondary ms-1" title="Has {{ $ticket->childTickets->count() }} sub-ticket(s)">
                                        <i class="fas fa-code-branch"></i> {{ $ticket->childTickets->count() }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div>{{ $ticket->requestor_name }}</div>
                                <small class="text-muted">{{ $ticket->requestor_email }}</small>
                            </td>
                            <td>
                                <span class="badge {{ $ticket->getProblemCategoryBadgeClass() }}">
                                    <i class="fas {{ $ticket->getProblemCategoryIcon() }} me-1"></i>
                                    {{ $ticket->getProblemCategoryLabel() }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $ticket->getStatusBadgeClass() }}">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                            </td>
                            <td>
                                @if($ticket->technicians->count() > 0)
                                    @foreach($ticket->technicians->take(2) as $tech)
                                        <span class="badge bg-primary">{{ $tech->name }}</span>
                                    @endforeach
                                    @if($ticket->technicians->count() > 2)
                                        <span class="badge bg-secondary">+{{ $ticket->technicians->count() - 2 }}</span>
                                    @endif
                                @elseif($ticket->assignedUser)
                                    <span class="badge bg-primary">{{ $ticket->assignedUser->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                {{ $ticket->created_at->format('d M Y') }}<br>
                                <small class="text-muted">{{ $ticket->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <a href="{{ route('admin.corrective-maintenance.show', $ticket) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        {{-- Child Tickets (Sub-tickets) Rows --}}
                        @foreach($ticket->childTickets as $childTicket)
                        <tr class="child-ticket">
                            <td>
                                <span class="text-muted ms-3"><i class="fas fa-level-up-alt fa-rotate-90 me-2"></i></span>
                                <a href="{{ route('admin.corrective-maintenance.show', $childTicket) }}" class="text-decoration-none">
                                    {{ $childTicket->ticket_number }}
                                </a>
                            </td>
                            <td>
                                <div>{{ $childTicket->requestor_name }}</div>
                                <small class="text-muted">{{ $childTicket->requestor_email }}</small>
                            </td>
                            <td>
                                <span class="badge {{ $childTicket->getProblemCategoryBadgeClass() }}">
                                    <i class="fas {{ $childTicket->getProblemCategoryIcon() }} me-1"></i>
                                    {{ $childTicket->getProblemCategoryLabel() }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $childTicket->getStatusBadgeClass() }}">
                                    {{ ucfirst(str_replace('_', ' ', $childTicket->status)) }}
                                </span>
                            </td>
                            <td>
                                @if($childTicket->technicians->count() > 0)
                                    @foreach($childTicket->technicians->take(2) as $tech)
                                        <span class="badge bg-primary">{{ $tech->name }}</span>
                                    @endforeach
                                    @if($childTicket->technicians->count() > 2)
                                        <span class="badge bg-secondary">+{{ $childTicket->technicians->count() - 2 }}</span>
                                    @endif
                                @elseif($childTicket->assignedUser)
                                    <span class="badge bg-primary">{{ $childTicket->assignedUser->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                {{ $childTicket->created_at->format('d M Y') }}<br>
                                <small class="text-muted">{{ $childTicket->created_at->format('H:i') }}</small>
                            </td>
                            <td>
                                <a href="{{ route('admin.corrective-maintenance.show', $childTicket) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted mb-0">No tickets found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $tickets->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

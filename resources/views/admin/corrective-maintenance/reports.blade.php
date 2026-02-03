@extends('layouts.admin')

@section('title', 'CM Reports')
@section('page-title', 'Corrective Maintenance - Reports')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-file-alt me-2"></i>Corrective Maintenance Reports</h2>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Done</option>
                        <option value="further_repair" {{ request('status') == 'further_repair' ? 'selected' : '' }}>Further Repair</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="{{ route('admin.corrective-maintenance.reports') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Ticket #</th>
                            <th>Asset</th>
                            <th>Status</th>
                            <th>Problem</th>
                            <th>Submitted By</th>
                            <th>Duration</th>
                            <th>Submitted At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                        <tr>
                            <td>
                                <strong>{{ $report->cmRequest->ticket_number ?? '-' }}</strong>
                            </td>
                            <td>{{ $report->asset->asset_name ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $report->getStatusBadgeClass() }}">{{ $report->getStatusLabel() }}</span>
                            </td>
                            <td>{{ Str::limit($report->problem_detail, 50) }}</td>
                            <td>{{ $report->submitter->name ?? '-' }}</td>
                            <td>
                                @if($report->cmRequest && $report->cmRequest->work_duration)
                                    <span class="badge bg-info">{{ $report->cmRequest->work_duration }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $report->submitted_at?->format('d M Y, H:i') }}</td>
                            <td>
                                @if($report->cmRequest)
                                <a href="{{ route('admin.corrective-maintenance.show', $report->cmRequest) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No reports found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($reports->hasPages())
        <div class="card-footer">
            {{ $reports->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

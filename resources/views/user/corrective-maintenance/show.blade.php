@extends('layouts.user')

@section('title', 'Ticket ' . $ticket->ticket_number)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('user.corrective-maintenance.index') }}" class="text-decoration-none">
                <i class="fas fa-arrow-left me-2"></i>Back to My Tickets
            </a>
            <h2 class="mt-2"><i class="fas fa-ticket-alt me-2"></i>{{ $ticket->ticket_number }}</h2>
            @if($ticket->parentTicket)
                <small class="text-muted">
                    <i class="fas fa-link me-1"></i>Sub-ticket of
                    <a href="{{ route('user.corrective-maintenance.show', $ticket->parentTicket) }}">{{ $ticket->parentTicket->ticket_number }}</a>
                </small>
            @endif
        </div>
        <span class="badge {{ $ticket->getStatusBadgeClass() }} fs-5 px-3 py-2">
            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
        </span>
    </div>

    @php
        $myAssignment = $ticket->technicians->where('id', auth()->id())->first();
    @endphp

    <div class="row">
        <!-- Ticket Details -->
        <div class="col-lg-8">
            <!-- Assignment Info -->
            @if($myAssignment)
            <div class="card mb-4 border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-check me-2"></i>Your Assignment</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Shift:</strong><br>
                            <span class="badge bg-info">{{ $myAssignment->pivot->shift_info ?? '-' }}</span>
                        </div>
                        <div class="col-md-4">
                            <strong>Assigned At:</strong><br>
                            {{ $myAssignment->pivot->created_at ? \Carbon\Carbon::parse($myAssignment->pivot->created_at)->format('d M Y H:i') : '-' }}
                        </div>
                        <div class="col-md-4">
                            <strong>Acknowledged:</strong><br>
                            @if($myAssignment->pivot->acknowledged_at)
                                <span class="text-success"><i class="fas fa-check-circle"></i> {{ \Carbon\Carbon::parse($myAssignment->pivot->acknowledged_at)->format('d M Y H:i') }}</span>
                            @else
                                <form action="{{ route('user.corrective-maintenance.acknowledge', $ticket) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-check me-1"></i> Acknowledge
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Ticket Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Ticket Number</th>
                                    <td>{{ $ticket->ticket_number }}</td>
                                </tr>
                                <tr>
                                    <th>Problem Category</th>
                                    <td>
                                        <span class="badge {{ $ticket->getProblemCategoryBadgeClass() }}">
                                            <i class="fas {{ $ticket->getProblemCategoryIcon() }} me-1"></i>
                                            {{ $ticket->getProblemCategoryLabel() }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ $ticket->created_at->format('d M Y, H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Requestor</th>
                                    <td>{{ $ticket->requestor_name }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td><a href="mailto:{{ $ticket->requestor_email }}">{{ $ticket->requestor_email }}</a></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <h6><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Problem Description</h6>
                    <div class="bg-light p-3 rounded mb-3">
                        {{ $ticket->problem_description }}
                    </div>

                    @if($ticket->additional_notes)
                    <h6><i class="fas fa-sticky-note me-2"></i>Additional Notes</h6>
                    <div class="bg-light p-3 rounded mb-3">
                        {{ $ticket->additional_notes }}
                    </div>
                    @endif

                    @if($ticket->parent_ticket_id && $ticket->parentTicket && $ticket->parentTicket->report)
                    <h6><i class="fas fa-history me-2 text-info"></i>Previous Work Done (Parent Ticket)</h6>
                    <div class="bg-info bg-opacity-10 p-3 rounded mb-3 border-start border-info border-3">
                        {!! nl2br(e($ticket->parentTicket->report->work_done)) !!}
                        <div class="text-muted small mt-2">
                            <i class="fas fa-link me-1"></i>From: <a href="{{ route('user.corrective-maintenance.show', $ticket->parentTicket) }}">{{ $ticket->parentTicket->ticket_number }}</a>
                        </div>
                    </div>
                    @endif

                    @if($ticket->attachment_path)
                    <h6><i class="fas fa-paperclip me-2"></i>Attachment</h6>
                    <a href="{{ Storage::url($ticket->attachment_path) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-download me-2"></i>Download Attachment
                    </a>
                    @endif
                </div>
            </div>

            <!-- Submitted Report -->
            @if($ticket->report)
            <div class="card mb-4">
                <div class="card-header {{ $ticket->report->status === 'done' ? 'bg-success' : 'bg-warning' }} text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>Submitted Report
                        <span class="badge bg-light {{ $ticket->report->status === 'done' ? 'text-success' : 'text-warning' }} ms-2">
                            {{ $ticket->report->getStatusLabel() }}
                        </span>
                    </h5>
                </div>
                <div class="card-body">
                    @if($ticket->report->asset)
                    <div class="mb-3">
                        <strong><i class="fas fa-cogs me-1"></i> Asset:</strong>
                        <span class="ms-1">{{ $ticket->report->asset->asset_name }} ({{ $ticket->report->asset->asset_id }})</span>
                    </div>
                    @endif

                    <div class="mb-3">
                        <strong><i class="fas fa-search me-1"></i> Problem Detail:</strong>
                        <div class="bg-light p-3 rounded mt-1">{!! nl2br(e($ticket->report->problem_detail)) !!}</div>
                    </div>

                    <div class="mb-3">
                        <strong><i class="fas fa-wrench me-1"></i> Work Done:</strong>
                        <div class="bg-light p-3 rounded mt-1">{!! nl2br(e($ticket->report->work_done)) !!}</div>
                    </div>

                    @if($ticket->report->notes)
                    <div class="mb-3">
                        <strong><i class="fas fa-sticky-note me-1"></i> Notes:</strong>
                        <div class="bg-light p-3 rounded mt-1">{!! nl2br(e($ticket->report->notes)) !!}</div>
                    </div>
                    @endif

                    <div class="text-muted small">
                        <i class="fas fa-user me-1"></i> Submitted by {{ $ticket->report->submitter->name }}
                        on {{ $ticket->report->submitted_at->format('d M Y, H:i') }}
                        @if($ticket->work_duration)
                            | <i class="fas fa-clock me-1"></i> Duration: {{ $ticket->work_duration }}
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Child Tickets (Sub-tickets) - for further_repair tracking -->
            @if($ticket->childTickets && $ticket->childTickets->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-code-branch me-2"></i>Follow-up Tickets
                        <span class="badge bg-light text-info ms-2">{{ $ticket->childTickets->count() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($ticket->childTickets as $childTicket)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">
                                        <a href="{{ route('user.corrective-maintenance.show', $childTicket) }}" class="text-decoration-none">
                                            {{ $childTicket->ticket_number }}
                                        </a>
                                    </h6>
                                    <p class="mb-1 small text-muted">
                                        Created: {{ $childTicket->created_at->format('d M Y, H:i') }}
                                    </p>
                                    @if($childTicket->technicians->count() > 0)
                                    <p class="mb-0 small">
                                        <strong>Assigned:</strong>
                                        @foreach($childTicket->technicians->take(2) as $tech)
                                            <span class="badge bg-primary">{{ $tech->name }}</span>
                                        @endforeach
                                        @if($childTicket->technicians->count() > 2)
                                            <span class="badge bg-secondary">+{{ $childTicket->technicians->count() - 2 }}</span>
                                        @endif
                                    </p>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <span class="badge {{ $childTicket->getStatusBadgeClass() }}">
                                        {{ ucfirst(str_replace('_', ' ', $childTicket->status)) }}
                                    </span>
                                    @if($childTicket->work_duration)
                                    <br><small class="text-muted"><i class="fas fa-clock"></i> {{ $childTicket->work_duration }}</small>
                                    @endif
                                </div>
                            </div>
                            @if($childTicket->report)
                            <div class="mt-2 p-2 bg-light rounded small">
                                <strong>Report:</strong> {{ Str::limit($childTicket->report->work_done, 100) }}
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Actions & Other Technicians -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            @if($ticket->status == 'in_progress')
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Actions</h5>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#submitReportModal">
                        <i class="fas fa-file-alt me-2"></i>Submit Report
                    </button>
                </div>
            </div>
            @endif

            <!-- Other Technicians -->
            @if($ticket->technicians->count() > 1)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Team Members</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">Other technicians assigned to this ticket:</p>
                    @foreach($ticket->technicians as $tech)
                        @if($tech->id != auth()->id())
                        <div class="d-flex align-items-center mb-2 p-2 bg-light rounded">
                            <i class="fas fa-user-circle text-primary me-2"></i>
                            <div>
                                <strong>{{ $tech->name }}</strong>
                                @if($tech->pivot->shift_info)
                                    <br><span class="badge bg-info">{{ $tech->pivot->shift_info }}</span>
                                @endif
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Timeline -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline-vertical">
                        <div class="timeline-item active">
                            <div class="timeline-marker bg-secondary"></div>
                            <div class="timeline-content">
                                <strong>Submitted</strong>
                                <p class="mb-0 small text-muted">{{ $ticket->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                        <div class="timeline-item {{ $ticket->received_at ? 'active' : '' }}">
                            <div class="timeline-marker {{ $ticket->received_at ? 'bg-info' : 'bg-light border' }}"></div>
                            <div class="timeline-content">
                                <strong>Received & Assigned</strong>
                                @if($ticket->received_at)
                                    <p class="mb-0 small text-muted">{{ $ticket->received_at->format('d M Y, H:i') }}</p>
                                @else
                                    <p class="mb-0 small text-muted">Pending</p>
                                @endif
                            </div>
                        </div>
                        <div class="timeline-item {{ $ticket->report_submitted_at ? 'active' : '' }}">
                            <div class="timeline-marker {{ $ticket->report_submitted_at ? ($ticket->status === 'done' ? 'bg-success' : 'bg-warning') : 'bg-light border' }}"></div>
                            <div class="timeline-content">
                                <strong>Report Submitted</strong>
                                @if($ticket->report_submitted_at)
                                    <p class="mb-0 small text-muted">{{ $ticket->report_submitted_at->format('d M Y, H:i') }}</p>
                                    @if($ticket->work_duration)
                                        <p class="mb-0 small text-muted"><i class="fas fa-clock"></i> Duration: {{ $ticket->work_duration }}</p>
                                    @endif
                                @else
                                    <p class="mb-0 small text-muted">Pending</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Submit Report Modal -->
@if($ticket->status == 'in_progress')
<div class="modal fade" id="submitReportModal" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i>Submit Report</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('user.corrective-maintenance.submit-report', $ticket) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <!-- Status Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="statusDone" value="done" required checked>
                                <label class="form-check-label" for="statusDone">
                                    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Done</span>
                                </label>
                            </div>
                            @if(!$ticket->parent_ticket_id)
                            {{-- Only show Further Repair option for parent tickets --}}
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="statusFurtherRepair" value="further_repair">
                                <label class="form-check-label" for="statusFurtherRepair">
                                    <span class="badge bg-warning text-dark"><i class="fas fa-tools me-1"></i> Further Repair</span>
                                </label>
                            </div>
                            @else
                            {{-- For child tickets, show Failed option instead --}}
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="statusFailed" value="failed">
                                <label class="form-check-label" for="statusFailed">
                                    <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Failed</span>
                                </label>
                            </div>
                            @endif
                        </div>
                        @if($ticket->parent_ticket_id)
                        <small class="text-muted mt-2 d-block">
                            <i class="fas fa-info-circle me-1"></i>This is a follow-up ticket. Further repair option is not available.
                        </small>
                        @endif
                    </div>

                    <!-- Asset Selection -->
                    <div class="mb-3">
                        <label for="assetId" class="form-label fw-bold">Asset</label>
                        @if($ticket->parent_ticket_id && $ticket->parentTicket && $ticket->parentTicket->report && $ticket->parentTicket->report->asset_id)
                        {{-- For child ticket, show parent's asset as disabled --}}
                        @php $parentAsset = $ticket->parentTicket->report->asset; @endphp
                        <input type="hidden" name="asset_id" value="{{ $parentAsset->id }}">
                        <input type="text" class="form-control" disabled
                               value="{{ $parentAsset->asset_name }} ({{ $parentAsset->asset_id }}) - {{ $parentAsset->location ?? 'No location' }}">
                        <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Asset inherited from parent ticket</small>
                        @else
                        <select name="asset_id" id="assetId" class="form-select">
                            <option value="">-- Select Asset (Optional) --</option>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}">
                                    {{ $asset->asset_name }} ({{ $asset->asset_id }}) - {{ $asset->location ?? 'No location' }}
                                </option>
                            @endforeach
                        </select>
                        @endif
                    </div>

                    <!-- Problem Detail -->
                    <div class="mb-3">
                        <label for="problemDetail" class="form-label fw-bold">Problem Detail <span class="text-danger">*</span></label>
                        @if($ticket->parent_ticket_id && $ticket->parentTicket && $ticket->parentTicket->report)
                        {{-- For child ticket, show parent's problem detail as disabled --}}
                        <input type="hidden" name="problem_detail" value="{{ $ticket->parentTicket->report->problem_detail }}">
                        <textarea class="form-control" rows="3" disabled>{{ $ticket->parentTicket->report->problem_detail }}</textarea>
                        <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Problem detail inherited from parent ticket</small>
                        @else
                        <textarea name="problem_detail" id="problemDetail" class="form-control" rows="3" placeholder="Describe the detailed problem found..." required></textarea>
                        @endif
                    </div>

                    <!-- Work Done -->
                    <div class="mb-3">
                        <label for="workDone" class="form-label fw-bold">Work Done <span class="text-danger">*</span></label>
                        <textarea name="work_done" id="workDone" class="form-control" rows="3" placeholder="Describe what work was performed..." required></textarea>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label for="reportNotes" class="form-label fw-bold">Additional Notes</label>
                        <textarea name="notes" id="reportNotes" class="form-control" rows="2" placeholder="Any additional notes (optional)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Submit Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<style>
.timeline-vertical {
    position: relative;
    padding-left: 30px;
}
.timeline-vertical::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}
.timeline-vertical .timeline-item {
    position: relative;
    padding-bottom: 20px;
}
.timeline-vertical .timeline-item:last-child {
    padding-bottom: 0;
}
.timeline-vertical .timeline-marker {
    position: absolute;
    left: -26px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
}
</style>
@endsection

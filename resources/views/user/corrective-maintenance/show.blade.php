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
                                    <th>Priority</th>
                                    <td><span class="badge {{ $ticket->getPriorityBadgeClass() }}">{{ ucfirst($ticket->priority) }}</span></td>
                                </tr>
                                <tr>
                                    <th>Location</th>
                                    <td><strong>{{ $ticket->location }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Equipment</th>
                                    <td>{{ $ticket->equipment_name ?: '-' }}</td>
                                </tr>
                                @if($ticket->equipment_id)
                                <tr>
                                    <th>Equipment ID</th>
                                    <td>{{ $ticket->equipment_id }}</td>
                                </tr>
                                @endif
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
                                <tr>
                                    <th>Phone</th>
                                    <td>{{ $ticket->requestor_phone ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Department</th>
                                    <td>{{ $ticket->requestor_department ?: '-' }}</td>
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

                    @if($ticket->attachment_path)
                    <h6><i class="fas fa-paperclip me-2"></i>Attachment</h6>
                    <a href="{{ Storage::url($ticket->attachment_path) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-download me-2"></i>Download Attachment
                    </a>
                    @endif
                </div>
            </div>

            <!-- Work Notes -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clipboard me-2"></i>Work Notes</h5>
                </div>
                <div class="card-body">
                    @if($ticket->work_notes)
                        <div class="bg-light p-3 rounded mb-3">
                            {!! nl2br(e($ticket->work_notes)) !!}
                        </div>
                    @else
                        <p class="text-muted mb-0">No work notes yet.</p>
                    @endif

                    @if($ticket->status == 'in_progress')
                    <form action="{{ route('user.corrective-maintenance.update-notes', $ticket) }}" method="POST" class="mt-3">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <textarea name="work_notes" class="form-control" rows="3" placeholder="Add or update work notes...">{{ $ticket->work_notes }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-save me-2"></i>Save Notes
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            <!-- Resolution (if completed/failed) -->
            @if($ticket->resolution)
            <div class="card mb-4">
                <div class="card-header {{ $ticket->status == 'completed' ? 'bg-success text-white' : 'bg-danger text-white' }}">
                    <h5 class="mb-0">
                        <i class="fas {{ $ticket->status == 'completed' ? 'fa-check-circle' : 'fa-times-circle' }} me-2"></i>
                        Resolution
                    </h5>
                </div>
                <div class="card-body">
                    {!! nl2br(e($ticket->resolution)) !!}
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
                    <button type="button" class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#completeModal">
                        <i class="fas fa-check-circle me-2"></i>Complete Ticket
                    </button>
                    <button type="button" class="btn btn-danger w-100 mb-2" data-bs-toggle="modal" data-bs-target="#failModal">
                        <i class="fas fa-times-circle me-2"></i>Mark as Failed
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
                        <div class="timeline-item {{ $ticket->completed_at ? 'active' : '' }}">
                            <div class="timeline-marker {{ $ticket->completed_at ? ($ticket->status == 'completed' ? 'bg-success' : 'bg-danger') : 'bg-light border' }}"></div>
                            <div class="timeline-content">
                                <strong>{{ $ticket->status == 'failed' ? 'Failed' : 'Completed' }}</strong>
                                @if($ticket->completed_at)
                                    <p class="mb-0 small text-muted">{{ $ticket->completed_at->format('d M Y, H:i') }}</p>
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

<!-- Complete Modal -->
<div class="modal fade" id="completeModal" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Complete Ticket</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('user.corrective-maintenance.complete', $ticket) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="completed">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Resolution / Work Done <span class="text-danger">*</span></label>
                        <textarea name="resolution" class="form-control" rows="4" placeholder="Describe what was done to resolve the issue..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-2"></i>Mark as Completed
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Fail Modal -->
<div class="modal fade" id="failModal" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>Mark as Failed</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('user.corrective-maintenance.complete', $ticket) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="failed">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Reason for Failure <span class="text-danger">*</span></label>
                        <textarea name="resolution" class="form-control" rows="4" placeholder="Explain why the maintenance could not be completed..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-2"></i>Mark as Failed
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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

@extends('layouts.admin')

@section('title', 'Ticket ' . $ticket->ticket_number)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.corrective-maintenance.index') }}" class="text-decoration-none">
                <i class="fas fa-arrow-left me-2"></i>Back to Tickets
            </a>
            <h2 class="mt-2"><i class="fas fa-ticket-alt me-2"></i>{{ $ticket->ticket_number }}</h2>
        </div>
        <span class="badge {{ $ticket->getStatusBadgeClass() }} fs-5 px-3 py-2">
            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
        </span>
    </div>

    <div class="row">
        <!-- Ticket Details -->
        <div class="col-lg-8">
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

                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Problem Description</h6>
                    <div class="bg-light p-3 rounded mb-3">
                        {{ $ticket->problem_description }}
                    </div>

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

                    @if(!in_array($ticket->status, ['completed', 'failed', 'cancelled']))
                    <form action="{{ route('admin.corrective-maintenance.update-notes', $ticket) }}" method="POST" class="mt-3">
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

        <!-- Actions & Timeline -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Actions</h5>
                </div>
                <div class="card-body">
                    @if($ticket->status == 'pending')
                        <div class="alert alert-info mb-2">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>Ticket is waiting for processing. Auto-assignment happens when ticket is created based on shift schedule.</small>
                        </div>
                        <form action="{{ route('admin.corrective-maintenance.mark-received', $ticket) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-info w-100 mb-2">
                                <i class="fas fa-check me-2"></i>Mark as Received
                            </button>
                        </form>
                    @endif

                    @if($ticket->status == 'received')
                        @if($ticket->technicians->count() == 0)
                            <div class="alert alert-warning mb-2">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <small>No technicians were auto-assigned (none on duty). You can manually assign below.</small>
                            </div>
                            <button type="button" class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#assignModal">
                                <i class="fas fa-user-plus me-2"></i>Manual Assign
                            </button>
                        @else
                            <div class="alert alert-success mb-2">
                                <i class="fas fa-check-circle me-2"></i>
                                <small>Technicians have been auto-assigned.</small>
                            </div>
                        @endif
                    @endif

                    @if($ticket->status == 'in_progress')
                        <button type="button" class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#completeModal">
                            <i class="fas fa-check-circle me-2"></i>Complete Ticket
                        </button>
                        <button type="button" class="btn btn-danger w-100 mb-2" data-bs-toggle="modal" data-bs-target="#failModal">
                            <i class="fas fa-times-circle me-2"></i>Mark as Failed
                        </button>
                    @endif

                    @if(!in_array($ticket->status, ['completed', 'failed', 'cancelled']))
                        <hr>
                        <button type="button" class="btn btn-outline-secondary w-100" data-bs-toggle="modal" data-bs-target="#cancelModal">
                            <i class="fas fa-ban me-2"></i>Cancel Ticket
                        </button>
                    @endif

                    @if(in_array($ticket->status, ['completed', 'failed', 'cancelled']))
                        <div class="text-center text-muted">
                            <i class="fas fa-lock me-2"></i>This ticket is closed
                        </div>
                    @endif
                </div>
            </div>

            <!-- Timeline -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline-vertical">
                        <div class="timeline-item {{ $ticket->created_at ? 'active' : '' }}">
                            <div class="timeline-marker bg-secondary"></div>
                            <div class="timeline-content">
                                <strong>Submitted</strong>
                                <p class="mb-0 small text-muted">{{ $ticket->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                        <div class="timeline-item {{ $ticket->received_at ? 'active' : '' }}">
                            <div class="timeline-marker {{ $ticket->received_at ? 'bg-info' : 'bg-light border' }}"></div>
                            <div class="timeline-content">
                                <strong>Received</strong>
                                @if($ticket->received_at)
                                    <p class="mb-0 small text-muted">{{ $ticket->received_at->format('d M Y, H:i') }}</p>
                                @else
                                    <p class="mb-0 small text-muted">Pending</p>
                                @endif
                            </div>
                        </div>
                        <div class="timeline-item {{ $ticket->in_progress_at ? 'active' : '' }}">
                            <div class="timeline-marker {{ $ticket->in_progress_at ? 'bg-primary' : 'bg-light border' }}"></div>
                            <div class="timeline-content">
                                <strong>In Progress</strong>
                                @if($ticket->in_progress_at)
                                    <p class="mb-0 small text-muted">{{ $ticket->in_progress_at->format('d M Y, H:i') }}</p>
                                    @if($ticket->technicians->count() > 0)
                                        <p class="mb-0 small">Assigned to: <strong>{{ $ticket->technician_names }}</strong></p>
                                    @elseif($ticket->assignedUser)
                                        <p class="mb-0 small">Assigned to: <strong>{{ $ticket->assignedUser->name }}</strong></p>
                                    @endif
                                @else
                                    <p class="mb-0 small text-muted">Pending</p>
                                @endif
                            </div>
                        </div>
                        <div class="timeline-item {{ $ticket->completed_at ? 'active' : '' }}">
                            <div class="timeline-marker {{ $ticket->completed_at ? ($ticket->status == 'completed' ? 'bg-success' : 'bg-danger') : 'bg-light border' }}"></div>
                            <div class="timeline-content">
                                <strong>{{ $ticket->status == 'failed' ? 'Failed' : ($ticket->status == 'cancelled' ? 'Cancelled' : 'Completed') }}</strong>
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

            <!-- Assigned Technicians -->
            @if($ticket->technicians->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Assigned Technicians</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">Auto-assigned based on shift schedule:</p>
                    @foreach($ticket->technicians as $tech)
                        <div class="d-flex align-items-center mb-2 p-2 bg-light rounded">
                            <i class="fas fa-user-circle text-primary me-2"></i>
                            <div>
                                <strong>{{ $tech->name }}</strong>
                                <br><small class="text-muted">{{ $tech->email }}</small>
                                @if($tech->pivot->shift_info)
                                    <br><span class="badge bg-info">{{ $tech->pivot->shift_info }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @elseif($ticket->status == 'received')
            <div class="card mb-4">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>No Technicians Assigned</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">No technicians were on duty when this ticket was created. Waiting for manual assignment or shift change.</p>
                </div>
            </div>
            @endif

            <!-- Handler Info -->
            @if($ticket->handler)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-shield me-2"></i>Handled By</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0"><strong>{{ $ticket->handler->name }}</strong></p>
                    <small class="text-muted">{{ $ticket->handler->email }}</small>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Assign Modal -->
<div class="modal fade" id="assignModal" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Assign Technician</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.corrective-maintenance.assign', $ticket) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Assign to Maintenance Staff <span class="text-danger">*</span></label>
                        <select name="assigned_to" class="form-select" required>
                            <option value="">Select technician...</option>
                            @foreach($maintenanceStaff as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Work Notes (Optional)</label>
                        <textarea name="work_notes" class="form-control" rows="3" placeholder="Add initial work notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check me-2"></i>Assign & Start
                    </button>
                </div>
            </form>
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
            <form action="{{ route('admin.corrective-maintenance.complete', $ticket) }}" method="POST">
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
            <form action="{{ route('admin.corrective-maintenance.complete', $ticket) }}" method="POST">
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

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-ban me-2"></i>Cancel Ticket</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.corrective-maintenance.cancel', $ticket) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Are you sure you want to cancel this ticket?
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason for Cancellation (Optional)</label>
                        <textarea name="resolution" class="form-control" rows="3" placeholder="Provide a reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="fas fa-ban me-2"></i>Cancel Ticket
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

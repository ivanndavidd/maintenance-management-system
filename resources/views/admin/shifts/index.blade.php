@extends('layouts.admin')

@section('page-title', 'Shift Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Shift Management</h2>
        <a href="{{ route('admin.shifts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create New Schedule
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Schedule Name</th>
                            <th>Period</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Total Assignments</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $schedule)
                            <tr>
                                <td>
                                    <strong>{{ $schedule->name }}</strong>
                                    @if($schedule->notes)
                                        <br><small class="text-muted">{{ $schedule->notes }}</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $schedule->start_date->format('d M Y') }} -
                                    {{ $schedule->end_date->format('d M Y') }}
                                </td>
                                <td>
                                    @if($schedule->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif($schedule->status === 'draft')
                                        <span class="badge bg-secondary">Draft</span>
                                    @else
                                        <span class="badge bg-dark">Completed</span>
                                    @endif
                                </td>
                                <td>{{ $schedule->creator->name }}</td>
                                <td>{{ $schedule->assignments()->count() }} assignments</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.shifts.edit', $schedule) }}"
                                           class="btn btn-sm btn-info"
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        @if($schedule->status === 'draft')
                                            <form id="activate-form-{{ $schedule->id }}"
                                                  action="{{ route('admin.shifts.activate', $schedule) }}"
                                                  method="POST"
                                                  class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="button"
                                                        class="btn btn-sm btn-success"
                                                        title="Activate"
                                                        onclick="showConfirmModal('Activate this schedule? This will deactivate any overlapping schedules.', () => document.getElementById('activate-form-{{ $schedule->id }}').submit())">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if($schedule->status !== 'active')
                                            <form id="delete-form-{{ $schedule->id }}"
                                                  action="{{ route('admin.shifts.destroy', $schedule) }}"
                                                  method="POST"
                                                  class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button"
                                                        class="btn btn-sm btn-danger"
                                                        title="Delete"
                                                        onclick="showConfirmModal('Are you sure you want to delete this schedule?', () => document.getElementById('delete-form-{{ $schedule->id }}').submit())">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No shift schedules found. Create your first schedule to get started.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $schedules->links() }}
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Shift Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="border-start border-primary border-4 ps-3 mb-3">
                        <h6 class="text-primary">Shift 1</h6>
                        <p class="mb-0 small">22:00 - 05:00 (7 hours)</p>
                        <p class="mb-0 small text-muted">Night shift, crosses midnight</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border-start border-success border-4 ps-3 mb-3">
                        <h6 class="text-success">Shift 2</h6>
                        <p class="mb-0 small">06:00 - 13:00 (7 hours)</p>
                        <p class="mb-0 small text-muted">Morning shift</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border-start border-warning border-4 ps-3 mb-3">
                        <h6 class="text-warning">Shift 3</h6>
                        <p class="mb-0 small">14:00 - 21:00 (7 hours)</p>
                        <p class="mb-0 small text-muted">Afternoon shift</p>
                    </div>
                </div>
            </div>
            <div class="alert alert-info mb-0 mt-3">
                <i class="fas fa-info-circle"></i>
                <strong>Notes:</strong>
                <ul class="mb-0 mt-2">
                    <li>Each shift can have maximum 4 users assigned</li>
                    <li>Minimum working hours per person is 8 hours per week</li>
                    <li>Users can work more than 8 hours if needed</li>
                    <li>Schedules are typically set on a weekly basis</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- Confirm Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="confirmMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
function showConfirmModal(message, onConfirm) {
    document.getElementById('confirmMessage').textContent = message;
    const btn = document.getElementById('confirmBtn');
    const newBtn = btn.cloneNode(true);
    btn.parentNode.replaceChild(newBtn, btn);
    newBtn.addEventListener('click', function() {
        onConfirm();
        bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
    });
    new bootstrap.Modal(document.getElementById('confirmModal')).show();
}
</script>
@endsection

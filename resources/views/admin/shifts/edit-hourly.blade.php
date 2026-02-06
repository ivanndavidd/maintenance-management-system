@extends('layouts.admin')

@section('page-title', 'Edit Shift Schedule')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Edit Shift Schedule: {{ $shift->name }}</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route(($routePrefix ?? 'admin').'.shifts.index') }}">Shift Management</a></li>
                <li class="breadcrumb-item active">{{ $shift->name }}</li>
            </ol>
        </nav>
    </div>

    <!-- Schedule Information Card -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Schedule Information</h5>
            <span class="badge bg-{{ $shift->status === 'active' ? 'success' : ($shift->status === 'draft' ? 'secondary' : 'dark') }}">
                {{ ucfirst($shift->status) }}
            </span>
        </div>
        <div class="card-body">
            <form action="{{ route(($routePrefix ?? 'admin').'.shifts.update', $shift) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="name" class="form-label">Schedule Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $shift->name }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $shift->start_date->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $shift->end_date->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Update
                        </button>
                    </div>
                </div>

                <div class="mb-0">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="2">{{ $shift->notes }}</textarea>
                </div>
            </form>
        </div>
    </div>

    <!-- User Selection Panel -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-users"></i> Select User to Assign
                <span class="text-muted small" id="selected-user-display"></span>
            </h5>
        </div>
        <div class="card-body">
            <div class="row" id="user-selection">
                @foreach($users as $user)
                    <div class="col-md-2 col-sm-3 col-4 mb-2">
                        <div class="user-select-card p-2 border rounded text-center cursor-pointer"
                             data-user-id="{{ $user->id }}"
                             data-user-name="{{ $user->name }}"
                             data-user-color="{{ \App\Models\ShiftAssignment::generateColorForUser($user->id) }}">
                            <div class="user-avatar mx-auto mb-2"
                                 style="width: 40px; height: 40px; background-color: {{ \App\Models\ShiftAssignment::generateColorForUser($user->id) }}; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 18px;">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div class="small fw-bold">{{ $user->name }}</div>
                            <div class="text-muted" style="font-size: 10px;">
                                <span class="user-hours-{{ $user->id }}">{{ $userHours[$user->id] ?? 0 }}</span>h
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="alert alert-info mb-0 mt-3">
                <small>
                    <i class="fas fa-info-circle"></i>
                    <strong>Instructions:</strong>
                    <br>1. Click a user to select
                    <br>2. Click AND HOLD mouse on a cell, then DRAG to select multiple cells
                    <br>3. Release mouse to assign selected cells
                    <br>4. Click on assigned cell to remove
                </small>
            </div>
        </div>
    </div>

    <!-- Hourly Shift Calendar -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Hourly Shift Calendar</h5>
            <div>
                <button class="btn btn-sm btn-outline-danger" onclick="clearAllAssignments()">
                    <i class="fas fa-trash"></i> Clear All
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
                <table class="table table-bordered table-sm shift-calendar-hourly mb-0" id="shift-calendar">
                    <thead class="table-light sticky-top" style="top: 0; z-index: 10;">
                        <tr>
                            <th style="width: 60px; position: sticky; left: 0; z-index: 11; background: white;">Time</th>
                            @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                <th colspan="4" class="text-center">{{ $day }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Reorder hours: [22, 23, 0, 1, 2, ..., 21]
                            // This shows previous day's 22-23 at the top
                            $hourOrder = array_merge([22, 23], range(0, 21));
                        @endphp
                        @foreach($hourOrder as $hour)
                            @php
                                // Add shift separator border after hours 5, 13, and 21
                                $isShiftEnd = in_array($hour, [5, 13, 21]);
                                $shiftBorder = $isShiftEnd ? 'border-bottom: 3px solid #0d6efd !important;' : '';
                            @endphp
                            <tr class="{{ $hour >= 22 ? 'previous-day-hours' : '' }}">
                                <td class="text-center fw-bold align-middle time-cell"
                                    style="position: sticky; left: 0; z-index: 9; background: {{ $hour >= 22 ? '#fff3cd' : '#f8f9fa' }} !important; {{ $shiftBorder }}">
                                    {{ sprintf('%02d:00', $hour) }}
                                    @if($hour >= 22)
                                        <small class="d-block text-muted" style="font-size: 9px;">(Prev)</small>
                                    @endif
                                </td>
                                @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                    @for($col = 0; $col < 4; $col++)
                                        <td class="shift-cell p-0"
                                            data-day="{{ $day }}"
                                            data-hour="{{ $hour }}"
                                            data-column="{{ $col }}"
                                            style="width: 40px; height: 35px; cursor: pointer; position: relative; {{ $shiftBorder }}">
                                            @if(isset($grid[$day][$hour][$col]) && $grid[$day][$hour][$col])
                                                @php
                                                    $assignment = $grid[$day][$hour][$col];
                                                @endphp

                                            @if($assignment->change_action === 'cancelled')
                                                <!-- Cancelled shift - show X mark -->
                                                <div class="assigned-cell cancelled-cell h-100 w-100 d-flex align-items-center justify-content-center"
                                                     style="background-color: #dc3545; color: white; font-weight: bold; font-size: 16px; cursor: pointer;"
                                                     data-assignment-id="{{ $assignment->id }}"
                                                     data-tooltip="{{ $assignment->user->name }} - {{ $assignment->change_reason }}">
                                                    ❌
                                                </div>
                                            @elseif($assignment->change_action === 'replaced')
                                                <!-- Replaced shift - show new user -->
                                                <div class="assigned-cell h-100 w-100 d-flex align-items-center justify-content-center"
                                                     style="background-color: {{ \App\Models\ShiftAssignment::generateColorForUser($assignment->new_user_id) }}; color: white; font-weight: bold; font-size: 11px;"
                                                     data-assignment-id="{{ $assignment->id }}"
                                                     data-user-id="{{ $assignment->new_user_id }}">
                                                    {{ substr($assignment->newUser->name, 0, 1) }}
                                                </div>
                                            @else
                                                <!-- Normal assignment -->
                                                <div class="assigned-cell h-100 w-100 d-flex align-items-center justify-content-center"
                                                     style="background-color: {{ $assignment->color }}; color: white; font-weight: bold; font-size: 11px;"
                                                     data-assignment-id="{{ $assignment->id }}"
                                                     data-user-id="{{ $assignment->user_id }}">
                                                    {{ substr($assignment->user->name, 0, 1) }}
                                                </div>
                                            @endif
                                            @endif
                                        </td>
                                    @endfor
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-muted">
            <small>
                <i class="fas fa-info-circle"></i>
                Each row = 1 hour. Each day has 4 columns (max 4 users per hour).
                <strong class="text-primary">DRAG to select multiple cells!</strong>
            </small>
        </div>
    </div>
</div>

<!-- Shift Details for All Days -->
<div class="card mt-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="fas fa-calendar-week"></i> Shift Details
        </h5>
    </div>
    <div class="card-body">
        @php
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $scheduleStart = \Carbon\Carbon::parse($shift->start_date);
        @endphp

        @foreach($days as $index => $day)
            @php
                $currentDate = (clone $scheduleStart)->addDays($index);
                $dateStr = $currentDate->format('d - M - Y');

                // Get assignments for this day, excluding Sunday 22-23 assignments
                $assignmentsQuery = \App\Models\ShiftAssignment::where('shift_schedule_id', $shift->id)
                    ->where('day_of_week', $day);

                // For Monday: exclude assignments with original_calendar_day='sunday'
                // (those are Sunday 22-23 assignments, not actual Monday assignments)
                if ($day === 'monday') {
                    $assignmentsQuery->where(function($q) {
                        $q->whereNull('original_calendar_day')
                          ->orWhere('original_calendar_day', '!=', 'sunday');
                    });
                }

                $assignments = $assignmentsQuery->with(['user', 'changeLogs' => function($q) use ($currentDate) {
                        $q->whereDate('effective_date', $currentDate->format('Y-m-d'))
                          ->with(['originalUser', 'newUser'])
                          ->orderBy('created_at', 'desc');
                    }])
                    ->get();

                $shift1Users = $assignments->where('shift_id', 1);
                $shift2Users = $assignments->where('shift_id', 2);
                $shift3Users = $assignments->where('shift_id', 3);

                $allChanges = [];
                foreach($assignments as $assign) {
                    foreach($assign->changeLogs as $log) {
                        $allChanges[] = $log;
                    }
                }
            @endphp

            <div class="mb-4 pb-3 border-bottom">
                <h6 class="mb-3 text-primary">
                    <i class="fas fa-calendar-day"></i> {{ $dayNames[$index] }}, {{ $dateStr }}
                </h6>

                <div class="row">
                    <!-- Shift 1 -->
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-light">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <strong class="text-primary">Shift 1 (22:00-05:00)</strong>
                                </div>
                            </div>
                            <div>
                                @if($shift1Users->count() > 0)
                                    @foreach($shift1Users as $assignment)
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            @if($assignment->change_action === 'cancelled')
                                                <span class="badge bg-danger text-decoration-line-through">{{ $assignment->user->name }} (Cancelled)</span>
                                            @elseif($assignment->change_action === 'replaced')
                                                <span class="badge bg-warning">{{ $assignment->user->name }} → {{ $assignment->newUser->name }}</span>
                                            @else
                                                <span class="badge bg-info">{{ $assignment->user->name }}</span>
                                            @endif
                                            <button class="btn btn-sm btn-outline-primary"
                                                    onclick="openChangeModal({{ $assignment->id }}, '{{ $day }}', '{{ $dateStr }}', 1, '{{ $assignment->user->name }}')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Shift 2 -->
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-light">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <strong class="text-success">Shift 2 (06:00-13:00)</strong>
                                </div>
                            </div>
                            <div>
                                @if($shift2Users->count() > 0)
                                    @foreach($shift2Users as $assignment)
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            @if($assignment->change_action === 'cancelled')
                                                <span class="badge bg-danger text-decoration-line-through">{{ $assignment->user->name }} (Cancelled)</span>
                                            @elseif($assignment->change_action === 'replaced')
                                                <span class="badge bg-warning">{{ $assignment->user->name }} → {{ $assignment->newUser->name }}</span>
                                            @else
                                                <span class="badge bg-info">{{ $assignment->user->name }}</span>
                                            @endif
                                            <button class="btn btn-sm btn-outline-primary"
                                                    onclick="openChangeModal({{ $assignment->id }}, '{{ $day }}', '{{ $dateStr }}', 2, '{{ $assignment->user->name }}')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Shift 3 -->
                    <div class="col-md-4">
                        <div class="p-3 border rounded bg-light">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <strong class="text-warning">Shift 3 (14:00-21:00)</strong>
                                </div>
                            </div>
                            <div>
                                @if($shift3Users->count() > 0)
                                    @foreach($shift3Users as $assignment)
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            @if($assignment->change_action === 'cancelled')
                                                <span class="badge bg-danger text-decoration-line-through">{{ $assignment->user->name }} (Cancelled)</span>
                                            @elseif($assignment->change_action === 'replaced')
                                                <span class="badge bg-warning">{{ $assignment->user->name }} → {{ $assignment->newUser->name }}</span>
                                            @else
                                                <span class="badge bg-info">{{ $assignment->user->name }}</span>
                                            @endif
                                            <button class="btn btn-sm btn-outline-primary"
                                                    onclick="openChangeModal({{ $assignment->id }}, '{{ $day }}', '{{ $dateStr }}', 3, '{{ $assignment->user->name }}')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shift Changes -->
                @if(count($allChanges) > 0)
                    <div class="mt-3">
                        <h6 class="text-muted mb-2">
                            <i class="fas fa-history"></i> Shift Changes:
                        </h6>
                        <ul class="list-unstyled ms-3">
                            @foreach($allChanges as $change)
                                <li class="mb-2 text-muted">
                                    <i class="fas fa-circle" style="font-size: 6px; vertical-align: middle;"></i>
                                    <small><strong>{{ $change->created_at->format('H:i A') }}</strong> - {{ $change->change_description }}</small>
                                    <br>
                                    <small class="ms-3 fst-italic">Reason: {{ $change->reason }}</small>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

<!-- Select User First Modal -->
<div class="modal fade" id="selectUserFirstModal" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> User Required
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-user-slash fa-3x text-warning mb-3"></i>
                <p class="mb-0">Please select a user first before assigning schedule!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-check"></i> OK
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmAssignmentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-check text-primary"></i> Confirm Assignment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h6 class="text-muted mb-2">User to assign:</h6>
                    <div class="d-flex align-items-center">
                        <div id="modal-user-color" style="width: 20px; height: 20px; border-radius: 3px; margin-right: 10px;"></div>
                        <strong id="modal-user-name" class="fs-5"></strong>
                    </div>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Working hours:</h6>
                    <span id="modal-hour-range" class="badge bg-info fs-6"></span>
                </div>
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle"></i>
                    <small>System will automatically convert to shift-based storage for database efficiency.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="confirmAssignBtn">
                    <i class="fas fa-check"></i> Yes, Assign Schedule
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Shift Change Modal -->
<div class="modal fade" id="shiftChangeModal" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt"></i> Change Shift Assignment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="changeShiftId">
                <input type="hidden" id="changeDay">
                <input type="hidden" id="changeAssignmentId">

                <div class="alert alert-info">
                    <strong>Current Assignment:</strong>
                    <div id="currentUserDisplay" class="mt-2"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Change Type <span class="text-danger">*</span></label>
                    <select class="form-select" id="changeType" onchange="toggleReplacementUser()">
                        <option value="">Select type...</option>
                        <option value="replacement">Replace with another user</option>
                        <option value="cancellation">Cancel shift (no replacement)</option>
                    </select>
                </div>

                <div class="mb-3" id="replacementUserDiv" style="display: none;">
                    <label class="form-label">Replacement User <span class="text-danger">*</span></label>
                    <select class="form-select" id="replacementUserId">
                        <option value="">Select user...</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="changeReason" rows="3"
                              placeholder="e.g., Sick leave, Emergency, etc."></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Effective Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="effectiveDate">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="submitShiftChange()">
                    <i class="fas fa-check"></i> Apply Change
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Remove Assignment Modal (No Backdrop) -->
<div class="modal fade" id="removeAssignmentModal" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h6 class="modal-title mb-0">
                    <i class="fas fa-trash-alt"></i> Remove Assignment
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mb-2"><strong>Remove this assignment?</strong></p>
                <p class="text-muted small mb-0" id="removeAssignmentDetails"></p>
            </div>
            <div class="modal-footer justify-content-center p-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="confirmRemoveAssignments()">
                    <i class="fas fa-check"></i> OK
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-success">
            <div class="modal-header bg-success text-white">
                <h6 class="modal-title mb-0">
                    <i class="fas fa-check-circle"></i> Success
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <p class="mb-0" id="successMessage"></p>
            </div>
            <div class="modal-footer justify-content-center p-2">
                <button type="button" class="btn btn-sm btn-success" data-bs-dismiss="modal">
                    <i class="fas fa-check"></i> OK
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-circle"></i> Assignment Error
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-user-times fa-4x text-danger mb-3"></i>
                <h6 class="fw-bold mb-2" id="errorTitle">User Already Assigned</h6>
                <p class="mb-2" id="errorMessage">User is already assigned to this shift.</p>
                <div class="alert alert-warning mt-3 mb-0">
                    <small>
                        <i class="fas fa-info-circle"></i>
                        Each user can only be assigned <strong>once per shift</strong>, regardless of column.
                    </small>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

<style>
/* Modal always on top */
.modal-backdrop {
    z-index: 99999 !important;
}

.modal {
    z-index: 100000 !important;
}

.modal-dialog {
    z-index: 100001 !important;
}

.user-select-card {
    transition: all 0.2s;
    border: 2px solid transparent !important;
}

.user-select-card:hover {
    background-color: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.user-select-card.selected {
    border-color: #0d6efd !important;
    background-color: #e7f1ff;
    box-shadow: 0 0 0 3px rgba(13,110,253,0.25);
}

.shift-cell {
    transition: background-color 0.1s;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

.shift-cell:hover:not(:has(.assigned-cell)) {
    background-color: #e3f2fd !important;
}

.shift-cell.selecting {
    background-color: #90caf9 !important;
    border: 2px solid #1976d2 !important;
}

.shift-cell.removing {
    background-color: #ffcdd2 !important;
    border: 2px solid #c62828 !important;
}

.assigned-cell {
    cursor: pointer;
    transition: opacity 0.2s;
}

.assigned-cell:hover {
    opacity: 0.7;
}

.assigned-cell.selected-for-removal {
    outline: 3px solid #c62828;
    outline-offset: -3px;
    opacity: 0.8;
}

.cancelled-cell {
    cursor: pointer !important;
    animation: pulse-red 2s infinite;
}

.cancelled-cell:hover {
    opacity: 0.9 !important;
}

@keyframes pulse-red {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

/* Custom instant tooltip */
.custom-tooltip {
    position: fixed;
    background-color: rgba(0, 0, 0, 0.9);
    color: white;
    padding: 6px 10px;
    border-radius: 4px;
    font-size: 12px;
    white-space: nowrap;
    z-index: 9999;
    pointer-events: none;
    display: none;
}

.shift-calendar-hourly {
    font-size: 12px;
}

.shift-calendar-hourly td,
.shift-calendar-hourly th {
    border: 1px solid #dee2e6;
    padding: 2px;
}

/* Alternating background per day - every 4 columns */
/* Column 1 = Time, 2-5 = Monday, 6-9 = Tuesday, 10-13 = Wed, 14-17 = Thu, 18-21 = Fri, 22-25 = Sat, 26-29 = Sun */

/* Monday - White */
.shift-calendar-hourly tbody td:nth-child(2),
.shift-calendar-hourly tbody td:nth-child(3),
.shift-calendar-hourly tbody td:nth-child(4),
.shift-calendar-hourly tbody td:nth-child(5) {
    background-color: #ffffff;
}

/* Tuesday - Gray */
.shift-calendar-hourly tbody td:nth-child(6),
.shift-calendar-hourly tbody td:nth-child(7),
.shift-calendar-hourly tbody td:nth-child(8),
.shift-calendar-hourly tbody td:nth-child(9) {
    background-color: #f8f9fa;
}

/* Wednesday - White */
.shift-calendar-hourly tbody td:nth-child(10),
.shift-calendar-hourly tbody td:nth-child(11),
.shift-calendar-hourly tbody td:nth-child(12),
.shift-calendar-hourly tbody td:nth-child(13) {
    background-color: #ffffff;
}

/* Thursday - Gray */
.shift-calendar-hourly tbody td:nth-child(14),
.shift-calendar-hourly tbody td:nth-child(15),
.shift-calendar-hourly tbody td:nth-child(16),
.shift-calendar-hourly tbody td:nth-child(17) {
    background-color: #f8f9fa;
}

/* Friday - White */
.shift-calendar-hourly tbody td:nth-child(18),
.shift-calendar-hourly tbody td:nth-child(19),
.shift-calendar-hourly tbody td:nth-child(20),
.shift-calendar-hourly tbody td:nth-child(21) {
    background-color: #ffffff;
}

/* Saturday - Gray */
.shift-calendar-hourly tbody td:nth-child(22),
.shift-calendar-hourly tbody td:nth-child(23),
.shift-calendar-hourly tbody td:nth-child(24),
.shift-calendar-hourly tbody td:nth-child(25) {
    background-color: #f8f9fa;
}

/* Sunday - White */
.shift-calendar-hourly tbody td:nth-child(26),
.shift-calendar-hourly tbody td:nth-child(27),
.shift-calendar-hourly tbody td:nth-child(28),
.shift-calendar-hourly tbody td:nth-child(29) {
    background-color: #ffffff;
}

/* Shift Separators - now handled by inline styles in the view */
/* Borders are added after hours 5, 13, and 21 to separate shifts */

.sticky-top {
    position: sticky;
    top: 0;
    background: white;
    z-index: 10;
}

.time-cell {
    font-size: 11px;
}

.cursor-pointer {
    cursor: pointer;
}
</style>

@push('scripts')
<script>
const routePrefix = '{{ $routePrefix ?? "admin" }}';
const shiftsBaseUrl = `/${routePrefix}/shifts`;
const assignUserHourlyUrl = '{{ route(($routePrefix ?? "admin").".shifts.assign-user-hourly", $shift) }}';
const removeAssignmentsUrl = '{{ route(($routePrefix ?? "admin").".shifts.remove-assignments", $shift) }}';
const removeAssignmentUrl = '{{ route(($routePrefix ?? "admin").".shifts.remove-assignment", $shift) }}';
const clearAllAssignmentsUrl = '{{ route(($routePrefix ?? "admin").".shifts.clear-all-assignments", $shift) }}';

let selectedUser = null;
let isSelecting = false;
let isRemovingMode = false;
let startCell = null;
let selectedCells = new Set();
let cellsToRemove = new Set();

// Track user assignments by shift (to prevent duplicate assignments in different columns)
// Format: { 'schedule_id_day_shift_id': [{user_id: '2', column: 0}, {user_id: '3', column: 1}] }
let userAssignmentsByShift = {};

// Show error modal with custom message
function showErrorModal(title, message) {
    document.getElementById('errorTitle').textContent = title;
    document.getElementById('errorMessage').textContent = message;

    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'), {
        backdrop: false,
        keyboard: true
    });
    errorModal.show();
}

console.log('Shift calendar script loaded');

// Initialize user assignments tracking from existing DOM data
document.querySelectorAll('.assigned-cell[data-user-id]').forEach(cell => {
    const cellElement = cell.closest('.shift-cell');
    if (cellElement) {
        const day = cellElement.dataset.day;
        const hour = parseInt(cellElement.dataset.hour);
        const userId = cell.dataset.userId;
        const columnIndex = parseInt(cellElement.dataset.column);

        // Determine shift ID
        let shiftId;
        if ((hour >= 22 && hour <= 23) || (hour >= 0 && hour <= 5)) {
            shiftId = 1;
        } else if (hour >= 6 && hour <= 13) {
            shiftId = 2;
        } else if (hour >= 14 && hour <= 21) {
            shiftId = 3;
        }

        const scheduleId = '{{ $shift->id }}';
        const key = `${scheduleId}_${day}_${shiftId}`;

        // Track this assignment with user_id and column_index
        if (!userAssignmentsByShift[key]) {
            userAssignmentsByShift[key] = [];
        }

        // Check if this user+column combo already exists
        const exists = userAssignmentsByShift[key].find(item => item.user_id === userId && item.column === columnIndex);
        if (!exists) {
            userAssignmentsByShift[key].push({user_id: userId, column: columnIndex});
        }
    }
});

// User selection
document.querySelectorAll('.user-select-card').forEach(card => {
    card.addEventListener('click', function(e) {
        e.preventDefault();
        console.log('User card clicked:', this.dataset.userName);

        // Remove previous selection
        document.querySelectorAll('.user-select-card').forEach(c => c.classList.remove('selected'));

        // Select this user
        this.classList.add('selected');
        selectedUser = {
            id: this.dataset.userId,
            name: this.dataset.userName,
            color: this.dataset.userColor
        };

        // Update display
        document.getElementById('selected-user-display').textContent = '(Selected: ' + selectedUser.name + ')';

        console.log('Selected user:', selectedUser);
    });
});

// Get all shift cells
const shiftCells = document.querySelectorAll('.shift-cell');
console.log('Total shift cells:', shiftCells.length);

// Prevent default drag behavior
document.addEventListener('dragstart', function(e) {
    e.preventDefault();
    return false;
});

// Mouse down - start selection
shiftCells.forEach(cell => {
    cell.addEventListener('mousedown', function(e) {
        e.preventDefault();
        console.log('Mouse down on cell:', this.dataset.day, this.dataset.hour, this.dataset.column);

        // If clicking on assigned cell, start remove mode
        if (this.querySelector('.assigned-cell')) {
            console.log('Clicking on assigned cell, starting remove mode...');

            // Start remove mode
            isRemovingMode = true;
            isSelecting = true;
            startCell = this;
            cellsToRemove.clear();

            // Clear previous highlights
            document.querySelectorAll('.shift-cell').forEach(c => {
                c.classList.remove('selecting', 'removing');
            });
            document.querySelectorAll('.assigned-cell').forEach(c => {
                c.classList.remove('selected-for-removal');
            });

            // Mark this cell for removal
            this.classList.add('removing');
            const assignedCell = this.querySelector('.assigned-cell');
            if (assignedCell) {
                assignedCell.classList.add('selected-for-removal');
                cellsToRemove.add(this);
            }

            console.log('Started remove mode');
            return;
        }

        if (!selectedUser) {
            const selectUserModal = new bootstrap.Modal(document.getElementById('selectUserFirstModal'));
            selectUserModal.show();
            console.log('No user selected');
            return;
        }

        // Start normal assignment selection
        isRemovingMode = false;
        isSelecting = true;
        startCell = this;
        selectedCells.clear();

        // Clear previous highlights
        document.querySelectorAll('.shift-cell').forEach(c => {
            c.classList.remove('selecting', 'removing');
        });

        // Add this cell
        this.classList.add('selecting');
        selectedCells.add(this);

        console.log('Started selecting from:', this.dataset.day, this.dataset.hour);
    });

    // Mouse enter - continue selection (rectangular selection)
    cell.addEventListener('mouseenter', function(e) {
        if (isSelecting && isRemovingMode) {
            // Remove mode - select assigned cells
            if (this.querySelector('.assigned-cell')) {
                updateRemoveSelection(startCell, this);
            }
        } else if (isSelecting && !this.querySelector('.assigned-cell')) {
            // Normal assignment mode
            updateSelection(startCell, this);
        }
    });

    // Mouse up on cell - end selection
    cell.addEventListener('mouseup', function(e) {
        if (isSelecting) {
            console.log('Mouse up, finishing selection');
            if (isRemovingMode) {
                finishRemoveSelection();
            } else {
                finishSelection();
            }
        }
    });
});

// Mouse up on document - end selection (in case mouse released outside cells)
document.addEventListener('mouseup', function(e) {
    if (isSelecting) {
        console.log('Mouse up on document, finishing selection');
        if (isRemovingMode) {
            finishRemoveSelection();
        } else {
            finishSelection();
        }
    }
});

// Variable to store pending assignment
let pendingAssignmentCells = null;

// NEW CONCEPT: Display hours on the SAME day they are stored
// Monday Shift 1 (22:00-05:00) -> all hours display on MONDAY column
// No more conversion of hours 22-23 back to previous day
function getCalendarDayForDisplay(dutyDay, hour) {
    // Simply return the duty day as-is
    // All hours in a shift belong to the same day column
    return dutyDay;
}

// Update rectangular selection
function updateSelection(start, end) {
    // Clear all previous selections
    document.querySelectorAll('.shift-cell').forEach(c => c.classList.remove('selecting'));
    selectedCells.clear();

    // Get boundaries
    const startDay = start.dataset.day;
    const endDay = end.dataset.day;
    const startHour = parseInt(start.dataset.hour);
    const endHour = parseInt(end.dataset.hour);
    const startCol = parseInt(start.dataset.column);
    const endCol = parseInt(end.dataset.column);

    // For simplicity, only select cells in same day and column
    // but allow hour range selection
    if (startDay === endDay && startCol === endCol) {
        // Use same hour order as display: [22, 23, 0, 1, 2, ..., 21]
        const hourOrder = [22, 23, ...Array.from({length: 22}, (_, i) => i)];

        const startIndex = hourOrder.indexOf(startHour);
        const endIndex = hourOrder.indexOf(endHour);
        const minIndex = Math.min(startIndex, endIndex);
        const maxIndex = Math.max(startIndex, endIndex);

        // Select all cells in range based on display order
        for (let i = minIndex; i <= maxIndex; i++) {
            const hour = hourOrder[i];
            const cell = document.querySelector(
                `[data-day="${startDay}"][data-hour="${hour}"][data-column="${startCol}"]`
            );
            if (cell && !cell.querySelector('.assigned-cell')) {
                cell.classList.add('selecting');
                selectedCells.add(cell);
            }
        }
    } else {
        // Different day or column, just select the end cell
        if (!end.querySelector('.assigned-cell')) {
            end.classList.add('selecting');
            selectedCells.add(end);
        }
    }
}

// Finish selection and show confirmation modal
function finishSelection() {
    if (!isSelecting) return;

    isSelecting = false;

    if (selectedCells.size > 0) {
        console.log('Finishing selection, total cells:', selectedCells.size);
        const cellsArray = Array.from(selectedCells);

        // Store pending assignment
        pendingAssignmentCells = cellsArray;

        // Show confirmation modal
        showConfirmationModal(cellsArray);
    } else {
        // Clear highlights
        document.querySelectorAll('.shift-cell').forEach(c => c.classList.remove('selecting'));
    }
}

// Update remove selection
function updateRemoveSelection(start, end) {
    // Clear all previous selections
    document.querySelectorAll('.shift-cell').forEach(c => c.classList.remove('removing'));
    document.querySelectorAll('.assigned-cell').forEach(c => c.classList.remove('selected-for-removal'));
    cellsToRemove.clear();

    // Get boundaries
    const startDay = start.dataset.day;
    const endDay = end.dataset.day;
    const startHour = parseInt(start.dataset.hour);
    const endHour = parseInt(end.dataset.hour);
    const startCol = parseInt(start.dataset.column);
    const endCol = parseInt(end.dataset.column);

    // For simplicity, only select cells in same day and column
    // but allow hour range selection
    if (startDay === endDay && startCol === endCol) {
        // Use same hour order as display: [22, 23, 0, 1, 2, ..., 21]
        const hourOrder = [22, 23, ...Array.from({length: 22}, (_, i) => i)];

        const startIndex = hourOrder.indexOf(startHour);
        const endIndex = hourOrder.indexOf(endHour);
        const minIndex = Math.min(startIndex, endIndex);
        const maxIndex = Math.max(startIndex, endIndex);

        // Select all assigned cells in range based on display order
        for (let i = minIndex; i <= maxIndex; i++) {
            const hour = hourOrder[i];
            const cell = document.querySelector(
                `[data-day="${startDay}"][data-hour="${hour}"][data-column="${startCol}"]`
            );
            if (cell && cell.querySelector('.assigned-cell')) {
                cell.classList.add('removing');
                const assignedCell = cell.querySelector('.assigned-cell');
                assignedCell.classList.add('selected-for-removal');
                cellsToRemove.add(cell);
            }
        }
    } else {
        // Different day or column, just select the end cell
        if (end.querySelector('.assigned-cell')) {
            end.classList.add('removing');
            const assignedCell = end.querySelector('.assigned-cell');
            assignedCell.classList.add('selected-for-removal');
            cellsToRemove.add(end);
        }
    }
}

// Finish remove selection and show confirmation modal
function finishRemoveSelection() {
    if (!isSelecting) return;

    isSelecting = false;
    isRemovingMode = false;

    if (cellsToRemove.size > 0) {
        console.log('Finishing remove selection, total cells:', cellsToRemove.size);
        showRemoveConfirmationModal(Array.from(cellsToRemove));
    } else {
        // Clear highlights
        document.querySelectorAll('.shift-cell').forEach(c => c.classList.remove('removing'));
        document.querySelectorAll('.assigned-cell').forEach(c => c.classList.remove('selected-for-removal'));
    }
}

// Show remove confirmation modal
function showRemoveConfirmationModal(cells) {
    if (cells.length === 0) return;

    // Build removal data: group hours by assignment ID
    const removalData = {};

    cells.forEach(cell => {
        const assignedCell = cell.querySelector('.assigned-cell');
        if (assignedCell) {
            const assignmentId = assignedCell.dataset.assignmentId;
            const hour = parseInt(cell.dataset.hour);

            if (!removalData[assignmentId]) {
                removalData[assignmentId] = {
                    assignment_id: assignmentId,
                    hours: []
                };
            }

            if (!removalData[assignmentId].hours.includes(hour)) {
                removalData[assignmentId].hours.push(hour);
            }
        }
    });

    const removalArray = Object.values(removalData);

    console.log('Removal data:', removalArray);

    if (removalArray.length === 0) {
        console.error('No valid assignments found');
        return;
    }

    // Count total hours to remove
    const totalHours = removalArray.reduce((sum, item) => sum + item.hours.length, 0);

    // Update modal details
    const detailsText = `${totalHours} hour(s) will be removed from ${removalArray.length} assignment(s)`;
    document.getElementById('removeAssignmentDetails').textContent = detailsText;

    // Store for confirmation
    window.pendingRemovalData = removalArray;
    window.pendingRemovalCells = cells;

    console.log('Stored pending removal data:', window.pendingRemovalData);

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('removeAssignmentModal'));
    modal.show();
}

// Show confirmation modal
function showConfirmationModal(cells) {
    if (!selectedUser) {
        console.error('No user selected');
        return;
    }

    // Check if user already has assignment in this shift
    const firstCell = cells[0];
    const day = firstCell.dataset.day;
    const firstHour = parseInt(firstCell.dataset.hour);

    // Determine which shift this belongs to
    let shiftId;
    if ((firstHour >= 22 && firstHour <= 23) || (firstHour >= 0 && firstHour <= 5)) {
        shiftId = 1; // Shift 1: 22:00-05:00
    } else if (firstHour >= 6 && firstHour <= 13) {
        shiftId = 2; // Shift 2: 06:00-13:00
    } else if (firstHour >= 14 && firstHour <= 21) {
        shiftId = 3; // Shift 3: 14:00-21:00
    }

    // Check tracking object for existing assignment
    const scheduleId = '{{ $shift->id }}';
    const trackingKey = `${scheduleId}_${day}_${shiftId}`;

    // Get column index from first selected cell
    const selectedColumn = parseInt(firstCell.dataset.column);

    // Check if THIS USER is already assigned to this shift in a DIFFERENT column
    if (userAssignmentsByShift[trackingKey]) {
        const existingAssignment = userAssignmentsByShift[trackingKey].find(
            item => item.user_id === selectedUser.id && item.column !== selectedColumn
        );

        if (existingAssignment) {
            const shiftNames = {1: 'Shift 1 (22:00-05:00)', 2: 'Shift 2 (06:00-13:00)', 3: 'Shift 3 (14:00-21:00)'};
            const dayName = day.charAt(0).toUpperCase() + day.slice(1);

            // Show error modal
            showErrorModal(
                `${selectedUser.name} Already Assigned`,
                `User ${selectedUser.name} is already assigned to ${shiftNames[shiftId]} on ${dayName} in column ${existingAssignment.column + 1}. Cannot assign to a different column in the same shift.`
            );

            // Clear selection
            document.querySelectorAll('.shift-cell').forEach(c => c.classList.remove('selecting'));
            selectedCells.clear();
            return;
        }
    }

    // Update modal content
    document.getElementById('modal-user-name').textContent = selectedUser.name;
    document.getElementById('modal-user-color').style.backgroundColor = selectedUser.color;

    // Calculate hour range from selected cells using display order
    const hourOrder = [22, 23, ...Array.from({length: 22}, (_, i) => i)];
    const hours = cells.map(cell => parseInt(cell.dataset.hour));

    // Sort hours by their position in hourOrder
    hours.sort((a, b) => {
        const indexA = hourOrder.indexOf(a);
        const indexB = hourOrder.indexOf(b);
        return indexA - indexB;
    });

    const minHour = hours[0];
    const maxHour = hours[hours.length - 1];
    const hourRange = `${String(minHour).padStart(2, '0')}:00 - ${String(maxHour).padStart(2, '0')}:00`;
    document.getElementById('modal-hour-range').textContent = hourRange;

    // Show modal without backdrop
    var myModal = new bootstrap.Modal(document.getElementById('confirmAssignmentModal'), {
        backdrop: false,
        keyboard: true
    });
    myModal.show();
}

// Handle confirmation button click
document.getElementById('confirmAssignBtn').addEventListener('click', function() {
    if (pendingAssignmentCells) {
        // Close modal
        var modal = bootstrap.Modal.getInstance(document.getElementById('confirmAssignmentModal'));
        if (modal) modal.hide();

        // Assign user to cells
        assignUserToCells(pendingAssignmentCells);

        // Clear pending assignment
        pendingAssignmentCells = null;
    }
});

// Clear highlights when modal is closed (cancel)
document.getElementById('confirmAssignmentModal').addEventListener('hidden.bs.modal', function() {
    // Clear highlights
    document.querySelectorAll('.shift-cell').forEach(c => c.classList.remove('selecting'));
    selectedCells.clear();
    pendingAssignmentCells = null;
});

// Assign user to multiple cells
function assignUserToCells(cells) {
    if (!selectedUser) {
        console.error('No user selected');
        return;
    }

    console.log('Assigning user to cells:', cells.length);

    const assignments = cells.map(cell => ({
        day: cell.dataset.day,
        hour: parseInt(cell.dataset.hour),
        column: parseInt(cell.dataset.column)
    }));

    console.log('Assignment data:', assignments);

    // Send AJAX request
    const url = assignUserHourlyUrl;
    console.log('Sending to URL:', url);

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            user_id: selectedUser.id,
            assignments: assignments
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);

        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server returned non-JSON response. Got: ' + contentType);
        }

        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);

        if (data.success) {
            console.log('✅ Assignment successful! Updating UI...');
            console.log('Assignments received:', data.assignments);

            // Update UI for each assignment
            data.assignments.forEach(assignment => {
                // Use selected_hours if available, otherwise use all shift hours
                const hours = assignment.selected_hours || [];

                if (!hours || hours.length === 0) {
                    console.error('❌ No hours in assignment:', assignment);
                    return;
                }

                console.log(`📝 Processing assignment: duty_day=${assignment.day_of_week}, shift=${assignment.shift_id}, hours=[${hours}]`);

                // Update each hour cell for this assignment
                hours.forEach(hour => {
                    // Get calendar day for display (reverse overnight shift mapping)
                    const displayDay = getCalendarDayForDisplay(assignment.day_of_week, hour);

                    const cell = document.querySelector(
                        `[data-day="${displayDay}"][data-hour="${hour}"][data-column="${assignment.column_index}"]`
                    );

                    if (cell) {
                        cell.innerHTML = `
                            <div class="assigned-cell h-100 w-100 d-flex align-items-center justify-content-center"
                                 style="background-color: ${selectedUser.color}; color: white; font-weight: bold; font-size: 11px;"
                                 data-assignment-id="${assignment.id}"
                                 data-user-id="${selectedUser.id}">
                                ${selectedUser.name.charAt(0)}
                            </div>
                        `;

                        // Click handler removed - now using drag selection for removal
                    } else {
                        console.error(`❌ Cell NOT found: display_day=${displayDay}, hour=${hour}, column=${assignment.column_index}`);
                    }
                });
            });

            // Update user total hours
            const hoursSpan = document.querySelector(`.user-hours-${selectedUser.id}`);
            if (hoursSpan) {
                hoursSpan.textContent = data.total_hours;
            }

            // Update tracking object with new assignments
            data.assignments.forEach(assignment => {
                const scheduleId = '{{ $shift->id }}';
                const trackingKey = `${scheduleId}_${assignment.day_of_week}_${assignment.shift_id}`;

                if (!userAssignmentsByShift[trackingKey]) {
                    userAssignmentsByShift[trackingKey] = [];
                }

                // Check if this user+column combo already exists
                const exists = userAssignmentsByShift[trackingKey].find(
                    item => item.user_id === selectedUser.id && item.column === assignment.column_index
                );

                if (!exists) {
                    userAssignmentsByShift[trackingKey].push({
                        user_id: selectedUser.id,
                        column: assignment.column_index
                    });
                }
            });

            showToast(`✓ Assigned ${selectedUser.name} to ${data.assignments.length} cell(s)`, 'success');
        } else {
            // Show error message from backend using modal
            const errorMessage = data.error || data.message || 'Unknown error occurred';
            showErrorModal('Assignment Failed', errorMessage);

            // Clear selection
            document.querySelectorAll('.shift-cell').forEach(c => c.classList.remove('selecting'));
            selectedCells.clear();
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);

        // Show error modal
        showErrorModal('Error Occurred', error.message || 'An unexpected error occurred while processing your request.');

        // Clear selection
        document.querySelectorAll('.shift-cell').forEach(c => c.classList.remove('selecting'));
        selectedCells.clear();
    });
}

// Confirm and execute multiple assignment removals
function confirmRemoveAssignments() {
    const removalData = window.pendingRemovalData;
    const cells = window.pendingRemovalCells;

    console.log('confirmRemoveAssignments called');
    console.log('Pending removal data:', removalData);
    console.log('Pending removal cells:', cells);

    if (!removalData || removalData.length === 0) {
        console.error('No assignments to remove');
        return;
    }

    console.log('Sending remove request with data:', removalData);

    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('removeAssignmentModal'));
    if (modal) modal.hide();

    // Send bulk remove request
    const url = removeAssignmentsUrl;
    console.log('Request URL:', url);

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            removals: removalData
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            console.error('Non-JSON response received');
            throw new Error('Server returned non-JSON response');
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);

        if (data.success) {
            // First, collect affected assignments data BEFORE clearing cells
            const affectedAssignments = {};

            cells.forEach(cell => {
                const assignedCell = cell.querySelector('.assigned-cell');
                if (assignedCell) {
                    const userId = assignedCell.dataset.userId;
                    const day = cell.dataset.day;
                    const hour = parseInt(cell.dataset.hour);
                    const columnIndex = parseInt(cell.dataset.column);

                    // Determine shift ID
                    let shiftId;
                    if ((hour >= 22 && hour <= 23) || (hour >= 0 && hour <= 5)) {
                        shiftId = 1;
                    } else if (hour >= 6 && hour <= 13) {
                        shiftId = 2;
                    } else if (hour >= 14 && hour <= 21) {
                        shiftId = 3;
                    }

                    const key = `${userId}_${day}_${shiftId}_${columnIndex}`;
                    affectedAssignments[key] = { userId, day, shiftId, columnIndex };
                }
            });

            // Now clear the selected cells
            cells.forEach(cell => {
                cell.innerHTML = '';
                cell.classList.remove('removing');
            });

            // For each affected assignment, check if user still has hours in that shift+column
            Object.values(affectedAssignments).forEach(({ userId, day, shiftId, columnIndex }) => {
                const scheduleId = '{{ $shift->id }}';
                const trackingKey = `${scheduleId}_${day}_${shiftId}`;

                // Check if any cells for this user+column still exist in the shift
                const remainingCells = document.querySelectorAll(
                    `[data-day="${day}"][data-column="${columnIndex}"] .assigned-cell[data-user-id="${userId}"]`
                );

                // Filter to only cells in this shift
                let hasRemainingHours = false;
                remainingCells.forEach(cell => {
                    const cellElement = cell.closest('.shift-cell');
                    const hour = parseInt(cellElement.dataset.hour);

                    let cellShiftId;
                    if ((hour >= 22 && hour <= 23) || (hour >= 0 && hour <= 5)) {
                        cellShiftId = 1;
                    } else if (hour >= 6 && hour <= 13) {
                        cellShiftId = 2;
                    } else if (hour >= 14 && hour <= 21) {
                        cellShiftId = 3;
                    }

                    if (cellShiftId === shiftId) {
                        hasRemainingHours = true;
                    }
                });

                // If no remaining hours, remove from tracking
                if (!hasRemainingHours && userAssignmentsByShift[trackingKey]) {
                    const index = userAssignmentsByShift[trackingKey].findIndex(
                        item => item.user_id === userId && item.column === columnIndex
                    );

                    if (index > -1) {
                        userAssignmentsByShift[trackingKey].splice(index, 1);

                        // If array is empty, delete the key
                        if (userAssignmentsByShift[trackingKey].length === 0) {
                            delete userAssignmentsByShift[trackingKey];
                        }
                    }
                }
            });

            // Clear selections
            document.querySelectorAll('.assigned-cell').forEach(c => {
                c.classList.remove('selected-for-removal');
            });

            // Update user hours if available
            if (data.user_hours) {
                Object.keys(data.user_hours).forEach(userId => {
                    const hoursSpan = document.querySelector(`.user-hours-${userId}`);
                    if (hoursSpan) {
                        hoursSpan.textContent = data.user_hours[userId];
                    }
                });
            }

            const totalHours = removalData.reduce((sum, item) => sum + item.hours.length, 0);
            showToast(`✓ Removed ${totalHours} hour(s)`, 'success');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('Error occurred: ' + error.message);
    });

    // Clear pending data
    window.pendingRemovalData = null;
    window.pendingRemovalCells = null;
}

// OLD removeAssignment function - DEPRECATED
// Now using drag selection with modal confirmation (confirmRemoveAssignments)
// This function is kept for reference but should not be called
/*
function removeAssignment(cell) {
    const assignedCell = cell.querySelector('.assigned-cell');
    if (!assignedCell) return;

    const assignmentId = assignedCell.dataset.assignmentId;
    const userId = assignedCell.dataset.userId;

    console.log('Removing assignment:', assignmentId);

    fetch(removeAssignmentUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            assignment_id: assignmentId
        })
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server returned non-JSON response');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            cell.innerHTML = '';

            // Update user total hours
            const hoursSpan = document.querySelector(`.user-hours-${userId}`);
            if (hoursSpan) {
                hoursSpan.textContent = data.total_hours;
            }

            showToast('Assignment removed', 'success');
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred: ' + error.message);
    });
}
*/

// Clear all assignments
function clearAllAssignments() {
    if (!confirm('Are you sure you want to clear ALL assignments? This cannot be undone.')) {
        return;
    }

    fetch(clearAllAssignmentsUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear all cells
            document.querySelectorAll('.shift-cell').forEach(cell => {
                cell.innerHTML = '';
            });

            // Reset all user hours
            document.querySelectorAll('[class^="user-hours-"]').forEach(span => {
                span.textContent = '0';
            });

            showToast('All assignments cleared', 'success');
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

// Toast notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 250px;';
    toast.innerHTML = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Click handlers removed - now using drag selection for removal
// Users should click and drag on assigned cells to select them for removal

console.log('All event listeners attached');

// ==================== SHIFT DETAIL CARD FUNCTIONS ====================

// Open change modal
function openChangeModal(assignmentId, day, dateStr, shiftId, currentUserName) {
    // Set hidden fields
    document.getElementById('changeShiftId').value = shiftId;
    document.getElementById('changeDay').value = day;
    document.getElementById('changeAssignmentId').value = assignmentId;

    // Set current user display
    document.getElementById('currentUserDisplay').innerHTML = `
        <strong class="text-primary">${currentUserName}</strong> - Shift ${shiftId}
    `;

    // Set default effective date
    const dateObj = parseDisplayDate(dateStr);
    document.getElementById('effectiveDate').value = dateObj.toISOString().split('T')[0];

    // Reset form
    document.getElementById('changeType').value = '';
    document.getElementById('replacementUserId').value = '';
    document.getElementById('changeReason').value = '';
    document.getElementById('replacementUserDiv').style.display = 'none';

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('shiftChangeModal'));
    modal.show();
}

// Toggle replacement user dropdown
function toggleReplacementUser() {
    const changeType = document.getElementById('changeType').value;
    const replacementDiv = document.getElementById('replacementUserDiv');

    if (changeType === 'replacement') {
        replacementDiv.style.display = 'block';
    } else {
        replacementDiv.style.display = 'none';
    }
}

// Submit shift change
function submitShiftChange() {
    const assignmentId = document.getElementById('changeAssignmentId').value;
    const changeType = document.getElementById('changeType').value;
    const newUserId = document.getElementById('replacementUserId').value;
    const reason = document.getElementById('changeReason').value;
    const effectiveDate = document.getElementById('effectiveDate').value;

    // Validation
    if (!changeType) {
        alert('Please select change type');
        return;
    }

    if (changeType === 'replacement' && !newUserId) {
        alert('Please select replacement user');
        return;
    }

    if (!reason.trim()) {
        alert('Please enter reason for change');
        return;
    }

    if (!effectiveDate) {
        alert('Please select effective date');
        return;
    }

    // Close change modal first
    const changeModal = bootstrap.Modal.getInstance(document.getElementById('shiftChangeModal'));
    if (changeModal) changeModal.hide();

    // Submit
    fetch(`${shiftsBaseUrl}/change-assignment`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            assignment_id: assignmentId,
            change_type: changeType,
            new_user_id: newUserId || null,
            reason: reason,
            effective_date: effectiveDate
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Update UI based on change type
            if (result.change_type === 'cancellation') {
                // Find all cells with this assignment and mark as cancelled
                updateCellsAsCancelled(assignmentId, result.assignment.user.name, reason);

                // Also update shift details badge
                updateShiftDetailsBadge(assignmentId, 'cancelled', result.assignment.user.name);
            } else if (result.change_type === 'replacement') {
                // Update cells with new user
                updateCellsWithNewUser(assignmentId, result.assignment);

                // Also update shift details badge
                updateShiftDetailsBadge(assignmentId, 'replaced', result.assignment.user.name);
            }

            // Show success modal
            showSuccessModal(result.message);
        } else {
            alert('Error: ' + (result.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to apply change');
    });
}

// Show success modal
function showSuccessModal(message) {
    document.getElementById('successMessage').textContent = message;
    const modal = new bootstrap.Modal(document.getElementById('successModal'));
    modal.show();
}

// Update cells as cancelled (show X mark)
function updateCellsAsCancelled(assignmentId, userName, reason) {
    // Find all assigned-cell divs with this assignment ID
    const assignedCells = document.querySelectorAll(`.assigned-cell[data-assignment-id="${assignmentId}"]`);

    console.log('Found cells to cancel:', assignedCells.length);

    assignedCells.forEach(cell => {
        // Update the cell in place
        cell.className = 'assigned-cell cancelled-cell h-100 w-100 d-flex align-items-center justify-content-center';
        cell.style.backgroundColor = '#dc3545';
        cell.style.color = 'white';
        cell.style.fontWeight = 'bold';
        cell.style.fontSize = '16px';
        cell.style.cursor = 'pointer';
        cell.setAttribute('data-tooltip', `${userName} - ${reason}`);
        cell.innerHTML = '❌';

        // Add hover events for tooltip
        cell.addEventListener('mouseenter', function(e) {
            const tooltipEl = document.querySelector('.custom-tooltip');
            if (tooltipEl && this.hasAttribute('data-tooltip')) {
                const tooltipText = this.getAttribute('data-tooltip');
                tooltipEl.textContent = tooltipText;
                tooltipEl.style.display = 'block';
            }
        });

        cell.addEventListener('mouseleave', function(e) {
            const tooltipEl = document.querySelector('.custom-tooltip');
            if (tooltipEl) {
                tooltipEl.style.display = 'none';
            }
        });

        console.log('Cell updated to cancelled:', cell);
    });
}

// Update cells with new user (replacement)
function updateCellsWithNewUser(assignmentId, assignment) {
    // Find all cells with this assignment ID
    const cells = document.querySelectorAll(`.assigned-cell[data-assignment-id="${assignmentId}"]`);

    cells.forEach(cell => {
        // Update with new user
        cell.style.backgroundColor = assignment.color;
        cell.textContent = assignment.user.name.charAt(0);
        cell.dataset.userId = assignment.user_id;
    });
}

// Update shift details badge (for cancelled or replaced assignments)
function updateShiftDetailsBadge(assignmentId, status, userName) {
    // Find the badge in shift details section by searching for edit button with this assignment ID
    const editButton = document.querySelector(`button[onclick*="openChangeModal(${assignmentId}"]`);

    if (editButton) {
        const badgeContainer = editButton.closest('.d-flex').querySelector('.badge');

        if (badgeContainer) {
            if (status === 'cancelled') {
                // Mark as cancelled
                badgeContainer.className = 'badge bg-danger text-decoration-line-through';
                badgeContainer.innerHTML = `${userName} (Cancelled)`;
            } else if (status === 'replaced') {
                // Mark as replaced
                badgeContainer.className = 'badge bg-warning';
                badgeContainer.innerHTML = `${userName} (Replaced)`;
            }
        }
    }
}

// Helper: Parse display date (e.g., "22 - Dec - 2025")
function parseDisplayDate(dateStr) {
    const months = {
        'Jan': 0, 'Feb': 1, 'Mar': 2, 'Apr': 3, 'May': 4, 'Jun': 5,
        'Jul': 6, 'Aug': 7, 'Sep': 8, 'Oct': 9, 'Nov': 10, 'Dec': 11
    };

    const parts = dateStr.split(' - ');
    const day = parseInt(parts[0]);
    const month = months[parts[1]];
    const year = parseInt(parts[2]);

    return new Date(year, month, day);
}

// Custom instant tooltip system
document.addEventListener('DOMContentLoaded', function() {
    // Create tooltip element
    const tooltip = document.createElement('div');
    tooltip.className = 'custom-tooltip';
    document.body.appendChild(tooltip);

    // Add event listeners to all cancelled cells
    document.addEventListener('mouseenter', function(e) {
        if (e.target.classList.contains('cancelled-cell') && e.target.hasAttribute('data-tooltip')) {
            const tooltipText = e.target.getAttribute('data-tooltip');
            tooltip.textContent = tooltipText;
            tooltip.style.display = 'block';
        }
    }, true);

    document.addEventListener('mousemove', function(e) {
        if (tooltip.style.display === 'block') {
            // Position tooltip near cursor
            const offsetX = 15;
            const offsetY = 15;

            // Get tooltip dimensions
            const tooltipRect = tooltip.getBoundingClientRect();

            // Calculate position
            let left = e.clientX + offsetX;
            let top = e.clientY + offsetY;

            // Prevent tooltip from going off-screen (right)
            if (left + tooltipRect.width > window.innerWidth) {
                left = e.clientX - tooltipRect.width - offsetX;
            }

            // Prevent tooltip from going off-screen (bottom)
            if (top + tooltipRect.height > window.innerHeight) {
                top = e.clientY - tooltipRect.height - offsetY;
            }

            // Prevent tooltip from going off-screen (left)
            if (left < 0) {
                left = offsetX;
            }

            // Prevent tooltip from going off-screen (top)
            if (top < 0) {
                top = offsetY;
            }

            tooltip.style.left = left + 'px';
            tooltip.style.top = top + 'px';
        }
    });

    document.addEventListener('mouseleave', function(e) {
        if (e.target.classList.contains('cancelled-cell')) {
            tooltip.style.display = 'none';
        }
    }, true);
});

</script>
@endpush

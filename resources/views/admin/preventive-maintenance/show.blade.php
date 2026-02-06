@extends('layouts.admin')

@section('page-title', 'PM Schedule Details')

@push('styles')
<style>
    .date-card {
        border: 2px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    .date-card-header {
        background: linear-gradient(135deg, #2c3e50, #3498db);
        color: white;
        padding: 12px 15px;
        border-radius: 6px 6px 0 0;
    }
    .cleaning-group-card {
        border: 1px solid #dee2e6;
        border-radius: 6px;
        margin: 10px;
    }
    .cleaning-group-header {
        background: linear-gradient(135deg, #1e3c72, #2a5298);
        color: white;
        padding: 10px 15px;
        border-radius: 5px 5px 0 0;
    }
    .pm-table th { background: #f8f9fa; font-weight: 600; vertical-align: middle; }
    .pm-table td { vertical-align: middle; }
    .progress-card { border-left: 4px solid; }
    .progress-card.completed { border-left-color: #28a745; }
    .progress-card.pending { border-left-color: #6c757d; }
    .progress-card.in-progress { border-left-color: #ffc107; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="fas fa-calendar-check text-primary me-2"></i>
                {{ $schedule->title ?: 'PM Schedule' }}
            </h4>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar me-1"></i>
                {{ $schedule->scheduled_month->format('F Y') }}
                <span class="badge bg-{{ $schedule->status_badge }} ms-2">{{ ucfirst($schedule->status) }}</span>
            </p>
        </div>
        <div>
            @if($schedule->status === 'draft')
                <form action="{{ route($routePrefix.'Prefix.'.preventive-maintenance.activate', $schedule) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle me-1"></i> Activate
                    </button>
                </form>
            @endif
            <a href="{{ route($routePrefix.'Prefix.'.preventive-maintenance.edit', $schedule) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i> Edit Info
            </a>
            <a href="{{ route($routePrefix.'Prefix.'.preventive-maintenance.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    @php $stats = $schedule->task_stats; @endphp
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card progress-card pending">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    <small class="text-muted">Total Tasks</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card progress-card completed">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-success">{{ $stats['completed'] }}</h3>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card progress-card in-progress">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-warning">{{ $stats['in_progress'] }}</h3>
                    <small class="text-muted">In Progress</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $stats['progress'] }}%</h3>
                    <div class="progress mt-2" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ $stats['progress'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Date Button -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-calendar-plus me-2"></i>Schedule Dates</h5>
            <div class="d-flex align-items-center gap-2">
                <div class="input-group input-group-sm" style="width: auto;">
                    <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                    <input type="date" id="filterDateInput" class="form-control form-control-sm" style="width: 160px;" oninput="filterDates()" min="{{ $schedule->scheduled_month->startOfMonth()->format('Y-m-d') }}" max="{{ $schedule->scheduled_month->endOfMonth()->format('Y-m-d') }}">
                    <button class="btn btn-outline-secondary" onclick="clearDateFilter()" id="clearFilterBtn" style="display:none;" title="Clear filter">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <span class="text-muted">|</span>
                <div class="input-group input-group-sm" style="width: auto;">
                    <input type="date" id="newDateInput" class="form-control form-control-sm" style="width: 160px;" min="{{ $schedule->scheduled_month->startOfMonth()->format('Y-m-d') }}" max="{{ $schedule->scheduled_month->endOfMonth()->format('Y-m-d') }}">
                    <button class="btn btn-primary" onclick="addDate()">
                        <i class="fas fa-plus me-1"></i> Add Date
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Dates with their structure -->
    @foreach($schedule->scheduleDates as $scheduleDate)
        <div class="date-card" id="date-{{ $scheduleDate->id }}" data-date="{{ $scheduleDate->schedule_date->format('Y-m-d') }}">
            <div class="date-card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-calendar-day me-2"></i>
                    <strong>{{ $scheduleDate->schedule_date->format('l, d F Y') }}</strong>
                    <span class="badge bg-light text-dark ms-2">{{ $scheduleDate->cleaningGroups->count() }} Cleaning Groups</span>
                </div>
                <div>
                    <button class="btn btn-sm btn-outline-light me-1"
                            onclick="showAddStandaloneTaskModal({{ $scheduleDate->id }}, '{{ $scheduleDate->schedule_date->format('Y-m-d') }}')">
                        <i class="fas fa-plus me-1"></i> Add Task
                    </button>
                    <button class="btn btn-sm btn-outline-light me-1"
                            onclick="addCleaningGroup({{ $scheduleDate->id }})">
                        <i class="fas fa-plus me-1"></i> Add Cleaning Group
                    </button>
                    <button class="btn btn-sm btn-outline-danger"
                            onclick="deleteDate({{ $scheduleDate->id }})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="p-3">
                @foreach($scheduleDate->cleaningGroups as $cleaningGroup)
                    <div class="cleaning-group-card">
                        <div class="cleaning-group-header d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-broom me-2"></i>
                                <strong>{{ $cleaningGroup->name }}</strong>
                                <span class="badge bg-light text-dark ms-2">{{ $cleaningGroup->spr_count }} SPR</span>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-outline-light me-1"
                                        onclick="addSprGroup({{ $cleaningGroup->id }}, '{{ addslashes($cleaningGroup->name) }}')">
                                    <i class="fas fa-plus"></i> Add SPR
                                </button>
                                <button class="btn btn-sm btn-outline-danger"
                                        onclick="deleteCleaningGroup({{ $cleaningGroup->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="p-2">
                            @foreach($cleaningGroup->sprGroups as $sprGroup)
                                <div class="card mb-2">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                        <span>
                                            <i class="fas fa-folder text-primary me-2"></i>
                                            <strong>{{ $sprGroup->name }}</strong>
                                            <span class="badge bg-secondary ms-2">{{ $sprGroup->tasks->count() }} tasks</span>
                                        </span>
                                        <div>
                                            <button class="btn btn-sm btn-outline-success me-1"
                                                    onclick="showAddTaskModal({{ $sprGroup->id }}, '{{ addslashes($sprGroup->name) }}', '{{ $scheduleDate->schedule_date->format('Y-m-d') }}')">
                                                <i class="fas fa-plus"></i> Add Task
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteSprGroup({{ $sprGroup->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm table-hover pm-table mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Task</th>
                                                    <th width="100">Frequency</th>
                                                    <th width="150">Equipment</th>
                                                    <th width="120">Shift</th>
                                                    <th width="100">Status</th>
                                                    <th width="80">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($sprGroup->tasks as $task)
                                                    <tr>
                                                        <td>
                                                            {{ $task->task_name }}
                                                            @if($task->task_description)
                                                                <br><small class="text-muted">{{ $task->task_description }}</small>
                                                            @endif
                                                        </td>
                                                        <td><span class="badge bg-light text-dark border">{{ $task->frequency_label }}</span></td>
                                                        <td>
                                                            @if($task->equipment_type)
                                                                <span class="badge bg-light text-dark">{{ $task->equipment_type }}</span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($task->assigned_shift_id)
                                                                <span class="badge bg-primary">{{ $task->assigned_shift_name }}</span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-{{ $task->status_badge }}">
                                                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="dropdown">
                                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                                    <i class="fas fa-ellipsis-v"></i>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-end">
                                                                    <li><a class="dropdown-item" href="#" onclick="showEditTaskModal({{ $task->id }}, {{ json_encode(['task_name' => $task->task_name, 'frequency' => $task->frequency, 'equipment_type' => $task->equipment_type, 'assigned_shift_id' => $task->assigned_shift_id]) }}, '{{ $scheduleDate->schedule_date->format('Y-m-d') }}')"><i class="fas fa-edit text-primary me-2"></i> Edit</a></li>
                                                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteTask({{ $task->id }})"><i class="fas fa-trash me-2"></i> Delete</a></li>
                                                                </ul>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-3">No tasks yet</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach

                            @if($cleaningGroup->sprGroups->count() == 0)
                                <div class="text-center text-muted py-3">
                                    No SPR groups. Click "Add SPR" to create one.
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                @if($scheduleDate->standaloneTasks->count() > 0)
                    <div class="card mb-2">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                            <span>
                                <i class="fas fa-clipboard-list text-secondary me-2"></i>
                                <strong>Standalone Tasks</strong>
                                <span class="badge bg-secondary ms-2">{{ $scheduleDate->standaloneTasks->count() }} tasks</span>
                            </span>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-hover pm-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Task</th>
                                        <th width="100">Frequency</th>
                                        <th width="150">Equipment</th>
                                        <th width="120">Shift</th>
                                        <th width="100">Status</th>
                                        <th width="80">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($scheduleDate->standaloneTasks as $task)
                                        <tr>
                                            <td>
                                                {{ $task->task_name }}
                                                @if($task->task_description)
                                                    <br><small class="text-muted">{{ $task->task_description }}</small>
                                                @endif
                                            </td>
                                            <td><span class="badge bg-light text-dark border">{{ $task->frequency_label }}</span></td>
                                            <td>
                                                @if($task->equipment_type)
                                                    <span class="badge bg-light text-dark">{{ $task->equipment_type }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($task->assigned_shift_id)
                                                    <span class="badge bg-primary">{{ $task->assigned_shift_name }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $task->status_badge }}">
                                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="#" onclick="showEditTaskModal({{ $task->id }}, {{ json_encode(['task_name' => $task->task_name, 'frequency' => $task->frequency, 'equipment_type' => $task->equipment_type, 'assigned_shift_id' => $task->assigned_shift_id]) }}, '{{ $scheduleDate->schedule_date->format('Y-m-d') }}')"><i class="fas fa-edit text-primary me-2"></i> Edit</a></li>
                                                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteTask({{ $task->id }})"><i class="fas fa-trash me-2"></i> Delete</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if($scheduleDate->cleaningGroups->count() == 0 && $scheduleDate->standaloneTasks->count() == 0)
                    <div class="text-center text-muted py-3">
                        No tasks or cleaning groups. Click "Add Task" or "Add Cleaning Group" to start.
                    </div>
                @endif
            </div>
        </div>
    @endforeach

    @if($schedule->scheduleDates->count() == 0)
        <div class="card">
            <div class="card-body text-center py-5 text-muted">
                <i class="fas fa-calendar-plus fa-3x mb-3"></i>
                <p>No dates added yet. Use the date picker above to add dates to this schedule.</p>
            </div>
        </div>
    @endif

    <!-- Schedule Info -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Schedule Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><th width="150">Created By</th><td>{{ $schedule->creator?->name ?? 'System' }}</td></tr>
                        <tr><th>Created At</th><td>{{ $schedule->created_at->format('d M Y, H:i') }}</td></tr>
                        <tr><th>Last Updated</th><td>{{ $schedule->updated_at->format('d M Y, H:i') }}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    @if($schedule->description)
                        <h6>Description</h6>
                        <p class="text-muted">{{ $schedule->description }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Cleaning Group Modal -->
<div class="modal fade" id="addCleaningGroupModal" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-broom me-2"></i>Add Cleaning Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cleaningGroupDateId">
                <div class="mb-3">
                    <label class="form-label">Cleaning Group Name <span class="text-danger">*</span></label>
                    <input type="text" id="newCleaningGroupName" class="form-control" placeholder="e.g., Cleaning Area A">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitCleaningGroup()">
                    <i class="fas fa-plus me-1"></i> Add
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add SPR Group Modal -->
<div class="modal fade" id="addSprGroupModal" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-folder me-2"></i>Add SPR to <span id="sprModalCleaningName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="sprGroupCleaningId">
                <div class="mb-3">
                    <label class="form-label">SPR Name <span class="text-danger">*</span></label>
                    <input type="text" id="newSprGroupName" class="form-control" placeholder="e.g., SPR-001">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitSprGroup()">
                    <i class="fas fa-plus me-1"></i> Add
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Task to <span id="taskModalSprName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="taskModalSprId">
                <div class="mb-3">
                    <label class="form-label">Task Name</label>
                    <input type="text" id="newTaskName" class="form-control" placeholder="e.g., Check belt tension">
                </div>
                <div class="mb-3">
                    <label class="form-label">Frequency</label>
                    <select id="newTaskFrequency" class="form-select">
                        @foreach($frequencies as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Equipment Type</label>
                    <select id="newTaskEquipment" class="form-select">
                        <option value="">Select Equipment</option>
                        @foreach($equipmentTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Assign to Shift</label>
                    <select id="newTaskShift" class="form-select">
                        <option value="">Loading shifts...</option>
                    </select>
                    <div id="shiftWarning" class="alert alert-warning mt-2 mb-0 py-2 px-3 d-none" style="font-size: 0.85rem;">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <span id="shiftWarningText"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addTask()">Add</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editTaskId">
                <div class="mb-3">
                    <label class="form-label">Task Name</label>
                    <input type="text" id="editTaskName" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Frequency</label>
                    <select id="editTaskFrequency" class="form-select">
                        @foreach($frequencies as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Equipment Type</label>
                    <select id="editTaskEquipment" class="form-select">
                        <option value="">Select Equipment</option>
                        @foreach($equipmentTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Assign to Shift</label>
                    <select id="editTaskShift" class="form-select">
                        <option value="">Loading shifts...</option>
                    </select>
                    <div id="editShiftWarning" class="alert alert-warning mt-2 mb-0 py-2 px-3 d-none" style="font-size: 0.85rem;">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <span id="editShiftWarningText"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitEditTask()">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="deleteConfirmMessage" class="mb-0"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="deleteConfirmBtn">
                    <i class="fas fa-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const scheduleId = {{ $schedule->id }};
const csrfToken = '{{ csrf_token() }}';
const routePrefix = '{{ $routePrefix ?? "admin" }}';
const baseUrl = `/${routePrefix}/preventive-maintenance`;

function filterDates() {
    const filterValue = document.getElementById('filterDateInput').value;
    const clearBtn = document.getElementById('clearFilterBtn');
    const dateCards = document.querySelectorAll('.date-card');

    clearBtn.style.display = filterValue ? 'inline-block' : 'none';

    dateCards.forEach(card => {
        if (!filterValue) {
            card.style.display = '';
        } else {
            card.style.display = card.dataset.date === filterValue ? '' : 'none';
        }
    });
}

function clearDateFilter() {
    document.getElementById('filterDateInput').value = '';
    filterDates();
}

function addDate() {
    const dateInput = document.getElementById('newDateInput');
    const date = dateInput.value;
    if (!date) { alert('Please select a date'); return; }

    const btn = document.querySelector('.card-header .btn-primary');
    setSubmitting(btn, true);

    fetch(`${baseUrl}/${scheduleId}/date`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ schedule_date: date })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to add date');
            setSubmitting(btn, false);
        }
    })
    .catch(() => { alert('An error occurred'); setSubmitting(btn, false); });
}

function showDeleteModal(message, onConfirm) {
    document.getElementById('deleteConfirmMessage').textContent = message;
    const btn = document.getElementById('deleteConfirmBtn');
    const newBtn = btn.cloneNode(true);
    btn.parentNode.replaceChild(newBtn, btn);
    newBtn.addEventListener('click', function() {
        onConfirm();
        bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal')).hide();
    });
    new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
}

function deleteDate(id) {
    showDeleteModal('Delete this date and all its cleaning groups, SPR groups, and tasks?', function() {
        fetch(`${baseUrl}/date/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken }
        })
        .then(response => response.json())
        .then(data => { if (data.success) location.reload(); });
    });
}

function addCleaningGroup(scheduleDateId) {
    document.getElementById('cleaningGroupDateId').value = scheduleDateId;
    document.getElementById('newCleaningGroupName').value = '';
    new bootstrap.Modal(document.getElementById('addCleaningGroupModal')).show();
}

function setSubmitting(btn, submitting) {
    btn.disabled = submitting;
    if (submitting) {
        btn.dataset.originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
    } else {
        btn.innerHTML = btn.dataset.originalHtml;
    }
}

function submitCleaningGroup() {
    const scheduleDateId = document.getElementById('cleaningGroupDateId').value;
    const name = document.getElementById('newCleaningGroupName').value.trim();
    if (!name) { alert('Please enter a name'); return; }

    const btn = event.target.closest('button') || document.querySelector('#addCleaningGroupModal .btn-primary');
    setSubmitting(btn, true);

    fetch(`${baseUrl}/date/${scheduleDateId}/cleaning-group`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ name: name })
    })
    .then(response => response.json())
    .then(data => { if (data.success) location.reload(); else setSubmitting(btn, false); })
    .catch(() => setSubmitting(btn, false));
}

function addSprGroup(cleaningGroupId, cleaningGroupName) {
    document.getElementById('sprGroupCleaningId').value = cleaningGroupId;
    document.getElementById('sprModalCleaningName').textContent = cleaningGroupName;
    document.getElementById('newSprGroupName').value = '';
    new bootstrap.Modal(document.getElementById('addSprGroupModal')).show();
}

function submitSprGroup() {
    const cleaningGroupId = document.getElementById('sprGroupCleaningId').value;
    const name = document.getElementById('newSprGroupName').value.trim();
    if (!name) { alert('Please enter a name'); return; }

    const btn = event.target.closest('button') || document.querySelector('#addSprGroupModal .btn-primary');
    setSubmitting(btn, true);

    fetch(`${baseUrl}/cleaning-group/${cleaningGroupId}/spr`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ name: name })
    })
    .then(response => response.json())
    .then(data => { if (data.success) location.reload(); else setSubmitting(btn, false); })
    .catch(() => setSubmitting(btn, false));
}

let standaloneMode = false;
let standaloneScheduleDateId = null;

function showAddStandaloneTaskModal(scheduleDateId, scheduleDate) {
    standaloneMode = true;
    standaloneScheduleDateId = scheduleDateId;
    document.getElementById('taskModalSprId').value = '';
    document.getElementById('taskModalSprName').textContent = 'Date (Standalone)';
    document.getElementById('newTaskName').value = '';

    const shiftSelect = document.getElementById('newTaskShift');
    shiftSelect.innerHTML = '<option value="">Loading shifts...</option>';
    shiftSelect.disabled = true;

    const warningDiv = document.getElementById('shiftWarning');
    warningDiv.classList.add('d-none');

    new bootstrap.Modal(document.getElementById('addTaskModal')).show();

    fetch(`${baseUrl}/shifts-for-date?date=${scheduleDate}`, {
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        shiftSelect.innerHTML = '<option value="">Select Shift</option>';
        shiftSelect.disabled = false;
        if (data.shifts && data.shifts.length > 0) {
            data.shifts.forEach(shift => {
                const option = document.createElement('option');
                option.value = shift.id;
                option.textContent = shift.name;
                shiftSelect.appendChild(option);
            });
        }
        if (data.warning) {
            document.getElementById('shiftWarningText').textContent = data.warning;
            warningDiv.classList.remove('d-none');
        }
    })
    .catch(() => {
        shiftSelect.innerHTML = '<option value="">Failed to load shifts</option>';
        shiftSelect.disabled = false;
    });
}

function showAddTaskModal(sprGroupId, sprGroupName, scheduleDate) {
    standaloneMode = false;
    standaloneScheduleDateId = null;
    document.getElementById('taskModalSprId').value = sprGroupId;
    document.getElementById('taskModalSprName').textContent = sprGroupName;
    document.getElementById('newTaskName').value = '';

    // Reset shift dropdown
    const shiftSelect = document.getElementById('newTaskShift');
    shiftSelect.innerHTML = '<option value="">Loading shifts...</option>';
    shiftSelect.disabled = true;

    const warningDiv = document.getElementById('shiftWarning');
    const warningText = document.getElementById('shiftWarningText');
    warningDiv.classList.add('d-none');

    new bootstrap.Modal(document.getElementById('addTaskModal')).show();

    // Fetch shifts for this date
    fetch(`${baseUrl}/shifts-for-date?date=${scheduleDate}`, {
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        shiftSelect.innerHTML = '<option value="">Select Shift</option>';
        shiftSelect.disabled = false;

        if (data.shifts && data.shifts.length > 0) {
            data.shifts.forEach(shift => {
                const option = document.createElement('option');
                option.value = shift.id;
                option.textContent = shift.name;
                shiftSelect.appendChild(option);
            });
        }

        if (data.warning) {
            warningText.textContent = data.warning;
            warningDiv.classList.remove('d-none');
        }
    })
    .catch(() => {
        shiftSelect.innerHTML = '<option value="">Failed to load shifts</option>';
        shiftSelect.disabled = false;
    });
}

function addTask() {
    const data = {
        task_name: document.getElementById('newTaskName').value,
        frequency: document.getElementById('newTaskFrequency').value,
        equipment_type: document.getElementById('newTaskEquipment').value,
        assigned_shift_id: document.getElementById('newTaskShift').value || null
    };
    if (!data.task_name) { alert('Please enter a task name'); return; }

    const btn = document.querySelector('#addTaskModal .btn-primary');
    setSubmitting(btn, true);

    let url;
    if (standaloneMode && standaloneScheduleDateId) {
        url = `${baseUrl}/date/${standaloneScheduleDateId}/standalone-task`;
    } else {
        const sprGroupId = document.getElementById('taskModalSprId').value;
        url = `${baseUrl}/spr/${sprGroupId}/task`;
    }

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => { if (data.success) location.reload(); else setSubmitting(btn, false); })
    .catch(() => setSubmitting(btn, false));
}

function showEditTaskModal(taskId, taskData, scheduleDate) {
    document.getElementById('editTaskId').value = taskId;
    document.getElementById('editTaskName').value = taskData.task_name;
    document.getElementById('editTaskFrequency').value = taskData.frequency;
    document.getElementById('editTaskEquipment').value = taskData.equipment_type || '';

    const shiftSelect = document.getElementById('editTaskShift');
    shiftSelect.innerHTML = '<option value="">Loading shifts...</option>';
    shiftSelect.disabled = true;

    const warningDiv = document.getElementById('editShiftWarning');
    warningDiv.classList.add('d-none');

    new bootstrap.Modal(document.getElementById('editTaskModal')).show();

    fetch(`${baseUrl}/shifts-for-date?date=${scheduleDate}`, {
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        shiftSelect.innerHTML = '<option value="">No Shift</option>';
        shiftSelect.disabled = false;
        if (data.shifts && data.shifts.length > 0) {
            data.shifts.forEach(shift => {
                const option = document.createElement('option');
                option.value = shift.id;
                option.textContent = shift.name;
                if (shift.id == taskData.assigned_shift_id) option.selected = true;
                shiftSelect.appendChild(option);
            });
        }
        if (data.warning) {
            document.getElementById('editShiftWarningText').textContent = data.warning;
            warningDiv.classList.remove('d-none');
        }
    })
    .catch(() => {
        shiftSelect.innerHTML = '<option value="">Failed to load shifts</option>';
        shiftSelect.disabled = false;
    });
}

function submitEditTask() {
    const taskId = document.getElementById('editTaskId').value;
    const data = {
        task_name: document.getElementById('editTaskName').value,
        frequency: document.getElementById('editTaskFrequency').value,
        equipment_type: document.getElementById('editTaskEquipment').value,
        assigned_shift_id: document.getElementById('editTaskShift').value || null
    };
    if (!data.task_name) { alert('Please enter a task name'); return; }

    const btn = document.querySelector('#editTaskModal .btn-primary');
    setSubmitting(btn, true);

    fetch(`${baseUrl}/task/${taskId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => { if (data.success) location.reload(); else setSubmitting(btn, false); })
    .catch(() => setSubmitting(btn, false));
}

function updateTaskStatus(taskId, status) {
    fetch(`${baseUrl}/task/${taskId}/status`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => { if (data.success) location.reload(); else alert('Failed to update status'); })
    .catch(() => alert('An error occurred'));
}

function deleteCleaningGroup(id) {
    showDeleteModal('Delete this cleaning group and all its SPR groups and tasks?', function() {
        fetch(`${baseUrl}/cleaning-group/${id}`, {
            method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken }
        }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
    });
}

function deleteSprGroup(id) {
    showDeleteModal('Delete this SPR group and all its tasks?', function() {
        fetch(`${baseUrl}/spr/${id}`, {
            method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken }
        }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
    });
}

function deleteTask(id) {
    showDeleteModal('Delete this task?', function() {
        fetch(`${baseUrl}/task/${id}`, {
            method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken }
        }).then(r => r.json()).then(data => { if (data.success) location.reload(); });
    });
}
</script>
@endpush
@endsection

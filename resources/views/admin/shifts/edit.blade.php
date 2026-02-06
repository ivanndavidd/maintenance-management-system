@extends('layouts.admin')

@section('page-title', 'Edit Shift Schedule')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Edit Shift Schedule: {{ $shift->name }}</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'Prefix.'.shifts.index') }}">Shift Management</a></li>
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
            <form action="{{ route($routePrefix.'Prefix.'.shifts.update', $shift) }}" method="POST">
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
            <h5 class="mb-0">Available Users</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($users as $user)
                    <div class="col-md-3 col-sm-4 col-6 mb-2">
                        <div class="user-card p-2 border rounded cursor-pointer" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}">
                            <div class="d-flex align-items-center">
                                <div class="user-avatar me-2">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 12px;">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="small fw-bold">{{ $user->name }}</div>
                                    <div class="text-muted" style="font-size: 10px;">
                                        <span class="user-hours-{{ $user->id }}">{{ $userHours[$user->id] ?? 0 }}</span>h total
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="alert alert-info mb-0 mt-3">
                <small><i class="fas fa-info-circle"></i> Click on a user, then click on a shift cell to assign them. Click on an assigned user to remove them.</small>
            </div>
        </div>
    </div>

    <!-- Shift Calendar -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Shift Calendar</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered shift-calendar mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 100px;">Time</th>
                            <th>Monday</th>
                            <th>Tuesday</th>
                            <th>Wednesday</th>
                            <th>Thursday</th>
                            <th>Friday</th>
                            <th>Saturday</th>
                            <th>Sunday</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(['shift_1' => 'Shift 1<br>22:00-05:00', 'shift_2' => 'Shift 2<br>06:00-13:00', 'shift_3' => 'Shift 3<br>14:00-21:00'] as $shiftType => $shiftLabel)
                            <tr>
                                <td class="text-center fw-bold align-middle bg-light">
                                    {!! $shiftLabel !!}
                                </td>
                                @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                    <td class="shift-cell p-2"
                                        data-day="{{ $day }}"
                                        data-shift="{{ $shiftType }}"
                                        style="min-height: 80px; cursor: pointer; vertical-align: top;">
                                        <div class="assigned-users" id="cell-{{ $day }}-{{ $shiftType }}">
                                            @foreach($assignments[$day][$shiftType] as $assignment)
                                                <div class="assigned-user badge bg-primary mb-1 d-flex align-items-center justify-content-between"
                                                     data-user-id="{{ $assignment->user_id }}"
                                                     style="cursor: pointer;">
                                                    <span>{{ $assignment->user->name }}</span>
                                                    <i class="fas fa-times ms-1" style="font-size: 10px;"></i>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="text-muted small text-center mt-1">
                                            <span class="count-{{ $day }}-{{ $shiftType }}">{{ $assignments[$day][$shiftType]->count() }}</span>/4
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.user-card {
    transition: all 0.2s;
}

.user-card:hover {
    background-color: #f8f9fa;
    border-color: #0d6efd !important;
}

.user-card.selected {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd !important;
}

.user-card.selected .text-muted {
    color: rgba(255,255,255,0.8) !important;
}

.user-card.selected .bg-primary {
    background-color: white !important;
    color: #0d6efd !important;
}

.shift-cell {
    transition: background-color 0.2s;
}

.shift-cell:hover {
    background-color: #f8f9fa;
}

.shift-cell.can-assign {
    background-color: #d1ecf1;
    border: 2px dashed #0d6efd;
}

.assigned-user {
    font-size: 11px;
    padding: 4px 8px;
}

.assigned-user:hover {
    background-color: #dc3545 !important;
}

.cursor-pointer {
    cursor: pointer;
}

.shift-calendar td, .shift-calendar th {
    border: 1px solid #dee2e6;
}
</style>

@push('scripts')
<script>
const routePrefix = '{{ $routePrefix ?? "admin" }}';
const assignUserUrl = '{{ route(($routePrefix ?? "admin").".shifts.assign-user", $shift) }}';
const removeUserUrl = '{{ route(($routePrefix ?? "admin").".shifts.remove-user", $shift) }}';

let selectedUser = null;

// User selection
document.querySelectorAll('.user-card').forEach(card => {
    card.addEventListener('click', function() {
        // Remove previous selection
        document.querySelectorAll('.user-card').forEach(c => c.classList.remove('selected'));

        // Select this user
        this.classList.add('selected');
        selectedUser = {
            id: this.dataset.userId,
            name: this.dataset.userName
        };

        // Highlight cells that can be assigned
        document.querySelectorAll('.shift-cell').forEach(cell => {
            const count = parseInt(cell.querySelector(`[class^="count-"]`).textContent);
            if (count < 4) {
                cell.classList.add('can-assign');
            }
        });
    });
});

// Shift cell click - assign user
document.querySelectorAll('.shift-cell').forEach(cell => {
    cell.addEventListener('click', function(e) {
        // Don't trigger if clicking on assigned user badge
        if (e.target.closest('.assigned-user')) {
            return;
        }

        if (!selectedUser) {
            alert('Please select a user first');
            return;
        }

        const day = this.dataset.day;
        const shift = this.dataset.shift;
        const count = parseInt(this.querySelector(`[class^="count-"]`).textContent);

        if (count >= 4) {
            alert('This shift already has maximum 4 users assigned');
            return;
        }

        // Send AJAX request to assign user
        fetch(assignUserUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                user_id: selectedUser.id,
                day_of_week: day,
                shift_type: shift
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add badge to cell
                const cellDiv = document.getElementById(`cell-${day}-${shift}`);
                const badge = document.createElement('div');
                badge.className = 'assigned-user badge bg-primary mb-1 d-flex align-items-center justify-content-between';
                badge.dataset.userId = selectedUser.id;
                badge.style.cursor = 'pointer';
                badge.innerHTML = `<span>${selectedUser.name}</span><i class="fas fa-times ms-1" style="font-size: 10px;"></i>`;

                // Add click handler for removal
                badge.addEventListener('click', function(e) {
                    e.stopPropagation();
                    removeUserFromShift(this, day, shift);
                });

                cellDiv.appendChild(badge);

                // Update count
                const countSpan = this.querySelector(`.count-${day}-${shift}`);
                countSpan.textContent = parseInt(countSpan.textContent) + 1;

                // Update user total hours
                document.querySelector(`.user-hours-${selectedUser.id}`).textContent = data.total_hours;

                // Show success message
                showToast('User assigned successfully', 'success');
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while assigning user');
        });
    });
});

// Remove user from shift
document.querySelectorAll('.assigned-user').forEach(badge => {
    badge.addEventListener('click', function(e) {
        e.stopPropagation();
        const cell = this.closest('.shift-cell');
        const day = cell.dataset.day;
        const shift = cell.dataset.shift;

        removeUserFromShift(this, day, shift);
    });
});

function removeUserFromShift(badge, day, shift) {
    if (!confirm('Remove this user from the shift?')) {
        return;
    }

    const userId = badge.dataset.userId;

    fetch(removeUserUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            user_id: userId,
            day_of_week: day,
            shift_type: shift
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove badge
            badge.remove();

            // Update count
            const cell = document.querySelector(`[data-day="${day}"][data-shift="${shift}"]`);
            const countSpan = cell.querySelector(`.count-${day}-${shift}`);
            countSpan.textContent = parseInt(countSpan.textContent) - 1;

            // Update user total hours
            document.querySelector(`.user-hours-${userId}`).textContent = data.total_hours;

            // Show success message
            showToast('User removed successfully', 'success');
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while removing user');
    });
}

function showToast(message, type = 'success') {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} position-fixed top-0 end-0 m-3`;
    toast.style.zIndex = '9999';
    toast.innerHTML = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Clear selection when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.user-card') && !e.target.closest('.shift-cell')) {
        document.querySelectorAll('.user-card').forEach(c => c.classList.remove('selected'));
        document.querySelectorAll('.shift-cell').forEach(c => c.classList.remove('can-assign'));
        selectedUser = null;
    }
});
</script>
@endpush
@endsection

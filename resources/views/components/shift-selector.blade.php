@props(['name' => 'shift_date', 'selectedDate' => null, 'selectedShiftType' => null, 'selectedShiftScheduleId' => null])

<div class="card mb-3" id="shift-selector">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-calendar-week"></i> Shift Assignment (Optional)
        </h6>
    </div>
    <div class="card-body">
        <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" id="use_shift" name="use_shift" value="1"
                   {{ ($selectedDate || $selectedShiftType) ? 'checked' : '' }}>
            <label class="form-check-label" for="use_shift">
                Assign this task to a specific shift
            </label>
        </div>

        <div id="shift-fields" style="display: {{ ($selectedDate || $selectedShiftType) ? 'block' : 'none' }};">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="shift_date" class="form-label">Shift Date</label>
                        <input type="date"
                               class="form-control"
                               id="shift_date"
                               name="shift_date"
                               value="{{ $selectedDate }}"
                               onchange="loadShiftData()">
                        <small class="text-muted">Select the date to see available shifts</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="shift_type" class="form-label">Shift</label>
                        <select class="form-select" id="shift_type" name="shift_type">
                            <option value="">Select a shift</option>
                            <option value="shift_1" {{ $selectedShiftType == 'shift_1' ? 'selected' : '' }}>Shift 1 (22:00-05:00)</option>
                            <option value="shift_2" {{ $selectedShiftType == 'shift_2' ? 'selected' : '' }}>Shift 2 (06:00-13:00)</option>
                            <option value="shift_3" {{ $selectedShiftType == 'shift_3' ? 'selected' : '' }}>Shift 3 (14:00-21:00)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="shift-info" class="alert alert-info" style="display: none;">
                <strong><i class="fas fa-info-circle"></i> Shift Information</strong>
                <div id="shift-details" class="mt-2"></div>
            </div>

            <div id="shift-users" class="mt-3" style="display: none;">
                <label class="form-label">Users in this shift:</label>
                <div id="shift-users-list" class="d-flex flex-wrap gap-2"></div>
                <input type="hidden" id="shift_schedule_id" name="shift_schedule_id" value="{{ $selectedShiftScheduleId }}">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Toggle shift fields
document.getElementById('use_shift').addEventListener('change', function() {
    const shiftFields = document.getElementById('shift-fields');
    shiftFields.style.display = this.checked ? 'block' : 'none';

    if (!this.checked) {
        // Clear shift fields
        document.getElementById('shift_date').value = '';
        document.getElementById('shift_type').value = '';
        document.getElementById('shift_schedule_id').value = '';
        document.getElementById('shift-info').style.display = 'none';
        document.getElementById('shift-users').style.display = 'none';
    }
});

// Load shift data when date or shift type changes
document.getElementById('shift_type').addEventListener('change', loadShiftData);

function loadShiftData() {
    const date = document.getElementById('shift_date').value;
    const shiftType = document.getElementById('shift_type').value;

    if (!date || !shiftType) {
        document.getElementById('shift-info').style.display = 'none';
        document.getElementById('shift-users').style.display = 'none';
        return;
    }

    // Show loading state
    document.getElementById('shift-details').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading shift information...';
    document.getElementById('shift-info').style.display = 'block';

    // Fetch shift data
    fetch(`{{ route('admin.shifts.get-shift-for-date') }}?date=${date}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Find the selected shift
                const selectedShift = data.shifts.find(s => s.shift_type === shiftType);

                if (selectedShift) {
                    // Update shift schedule ID
                    document.getElementById('shift_schedule_id').value = data.schedule.id;

                    // Display shift details
                    let detailsHtml = `
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Schedule:</strong> ${data.schedule.name}<br>
                                <strong>Shift:</strong> ${selectedShift.shift_name}
                            </div>
                            <div class="col-md-6">
                                <strong>Users assigned:</strong> ${selectedShift.users.length} users
                            </div>
                        </div>
                    `;
                    document.getElementById('shift-details').innerHTML = detailsHtml;

                    // Display users
                    if (selectedShift.users.length > 0) {
                        let usersHtml = '';
                        selectedShift.users.forEach(user => {
                            usersHtml += `
                                <span class="badge bg-primary">
                                    <i class="fas fa-user"></i> ${user.name}
                                </span>
                            `;
                        });
                        document.getElementById('shift-users-list').innerHTML = usersHtml;
                        document.getElementById('shift-users').style.display = 'block';
                    } else {
                        document.getElementById('shift-users').style.display = 'none';
                        document.getElementById('shift-details').innerHTML += '<br><small class="text-warning"><i class="fas fa-exclamation-triangle"></i> No users assigned to this shift yet.</small>';
                    }
                } else {
                    document.getElementById('shift-details').innerHTML = '<span class="text-warning"><i class="fas fa-exclamation-triangle"></i> No shift data found for the selected shift type.</span>';
                    document.getElementById('shift-users').style.display = 'none';
                }
            } else {
                document.getElementById('shift-details').innerHTML = `<span class="text-danger"><i class="fas fa-times-circle"></i> ${data.message}</span>`;
                document.getElementById('shift-users').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('shift-details').innerHTML = '<span class="text-danger"><i class="fas fa-times-circle"></i> Error loading shift data.</span>';
            document.getElementById('shift-users').style.display = 'none';
        });
}

// Load shift data on page load if date and shift type are pre-selected
window.addEventListener('load', function() {
    const date = document.getElementById('shift_date').value;
    const shiftType = document.getElementById('shift_type').value;

    if (date && shiftType) {
        loadShiftData();
    }
});
</script>
@endpush

@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Create New Shift Schedule</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.shifts.index') }}">Shift Management</a></li>
                <li class="breadcrumb-item active">Create Schedule</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Schedule Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.shifts.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                    <input type="date"
                                           class="form-control @error('start_date') is-invalid @enderror"
                                           id="start_date"
                                           name="start_date"
                                           value="{{ old('start_date') }}"
                                           required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Only Mondays can be selected. Dates with existing schedules are disabled.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                                    <input type="date"
                                           class="form-control @error('end_date') is-invalid @enderror"
                                           id="end_date"
                                           name="end_date"
                                           value="{{ old('end_date') }}"
                                           required>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Auto-filled (Sunday of the week)</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes"
                                      name="notes"
                                      rows="3"
                                      placeholder="Any additional notes about this schedule...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.shifts.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Schedule
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Guide</h5>
                </div>
                <div class="card-body">
                    <ol class="ps-3">
                        <li class="mb-2">Select the week period (Monday - Sunday)</li>
                        <li class="mb-2">Schedule name will be auto-generated</li>
                        <li class="mb-2">Add any relevant notes (optional)</li>
                        <li class="mb-2">Click "Create Schedule"</li>
                        <li class="mb-2">You'll be redirected to assign users to shifts</li>
                    </ol>

                    <div class="alert alert-info mb-0 mt-3">
                        <small>
                            <i class="fas fa-lightbulb"></i>
                            <strong>Tip:</strong> After creating the schedule, you can assign users to specific shifts using the visual calendar interface.
                        </small>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Available Users</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">Users available for shift assignment:</p>
                    <div class="list-group list-group-flush">
                        @foreach($users as $user)
                            <div class="list-group-item px-0 py-2">
                                <i class="fas fa-user-circle text-primary"></i>
                                {{ $user->name }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    // Existing shift schedule start dates from backend
    const existingDates = @json($existingStartDates);

    // Helper function to format date as YYYY-MM-DD in local timezone
    function formatDateLocal(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Initialize Flatpickr for start_date - only Mondays can be selected
    // Allow past dates if no schedule exists for that week
    const startDatePicker = flatpickr("#start_date", {
        dateFormat: "Y-m-d",
        // No minDate restriction - allow past Mondays that don't have schedules
        disable: [
            // Disable all dates except Mondays
            function(date) {
                // Return true to disable the date
                // date.getDay() returns 0 (Sunday) to 6 (Saturday)
                // We want to ENABLE Monday (1), so DISABLE all others
                if (date.getDay() !== 1) {
                    return true;
                }

                // Check if this Monday already has a schedule (disable if exists)
                // Use local timezone format instead of UTC
                const dateStr = formatDateLocal(date);
                return existingDates.includes(dateStr);
            }
        ],
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length > 0) {
                const startDate = selectedDates[0];

                // Auto-calculate end date (Sunday = +6 days)
                const endDate = new Date(startDate);
                endDate.setDate(endDate.getDate() + 6);

                // Format end date as YYYY-MM-DD
                const year = endDate.getFullYear();
                const month = String(endDate.getMonth() + 1).padStart(2, '0');
                const day = String(endDate.getDate()).padStart(2, '0');
                const endDateStr = `${year}-${month}-${day}`;

                // Set end date value
                document.getElementById('end_date').value = endDateStr;
                endDatePicker.setDate(endDateStr);
            }
        }
    });

    // Initialize Flatpickr for end_date - readonly, auto-calculated
    const endDatePicker = flatpickr("#end_date", {
        dateFormat: "Y-m-d",
        clickOpens: false, // Prevent opening the calendar
        allowInput: false, // Prevent manual input
    });

    // Show message when trying to click end_date
    document.getElementById('end_date').addEventListener('focus', function(e) {
        e.preventDefault();
        this.blur();
        alert('End date is automatically calculated based on the start date (Monday). It will always be the Sunday of the same week.');
    });
</script>
@endpush
@endsection

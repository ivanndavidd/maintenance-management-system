@extends('layouts.admin')

@section('page-title', 'PM Schedule Calendar')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Preventive Maintenance Calendar</h4>
            <p class="text-muted mb-0">Schedule and manage preventive maintenance tasks</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" id="btnNewEvent">
                <i class="fas fa-plus me-1"></i> New Task
            </button>
        </div>
    </div>

    <!-- Alert Container -->
    <div id="alertContainer"></div>

    <!-- Calendar -->
    <div class="card">
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>
</div>

<!-- Event Modal -->
<div class="modal" id="eventModal" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="eventModalTitle">
                    <i class="fas fa-calendar-plus me-2"></i><span id="modalTitleText">New PM Task</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="eventForm">
                <div class="modal-body">
                    <input type="hidden" id="taskId" name="task_id">
                    <input type="hidden" id="isEdit" name="is_edit" value="0">

                    <!-- Task Title -->
                    <div class="mb-3">
                        <label for="taskName" class="form-label fw-bold">Task Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="taskName" name="task_name" placeholder="Enter task name" required>
                    </div>

                    <!-- Task Description -->
                    <div class="mb-3">
                        <label for="taskDescription" class="form-label fw-bold">Description</label>
                        <textarea class="form-control" id="taskDescription" name="task_description" rows="2" placeholder="Task description (optional)"></textarea>
                    </div>

                    <!-- Date & Shift Row -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="taskDate" class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="taskDate" name="task_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="assignedShift" class="form-label fw-bold">
                                <i class="fas fa-user-clock me-1"></i>Assign to Shift <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="assignedShift" name="assigned_shift_id" required>
                                <option value="">-- Select Shift --</option>
                                <option value="1">Shift 1</option>
                                <option value="2">Shift 2</option>
                                <option value="3">Shift 3</option>
                            </select>
                        </div>
                    </div>

                    <!-- Recurring Settings Toggle -->
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="isRecurring" name="is_recurring">
                            <label class="form-check-label fw-bold" for="isRecurring">
                                <i class="fas fa-repeat me-1"></i>Recurring Task
                            </label>
                        </div>
                    </div>

                    <!-- Recurring Options (hidden by default) -->
                    <div id="recurringOptions" style="display: none;">
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-sync me-1"></i>Recurrence Pattern</h6>

                                <!-- Repeat Every -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Repeat every</label>
                                        <input type="number" class="form-control" id="recurrenceInterval" name="recurrence_interval" value="1" min="1">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Frequency</label>
                                        <select class="form-select" id="recurrencePattern" name="recurrence_pattern">
                                            <option value="daily">Day(s)</option>
                                            <option value="weekly" selected>Week(s)</option>
                                            <option value="monthly">Month(s)</option>
                                            <option value="yearly">Year(s)</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Weekly Options (days of week) -->
                                <div id="weeklyOptions" class="mb-3">
                                    <label class="form-label fw-bold">Repeat on</label>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <input type="checkbox" class="btn-check" id="day-mon" value="Mon" name="recurrence_days[]">
                                        <label class="btn btn-outline-primary" for="day-mon">M</label>

                                        <input type="checkbox" class="btn-check" id="day-tue" value="Tue" name="recurrence_days[]">
                                        <label class="btn btn-outline-primary" for="day-tue">T</label>

                                        <input type="checkbox" class="btn-check" id="day-wed" value="Wed" name="recurrence_days[]">
                                        <label class="btn btn-outline-primary" for="day-wed">W</label>

                                        <input type="checkbox" class="btn-check" id="day-thu" value="Thu" name="recurrence_days[]">
                                        <label class="btn btn-outline-primary" for="day-thu">T</label>

                                        <input type="checkbox" class="btn-check" id="day-fri" value="Fri" name="recurrence_days[]">
                                        <label class="btn btn-outline-primary" for="day-fri">F</label>

                                        <input type="checkbox" class="btn-check" id="day-sat" value="Sat" name="recurrence_days[]">
                                        <label class="btn btn-outline-primary" for="day-sat">S</label>

                                        <input type="checkbox" class="btn-check" id="day-sun" value="Sun" name="recurrence_days[]">
                                        <label class="btn btn-outline-primary" for="day-sun">S</label>
                                    </div>
                                </div>

                                <!-- Monthly Options (day of month) -->
                                <div id="monthlyOptions" class="mb-3" style="display: none;">
                                    <label class="form-label fw-bold">On day</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <select class="form-select" id="recurrenceDayOfMonth" name="recurrence_day_of_month">
                                                <option value="0">On the same day of month</option>
                                                @for($i = 1; $i <= 31; $i++)
                                                    <option value="{{ $i }}">Day {{ $i }}</option>
                                                @endfor
                                                <option value="-1">Last day of month</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- End Date -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Start Date</label>
                                        <input type="date" class="form-control" id="recurrenceStartDate" name="recurrence_start_date">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">End Date</label>
                                        <input type="date" class="form-control" id="recurrenceEndDate" name="recurrence_end_date" placeholder="Leave empty for no end">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Equipment Type -->
                    <div class="mb-3">
                        <label for="equipmentType" class="form-label fw-bold">Equipment Type</label>
                        <input type="text" class="form-control" id="equipmentType" name="equipment_type" placeholder="e.g. Forklift, Conveyor">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Save Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Event Action Popover -->
<div id="eventActionPopover" style="display: none; position: absolute; z-index: 1050;">
    <div class="card shadow border" style="min-width: 280px; border-radius: 8px;">
        <div class="card-body p-3">
            <!-- Event Title with Icon and Close -->
            <div class="d-flex align-items-start mb-3">
                <div class="me-2">
                    <i class="fas fa-calendar-check text-primary" style="font-size: 20px;"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-0 fw-semibold" id="eventActionTitle">Event Title</h6>
                    <small class="text-muted" id="eventActionDateTime"></small>
                </div>
                <button type="button" class="btn-close btn-sm ms-2" id="closePopover" style="font-size: 0.7rem;"></button>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-2">
                <div class="flex-fill position-relative">
                    <button class="btn btn-sm btn-outline-primary w-100 d-flex align-items-center justify-content-center" type="button" id="btnEditEvent">
                        <i class="fas fa-edit me-2"></i>Edit
                        <i class="fas fa-chevron-down ms-2" id="editChevron" style="display: none; font-size: 10px;"></i>
                    </button>
                    <div class="custom-dropdown-menu" id="editRecurringOptions" style="display: none;">
                        <a class="custom-dropdown-item" href="#" data-edit-type="this">This event</a>
                        <a class="custom-dropdown-item" href="#" data-edit-type="following">This and all following events</a>
                        <a class="custom-dropdown-item" href="#" data-edit-type="all">All events in the series</a>
                    </div>
                </div>
                <div class="flex-fill position-relative">
                    <button class="btn btn-sm btn-outline-danger w-100 d-flex align-items-center justify-content-center" type="button" id="btnDeleteEvent">
                        <i class="fas fa-trash me-2"></i>Delete
                        <i class="fas fa-chevron-down ms-2" id="deleteChevron" style="display: none; font-size: 10px;"></i>
                    </button>
                    <div class="custom-dropdown-menu" id="deleteRecurringOptions" style="display: none;">
                        <a class="custom-dropdown-item" href="#" data-delete-type="this">This event</a>
                        <a class="custom-dropdown-item" href="#" data-delete-type="following">This and all following events</a>
                        <a class="custom-dropdown-item" href="#" data-delete-type="all">All events in the series</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
#calendar {
    min-height: 600px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Outlook-style FullCalendar customization */
:root {
  --fc-border-color: #e1dfdd;
  --fc-button-bg-color: #0078d4;
  --fc-button-border-color: #0078d4;
  --fc-button-hover-bg-color: #106ebe;
  --fc-button-hover-border-color: #106ebe;
  --fc-button-active-bg-color: #005a9e;
  --fc-button-active-border-color: #005a9e;
  --fc-today-bg-color: rgba(0, 120, 212, 0.05);
}

/* Calendar grid styling */
.fc .fc-scrollgrid {
    border-color: #e1dfdd !important;
}

.fc .fc-col-header-cell {
    background-color: #faf9f8;
    border-color: #e1dfdd;
    padding: 8px 4px;
    font-weight: 400;
    font-size: 12px;
    color: #323130;
}

.fc .fc-col-header-cell a {
    color: #323130;
    text-decoration: none;
}

.fc .fc-daygrid-day {
    background-color: #ffffff;
}

.fc .fc-daygrid-day-top {
    padding: 4px 6px;
    text-align: right;
}

.fc .fc-daygrid-day-number {
    color: #323130;
    font-size: 12px;
    padding: 4px 6px;
    text-decoration: none;
    display: inline-block;
    min-width: 24px;
    text-align: center;
}

.fc .fc-daygrid-day-frame {
    min-height: 100px;
    position: relative;
}

.fc .fc-day-today {
    background-color: rgba(0, 120, 212, 0.05) !important;
}

.fc .fc-day-today .fc-daygrid-day-number {
    background-color: #0078d4;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

/* Event styling - Outlook style */
.fc-event {
    cursor: pointer;
    border-left-width: 4px !important;
    border-radius: 2px !important;
    padding: 3px 5px !important;
    margin-bottom: 2px !important;
    font-size: 11px;
    box-shadow: 0 0.5px 1.5px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
    position: relative;
}

.fc-event:hover {
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    transform: translateY(-1px);
}

.fc-event .fc-event-time {
    font-weight: 600;
    font-size: 11px;
    color: #201f1e !important;
}

.fc-event .fc-event-title {
    font-weight: 500;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #201f1e !important;
}

.fc-event .fc-event-main {
    color: #201f1e !important;
}

/* Shift badge on event */
.fc-event .shift-badge {
    display: inline-block;
    font-size: 9px;
    font-weight: 600;
    padding: 2px 5px;
    border-radius: 3px;
    margin-left: 6px;
    background-color: rgba(0,0,0,0.12);
    color: #201f1e;
    white-space: nowrap;
}

/* Shift color coding - Outlook palette with better contrast */
.shift-1 {
    background-color: rgba(0, 120, 212, 0.15) !important;
    border-left-color: #0078d4 !important;
}

.shift-2 {
    background-color: rgba(194, 57, 179, 0.15) !important;
    border-left-color: #c239b3 !important;
}

.shift-3 {
    background-color: rgba(0, 183, 195, 0.15) !important;
    border-left-color: #00b7c3 !important;
}

.no-shift {
    background-color: rgba(96, 94, 92, 0.15) !important;
    border-left-color: #605e5c !important;
}

/* Tooltip styling */
.fc-event-tooltip {
    position: absolute;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    color: #323130;
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid #e1dfdd;
    font-size: 12px;
    z-index: 10000;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    pointer-events: none;
    white-space: nowrap;
    display: none;
}

.fc-event:hover .fc-event-tooltip {
    display: block;
}

/* Recurring event indicator */
.fc-event.recurring::after {
    content: '\f01e';
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    margin-left: 4px;
    font-size: 9px;
    opacity: 0.7;
}

/* Header toolbar */
.fc .fc-toolbar {
    padding: 12px 0;
}

.fc .fc-toolbar-title {
    font-size: 20px;
    font-weight: 600;
    color: #323130;
}

.fc .fc-button {
    font-size: 13px;
    padding: 6px 12px;
    border-radius: 2px;
    text-transform: none;
    font-weight: 600;
}

/* Week numbers */
.fc .fc-daygrid-week-number {
    background-color: #faf9f8;
    color: #605e5c;
    font-size: 11px;
    padding: 4px;
}

/* Hide FullCalendar default loading indicator */
.fc .fc-view-harness.fc-view-harness-active > .fc-view.fc-view-processing {
  opacity: 1 !important;
}

/* Completely hide all FullCalendar loading overlays */
.fc-loading-overlay,
.fc-scroller-liquid-absolute,
.fc .fc-loading {
  display: none !important;
  opacity: 0 !important;
  visibility: hidden !important;
}

/* Hide all possible loading/saving overlays - AGGRESSIVE */
div[style*="position: fixed"],
div[style*="position: absolute"] {
  &:has(*:contains("Saving")),
  &:has(*:contains("Loading")) {
    display: none !important;
  }
}

/* Target any overlay with dark background */
body > div[style*="background"],
body > div[style*="rgba"] {
  display: none !important;
}

/* Hide FullCalendar loading class completely */
.fc-loading,
body.fc-loading::before,
body.fc-loading::after {
  display: none !important;
  content: none !important;
}

/* CRITICAL: Hide global loading overlay from admin layout */
#globalLoading,
.loading-overlay {
  display: none !important;
  opacity: 0 !important;
  visibility: hidden !important;
  pointer-events: none !important;
  z-index: -9999 !important;
}

/* More events link */
.fc .fc-daygrid-more-link {
    color: #0078d4;
    font-size: 11px;
    font-weight: 600;
}

.fc .fc-daygrid-more-link:hover {
    color: #106ebe;
    background-color: rgba(0, 120, 212, 0.05);
}

/* Day cell hover */
.fc .fc-daygrid-day:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

/* Time grid view */
.fc-timegrid-slot {
    height: 2.5em;
}

.fc-timegrid-slot-label {
    border-color: #e1dfdd !important;
    font-size: 11px;
    color: #605e5c;
}

.fc-timegrid-axis {
    background-color: #faf9f8;
}

/* Force Bootstrap tooltip to use white background */
.tooltip .tooltip-inner {
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
    color: #323130 !important;
    border: 1px solid #e1dfdd !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
}

.tooltip .tooltip-arrow::before {
    border-top-color: #ffffff !important;
}

.tooltip.bs-tooltip-top .tooltip-arrow::before,
.tooltip.bs-tooltip-auto[data-popper-placement^="top"] .tooltip-arrow::before {
    border-top-color: #e1dfdd !important;
}

.tooltip.bs-tooltip-bottom .tooltip-arrow::before,
.tooltip.bs-tooltip-auto[data-popper-placement^="bottom"] .tooltip-arrow::before {
    border-bottom-color: #e1dfdd !important;
}

.tooltip.bs-tooltip-start .tooltip-arrow::before,
.tooltip.bs-tooltip-auto[data-popper-placement^="left"] .tooltip-arrow::before {
    border-left-color: #e1dfdd !important;
}

.tooltip.bs-tooltip-end .tooltip-arrow::before,
.tooltip.bs-tooltip-auto[data-popper-placement^="right"] .tooltip-arrow::before {
    border-right-color: #e1dfdd !important;
}

/* Custom Dropdown Menu (non-Bootstrap) */
.custom-dropdown-menu {
    position: fixed;
    margin-top: 4px;
    background: #ffffff;
    border: 1px solid rgba(0, 0, 0, 0.15);
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 1070;
    min-width: 240px;
    max-width: 300px;
}

.custom-dropdown-item {
    display: block;
    padding: 8px 16px;
    color: #323130;
    text-decoration: none;
    font-size: 13px;
    transition: background-color 0.15s ease;
    cursor: pointer;
}

.custom-dropdown-item:first-child {
    border-top-left-radius: 4px;
    border-top-right-radius: 4px;
}

.custom-dropdown-item:last-child {
    border-bottom-left-radius: 4px;
    border-bottom-right-radius: 4px;
}

.custom-dropdown-item:hover {
    background-color: #f3f2f1;
    color: #201f1e;
}

.custom-dropdown-item:active {
    background-color: #edebe9;
}
</style>
@endpush

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script>

document.addEventListener('DOMContentLoaded', function() {
    // CRITICAL: Override global loading functions to do nothing on this page
    window.showLoading = function() { /* do nothing */ };
    window.hideLoading = function() { /* do nothing */ };

    // Force hide any existing loading overlay
    const globalLoading = document.getElementById('globalLoading');
    if (globalLoading) {
        globalLoading.style.display = 'none';
        globalLoading.style.opacity = '0';
        globalLoading.style.visibility = 'hidden';
        globalLoading.style.pointerEvents = 'none';
        globalLoading.classList.remove('show');
    }

    const calendarEl = document.getElementById('calendar');
    const eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
    const eventActionPopover = document.getElementById('eventActionPopover');
    const eventForm = document.getElementById('eventForm');
    const isRecurringCheckbox = document.getElementById('isRecurring');
    const recurringOptions = document.getElementById('recurringOptions');
    const recurrencePattern = document.getElementById('recurrencePattern');
    const weeklyOptions = document.getElementById('weeklyOptions');
    const monthlyOptions = document.getElementById('monthlyOptions');

    // Store currently selected event for action popover
    let currentEvent = null;

    // Function to show alert message
    function showAlert(message, type = 'danger') {
        const alertContainer = document.getElementById('alertContainer');
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        alertContainer.appendChild(alertDiv);

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 150);
        }, 5000);

        // Scroll to alert
        alertContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Initialize calendar
    const calendar = new FullCalendar.Calendar(calendarEl, {
        // Completely disable loading UI
        displayEventTime: false,
        displayEventEnd: false,
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        editable: true,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: 3,
        weekends: true,
        height: 'auto',
        firstDay: 0, // Sunday
        weekNumbers: false,
        navLinks: false, // Disable day/week links to remove underline
        dayHeaderFormat: { weekday: 'long' }, // Full day names (Sunday, Monday, etc)
        progressiveEventRendering: true, // Disable loading overlay completely
        loading: function(isLoading) {
            // Completely disable loading indicator - do nothing
            return false;
        },

        // Event rendering
        eventDidMount: function(info) {
            // Add recurring class to recurring events
            if (info.event.extendedProps.is_recurring || info.event.extendedProps.parent_task_id) {
                info.el.classList.add('recurring');
            }

            // Add shift badge to event title
            const shiftId = info.event.extendedProps.assigned_shift_id;
            if (shiftId) {
                const titleEl = info.el.querySelector('.fc-event-title');
                if (titleEl) {
                    const shiftBadge = document.createElement('span');
                    shiftBadge.className = 'shift-badge';
                    const shiftLabel = shiftId === 1 ? 'Shift 1' : shiftId === 2 ? 'Shift 2' : 'Shift 3';
                    shiftBadge.textContent = shiftLabel;
                    titleEl.appendChild(shiftBadge);
                }
            }

            // Add enhanced tooltip with clean design
            const shiftNames = {
                1: 'Shift 1 (22:00 - 05:00)',
                2: 'Shift 2 (06:00 - 13:00)',
                3: 'Shift 3 (14:00 - 21:00)'
            };

            let tooltipContent = `<div style="text-align: left;">`;
            tooltipContent += `<div style="font-weight: 600; font-size: 13px; margin-bottom: 6px;">${info.event.title}</div>`;

            if (shiftId) {
                tooltipContent += `<div style="font-size: 12px; margin-bottom: 4px;">👤 ${shiftNames[shiftId]}</div>`;
            }

            if (info.event.extendedProps.equipment_type) {
                tooltipContent += `<div style="font-size: 12px; margin-bottom: 4px;">🔧 ${info.event.extendedProps.equipment_type}</div>`;
            }

            if (info.event.extendedProps.is_recurring || info.event.extendedProps.parent_task_id) {
                tooltipContent += `<div style="font-size: 11px; margin-top: 6px; padding-top: 6px; border-top: 1px solid rgba(0,0,0,0.1);">🔄 Recurring Event</div>`;
            }

            tooltipContent += `</div>`;

            info.el.setAttribute('data-bs-toggle', 'tooltip');
            info.el.setAttribute('data-bs-html', 'true');
            info.el.setAttribute('data-bs-placement', 'top');
            info.el.setAttribute('title', tooltipContent);

            // Initialize Bootstrap tooltip with custom template
            new bootstrap.Tooltip(info.el, {
                html: true,
                trigger: 'hover',
                container: 'body',
                customClass: 'event-tooltip-modern',
                template: '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); max-width: 300px; padding: 10px 12px; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); text-align: left; border: 1px solid #e1dfdd; color: #323130;"></div></div>'
            });
        },

        // Handle date click/select (for new events)
        select: function(info) {
            openEventModal(null, info.startStr);
        },

        // Handle event click (show action popover)
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            info.jsEvent.stopPropagation();
            currentEvent = info.event;

            // Set event title
            document.getElementById('eventActionTitle').textContent = info.event.title;

            // Set event date info
            const eventDate = new Date(info.event.start);
            const dateOptions = { weekday: 'short', month: 'numeric', day: 'numeric', year: 'numeric' };
            let dateTimeText = eventDate.toLocaleDateString('en-US', dateOptions);

            // Add series indicator if recurring
            if (info.event.extendedProps.is_recurring || info.event.extendedProps.parent_task_id) {
                dateTimeText += ' • Series';
            }

            document.getElementById('eventActionDateTime').textContent = dateTimeText;

            // Show/hide dropdowns based on recurring status
            const isRecurring = info.event.extendedProps.is_recurring || info.event.extendedProps.parent_task_id;

            const editBtn = document.getElementById('btnEditEvent');
            const editDropdown = document.getElementById('editRecurringOptions');
            const editChevron = document.getElementById('editChevron');

            const deleteBtn = document.getElementById('btnDeleteEvent');
            const deleteDropdown = document.getElementById('deleteRecurringOptions');
            const deleteChevron = document.getElementById('deleteChevron');

            // Hide dropdown menus (will be shown on button click if recurring)
            editDropdown.style.display = 'none';
            deleteDropdown.style.display = 'none';

            // Store recurring status and show/hide chevrons
            if (isRecurring) {
                editBtn.setAttribute('data-is-recurring', 'true');
                editChevron.style.display = 'inline-block';

                deleteBtn.setAttribute('data-is-recurring', 'true');
                deleteChevron.style.display = 'inline-block';
            } else {
                editBtn.removeAttribute('data-is-recurring');
                editChevron.style.display = 'none';

                deleteBtn.removeAttribute('data-is-recurring');
                deleteChevron.style.display = 'none';
            }

            // Position popover near the clicked event with smart positioning
            const clickX = info.jsEvent.pageX;
            const clickY = info.jsEvent.pageY;

            // Show popover first to get dimensions
            eventActionPopover.style.display = 'block';
            eventActionPopover.style.left = clickX + 'px';
            eventActionPopover.style.top = clickY + 'px';

            // Adjust position to prevent going off screen
            setTimeout(() => {
                const rect = eventActionPopover.getBoundingClientRect();
                const viewportWidth = window.innerWidth;
                const viewportHeight = window.innerHeight;

                let finalX = clickX;
                let finalY = clickY;

                // Check if popover goes off right edge
                if (rect.right > viewportWidth) {
                    finalX = clickX - rect.width;
                }

                // Check if popover goes off bottom edge - move it above click point
                if (rect.bottom > viewportHeight) {
                    finalY = clickY - rect.height - 10; // 10px offset above
                }

                // Check if popover goes off top edge after adjustment
                if (finalY < 0) {
                    finalY = 10; // 10px from top
                }

                // Check if popover goes off left edge
                if (finalX < 0) {
                    finalX = 10; // 10px from left
                }

                eventActionPopover.style.left = finalX + 'px';
                eventActionPopover.style.top = finalY + 'px';
            }, 10);
        },

        // Handle event drag/drop
        eventDrop: function(info) {
            updateEventDate(info.event);
        },

        // Handle event resize
        eventResize: function(info) {
            updateEventDuration(info.event);
        },

        // Load all events once - no dynamic fetching on month change
        events: []
    });

    calendar.render();

    // Fetch all events once at initialization (1 year range)
    const today = new Date();
    const startDate = new Date(today.getFullYear(), 0, 1); // Jan 1 this year
    const endDate = new Date(today.getFullYear() + 1, 11, 31); // Dec 31 next year

    fetch(`/admin/preventive-maintenance/calendar/events?start=${startDate.toISOString().split('T')[0]}&end=${endDate.toISOString().split('T')[0]}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (Array.isArray(data)) {
                // Add all events to calendar at once
                data.forEach(event => {
                    calendar.addEvent(event);
                });
            } else if (data.error) {
                console.error('Server error:', data.message);
                showAlert('Error loading calendar events: ' + data.message, 'warning');
            }
        })
        .catch(error => {
            console.error('Error fetching events:', error);
            showAlert('Failed to load calendar events. Please refresh the page.', 'warning');
        });

    // Override refetchEvents to prevent loading indicator
    const originalRefetch = calendar.refetchEvents.bind(calendar);
    calendar.refetchEvents = function() {
        // Temporarily hide any loading overlays
        const style = document.createElement('style');
        style.id = 'no-loading-temp';
        style.textContent = `
            .fc-loading, .fc-loading-overlay,
            body > div[style*="position: fixed"],
            body > div[style*="position: absolute"] {
                display: none !important;
            }
        `;
        document.head.appendChild(style);

        // Call original refetch
        originalRefetch();

        // Remove temp style after a short delay
        setTimeout(() => {
            const tempStyle = document.getElementById('no-loading-temp');
            if (tempStyle) tempStyle.remove();
        }, 1000);
    };

    // New Event Button
    document.getElementById('btnNewEvent').addEventListener('click', function() {
        openEventModal(null, new Date().toISOString().split('T')[0]);
    });

    // Reset form when modal is fully hidden
    document.getElementById('eventModal').addEventListener('hidden.bs.modal', function () {
        eventForm.reset();

        // Force hide recurring options
        isRecurringCheckbox.checked = false;
        recurringOptions.style.display = 'none';
        weeklyOptions.style.display = 'none';
        monthlyOptions.style.display = 'none';

        // Reset all recurring fields to default values
        document.getElementById('recurrenceInterval').value = '1';
        document.getElementById('recurrencePattern').value = 'weekly';
        document.getElementById('recurrenceDayOfMonth').value = '0';
        document.getElementById('recurrenceStartDate').value = '';
        document.getElementById('recurrenceEndDate').value = '';

        // Uncheck all weekly days
        document.querySelectorAll('input[name="recurrence_days[]"]').forEach(cb => {
            cb.checked = false;
        });

        // Reset task fields
        document.getElementById('taskId').value = '';
        document.getElementById('isEdit').value = '0';
        document.getElementById('taskName').value = '';
        document.getElementById('taskDescription').value = '';
        document.getElementById('taskDate').value = '';
        document.getElementById('assignedShift').value = '';
        document.getElementById('equipmentType').value = '';
    });

    // Close popover button
    document.getElementById('closePopover').addEventListener('click', function() {
        eventActionPopover.style.display = 'none';
    });

    // Close popover when clicking outside
    document.addEventListener('click', function(e) {
        const editDropdown = document.getElementById('editRecurringOptions');
        const editBtn = document.getElementById('btnEditEvent');
        const deleteDropdown = document.getElementById('deleteRecurringOptions');
        const deleteBtn = document.getElementById('btnDeleteEvent');

        // Close edit dropdown if clicking outside
        if (editDropdown && !editBtn.contains(e.target) && !editDropdown.contains(e.target)) {
            editDropdown.style.display = 'none';
        }

        // Close delete dropdown if clicking outside
        if (deleteDropdown && !deleteBtn.contains(e.target) && !deleteDropdown.contains(e.target)) {
            deleteDropdown.style.display = 'none';
        }

        // Close popover if clicking outside
        if (!eventActionPopover.contains(e.target) && !e.target.closest('.fc-event')) {
            eventActionPopover.style.display = 'none';
            // Also hide dropdowns when popover closes
            if (editDropdown) {
                editDropdown.style.display = 'none';
            }
            if (deleteDropdown) {
                deleteDropdown.style.display = 'none';
            }
        }
    });

    // Handle Edit button click - custom dropdown logic (no Bootstrap)
    document.getElementById('btnEditEvent').addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const isRecurring = this.hasAttribute('data-is-recurring');
        const editDropdown = document.getElementById('editRecurringOptions');
        const deleteDropdown = document.getElementById('deleteRecurringOptions');

        if (!isRecurring) {
            // Non-recurring event - directly edit
            eventActionPopover.style.display = 'none';
            if (currentEvent) {
                openEventModal(currentEvent);
            }
        } else {
            // Close delete dropdown if open
            deleteDropdown.style.display = 'none';

            // Recurring event - toggle custom dropdown
            const isVisible = editDropdown.style.display === 'block';

            if (isVisible) {
                editDropdown.style.display = 'none';
            } else {
                // Show dropdown first to get its height
                editDropdown.style.display = 'block';
                editDropdown.style.visibility = 'hidden';

                const btnRect = this.getBoundingClientRect();
                const dropdownHeight = editDropdown.offsetHeight;
                const viewportHeight = window.innerHeight;
                const spaceBelow = viewportHeight - btnRect.bottom;
                const spaceAbove = btnRect.top;

                // Position dropdown
                editDropdown.style.left = btnRect.left + 'px';

                // Check if dropdown fits below button
                if (spaceBelow >= dropdownHeight + 4) {
                    // Show below
                    editDropdown.style.top = (btnRect.bottom + 4) + 'px';
                } else if (spaceAbove >= dropdownHeight + 4) {
                    // Show above
                    editDropdown.style.top = (btnRect.top - dropdownHeight - 4) + 'px';
                } else {
                    // Not enough space either way, show below and let it scroll
                    editDropdown.style.top = (btnRect.bottom + 4) + 'px';
                }

                editDropdown.style.visibility = 'visible';
            }
        }
    });

    // Handle recurring edit options from custom dropdown
    document.querySelectorAll('#editRecurringOptions [data-edit-type]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Hide dropdown and popover immediately
            document.getElementById('editRecurringOptions').style.display = 'none';
            eventActionPopover.style.display = 'none';

            const editType = this.getAttribute('data-edit-type');

            if (currentEvent) {
                // Store edit type for later use in form submission
                currentEvent.extendedProps.editType = editType;
                openEventModal(currentEvent);
            }
        });
    });

    // Handle Delete button click - custom dropdown logic (no Bootstrap)
    document.getElementById('btnDeleteEvent').addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const isRecurring = this.hasAttribute('data-is-recurring');
        const deleteDropdown = document.getElementById('deleteRecurringOptions');
        const editDropdown = document.getElementById('editRecurringOptions');

        if (!isRecurring) {
            // Non-recurring event - directly delete
            if (confirm('Are you sure you want to delete this task?')) {
                deleteTask(currentEvent.id, 'this');
            }
        } else {
            // Close edit dropdown if open
            editDropdown.style.display = 'none';

            // Recurring event - toggle custom dropdown
            const isVisible = deleteDropdown.style.display === 'block';

            if (isVisible) {
                deleteDropdown.style.display = 'none';
            } else {
                // Show dropdown first to get its height
                deleteDropdown.style.display = 'block';
                deleteDropdown.style.visibility = 'hidden';

                const btnRect = this.getBoundingClientRect();
                const dropdownHeight = deleteDropdown.offsetHeight;
                const viewportHeight = window.innerHeight;
                const spaceBelow = viewportHeight - btnRect.bottom;
                const spaceAbove = btnRect.top;

                // Position dropdown
                deleteDropdown.style.left = btnRect.left + 'px';

                // Check if dropdown fits below button
                if (spaceBelow >= dropdownHeight + 4) {
                    // Show below
                    deleteDropdown.style.top = (btnRect.bottom + 4) + 'px';
                } else if (spaceAbove >= dropdownHeight + 4) {
                    // Show above
                    deleteDropdown.style.top = (btnRect.top - dropdownHeight - 4) + 'px';
                } else {
                    // Not enough space either way, show below and let it scroll
                    deleteDropdown.style.top = (btnRect.bottom + 4) + 'px';
                }

                deleteDropdown.style.visibility = 'visible';
            }
        }
    });

    // Handle recurring delete options from custom dropdown
    document.querySelectorAll('#deleteRecurringOptions [data-delete-type]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Hide dropdown immediately
            document.getElementById('deleteRecurringOptions').style.display = 'none';

            const deleteType = this.getAttribute('data-delete-type');
            const messages = {
                'this': 'Are you sure you want to delete this event?',
                'following': 'Are you sure you want to delete this and all following events?',
                'all': 'Are you sure you want to delete all events in this series?'
            };

            if (confirm(messages[deleteType])) {
                deleteTask(currentEvent.id, deleteType);
            }
        });
    });

    // Recurring checkbox toggle
    isRecurringCheckbox.addEventListener('change', function() {
        recurringOptions.style.display = this.checked ? 'block' : 'none';
        updateRecurrenceStartDate();
    });

    // Pattern change (show/hide weekly/monthly options)
    recurrencePattern.addEventListener('change', function() {
        const pattern = this.value;
        weeklyOptions.style.display = pattern === 'weekly' ? 'block' : 'none';
        monthlyOptions.style.display = pattern === 'monthly' ? 'block' : 'none';
    });

    // Task date change - update recurrence start date
    document.getElementById('taskDate').addEventListener('change', function() {
        updateRecurrenceStartDate();
    });

    function updateRecurrenceStartDate() {
        const taskDate = document.getElementById('taskDate').value;
        if (taskDate && isRecurringCheckbox.checked) {
            document.getElementById('recurrenceStartDate').value = taskDate;

            // Set end date to 1 year from start date
            const startDate = new Date(taskDate);
            const endDate = new Date(startDate);
            endDate.setFullYear(endDate.getFullYear() + 1);

            // Format as YYYY-MM-DD
            const endDateStr = endDate.toISOString().split('T')[0];
            document.getElementById('recurrenceEndDate').value = endDateStr;
        }
    }

    // Open event modal
    function openEventModal(event, dateStr) {
        // Reset form
        eventForm.reset();
        document.getElementById('isEdit').value = event ? '1' : '0';

        if (event) {
            // Edit mode
            document.getElementById('modalTitleText').textContent = 'Edit PM Task';
            document.getElementById('taskId').value = event.id;
            document.getElementById('taskName').value = event.title;
            // Load other fields from event.extendedProps
            if (event.extendedProps.description) {
                document.getElementById('taskDescription').value = event.extendedProps.description;
            }
            document.getElementById('taskDate').value = event.startStr.split('T')[0];
            if (event.extendedProps.assigned_shift_id) {
                document.getElementById('assignedShift').value = event.extendedProps.assigned_shift_id;
            }
            if (event.extendedProps.equipment_type) {
                document.getElementById('equipmentType').value = event.extendedProps.equipment_type;
            }

            // Load recurring settings if applicable
            if (event.extendedProps.is_recurring) {
                isRecurringCheckbox.checked = true;
                recurringOptions.style.display = 'block';
                document.getElementById('recurrencePattern').value = event.extendedProps.recurrence_pattern || 'weekly';
                document.getElementById('recurrenceInterval').value = event.extendedProps.recurrence_interval || 1;
                if (event.extendedProps.recurrence_start_date) {
                    document.getElementById('recurrenceStartDate').value = event.extendedProps.recurrence_start_date;
                }
                if (event.extendedProps.recurrence_end_date) {
                    document.getElementById('recurrenceEndDate').value = event.extendedProps.recurrence_end_date;
                }
                // ... load other recurring fields
            }
        } else {
            // New mode
            document.getElementById('modalTitleText').textContent = 'New PM Task';
            if (dateStr) {
                document.getElementById('taskDate').value = dateStr;
            }
        }

        eventModal.show();
    }

    // Form submit
    eventForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(eventForm);
        const taskId = document.getElementById('taskId').value;
        const isEdit = document.getElementById('isEdit').value === '1';

        // Convert is_recurring checkbox to boolean (send as 1 or 0)
        // Delete the default checkbox value first
        formData.delete('is_recurring');
        formData.append('is_recurring', isRecurringCheckbox.checked ? '1' : '0');

        // Ensure recurrence_interval is valid integer
        const intervalInput = document.getElementById('recurrenceInterval');
        if (intervalInput && intervalInput.value) {
            formData.set('recurrence_interval', parseInt(intervalInput.value) || 1);
        }

        // Collect recurring days - remove array entries first
        formData.delete('recurrence_days[]');
        formData.delete('recurrence_days');

        if (isRecurringCheckbox.checked && recurrencePattern.value === 'weekly') {
            const days = [];
            document.querySelectorAll('input[name="recurrence_days[]"]:checked').forEach(cb => {
                days.push(cb.value);
            });
            // Only append if there are selected days
            if (days.length > 0) {
                formData.append('recurrence_days', days.join(','));
            }
        }

        const url = isEdit
            ? `/admin/preventive-maintenance/calendar/tasks/${taskId}`
            : '/admin/preventive-maintenance/calendar/tasks';

        const method = isEdit ? 'PUT' : 'POST';

        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw err;
                }).catch(() => {
                    // If response is not JSON, throw generic error
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Close modal after success
                eventModal.hide();

                // Instead of refetching, manually update calendar events
                if (isEdit) {
                    // Update existing event
                    const existingEvent = calendar.getEventById(taskId);
                    if (existingEvent) {
                        existingEvent.remove();
                    }
                }

                // Fetch only the new/updated events
                const today = new Date();
                const startDate = new Date(today.getFullYear(), 0, 1);
                const endDate = new Date(today.getFullYear() + 1, 11, 31);

                fetch(`/admin/preventive-maintenance/calendar/events?start=${startDate.toISOString().split('T')[0]}&end=${endDate.toISOString().split('T')[0]}`)
                    .then(response => response.json())
                    .then(events => {
                        if (Array.isArray(events)) {
                            // Remove all existing events
                            calendar.getEvents().forEach(e => e.remove());
                            // Add updated events
                            events.forEach(event => calendar.addEvent(event));
                        }
                    });

                showAlert(data.message || 'Task saved successfully', 'success');
            } else {
                showAlert(data.message || 'Error saving task', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            let errorMessage = '';

            // Check if error has a message from server response
            if (error.message) {
                errorMessage = error.message;
            }

            // Check for validation errors
            if (error.errors) {
                errorMessage += '<br><strong>Validation errors:</strong><ul class="mb-0">';
                Object.keys(error.errors).forEach(field => {
                    const fieldName = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    errorMessage += `<li>${fieldName}: ${error.errors[field].join(', ')}</li>`;
                });
                errorMessage += '</ul>';
            }

            // Fallback if no message found
            if (!errorMessage) {
                errorMessage = 'Error saving task. Please try again.';
            }

            showAlert(errorMessage, 'danger');
        });
    });

    // Update event date via drag/drop
    function updateEventDate(event) {
        const oldDate = event.start;
        fetch(`/admin/preventive-maintenance/calendar/tasks/${event.id}/move`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                task_date: event.startStr.split('T')[0],
                start_time: event.startStr.split('T')[1]?.substring(0, 5),
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                showAlert('Error updating task date', 'danger');
                // Revert event position on error
                event.setStart(oldDate);
            }
        });
    }

    // Update event duration via resize
    function updateEventDuration(event) {
        const oldEnd = event.end;
        fetch(`/admin/preventive-maintenance/calendar/tasks/${event.id}/resize`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                end_time: event.endStr.split('T')[1]?.substring(0, 5),
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                showAlert('Error updating task duration', 'danger');
                // Revert event on error
                event.setEnd(oldEnd);
            }
        });
    }

    // Delete task function
    function deleteTask(taskId, deleteType) {
        eventActionPopover.style.display = 'none';

        fetch(`/admin/preventive-maintenance/calendar/tasks/${taskId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ delete_type: deleteType })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw err;
                }).catch(() => {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Remove events from calendar based on delete type
                if (deleteType === 'this') {
                    // Remove only this event
                    const event = calendar.getEventById(taskId);
                    if (event) event.remove();
                } else if (deleteType === 'following' || deleteType === 'all') {
                    // Reload all events to ensure consistency
                    const today = new Date();
                    const startDate = new Date(today.getFullYear(), 0, 1);
                    const endDate = new Date(today.getFullYear() + 1, 11, 31);

                    fetch(`/admin/preventive-maintenance/calendar/events?start=${startDate.toISOString().split('T')[0]}&end=${endDate.toISOString().split('T')[0]}`)
                        .then(response => response.json())
                        .then(events => {
                            if (Array.isArray(events)) {
                                calendar.getEvents().forEach(e => e.remove());
                                events.forEach(event => calendar.addEvent(event));
                            }
                        });
                }

                showAlert(data.message || 'Task deleted successfully', 'success');
            } else {
                showAlert(data.message || 'Error deleting task', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            let errorMessage = 'Error deleting task';
            if (error.message) {
                errorMessage += ': ' + error.message;
            }
            showAlert(errorMessage, 'danger');
        });
    }

    // Aggressive removal of any loading overlays that appear dynamically
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Element node
                    // Check if it's a loading overlay
                    const text = node.textContent || '';
                    if (text.includes('Saving') || text.includes('Loading') ||
                        node.className && (
                            node.className.includes('loading') ||
                            node.className.includes('spinner') ||
                            node.className.includes('overlay')
                        )) {
                        // Remove it immediately
                        node.remove();
                    }

                    // Also check child elements
                    const loadingElements = node.querySelectorAll('[class*="loading"], [class*="spinner"], [class*="overlay"]');
                    loadingElements.forEach(el => {
                        const elText = el.textContent || '';
                        if (elText.includes('Saving') || elText.includes('Loading')) {
                            el.remove();
                        }
                    });
                }
            });
        });
    });

    // Start observing the document body for added nodes
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});
</script>
@endpush

@endsection

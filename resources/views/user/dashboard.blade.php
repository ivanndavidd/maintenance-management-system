@extends('layouts.user')

@push('styles')
<style>
    /* Mini Calendar Styles */
    .mini-calendar {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .calendar-header {
        padding: 15px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px 12px 0 0;
        text-align: center;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 6px;
        padding: 8px;
        background: white;
    }

    .calendar-day-header {
        text-align: center;
        padding: 8px 4px;
        font-weight: 600;
        font-size: 10px;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .calendar-day {
        aspect-ratio: 1;
        border: none;
        padding: 4px;
        position: relative;
        min-height: 45px;
        max-height: 45px;
        background: white;
        border-radius: 8px;
        transition: all 0.2s;
        cursor: pointer;
    }

    .calendar-day:hover {
        background: #f5f5f5;
        transform: translateY(-2px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .calendar-day:hover .day-tooltip {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%);
    }

    .day-tooltip {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        margin-bottom: 8px;
        background: rgba(0, 0, 0, 0.9);
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 11px;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s;
        z-index: 1000;
        pointer-events: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }

    .day-tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-top-color: rgba(0, 0, 0, 0.9);
    }

    .day-tooltip .tooltip-row {
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 3px 0;
    }

    .day-tooltip .tooltip-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    .calendar-day.other-month {
        background: transparent;
    }

    .calendar-day.other-month .day-number {
        color: #ddd;
    }

    .calendar-day.today {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .calendar-day.today .day-number {
        color: white;
        font-weight: 700;
    }

    .day-number {
        font-size: 13px;
        font-weight: 500;
        padding: 2px;
        line-height: 1.2;
        color: #333;
        text-align: center;
    }

    /* Shift Indicator - Triangle in top-right corner */
    .shift-indicator {
        position: absolute;
        top: 0;
        right: 0;
        width: 0;
        height: 0;
        border-style: solid;
        border-width: 0 14px 14px 0;
        border-radius: 0 8px 0 0;
    }

    .shift-indicator.shift-1 {
        border-color: transparent #0d6efd transparent transparent;
    }

    .shift-indicator.shift-2 {
        border-color: transparent #ffc107 transparent transparent;
    }

    .shift-indicator.shift-3 {
        border-color: transparent #dc3545 transparent transparent;
    }

    /* Task Indicators - Circles at bottom */
    .task-indicators {
        position: absolute;
        bottom: 4px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 3px;
        justify-content: center;
    }

    .task-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
    }

    /* Month Navigation */
    .month-nav-btn {
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        padding: 5px 12px;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .month-nav-btn:hover {
        background: rgba(255,255,255,0.3);
    }

    .month-nav-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .task-dot.cm {
        background: #ffc107; /* Yellow for Corrective */
    }

    .task-dot.stock {
        background: #0d6efd; /* Blue for Stock Opname */
    }

    .task-dot.pm {
        background: #28a745; /* Green for Preventive */
    }

    /* Today Tasks Card */
    .today-task-item {
        padding: 12px;
        border-left: 4px solid;
        margin-bottom: 10px;
        border-radius: 4px;
        background: #f8f9fa;
        transition: all 0.2s;
    }

    .today-task-item:hover {
        transform: translateX(5px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .today-task-item.corrective {
        border-left-color: #ffc107;
        background: #fff8e1;
    }

    .today-task-item.preventive {
        border-left-color: #28a745;
        background: #e8f5e9;
    }

    .today-task-item.stock_opname {
        border-left-color: #0d6efd;
        background: #e7f3ff;
    }

    .task-type-badge {
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-home"></i> My Dashboard</h2>
            <p class="text-muted mb-0">Welcome back, {{ auth()->user()->name }}!</p>
        </div>
        <div class="text-end">
            <small class="text-muted">
                <i class="fas fa-calendar"></i> {{ now()->format('l, d F Y') }}
            </small>
        </div>
    </div>

    <!-- Row 1: Statistics Cards -->
    <div class="row g-3 mb-4">
        <!-- Pending Tasks Card -->
        <div class="col-xl-4 col-md-6">
            <div class="card border-warning shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-warning mb-1">Pending Tasks</h6>
                            <h2 class="mb-0 text-warning">{{ $tasks['pending'] }}</h2>
                            <small class="text-muted">Awaiting action</small>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-clock fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- In Progress Tasks Card -->
        <div class="col-xl-4 col-md-6">
            <div class="card border-primary shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-primary mb-1">In Progress</h6>
                            <h2 class="mb-0 text-primary">{{ $tasks['in_progress'] }}</h2>
                            <small class="text-muted">Currently working</small>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-tasks fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completed This Month Card -->
        <div class="col-xl-4 col-md-6">
            <div class="card border-success shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-success mb-1">Completed</h6>
                            <h2 class="mb-0 text-success">{{ $tasks['completed_this_month'] }}</h2>
                            <small class="text-muted">This month</small>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-check-circle fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-success bg-opacity-10 border-0">
                    <span class="text-success small">
                        <i class="fas fa-chart-line"></i> Completion Rate: {{ $completionRate }}%
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Today Tasks & Mini Calendar -->
    <div class="row g-3 mb-4">
        <!-- Today Tasks -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-calendar-day text-white"></i> <span class="text-white">Today's Tasks</span>
                        <span class="badge bg-white text-primary ms-2">{{ $todayTasks->count() }}</span>
                    </h5>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    @forelse($todayTasks as $task)
                        <a href="{{ $task['url'] }}" class="text-decoration-none">
                            <div class="today-task-item {{ $task['type'] }}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="task-type-badge
                                                {{ $task['type'] === 'corrective' ? 'bg-warning text-dark' : '' }}
                                                {{ $task['type'] === 'preventive' ? 'bg-success text-white' : '' }}
                                                {{ $task['type'] === 'stock_opname' ? 'bg-primary text-white' : '' }}
                                            ">
                                                @if($task['type'] === 'corrective')
                                                    <i class="fas fa-wrench"></i> CM
                                                @elseif($task['type'] === 'preventive')
                                                    <i class="fas fa-calendar-check"></i> PM
                                                @else
                                                    <i class="fas fa-boxes"></i> Stock
                                                @endif
                                            </span>
                                            <strong>{{ $task['title'] }}</strong>
                                        </div>
                                        <small class="text-muted">
                                            <i class="fas fa-hashtag"></i> {{ $task['id'] }}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $task['status'] === 'pending' ? 'warning' : 'primary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $task['status'])) }}
                                        </span>
                                        @if(isset($task['priority']))
                                            <br>
                                            <small class="badge bg-{{ $task['priority'] === 'high' ? 'danger' : ($task['priority'] === 'medium' ? 'warning' : 'secondary') }} mt-1">
                                                {{ ucfirst($task['priority']) }}
                                            </small>
                                        @endif
                                        @if(isset($task['shift']))
                                            <br>
                                            <small class="badge bg-info mt-1">
                                                Shift {{ $task['shift'] }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-clipboard-check fa-3x mb-3"></i>
                            <p class="mb-0">No tasks scheduled for today</p>
                            <small>Enjoy your day!</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Mini Calendar -->
        <div class="col-lg-6">
            <div class="mini-calendar">
                <div class="calendar-header">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <button class="month-nav-btn" onclick="changeMonth(-1)">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <h5 class="mb-0" id="calendarMonthYear">
                            <i class="fas fa-calendar-alt"></i> <span id="currentMonthDisplay">{{ now()->format('F Y') }}</span>
                        </h5>
                        <button class="month-nav-btn" onclick="changeMonth(1)">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    <small>My Schedule</small>
                </div>
                <div class="p-2">
                    <!-- Legend -->
                    <div class="mb-3 p-2 rounded" style="background: #f8f9fa; font-size: 11px;">
                        <div class="d-flex gap-3 flex-wrap justify-content-center align-items-center">
                            <div class="d-flex align-items-center gap-1">
                                <span class="task-dot cm"></span>
                                <span style="color: #6c757d;">CM</span>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <span class="task-dot stock"></span>
                                <span style="color: #6c757d;">Stock</span>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <span class="task-dot pm"></span>
                                <span style="color: #6c757d;">PM</span>
                            </div>
                            <div style="border-left: 1px solid #dee2e6; height: 15px; margin: 0 4px;"></div>
                            <div class="d-flex align-items-center gap-1">
                                <span style="color: #0d6efd; font-size: 11px;">▲</span>
                                <span style="color: #6c757d;">S1</span>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <span style="color: #ffc107; font-size: 11px;">▲</span>
                                <span style="color: #6c757d;">S2</span>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <span style="color: #dc3545; font-size: 11px;">▲</span>
                                <span style="color: #6c757d;">S3</span>
                            </div>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="calendar-grid" id="calendarGrid">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent CMR Tasks -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-tasks"></i> Recent CMR Tasks</h5>
        </div>
        <div class="card-body">
            @if($recentTasks->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ticket</th>
                                <th>Equipment</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTasks as $task)
                            <tr onclick="window.location='{{ route('user.corrective-maintenance.show', $task->id) }}'" style="cursor: pointer;">
                                <td><strong>{{ $task->ticket_number }}</strong></td>
                                <td>{{ $task->equipment_name ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $task->priority === 'high' ? 'danger' : ($task->priority === 'medium' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $task->status === 'pending' ? 'warning' : ($task->status === 'in_progress' ? 'primary' : 'success') }}">
                                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                    </span>
                                </td>
                                <td>{{ $task->created_at->format('d M Y, H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center text-muted py-4">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p class="mb-0">No recent tasks</p>
                </div>
            @endif
        </div>
    </div>

    <!-- My Performance -->
    <div class="card shadow-sm">
        <div class="card-header" style="background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%); color: white;">
            <h5 class="mb-0"><i class="fas fa-chart-line"></i> My Performance</h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-4">
                    <h3 class="text-primary">{{ $completionRate }}%</h3>
                    <p class="text-muted mb-0">Completion Rate</p>
                </div>
                <div class="col-md-4">
                    <h3 class="text-success">{{ $tasks['completed_this_month'] }}</h3>
                    <p class="text-muted mb-0">Completed This Month</p>
                </div>
                <div class="col-md-4">
                    <h3 class="text-warning">{{ $tasks['in_progress'] }}</h3>
                    <p class="text-muted mb-0">Active Tasks</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentMonth = {{ now()->month }};
let currentYear = {{ now()->year }};
const userId = {{ auth()->id() }};

// Initial calendar data from server
let calendarDataCache = @json($calendarData);

// Load calendar on page load
document.addEventListener('DOMContentLoaded', function() {
    renderCalendar();
});

function changeMonth(direction) {
    currentMonth += direction;

    if (currentMonth > 12) {
        currentMonth = 1;
        currentYear++;
    } else if (currentMonth < 1) {
        currentMonth = 12;
        currentYear--;
    }

    // Update display
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                        'July', 'August', 'September', 'October', 'November', 'December'];
    document.getElementById('currentMonthDisplay').textContent = `${monthNames[currentMonth - 1]} ${currentYear}`;

    // Re-render calendar with cached data (no API call needed)
    renderCalendar();
}

function renderCalendar() {
    const grid = document.getElementById('calendarGrid');
    grid.innerHTML = '';

    // Add day headers
    const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    dayHeaders.forEach(day => {
        const header = document.createElement('div');
        header.className = 'calendar-day-header';
        header.textContent = day;
        grid.appendChild(header);
    });

    // Calculate calendar dates
    const firstDay = new Date(currentYear, currentMonth - 1, 1);
    const lastDay = new Date(currentYear, currentMonth, 0);
    const daysInMonth = lastDay.getDate();
    const startingDayOfWeek = firstDay.getDay(); // 0 = Sunday

    // Add days from previous month
    const prevMonth = currentMonth === 1 ? 12 : currentMonth - 1;
    const prevYear = currentMonth === 1 ? currentYear - 1 : currentYear;
    const daysInPrevMonth = new Date(prevYear, prevMonth, 0).getDate();

    for (let i = startingDayOfWeek - 1; i >= 0; i--) {
        const day = daysInPrevMonth - i;
        const dateStr = formatDate(prevYear, prevMonth, day);
        addDayCell(day, dateStr, false, false);
    }

    // Add days of current month
    const today = new Date();
    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = formatDate(currentYear, currentMonth, day);
        const isToday = (day === today.getDate() && currentMonth === (today.getMonth() + 1) && currentYear === today.getFullYear());
        addDayCell(day, dateStr, true, isToday);
    }

    // Add days from next month to complete the grid
    const totalCells = grid.children.length - 7; // Minus day headers
    const remainingCells = (Math.ceil(totalCells / 7) * 7) - totalCells;
    const nextMonth = currentMonth === 12 ? 1 : currentMonth + 1;
    const nextYear = currentMonth === 12 ? currentYear + 1 : currentYear;

    for (let day = 1; day <= remainingCells; day++) {
        const dateStr = formatDate(nextYear, nextMonth, day);
        addDayCell(day, dateStr, false, false);
    }
}

function addDayCell(dayNumber, dateStr, isCurrentMonth, isToday) {
    const dayData = calendarDataCache[dateStr] || { shift: null, tasks: [] };

    const cell = document.createElement('div');
    cell.className = 'calendar-day';
    if (!isCurrentMonth) cell.classList.add('other-month');
    if (isToday) cell.classList.add('today');

    // Shift indicator (triangle)
    if (dayData.shift) {
        const shiftIndicator = document.createElement('div');
        shiftIndicator.className = `shift-indicator shift-${dayData.shift}`;
        cell.appendChild(shiftIndicator);
    }

    // Day number
    const dayNum = document.createElement('div');
    dayNum.className = 'day-number';
    dayNum.textContent = dayNumber;
    cell.appendChild(dayNum);

    // Task indicators (dots)
    if (dayData.tasks && dayData.tasks.length > 0) {
        const indicators = document.createElement('div');
        indicators.className = 'task-indicators';

        dayData.tasks.forEach(taskType => {
            const dot = document.createElement('div');
            dot.className = `task-dot ${taskType}`;
            indicators.appendChild(dot);
        });

        cell.appendChild(indicators);

        // Create tooltip with task counts
        const tooltip = createTooltip(dayData.tasks);
        cell.appendChild(tooltip);
    }

    document.getElementById('calendarGrid').appendChild(cell);
}

function createTooltip(tasks) {
    const tooltip = document.createElement('div');
    tooltip.className = 'day-tooltip';

    // Count each task type
    const taskCounts = {
        cm: tasks.filter(t => t === 'cm').length,
        stock: tasks.filter(t => t === 'stock').length,
        pm: tasks.filter(t => t === 'pm').length
    };

    let tooltipContent = '';

    if (taskCounts.cm > 0) {
        tooltipContent += `
            <div class="tooltip-row">
                <span class="tooltip-dot" style="background: #ffc107;"></span>
                <span>CM: ${taskCounts.cm}</span>
            </div>
        `;
    }

    if (taskCounts.stock > 0) {
        tooltipContent += `
            <div class="tooltip-row">
                <span class="tooltip-dot" style="background: #0d6efd;"></span>
                <span>Stock: ${taskCounts.stock}</span>
            </div>
        `;
    }

    if (taskCounts.pm > 0) {
        tooltipContent += `
            <div class="tooltip-row">
                <span class="tooltip-dot" style="background: #28a745;"></span>
                <span>PM: ${taskCounts.pm}</span>
            </div>
        `;
    }

    tooltip.innerHTML = tooltipContent;
    return tooltip;
}

function formatDate(year, month, day) {
    return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
}
</script>
@endpush

@endsection

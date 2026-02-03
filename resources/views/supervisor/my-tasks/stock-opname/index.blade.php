@extends('layouts.admin')

@section('page-title', 'My Stock Opname')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-clipboard-check"></i> My Stock Opname</h4>
            <p class="text-muted mb-0">View and execute your assigned stock opname schedules</p>
        </div>
    </div>

    <!-- Schedules Cards -->
    <div class="row">
        @forelse($schedules as $schedule)
            @php
                $progressPercentage = $schedule->getProgressPercentage();
                $daysRemaining = $schedule->getDaysRemaining();
                $isOverdue = $schedule->isOverdue();

                $statusColors = [
                    'draft' => 'secondary',
                    'active' => 'danger',
                    'completed' => 'success',
                    'cancelled' => 'dark'
                ];
                $statusColor = $statusColors[$schedule->status] ?? 'secondary';
            @endphp

            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-{{ $statusColor }} text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>{{ $schedule->schedule_code }}
                            @if($isOverdue && $schedule->status === 'active')
                                <span class="badge bg-danger ms-2">Overdue</span>
                            @endif
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-2">Execution Date</h6>
                        <p class="mb-3">
                            {{ \Carbon\Carbon::parse($schedule->execution_date)->format('d M Y') }}
                            @if($isOverdue && $schedule->status === 'active')
                                <span class="badge bg-danger">Overdue</span>
                            @endif
                        </p>

                        <h6 class="mb-2">Item Types</h6>
                        <div class="mb-3">
                            @php
                                $itemTypes = $schedule->scheduleItems->pluck('item_type')->unique();
                            @endphp
                            @foreach($itemTypes as $type)
                                <span class="badge bg-info me-1">{{ ucfirst($type) }}</span>
                            @endforeach
                        </div>

                        <h6 class="mb-2">Progress</h6>
                        <div class="progress mb-2" style="height: 25px;">
                            <div class="progress-bar" role="progressbar"
                                 style="width: {{ $progressPercentage }}%"
                                 aria-valuenow="{{ $progressPercentage }}"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                                {{ $schedule->completed_items }} / {{ $schedule->total_items }} ({{ $progressPercentage }}%)
                            </div>
                        </div>

                        <div class="row text-center mt-3">
                            <div class="col-4">
                                <div class="fw-bold">Total</div>
                                <div class="badge bg-secondary">{{ $schedule->total_items }}</div>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold">Completed</div>
                                <div class="badge bg-success">{{ $schedule->completed_items }}</div>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold">Pending</div>
                                <div class="badge bg-warning">{{ $schedule->pendingItems()->count() }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <a href="{{ route('supervisor.my-tasks.stock-opname.show', $schedule->id) }}"
                           class="btn btn-primary w-100">
                            <i class="fas fa-clipboard-check me-2"></i>View Details & Execute
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Stock Opname Assignments</h5>
                        <p class="text-muted">You don't have any stock opname schedules assigned to you.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($schedules->hasPages())
        <div class="d-flex justify-content-center">
            {{ $schedules->links() }}
        </div>
    @endif
</div>
@endsection

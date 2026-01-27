@extends('layouts.user')

@section('page-title', 'My Stock Opname Assignments')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2><i class="fas fa-clipboard-check"></i> My Stock Opname Assignments</h2>
        <p class="text-muted">View and execute your assigned stock opname schedules</p>
    </div>

    @if($schedules->count() > 0)
        <div class="row">
            @foreach($schedules as $schedule)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 {{ $schedule->isOverdue() ? 'border-danger' : '' }}">
                    <div class="card-header {{ $schedule->isOverdue() ? 'bg-danger text-white' : 'bg-primary text-white' }}">
                        <h6 class="mb-0">
                            <i class="fas fa-calendar-alt"></i> {{ $schedule->schedule_code }}
                        </h6>
                    </div>
                    <div class="card-body">
                        {{-- Execution Date --}}
                        <div class="mb-3">
                            <small class="text-muted d-block">Execution Date</small>
                            <strong>{{ $schedule->execution_date->format('d M Y') }}</strong>

                            @if($schedule->isOverdue())
                                <span class="badge bg-danger ms-2">Overdue</span>
                            @else
                                <span class="badge bg-success ms-2">Active</span>
                            @endif
                        </div>

                        {{-- Item Types --}}
                        <div class="mb-3">
                            <small class="text-muted d-block">Item Types</small>
                            <div>
                                @if($schedule->include_spareparts)
                                    <span class="badge bg-info me-1">Spareparts</span>
                                @endif
                                @if($schedule->include_tools)
                                    <span class="badge bg-warning me-1">Tools</span>
                                @endif
                                @if($schedule->include_assets)
                                    <span class="badge bg-success me-1">Assets</span>
                                @endif
                            </div>
                        </div>

                        {{-- Progress --}}
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-muted">Progress</small>
                                <small class="fw-bold">{{ $schedule->getProgressPercentage() }}%</small>
                            </div>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar {{ $schedule->getProgressPercentage() == 100 ? 'bg-success' : 'bg-primary' }}"
                                    role="progressbar"
                                    style="width: {{ $schedule->getProgressPercentage() }}%"
                                    aria-valuenow="{{ $schedule->getProgressPercentage() }}"
                                    aria-valuemin="0"
                                    aria-valuemax="100">
                                    {{ $schedule->completed_items }} / {{ $schedule->total_items }}
                                </div>
                            </div>
                        </div>

                        {{-- Statistics --}}
                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <div class="small text-muted">Total</div>
                                <div class="fw-bold">{{ $schedule->total_items }}</div>
                            </div>
                            <div class="col-4">
                                <div class="small text-success">Completed</div>
                                <div class="fw-bold text-success">{{ $schedule->completed_items }}</div>
                            </div>
                            <div class="col-4">
                                <div class="small text-warning">Pending</div>
                                <div class="fw-bold text-warning">{{ $schedule->pendingItems()->count() }}</div>
                            </div>
                        </div>

                        {{-- Days Remaining --}}
                        @if(!$schedule->isOverdue() && $schedule->status !== 'completed')
                        <div class="alert alert-info small mb-3">
                            <i class="fas fa-clock"></i>
                            @if($schedule->getDaysRemaining() > 0)
                                {{ $schedule->getDaysRemaining() }} days remaining
                            @elseif($schedule->getDaysRemaining() == 0)
                                Last day!
                            @else
                                {{ abs($schedule->getDaysRemaining()) }} days overdue
                            @endif
                        </div>
                        @endif

                        @if($schedule->notes)
                        <div class="mb-3">
                            <small class="text-muted d-block">Notes</small>
                            <small>{{ Str::limit($schedule->notes, 100) }}</small>
                        </div>
                        @endif
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('user.stock-opname.show', $schedule->id) }}" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-eye"></i> View Details & Execute
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No Stock Opname Assignments</h5>
                <p class="text-muted">You don't have any stock opname assignments at the moment.</p>
                <small class="text-muted">Check back later or contact your supervisor if you think this is an error.</small>
            </div>
        </div>
    @endif

    {{-- Info Card --}}
    <div class="card mt-4">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0"><i class="fas fa-info-circle"></i> How Stock Opname Works</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="fw-bold">Your Role:</h6>
                    <ul class="small">
                        <li>You are assigned based on your shift schedule</li>
                        <li>Work together with your team members</li>
                        <li>Execute opname for any pending item in the schedule</li>
                        <li>Items you complete will be marked and removed from everyone's pending list</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold">Execution Steps:</h6>
                    <ul class="small">
                        <li>Click "View Details & Execute" on a schedule</li>
                        <li>Choose an item from the pending list</li>
                        <li>Count the physical quantity</li>
                        <li>Input the count and submit</li>
                        <li>System will calculate discrepancy automatically</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

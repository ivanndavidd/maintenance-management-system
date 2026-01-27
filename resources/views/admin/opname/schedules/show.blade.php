@extends('layouts.admin')

@section('page-title', 'Schedule Details')

@push('styles')
<style>
    /* Ensure modal content is above backdrop */
    .modal {
        z-index: 1055 !important;
    }
    .modal-backdrop {
        z-index: 1050 !important;
    }
    .modal-dialog {
        z-index: 1056 !important;
    }
</style>
@endpush

@section('content')

<div class="container-fluid">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2><i class="fas fa-calendar-check"></i> Schedule Details</h2>
            <div class="btn-group">
                <a href="{{ route('admin.opname.schedules.export', $schedule) }}" class="btn btn-success" id="exportBtn"  onclick="handleExport(event)">
                    <i class="fas fa-file-excel"></i> Export to Excel
                </a>
                @if($schedule->canBeClosed())
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#closeTicketModal">
                        <i class="fas fa-check-circle"></i> Close Ticket
                    </button>
                @elseif($schedule->ticket_status === 'closed')
                    <a href="{{ route('admin.opname.compliance.show', $schedule) }}" class="btn btn-info">
                        <i class="fas fa-file-alt"></i> View Compliance Report
                    </a>
                @endif
            </div>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.opname.schedules.index') }}">Schedules</a></li>
                <li class="breadcrumb-item active">{{ $schedule->schedule_code }}</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            {{-- Schedule Information --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Schedule Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Schedule Code:</strong><br>
                            <span class="fs-5">{{ $schedule->schedule_code }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong><br>
                            @if($schedule->status === 'active')
                                <span class="badge bg-success fs-6">Active</span>
                            @elseif($schedule->status === 'inactive')
                                <span class="badge bg-secondary fs-6">Inactive</span>
                            @else
                                <span class="badge bg-primary fs-6">Completed</span>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Item Types:</strong><br>
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
                        <div class="col-md-6">
                            <strong>Execution Date:</strong><br>
                            <strong>{{ $schedule->execution_date->format('d M Y') }}</strong>
                            <br>
                            <small class="text-muted">
                                @php
                                    $daysRemaining = $schedule->getDaysRemaining();
                                @endphp
                                @if($daysRemaining > 0)
                                    <i class="fas fa-calendar-day"></i> {{ $daysRemaining }} days left
                                @elseif($daysRemaining == 0)
                                    <i class="fas fa-exclamation-circle text-warning"></i> Last day!
                                @else
                                    <i class="fas fa-times-circle text-danger"></i> {{ abs($daysRemaining) }} days overdue
                                @endif
                            </small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Locations:</strong><br>
                            @if($schedule->include_spareparts && $schedule->sparepart_locations)
                                <small class="text-muted">Spareparts:</small>
                                @foreach($schedule->sparepart_locations as $loc)
                                    <span class="badge bg-light text-dark border me-1">{{ $loc }}</span>
                                @endforeach
                                <br>
                            @endif
                            @if($schedule->include_tools)
                                <small class="text-muted">Tools: All locations</small><br>
                            @endif
                            @if($schedule->include_assets && $schedule->asset_locations)
                                <small class="text-muted">Assets:</small>
                                @foreach($schedule->asset_locations as $loc)
                                    <span class="badge bg-light text-dark border me-1">{{ $loc }}</span>
                                @endforeach
                            @endif
                        </div>
                        <div class="col-md-6">
                            <strong>Assigned Users:</strong><br>
                            @if($schedule->userAssignments->count() > 0)
                                @foreach($schedule->userAssignments->take(3) as $assignment)
                                    <span class="badge bg-primary me-1">
                                        {{ $assignment->user->name }}
                                        <small>({{ strtoupper(str_replace('_', ' ', $assignment->shift_type)) }})</small>
                                    </span>
                                    @if($loop->iteration == 3 && $schedule->userAssignments->count() > 3)
                                        <br><small class="text-muted">+{{ $schedule->userAssignments->count() - 3 }} more</small>
                                    @endif
                                @endforeach
                            @else
                                <span class="text-muted">No users assigned</span>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Created By:</strong><br>
                            {{ $schedule->createdByUser->name ?? 'N/A' }}
                            <br>
                            <small class="text-muted">{{ $schedule->created_at->format('d M Y H:i') }}</small>
                        </div>
                        <div class="col-md-6">
                            <strong>Progress:</strong><br>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar {{ $stats['progress_percentage'] == 100 ? 'bg-success' : 'bg-primary' }}"
                                     role="progressbar"
                                     style="width: {{ $stats['progress_percentage'] }}%">
                                    {{ $stats['progress_percentage'] }}%
                                </div>
                            </div>
                            <small class="text-muted">
                                {{ $stats['completed_items'] }} / {{ $stats['total_items'] }} items completed
                            </small>
                        </div>
                    </div>

                    @if($schedule->notes)
                    <div class="mb-3">
                        <strong>Notes:</strong><br>
                        <div class="p-3 bg-light rounded">
                            {{ $schedule->notes }}
                        </div>
                    </div>
                    @endif

                    <hr>

                    <div class="row">
                        <div class="col-md-4">
                            <strong>Total Items:</strong><br>
                            <span class="fs-5 text-primary">{{ $stats['total_items'] }}</span>
                        </div>
                        <div class="col-md-4">
                            <strong>Completed:</strong><br>
                            <span class="fs-5 text-success">{{ $stats['completed_items'] }}</span>
                        </div>
                        <div class="col-md-4">
                            <strong>Pending:</strong><br>
                            <span class="fs-5 text-secondary">{{ $stats['pending_items'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Analytics Dashboard --}}
            @if($schedule->completed_items > 0)
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Analytics & Statistics</h5>
                </div>
                <div class="card-body">
                    {{-- Discrepancy Analysis --}}
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3"><i class="fas fa-exclamation-triangle text-warning"></i> Discrepancy Analysis</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="card border-info">
                                    <div class="card-body text-center p-2">
                                        <div class="fs-4 fw-bold text-primary">{{ $analytics['discrepancy']['accuracy_rate'] }}%</div>
                                        <small class="text-muted">Accuracy Rate</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-success">
                                    <div class="card-body text-center p-2">
                                        <div class="fs-4 fw-bold text-success">{{ $analytics['discrepancy']['items_without_discrepancy'] }}</div>
                                        <small class="text-muted">Accurate</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-warning">
                                    <div class="card-body text-center p-2">
                                        <div class="fs-4 fw-bold text-warning">{{ $analytics['discrepancy']['items_with_discrepancy'] }}</div>
                                        <small class="text-muted">With Discrepancy</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-danger">
                                    <div class="card-body text-center p-2">
                                        <div class="fs-4 fw-bold text-danger">Rp {{ number_format(abs($analytics['discrepancy']['total_negative_value'] - $analytics['discrepancy']['total_positive_value']), 0, ',', '.') }}</div>
                                        <small class="text-muted">Net Value Impact</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-2 mt-2">
                            <div class="col-md-6">
                                <div class="alert alert-success mb-0 py-2">
                                    <strong><i class="fas fa-arrow-up"></i> Positive:</strong>
                                    {{ $analytics['discrepancy']['positive_discrepancy_count'] }} items (+{{ $analytics['discrepancy']['total_positive_qty'] }} qty)
                                    | Rp {{ number_format($analytics['discrepancy']['total_positive_value'], 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-danger mb-0 py-2">
                                    <strong><i class="fas fa-arrow-down"></i> Negative:</strong>
                                    {{ $analytics['discrepancy']['negative_discrepancy_count'] }} items (-{{ $analytics['discrepancy']['total_negative_qty'] }} qty)
                                    | Rp {{ number_format($analytics['discrepancy']['total_negative_value'], 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Review Status --}}
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3"><i class="fas fa-clipboard-check text-info"></i> Review Status</h6>
                        <div class="row g-2">
                            <div class="col-6 col-md-2">
                                <div class="text-center p-2 border rounded">
                                    <div class="fs-5 fw-bold text-warning">{{ $analytics['review']['pending_review'] }}</div>
                                    <small class="text-muted">Pending</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="text-center p-2 border rounded">
                                    <div class="fs-5 fw-bold text-success">{{ $analytics['review']['approved'] }}</div>
                                    <small class="text-muted">Approved</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="text-center p-2 border rounded">
                                    <div class="fs-5 fw-bold text-danger">{{ $analytics['review']['rejected'] }}</div>
                                    <small class="text-muted">Rejected</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-center p-2 border rounded">
                                    <div class="fs-5 fw-bold text-secondary">{{ $analytics['review']['no_review_needed'] }}</div>
                                    <small class="text-muted">No Review Needed</small>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="text-center p-2 border rounded bg-light">
                                    <div class="fs-5 fw-bold text-primary">{{ $analytics['sync']['sync_rate'] }}%</div>
                                    <small class="text-muted">Synced ({{ $analytics['sync']['synced'] }}/{{ $analytics['sync']['total_approved_with_discrepancy'] }})</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Item Type Breakdown --}}
                    <div class="mb-4">
                        <h6 class="fw-bold mb-3"><i class="fas fa-boxes text-primary"></i> Item Type Breakdown</h6>
                        <div class="row g-2">
                            @if($analytics['item_types']['spareparts']['total'] > 0)
                            <div class="col-md-4">
                                <div class="card border-primary">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-bold">Spareparts</div>
                                                <small class="text-muted">{{ $analytics['item_types']['spareparts']['completed'] }}/{{ $analytics['item_types']['spareparts']['total'] }} completed</small>
                                            </div>
                                            <div class="fs-4 fw-bold text-primary">{{ $analytics['item_types']['spareparts']['total'] }}</div>
                                        </div>
                                        <div class="progress mt-2" style="height: 5px;">
                                            <div class="progress-bar bg-primary" style="width: {{ $analytics['item_types']['spareparts']['total'] > 0 ? round(($analytics['item_types']['spareparts']['completed'] / $analytics['item_types']['spareparts']['total']) * 100) : 0 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if($analytics['item_types']['tools']['total'] > 0)
                            <div class="col-md-4">
                                <div class="card border-success">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-bold">Tools</div>
                                                <small class="text-muted">{{ $analytics['item_types']['tools']['completed'] }}/{{ $analytics['item_types']['tools']['total'] }} completed</small>
                                            </div>
                                            <div class="fs-4 fw-bold text-success">{{ $analytics['item_types']['tools']['total'] }}</div>
                                        </div>
                                        <div class="progress mt-2" style="height: 5px;">
                                            <div class="progress-bar bg-success" style="width: {{ $analytics['item_types']['tools']['total'] > 0 ? round(($analytics['item_types']['tools']['completed'] / $analytics['item_types']['tools']['total']) * 100) : 0 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if($analytics['item_types']['assets']['total'] > 0)
                            <div class="col-md-4">
                                <div class="card border-warning">
                                    <div class="card-body p-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-bold">Assets</div>
                                                <small class="text-muted">{{ $analytics['item_types']['assets']['completed'] }}/{{ $analytics['item_types']['assets']['total'] }} completed</small>
                                            </div>
                                            <div class="fs-4 fw-bold text-warning">{{ $analytics['item_types']['assets']['total'] }}</div>
                                        </div>
                                        <div class="progress mt-2" style="height: 5px;">
                                            <div class="progress-bar bg-warning" style="width: {{ $analytics['item_types']['assets']['total'] > 0 ? round(($analytics['item_types']['assets']['completed'] / $analytics['item_types']['assets']['total']) * 100) : 0 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- User Performance --}}
                    @if(count($analytics['user_performance']) > 0)
                    <div>
                        <h6 class="fw-bold mb-3"><i class="fas fa-users text-success"></i> User Performance</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th class="text-center">Items Counted</th>
                                        <th class="text-center">With Discrepancy</th>
                                        <th class="text-center">Accuracy Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($analytics['user_performance'] as $userStat)
                                    <tr>
                                        <td>{{ $userStat['user_name'] }}</td>
                                        <td class="text-center">{{ $userStat['total_items'] }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $userStat['items_with_discrepancy'] > 0 ? 'bg-warning' : 'bg-success' }}">
                                                {{ $userStat['items_with_discrepancy'] }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $userStat['accuracy_rate'] >= 95 ? 'bg-success' : ($userStat['accuracy_rate'] >= 85 ? 'bg-warning' : 'bg-danger') }}">
                                                {{ $userStat['accuracy_rate'] }}%
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Scheduled Items --}}
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-box"></i> Scheduled Items ({{ $schedule->total_items }})</h5>
                    <small class="text-muted">Showing {{ $scheduleItems->firstItem() ?? 0 }}-{{ $scheduleItems->lastItem() ?? 0 }} of {{ $scheduleItems->total() }}</small>
                </div>
                <div class="card-body">
                    {{-- Filter Form --}}
                    <form method="GET" action="{{ route('admin.opname.schedules.show', $schedule) }}" class="mb-3">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="Item code or name..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Item Type</label>
                                <select name="item_type" class="form-select form-select-sm">
                                    <option value="">All Types</option>
                                    <option value="sparepart" {{ request('item_type') == 'sparepart' ? 'selected' : '' }}>Sparepart</option>
                                    <option value="tool" {{ request('item_type') == 'tool' ? 'selected' : '' }}>Tool</option>
                                    <option value="asset" {{ request('item_type') == 'asset' ? 'selected' : '' }}>Asset</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Execution Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Review Status</label>
                                <select name="review_status" class="form-select form-select-sm">
                                    <option value="">All</option>
                                    <option value="pending_review" {{ request('review_status') == 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                                    <option value="approved" {{ request('review_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ request('review_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="no_review_needed" {{ request('review_status') == 'no_review_needed' ? 'selected' : '' }}>No Review Needed</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary btn-sm me-2">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="{{ route('admin.opname.schedules.show', $schedule) }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                        @if(request('search') || request('item_type') || request('status') || request('review_status'))
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    Filtered: {{ $scheduleItems->total() }} items
                                    @if(request('search'))
                                        | Search: "{{ request('search') }}"
                                    @endif
                                </small>
                            </div>
                        @endif
                    </form>

                    {{-- Stock Sync Status & Actions --}}
                    @php
                        $approvedItems = $schedule->items()->where('review_status', 'approved')->where('discrepancy_qty', '!=', 0)->get();
                        $syncedCount = $approvedItems->filter(fn($item) => $item->isSynced())->count();
                        $pendingSyncCount = $approvedItems->count() - $syncedCount;
                    @endphp

                    @if($pendingSyncCount > 0 || $syncedCount > 0)
                        <div class="alert alert-info d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <i class="fas fa-sync"></i>
                                <strong>Stock Sync Status:</strong>
                                {{ $syncedCount }} synced, {{ $pendingSyncCount }} pending sync
                                @if($syncedCount > 0)
                                    | <a href="{{ route('admin.adjustments.index') }}" target="_blank" class="alert-link">View Adjustments <i class="fas fa-external-link-alt"></i></a>
                                @endif
                            </div>
                            @if($pendingSyncCount > 0)
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#batchSyncModal">
                                    <i class="fas fa-sync"></i> Batch Sync ({{ $pendingSyncCount }})
                                </button>
                            @endif
                        </div>
                    @endif

                    @if($scheduleItems->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Item Code</th>
                                        <th>Item Name</th>
                                        <th>Type</th>
                                        <th>Sys Qty</th>
                                        <th>Phys Qty</th>
                                        <th>Disc</th>
                                        <th>Exec Status</th>
                                        <th>Review Status</th>
                                        <th>Sync Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($scheduleItems as $index => $scheduleItem)
                                    <tr class="{{ $scheduleItem->needsReview() ? 'table-warning' : '' }}">
                                        <td>{{ $scheduleItems->firstItem() + $index }}</td>
                                        <td><small>{{ $scheduleItem->getItemCode() }}</small></td>
                                        <td>
                                            <small>{{ $scheduleItem->getItemName() }}</small>
                                            @if($scheduleItem->notes)
                                                <br><small class="text-muted"><i class="fas fa-comment"></i> {{ Str::limit($scheduleItem->notes, 30) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($scheduleItem->item_type === 'sparepart')
                                                <span class="badge bg-info">SP</span>
                                            @elseif($scheduleItem->item_type === 'tool')
                                                <span class="badge bg-warning">TL</span>
                                            @else
                                                <span class="badge bg-success">AS</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($scheduleItem->system_quantity !== null)
                                                {{ $scheduleItem->system_quantity }}
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($scheduleItem->physical_quantity !== null)
                                                {{ $scheduleItem->physical_quantity }}
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($scheduleItem->hasDiscrepancy())
                                                <span class="badge {{ $scheduleItem->discrepancy_qty > 0 ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $scheduleItem->discrepancy_qty > 0 ? '+' : '' }}{{ $scheduleItem->discrepancy_qty }}
                                                </span>
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($scheduleItem->execution_status === 'pending')
                                                <span class="badge bg-secondary">Pending</span>
                                            @elseif($scheduleItem->execution_status === 'in_progress')
                                                <span class="badge bg-warning">In Progress</span>
                                            @elseif($scheduleItem->execution_status === 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @else
                                                <span class="badge bg-danger">Cancelled</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $scheduleItem->getReviewStatusBadgeColor() }}">
                                                {{ $scheduleItem->getReviewStatusLabel() }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($scheduleItem->isSynced())
                                                @php $adjustment = $scheduleItem->stockAdjustment(); @endphp
                                                <span class="badge bg-info" title="Synced to {{ $adjustment->adjustment_code }}">
                                                    <i class="fas fa-check-circle"></i> Synced
                                                </span>
                                            @elseif($scheduleItem->isApproved() && $scheduleItem->hasDiscrepancy())
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-clock"></i> Pending
                                                </span>
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($scheduleItem->needsReview())
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal{{ $scheduleItem->id }}" title="Approve">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $scheduleItem->id }}" title="Reject">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            @elseif($scheduleItem->isApproved() && $scheduleItem->hasDiscrepancy() && !$scheduleItem->isSynced())
                                                <form action="{{ route('admin.opname.items.sync-to-stock', $scheduleItem) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary btn-sm" title="Sync to Stock">
                                                        <i class="fas fa-sync"></i> Sync
                                                    </button>
                                                </form>
                                            @elseif($scheduleItem->isSynced())
                                                @php $adjustment = $scheduleItem->stockAdjustment(); @endphp
                                                <a href="{{ route('admin.adjustments.show', $adjustment) }}" class="btn btn-info btn-sm" title="View Adjustment" target="_blank">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            @elseif($scheduleItem->isReviewed())
                                                <small class="text-muted">
                                                    <i class="fas fa-user"></i> {{ $scheduleItem->reviewedByUser->name ?? 'N/A' }}
                                                    @if($scheduleItem->review_notes)
                                                        <br><i class="fas fa-comment"></i> {{ Str::limit($scheduleItem->review_notes, 20) }}
                                                    @endif
                                                </small>
                                            @else
                                                <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination Links --}}
                        @if($scheduleItems->hasPages())
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <small class="text-muted">
                                        Showing {{ $scheduleItems->firstItem() }} to {{ $scheduleItems->lastItem() }} of {{ $scheduleItems->total() }} items
                                    </small>
                                </div>
                                <div>
                                    {{ $scheduleItems->links() }}
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i> No items scheduled yet.
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <div class="col-lg-4">
            {{-- Actions --}}
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-tasks"></i> Actions</h6>
                </div>
                <div class="card-body">
                    @if($schedule->ticket_status !== 'closed')
                        <a href="{{ route('admin.opname.schedules.edit', $schedule) }}" class="btn btn-warning w-100 mb-2">
                            <i class="fas fa-edit"></i> Edit Schedule
                        </a>
                    @endif
                    <a href="{{ route('admin.opname.schedules.index') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

            {{-- Quick Stats --}}
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Quick Stats</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted">Total Items</small>
                        <h3 class="mb-0">{{ $stats['total_items'] }}</h3>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted">Completed Items</small>
                        <h3 class="mb-0 text-success">{{ $stats['completed_items'] }}</h3>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted">Pending Items</small>
                        <h3 class="mb-0 text-secondary">{{ $stats['pending_items'] }}</h3>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <small class="text-muted">Cancelled Items</small>
                        <h3 class="mb-0 {{ $stats['cancelled_items'] > 0 ? 'text-danger' : 'text-muted' }}">
                            {{ $stats['cancelled_items'] }}
                        </h3>
                    </div>
                    <div>
                        <small class="text-muted">Days Remaining</small>
                        <div class="mt-1">
                            @if($schedule->status === 'active')
                                @php
                                    $daysLeft = $stats['days_remaining'];
                                @endphp
                                @if($stats['is_overdue'])
                                    <span class="badge bg-danger">Overdue by {{ abs($daysLeft) }} days!</span>
                                @elseif($daysLeft == 0)
                                    <span class="badge bg-warning">Last day!</span>
                                @elseif($daysLeft <= 2)
                                    <span class="badge bg-warning">{{ $daysLeft }} days left</span>
                                @else
                                    <span class="text-muted">{{ $daysLeft }} days left</span>
                                @endif
                            @else
                                <span class="text-muted">{{ ucfirst($schedule->status) }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modals for Approve/Reject --}}
@foreach($scheduleItems as $item)
    @if($item->needsReview())
        {{-- Approve Modal --}}
        <div class="modal fade" id="approveModal{{ $item->id }}" tabindex="-1" data-bs-backdrop="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.opname.items.approve', $item) }}" method="POST" data-no-loading="true">
                        @csrf
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title"><i class="fas fa-check-circle"></i> Approve Discrepancy</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <strong>Item:</strong> {{ $item->getItemName() }}<br>
                                <strong>System Qty:</strong> {{ $item->system_quantity }}<br>
                                <strong>Physical Qty:</strong> {{ $item->physical_quantity }}<br>
                                <strong>Discrepancy:</strong> <span class="badge {{ $item->discrepancy_qty > 0 ? 'bg-success' : 'bg-danger' }}">
                                    {{ $item->discrepancy_qty > 0 ? '+' : '' }}{{ $item->discrepancy_qty }}
                                </span>
                            </div>

                            @if($item->notes)
                                <div class="mb-3">
                                    <strong>User Notes:</strong>
                                    <p class="text-muted">{{ $item->notes }}</p>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label">Review Notes (Optional)</label>
                                <textarea name="review_notes" class="form-control" rows="3" placeholder="Add notes about your approval..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check"></i> Approve Discrepancy
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Reject Modal --}}
        <div class="modal fade" id="rejectModal{{ $item->id }}" tabindex="-1" data-bs-backdrop="false">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.opname.items.reject', $item) }}" method="POST" data-no-loading="true">
                        @csrf
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title"><i class="fas fa-times-circle"></i> Reject Discrepancy</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                <strong><i class="fas fa-exclamation-triangle"></i> Warning:</strong>
                                Rejecting will reset this item to pending status. User will need to re-execute the opname.
                            </div>

                            <div class="alert alert-info">
                                <strong>Item:</strong> {{ $item->getItemName() }}<br>
                                <strong>System Qty:</strong> {{ $item->system_quantity }}<br>
                                <strong>Physical Qty:</strong> {{ $item->physical_quantity }}<br>
                                <strong>Discrepancy:</strong> <span class="badge {{ $item->discrepancy_qty > 0 ? 'bg-success' : 'bg-danger' }}">
                                    {{ $item->discrepancy_qty > 0 ? '+' : '' }}{{ $item->discrepancy_qty }}
                                </span>
                            </div>

                            @if($item->notes)
                                <div class="mb-3">
                                    <strong>User Notes:</strong>
                                    <p class="text-muted">{{ $item->notes }}</p>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label">Review Notes <span class="text-danger">*Required</span></label>
                                <textarea name="review_notes" class="form-control" rows="3" placeholder="Explain why you're rejecting this discrepancy..." required></textarea>
                                <small class="text-muted">Please provide a reason for rejection</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-times"></i> Reject & Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach

{{-- Batch Sync Modal --}}
<div class="modal fade" id="batchSyncModal" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-sync"></i> Batch Sync to Stock
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.opname.schedules.sync-to-stock', $schedule) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Important:</strong> This will create stock adjustments for all approved items with discrepancies and update actual inventory quantities.
                    </div>

                    <h6>Items to be synced:</h6>
                    <ul class="list-group mb-3" style="max-height: 300px; overflow-y: auto;">
                        @foreach($approvedItems->filter(fn($item) => !$item->isSynced()) as $item)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ $item->getItemName() }}</strong>
                                    <br><small class="text-muted">{{ $item->getItemCode() }} - {{ ucfirst($item->item_type) }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge {{ $item->discrepancy_qty > 0 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $item->discrepancy_qty > 0 ? '+' : '' }}{{ $item->discrepancy_qty }}
                                    </span>
                                    <br><small class="text-muted">{{ $item->system_quantity }} → {{ $item->physical_quantity }}</small>
                                </div>
                            </div>
                            <input type="hidden" name="item_ids[]" value="{{ $item->id }}">
                        </li>
                        @endforeach
                    </ul>

                    <p class="mb-0">
                        <strong>Total items:</strong> {{ $pendingSyncCount }}<br>
                        <strong>Action:</strong> Create stock adjustments and update inventory quantities
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sync"></i> Sync All Items
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Close Ticket Modal --}}
<div class="modal fade" id="closeTicketModal" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.opname.schedules.close-ticket', $schedule) }}" method="POST" data-no-loading="true">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle"></i> Close Ticket
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Confirm Close Ticket</strong>
                        <p class="mb-0 mt-2">
                            Are you sure you want to close this ticket? This will:
                        </p>
                        <ul class="mt-2 mb-0">
                            <li>Mark the schedule as completed</li>
                            <li>Move this ticket to Compliance Report</li>
                            <li>Calculate execution timing (early/ontime/late)</li>
                            <li>This action cannot be undone</li>
                        </ul>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-2 text-muted">Ticket Summary</h6>
                            <p class="mb-1"><strong>Ticket:</strong> {{ $schedule->schedule_code }}</p>
                            <p class="mb-1"><strong>Total Items:</strong> {{ $schedule->total_items }}</p>
                            <p class="mb-1"><strong>Completed:</strong> {{ $schedule->completed_items }}</p>
                            <p class="mb-1"><strong>Execution Date:</strong> {{ $schedule->execution_date->format('d M Y') }}</p>
                            <p class="mb-0"><strong>Today:</strong> {{ now()->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check-circle"></i> Close Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Force hide global loading when modals are shown
document.addEventListener('DOMContentLoaded', function() {
    // Get all modals on this page
    const modals = document.querySelectorAll('.modal');

    modals.forEach(function(modal) {
        // When modal is about to be shown
        modal.addEventListener('show.bs.modal', function() {
            // FORCE hide global loading overlay with inline styles
            const globalLoading = document.getElementById('globalLoading');
            if (globalLoading) {
                globalLoading.classList.remove('show');
                globalLoading.style.display = 'none';
                globalLoading.style.visibility = 'hidden';
                globalLoading.style.opacity = '0';
                globalLoading.style.zIndex = '-9999';
            }
        });

        // After modal is shown, ensure loading stays hidden
        modal.addEventListener('shown.bs.modal', function() {
            const globalLoading = document.getElementById('globalLoading');
            if (globalLoading) {
                globalLoading.classList.remove('show');
                globalLoading.style.display = 'none';
                globalLoading.style.visibility = 'hidden';
                globalLoading.style.opacity = '0';
                globalLoading.style.zIndex = '-9999';
            }
        });

        // When modal is hidden, ensure loading stays hidden
        modal.addEventListener('hidden.bs.modal', function() {
            const globalLoading = document.getElementById('globalLoading');
            if (globalLoading) {
                globalLoading.classList.remove('show');
                globalLoading.style.display = 'none';
            }
        });
    });
});

function handleExport(event) {
    // Stop propagation to prevent global loading overlay
    event.stopPropagation();
    event.stopImmediatePropagation();

    const btn = document.getElementById('exportBtn');
    const originalHtml = btn.innerHTML;

    // Show loading state on button only
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Exporting...';
    btn.classList.add('disabled');
    btn.style.pointerEvents = 'none';

    // Reset button after 2 seconds (cukup waktu untuk download dimulai)
    setTimeout(() => {
        btn.innerHTML = originalHtml;
        btn.classList.remove('disabled');
        btn.style.pointerEvents = 'auto';
    }, 2000);

    // Return true to allow default link behavior
    return true;
}
</script>
@endpush

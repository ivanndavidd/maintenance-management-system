@extends('layouts.admin')

@section('page-title', 'Sparepart Usage')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1"><i class="fas fa-history me-2"></i>Sparepart Usage</h5>
            <p class="text-muted mb-0">Record and track sparepart usage history</p>
        </div>
    </div>

    {{-- Search --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-12 col-md-5">
                    <input type="text" name="search" class="form-control" placeholder="Search by sparepart name or notes..." value="{{ request('search') }}">
                </div>
                <div class="col-12 col-md-4">
                    <select name="sparepart_id" class="form-select">
                        <option value="">All Spareparts</option>
                        @foreach($spareparts as $sp)
                            <option value="{{ $sp->id }}" {{ request('sparepart_id') == $sp->id ? 'selected' : '' }}>
                                {{ $sp->sparepart_name }} ({{ $sp->material_code ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i><span class="btn-text"> Filter</span></button>
                    <a href="{{ route($routePrefix . '.sparepart-usage.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i><span class="btn-text"> Reset</span></a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body p-0">
            @if($usages->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="d-none d-md-table-cell">Date</th>
                                <th class="d-none d-md-table-cell">Ticket / Task</th>
                                <th>Sparepart</th>
                                <th class="d-none d-lg-table-cell">Material Code</th>
                                <th class="text-center">Qty</th>
                                <th class="d-none d-lg-table-cell">Notes</th>
                                <th class="d-none d-md-table-cell">Recorded By</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($usages as $usage)
                            <tr>
                                <td class="d-none d-md-table-cell"><small>{{ $usage->used_at->format('d M Y') }}</small></td>
                                <td class="d-none d-md-table-cell">
                                    @if($usage->ticket_number && $usage->cmTicket)
                                        <a href="{{ route($routePrefix . '.corrective-maintenance.show', $usage->cmTicket) }}" class="badge bg-light text-primary border text-decoration-underline">
                                            {{ $usage->ticket_number }}
                                        </a>
                                    @elseif($usage->pm_report_id && $usage->pmReport?->task)
                                        @php
                                            $pmUrl = route($routePrefix . '.preventive-maintenance.reports') . '?open_report=' . $usage->pmReport->id;
                                        @endphp
                                        <a href="{{ $pmUrl }}" class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle text-decoration-none">PM</a>
                                        <small class="d-block mt-1" style="max-width:160px;">
                                            <a href="{{ $pmUrl }}" class="text-primary text-decoration-underline">
                                                {{ $usage->pmReport->task->task_name }}
                                            </a>
                                        </small>
                                    @else
                                        <small class="text-muted">-</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $usage->sparepart->sparepart_name ?? '-' }}</div>
                                    <small class="text-muted">{{ $usage->sparepart->equipment_type ?? '' }}</small>
                                    <div class="d-md-none"><small class="text-muted">{{ $usage->used_at->format('d M Y') }}</small></div>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    @if($usage->sparepart?->material_code)
                                        <span class="badge bg-light text-dark border">{{ $usage->sparepart->material_code }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark">
                                        {{ $usage->quantity_used }} {{ $usage->sparepart->unit ?? '' }}
                                    </span>
                                </td>
                                <td class="d-none d-lg-table-cell"><small class="text-muted">{{ $usage->notes ?? '-' }}</small></td>
                                <td class="d-none d-md-table-cell"><small>{{ $usage->usedByUser?->name ?? '-' }}</small></td>
                                <td class="text-center">
                                    @if(auth()->user()->isSuper())
                                    <form action="{{ route($routePrefix . '.sparepart-usage.destroy', $usage) }}" method="POST"
                                          onsubmit="return confirm('Delete this usage record? Stock will be restored.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete & restore stock">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($usages->hasPages())
                    <div class="p-3">{{ $usages->links() }}</div>
                @endif
            @else
                <div class="text-center text-muted py-5">
                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                    No usage records found.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

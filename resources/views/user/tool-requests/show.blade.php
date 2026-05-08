@extends('layouts.user')

@section('page-title', 'Tool Request Detail')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0">
                <i class="fas fa-clipboard-list"></i>
                {{ $toolRequest->request_number }}
            </h5>
            <small class="text-muted">Tool Usage Request Detail</small>
        </div>
        <a href="{{ route('user.tool-requests.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i><span class="btn-text"> Back</span>
        </a>
    </div>

    <div class="row">
        {{-- Detail Card --}}
        <div class="col-12 col-md-8 order-2 order-md-1">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Request Information</h6>
                    <span class="badge bg-{{ $toolRequest->getStatusBadgeClass() }}">
                        {{ $toolRequest->getStatusLabel() }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <small class="text-muted d-block">Tool</small>
                            <span class="fw-bold fs-6">{{ $toolRequest->tool->sparepart_name ?? '-' }}</span>
                            @if($toolRequest->tool->material_code)
                                <small class="text-muted ms-2">{{ $toolRequest->tool->material_code }}</small>
                            @endif
                        </div>
                        <div class="col-6 col-md-4">
                            <small class="text-muted d-block">Qty Requested</small>
                            <span class="fw-bold text-primary">{{ $toolRequest->quantity_requested }} {{ $toolRequest->tool->unit ?? '' }}</span>
                        </div>
                        <div class="col-6 col-md-4">
                            <small class="text-muted d-block">Usage Date</small>
                            <span>{{ $toolRequest->usage_date->format('d M Y') }}</span>
                        </div>
                        <div class="col-6 col-md-4">
                            <small class="text-muted d-block">Return Date</small>
                            <span>{{ $toolRequest->return_date ? $toolRequest->return_date->format('d M Y') : '-' }}</span>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Purpose</small>
                            <span>{{ $toolRequest->purpose }}</span>
                        </div>
                        @if($toolRequest->location)
                        <div class="col-12">
                            <small class="text-muted d-block">Work Location</small>
                            <span>{{ $toolRequest->location }}</span>
                        </div>
                        @endif
                        @if($toolRequest->notes)
                        <div class="col-12">
                            <small class="text-muted d-block">Notes</small>
                            <span>{{ $toolRequest->notes }}</span>
                        </div>
                        @endif
                        <div class="col-6 col-md-4">
                            <small class="text-muted d-block">Submitted</small>
                            <span>{{ $toolRequest->created_at->format('d M Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Review Info --}}
            @if($toolRequest->reviewed_at)
            <div class="card mb-3 border-{{ $toolRequest->status === 'approved' ? 'success' : 'danger' }}">
                <div class="card-header bg-{{ $toolRequest->status === 'approved' ? 'success' : 'danger' }} text-white">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-{{ $toolRequest->status === 'approved' ? 'check-circle' : 'times-circle' }}"></i>
                        Review Result
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <small class="text-muted d-block">Reviewed By</small>
                            <span>{{ $toolRequest->reviewer->name ?? '-' }}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Reviewed At</small>
                            <span>{{ $toolRequest->reviewed_at->format('d M Y H:i') }}</span>
                        </div>
                        @if($toolRequest->review_notes)
                        <div class="col-12">
                            <small class="text-muted d-block">Review Notes</small>
                            <span>{{ $toolRequest->review_notes }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Return Info --}}
            @if($toolRequest->returned_at)
            <div class="card mb-3 border-secondary">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-undo"></i> Return Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-12">
                            <small class="text-muted d-block">Returned At</small>
                            <span>{{ $toolRequest->returned_at->format('d M Y H:i') }}</span>
                        </div>
                        @if($toolRequest->return_notes)
                        <div class="col-12">
                            <small class="text-muted d-block">Return Notes</small>
                            <span>{{ $toolRequest->return_notes }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Actions sidebar --}}
        <div class="col-12 col-md-4 order-1 order-md-2">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">Actions</h6>
                </div>
                <div class="card-body d-grid gap-2">

                    {{-- Non-consumable: Mark as Returned --}}
                    @if($toolRequest->canBeMarkedReturned())
                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#returnModal">
                        <i class="fas fa-undo"></i> Mark as Returned
                    </button>
                    @endif

                    {{-- Cancel --}}
                    @if($toolRequest->canBeCancelledBy(auth()->id()))
                    <form action="{{ route('user.tool-requests.cancel', $toolRequest) }}" method="POST"
                          onsubmit="return confirm('Cancel this request?')">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                            <i class="fas fa-times"></i> Cancel Request
                        </button>
                    </form>
                    @endif

                    <a href="{{ route('user.tool-requests.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

            {{-- Status timeline --}}
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">Status</h6>
                </div>
                <div class="card-body p-3">
                    @php
                        $steps = [
                            ['status' => 'pending',  'label' => 'Submitted', 'icon' => 'paper-plane'],
                            ['status' => 'in_use',   'label' => 'Approved & In Use', 'icon' => 'tools'],
                            ['status' => 'returned', 'label' => 'Returned',  'icon' => 'undo'],
                        ];
                        $order = ['pending'=>0,'approved'=>1,'in_use'=>1,'returned'=>2];
                        $currentOrder = $order[$toolRequest->status] ?? -1;
                        if (in_array($toolRequest->status, ['rejected','cancelled'])) $currentOrder = -1;
                    @endphp

                    @if(in_array($toolRequest->status, ['rejected', 'cancelled']))
                        <div class="text-center text-muted py-2">
                            <span class="badge bg-{{ $toolRequest->getStatusBadgeClass() }} fs-6">
                                {{ $toolRequest->getStatusLabel() }}
                            </span>
                        </div>
                    @else
                        @foreach($steps as $step)
                        @php $stepOrder = $order[$step['status']] ?? 0; @endphp
                        <div class="d-flex align-items-center mb-2">
                            <div class="me-3 text-center" style="width:28px;">
                                @if($stepOrder <= $currentOrder)
                                    <i class="fas fa-{{ $step['icon'] }} text-success"></i>
                                @else
                                    <i class="fas fa-{{ $step['icon'] }} text-muted"></i>
                                @endif
                            </div>
                            <span class="{{ $stepOrder <= $currentOrder ? 'fw-bold' : 'text-muted' }}" style="font-size:13px;">
                                {{ $step['label'] }}
                            </span>
                            @if($toolRequest->status === $step['status'])
                                <span class="badge bg-{{ $toolRequest->getStatusBadgeClass() }} ms-2" style="font-size:10px;">Now</span>
                            @endif
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Return Modal --}}
@if($toolRequest->canBeMarkedReturned())
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('user.tool-requests.returned', $toolRequest) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title fw-bold"><i class="fas fa-undo"></i> Mark Tool as Returned</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Confirm that you have returned <strong>{{ $toolRequest->tool->sparepart_name }}</strong> ({{ $toolRequest->quantity_requested }} {{ $toolRequest->tool->unit }}) to the warehouse.</p>
                    <div class="mb-3">
                        <label class="form-label">Return Notes <small class="text-muted">(optional)</small></label>
                        <textarea name="return_notes" class="form-control" rows="3"
                                  placeholder="Condition of tool when returned, any issues, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-check"></i> Confirm Return
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

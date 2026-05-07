@extends('layouts.user')

@section('page-title', 'New Tool Request')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0"><i class="fas fa-plus-circle"></i> New Tool Usage Request</h5>
            <small class="text-muted">Fill in details for tool borrowing request</small>
        </div>
        <a href="{{ route('user.tool-requests.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i><span class="btn-text"> Back</span>
        </a>
    </div>

    <div class="row">
        <div class="col-12 col-md-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0 fw-bold">Request Details</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('user.tool-requests.store') }}" method="POST">
                        @csrf

                        {{-- Tool Selection --}}
                        <div class="mb-3">
                            <label class="form-label">Tool <span class="text-danger">*</span></label>
                            <select name="tool_id" id="tool_id"
                                    class="form-select @error('tool_id') is-invalid @enderror" required>
                                <option value="">-- Select Tool --</option>
                                @php $currentType = '' @endphp
                                @foreach($tools as $tool)
                                    @if($tool->equipment_type !== $currentType)
                                        @if($currentType !== '') </optgroup> @endif
                                        <optgroup label="{{ $tool->equipment_type ?? 'Other' }}">
                                        @php $currentType = $tool->equipment_type @endphp
                                    @endif
                                    <option value="{{ $tool->id }}"
                                            data-qty="{{ $tool->quantity }}"
                                            data-unit="{{ $tool->unit }}"
                                            data-type="{{ strtolower($tool->equipment_type) }}"
                                            {{ old('tool_id') == $tool->id ? 'selected' : '' }}>
                                        {{ $tool->sparepart_name }}
                                        @if($tool->material_code) ({{ $tool->material_code }}) @endif
                                        — Stock: {{ $tool->quantity }} {{ $tool->unit }}
                                    </option>
                                @endforeach
                                @if($currentType !== '') </optgroup> @endif
                            </select>
                            @error('tool_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="stockInfo" class="form-text d-none">
                                <i class="fas fa-info-circle"></i>
                                Available stock: <strong id="stockQty"></strong>
                            </div>
                            {{-- Consumable notice --}}
                            <div id="consumableNotice" class="alert alert-info py-2 mt-2 d-none" style="font-size:13px;">
                                <i class="fas fa-flask me-1"></i>
                                <strong>Consumable item</strong> — stock will be deducted immediately upon approval. No return required.
                            </div>
                            {{-- Non-consumable notice --}}
                            <div id="borrowNotice" class="alert alert-primary py-2 mt-2 d-none" style="font-size:13px;">
                                <i class="fas fa-tools me-1"></i>
                                <strong>Borrowing item</strong> — you must return this tool and mark it as returned after use.
                            </div>
                        </div>

                        {{-- Quantity --}}
                        <div class="mb-3">
                            <label class="form-label">Quantity Requested <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="quantity_requested" id="quantity_requested"
                                       class="form-control @error('quantity_requested') is-invalid @enderror"
                                       value="{{ old('quantity_requested', 1) }}" min="1" required>
                                <span class="input-group-text" id="unitLabel">unit</span>
                            </div>
                            @error('quantity_requested')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Dates --}}
                        <div class="row g-2 mb-3">
                            <div class="col-12 col-sm-6">
                                <label class="form-label">Usage Date <span class="text-danger">*</span></label>
                                <input type="date" name="usage_date"
                                       class="form-control @error('usage_date') is-invalid @enderror"
                                       value="{{ old('usage_date', now()->format('Y-m-d')) }}"
                                       min="{{ now()->format('Y-m-d') }}" required>
                                @error('usage_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12 col-sm-6" id="returnDateWrapper">
                                <label class="form-label">Estimated Return Date</label>
                                <input type="date" name="return_date" id="return_date"
                                       class="form-control @error('return_date') is-invalid @enderror"
                                       value="{{ old('return_date') }}">
                                <small class="text-muted">Optional</small>
                                @error('return_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Purpose --}}
                        <div class="mb-3">
                            <label class="form-label">Purpose / Reason <span class="text-danger">*</span></label>
                            <input type="text" name="purpose"
                                   class="form-control @error('purpose') is-invalid @enderror"
                                   value="{{ old('purpose') }}"
                                   placeholder="e.g., Belt conveyor repair at zone A"
                                   maxlength="500" required>
                            @error('purpose')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Location --}}
                        <div class="mb-3">
                            <label class="form-label">Work Location</label>
                            <input type="text" name="location"
                                   class="form-control @error('location') is-invalid @enderror"
                                   value="{{ old('location') }}"
                                   placeholder="e.g., Conveyor Zone B, 2nd Floor"
                                   maxlength="255">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Notes --}}
                        <div class="mb-4">
                            <label class="form-label">Additional Notes</label>
                            <textarea name="notes"
                                      class="form-control @error('notes') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Any additional information..."
                                      maxlength="1000">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-sm-flex">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit Request
                            </button>
                            <a href="{{ route('user.tool-requests.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Info sidebar --}}
        <div class="col-12 col-md-4 mt-3 mt-md-0">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> How It Works</h6>
                </div>
                <div class="card-body">
                    <ol class="small mb-0 ps-3">
                        <li class="mb-2">Fill in this form and submit your request.</li>
                        <li class="mb-2">Supervisor / Admin will review and <strong>approve</strong> or <strong>reject</strong> your request.</li>
                        <li class="mb-2">If approved, you can take the tool from the warehouse.</li>
                        <li class="mb-2">After use, mark the tool as <strong>returned</strong> from the request detail page.</li>
                    </ol>
                </div>
            </div>

            <div class="card mt-3 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Note</h6>
                </div>
                <div class="card-body small">
                    <ul class="mb-0 ps-3">
                        <li>Only tools with available stock can be requested.</li>
                        <li>Requests can only be cancelled while still <strong>pending</strong>.</li>
                        <li>Always return tools after use and mark as returned in the system.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('tool_id').addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const qty = opt.dataset.qty ?? 0;
    const unit = opt.dataset.unit ?? 'unit';
    const type = opt.dataset.type ?? '';
    const isConsumable = type === 'consumable';

    const info            = document.getElementById('stockInfo');
    const qtyEl           = document.getElementById('stockQty');
    const unitLabel       = document.getElementById('unitLabel');
    const returnWrapper   = document.getElementById('returnDateWrapper');
    const returnInput     = document.getElementById('return_date');
    const consumableNotice = document.getElementById('consumableNotice');
    const borrowNotice    = document.getElementById('borrowNotice');

    if (this.value) {
        qtyEl.textContent = qty + ' ' + unit;
        info.classList.remove('d-none');
        unitLabel.textContent = unit;
        document.getElementById('quantity_requested').max = qty;

        if (isConsumable) {
            returnWrapper.classList.add('d-none');
            returnInput.value = '';
            consumableNotice.classList.remove('d-none');
            borrowNotice.classList.add('d-none');
        } else {
            returnWrapper.classList.remove('d-none');
            consumableNotice.classList.add('d-none');
            borrowNotice.classList.remove('d-none');
        }
    } else {
        info.classList.add('d-none');
        unitLabel.textContent = 'unit';
        document.getElementById('quantity_requested').removeAttribute('max');
        returnWrapper.classList.remove('d-none');
        consumableNotice.classList.add('d-none');
        borrowNotice.classList.add('d-none');
    }
});

document.getElementById('tool_id').dispatchEvent(new Event('change'));
</script>
@endpush

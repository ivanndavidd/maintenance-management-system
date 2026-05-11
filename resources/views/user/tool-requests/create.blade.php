@extends($routePrefix === 'user' ? 'layouts.user' : 'layouts.admin')

@section('page-title', 'New Tool Request')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<style>
    .choices__inner { min-height: 38px; padding: 5px 12px; font-size: 14px; }
    .choices__list--dropdown .choices__item--selectable { padding: 9px 12px; font-size: 13px; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="mb-0"><i class="fas fa-plus-circle"></i> New Tool Usage Request</h5>
            <small class="text-muted">Fill in details for tool borrowing request</small>
        </div>
        <a href="{{ route($routePrefix.'.tool-requests.index') }}" class="btn btn-secondary btn-sm">
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
                    <form action="{{ route($routePrefix.'.tool-requests.store') }}" method="POST">
                        @csrf

                        {{-- Tool Selection --}}
                        <div class="mb-3">
                            <label class="form-label">Tool <span class="text-danger">*</span></label>
                            <select name="tool_id" id="tool_id"
                                    class="form-select @error('tool_id') is-invalid @enderror" required>
                                <option value="">-- Select Tool --</option>
                                @foreach($tools as $tool)
                                    <option value="{{ $tool->id }}"
                                            data-qty="{{ $tool->quantity }}"
                                            data-unit="{{ $tool->unit }}"
                                            {{ old('tool_id') == $tool->id ? 'selected' : '' }}>
                                        {{ $tool->sparepart_name }}
                                        @if($tool->material_code) ({{ $tool->material_code }}) @endif
                                        — Stock: {{ $tool->quantity }} {{ $tool->unit }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tool_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="stockInfo" class="form-text d-none">
                                <i class="fas fa-info-circle"></i>
                                Available stock: <strong id="stockQty"></strong>
                            </div>
                            <div id="borrowNotice" class="alert alert-primary py-2 mt-2 d-none" style="font-size:13px;">
                                <i class="fas fa-tools me-1"></i>
                                <strong>Borrowing</strong> — please return this tool after use and mark it as returned in the system.
                            </div>
                        </div>

                        {{-- Quantity --}}
                        <div class="mb-3">
                            <label class="form-label">Quantity Requested <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2 align-items-start">
                                <div class="flex-grow-1">
                                    <input type="number" name="quantity_requested" id="quantity_requested"
                                           class="form-control @error('quantity_requested') is-invalid @enderror"
                                           value="{{ old('quantity_requested', 1) }}" min="1" required>
                                    @error('quantity_requested')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <span class="input-group-text" id="unitLabel" style="white-space:nowrap;">unit</span>
                            </div>
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
                            <a href="{{ route($routePrefix.'.tool-requests.index') }}" class="btn btn-secondary">
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
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
// Tool data from server (for stock/unit lookup after Choices.js replaces the DOM select)
const toolsData = @json($tools->map(fn($t) => [
    'id'   => $t->id,
    'qty'  => $t->quantity,
    'unit' => $t->unit,
])->keyBy('id'));

document.addEventListener('DOMContentLoaded', function() {
    const selectEl = document.getElementById('tool_id');

    new Choices(selectEl, {
        searchEnabled:        true,
        searchPlaceholderValue: 'Ketik untuk cari tool...',
        itemSelectText:       '',
        shouldSort:           false,
        placeholder:          true,
        placeholderValue:     '-- Select Tool --',
        removeItemButton:     false,
    });

    const info         = document.getElementById('stockInfo');
    const qtyEl        = document.getElementById('stockQty');
    const unitLabel    = document.getElementById('unitLabel');
    const borrowNotice = document.getElementById('borrowNotice');
    const qtyInput     = document.getElementById('quantity_requested');

    // Qty error element
    const qtyError = document.createElement('div');
    qtyError.className = 'invalid-feedback d-none';
    qtyError.id = 'qtyExceedError';
    qtyInput.parentElement.classList.add('has-validation');
    qtyInput.parentElement.appendChild(qtyError);

    function onToolChange() {
        const val  = selectEl.value;
        const tool = val ? toolsData[val] : null;

        if (tool) {
            qtyEl.textContent = tool.qty + ' ' + tool.unit;
            info.classList.remove('d-none');
            unitLabel.textContent = tool.unit;
            qtyInput.max = tool.qty;
            borrowNotice.classList.remove('d-none');
        } else {
            info.classList.add('d-none');
            unitLabel.textContent = 'unit';
            qtyInput.removeAttribute('max');
            borrowNotice.classList.add('d-none');
        }
        validateQty();
    }

    function validateQty() {
        const val  = selectEl.value;
        const tool = val ? toolsData[val] : null;
        const max  = tool ? parseInt(tool.qty) : NaN;
        const entered = parseInt(qtyInput.value);
        const unit = tool ? tool.unit : 'unit';

        if (val && !isNaN(max) && !isNaN(entered) && entered > max) {
            qtyInput.classList.add('is-invalid');
            qtyError.textContent = 'Cannot exceed available stock: ' + max + ' ' + unit;
            qtyError.classList.remove('d-none');
            return false;
        }
        qtyInput.classList.remove('is-invalid');
        qtyError.classList.add('d-none');
        return true;
    }

    selectEl.addEventListener('change', onToolChange);
    qtyInput.addEventListener('input', validateQty);

    document.querySelector('form').addEventListener('submit', function(e) {
        if (!validateQty()) e.preventDefault();
    });

    // Trigger for old() value restore
    if (selectEl.value) onToolChange();
});
</script>
@endpush

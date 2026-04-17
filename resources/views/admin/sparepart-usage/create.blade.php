@extends('layouts.admin')

@section('page-title', 'Record Sparepart Usage')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-history me-2"></i>Record Sparepart Usage</h4>
            <p class="text-muted mb-0">Deduct sparepart stock by recording usage</p>
        </div>
        <a href="{{ route($routePrefix . '.sparepart-usage.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card" style="max-width: 640px;">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route($routePrefix . '.sparepart-usage.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Sparepart <span class="text-danger">*</span></label>
                    <select name="sparepart_id" class="form-select" required id="sparepartSelect">
                        <option value="">-- Select Sparepart --</option>
                        @foreach($spareparts as $sp)
                            <option value="{{ $sp->id }}"
                                data-unit="{{ $sp->unit }}"
                                data-stock="{{ $sp->quantity }}"
                                {{ old('sparepart_id') == $sp->id ? 'selected' : '' }}>
                                {{ $sp->sparepart_name }}
                                @if($sp->material_code) ({{ $sp->material_code }}) @endif
                                — Stock: {{ $sp->quantity }} {{ $sp->unit }}
                            </option>
                        @endforeach
                    </select>
                    <div id="stockInfo" class="form-text text-muted mt-1"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Quantity Used <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" name="quantity_used" id="quantityInput"
                               class="form-control" min="1" required
                               value="{{ old('quantity_used') }}"
                               placeholder="Enter quantity">
                        <span class="input-group-text" id="unitLabel">-</span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Date Used <span class="text-danger">*</span></label>
                    <input type="date" name="used_at" class="form-control"
                           value="{{ old('used_at', date('Y-m-d')) }}" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Notes</label>
                    <textarea name="notes" class="form-control" rows="3"
                              placeholder="Optional notes about this usage..."
                              maxlength="500">{{ old('notes') }}</textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Record Usage
                    </button>
                    <a href="{{ route($routePrefix . '.sparepart-usage.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const select = document.getElementById('sparepartSelect');
const unitLabel = document.getElementById('unitLabel');
const stockInfo = document.getElementById('stockInfo');
const qtyInput = document.getElementById('quantityInput');

function updateUnitAndStock() {
    const opt = select.options[select.selectedIndex];
    const unit = opt.dataset.unit || '-';
    const stock = opt.dataset.stock;
    unitLabel.textContent = unit;
    if (stock !== undefined && opt.value) {
        stockInfo.textContent = 'Current stock: ' + stock + ' ' + unit;
        qtyInput.max = stock;
    } else {
        stockInfo.textContent = '';
        qtyInput.removeAttribute('max');
    }
}

select.addEventListener('change', updateUnitAndStock);
updateUnitAndStock();
</script>
@endpush
@endsection

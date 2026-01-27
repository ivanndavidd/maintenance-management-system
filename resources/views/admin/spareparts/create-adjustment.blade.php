@extends('layouts.admin')

@section('page-title', 'Create Stock Adjustment - Spareparts')

@push('styles')
<!-- Choices.js CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<style>
.choices__inner {
    min-height: 38px;
    padding: 6px 12px;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
}
.choices__list--dropdown .choices__item--selectable {
    padding: 10px;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Create Stock Adjustment</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.spareparts.index') }}">Spareparts</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.spareparts.adjustments') }}">Stock Adjustments</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-plus-circle"></i> New Stock Adjustment</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.spareparts.adjustments.store') }}" method="POST">
                        @csrf

                        {{-- Sparepart Selection --}}
                        <div class="mb-3">
                            <label for="item_id" class="form-label">Sparepart <span class="text-danger">*</span></label>
                            <select name="item_id" id="item_id" class="form-select @error('item_id') is-invalid @enderror" required {{ isset($selectedSparepart) ? 'disabled' : '' }}>
                                <option value="">-- Select Sparepart --</option>
                                @foreach($spareparts as $sparepart)
                                <option value="{{ $sparepart->id }}"
                                    data-current-stock="{{ $sparepart->quantity }}"
                                    data-unit="{{ $sparepart->unit }}"
                                    data-min-stock="{{ $sparepart->minimum_stock }}"
                                    {{ (isset($selectedSparepart) && $selectedSparepart->id == $sparepart->id) || old('item_id') == $sparepart->id ? 'selected' : '' }}>
                                    {{ $sparepart->sparepart_name }} ({{ $sparepart->sparepart_id }}) - Current: {{ $sparepart->quantity }} {{ $sparepart->unit }}
                                </option>
                                @endforeach
                            </select>
                            @if(isset($selectedSparepart))
                                <input type="hidden" name="item_id" value="{{ $selectedSparepart->id }}">
                                <small class="text-muted">
                                    <i class="fas fa-lock"></i> Pre-selected from: <strong>{{ $selectedSparepart->sparepart_name }}</strong>
                                </small>
                            @endif
                            @error('item_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Current Stock Display --}}
                        <div id="current-stock-display" class="mb-3" style="display: none;">
                            <div class="alert alert-info">
                                <strong><i class="fas fa-info-circle"></i> Current Stock:</strong>
                                <span id="current-stock-value" class="badge bg-primary fs-6 ms-2">0</span>
                                <span id="current-stock-unit"></span>
                                <div class="mt-2 small" id="min-stock-warning" style="display: none;">
                                    <i class="fas fa-exclamation-triangle text-warning"></i> Minimum Stock: <span id="min-stock-value"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Adjustment Type --}}
                        <div class="mb-3">
                            <label for="adjustment_type" class="form-label">Adjustment Type <span class="text-danger">*</span></label>
                            <select name="adjustment_type" id="adjustment_type" class="form-select @error('adjustment_type') is-invalid @enderror" required>
                                <option value="">-- Select Type --</option>
                                <option value="add" {{ old('adjustment_type') == 'add' ? 'selected' : '' }}>
                                    Add Stock (Increase)
                                </option>
                                <option value="subtract" {{ old('adjustment_type') == 'subtract' ? 'selected' : '' }}>
                                    Subtract Stock (Decrease)
                                </option>
                                <option value="correction" {{ old('adjustment_type') == 'correction' ? 'selected' : '' }}>
                                    Correction (Manual Adjustment)
                                </option>
                            </select>
                            @error('adjustment_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Quantity --}}
                        <div class="mb-3">
                            <label for="adjustment_qty" class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="adjustment_qty" id="adjustment_qty" class="form-control @error('adjustment_qty') is-invalid @enderror"
                                value="{{ old('adjustment_qty') }}" required step="1">
                            @error('adjustment_qty')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted" id="qty-hint">Enter quantity amount (sign will be added automatically based on type)</small>
                        </div>

                        {{-- New Stock Preview --}}
                        <div id="new-stock-preview" class="mb-3" style="display: none;">
                            <div class="alert alert-success">
                                <strong><i class="fas fa-calculator"></i> New Stock After Adjustment:</strong>
                                <div class="mt-2">
                                    <span id="calculation-display"></span>
                                    <br>
                                    <span class="badge bg-success fs-6 mt-2" id="new-stock-value">0</span>
                                    <span id="new-stock-unit"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Reason Category --}}
                        <div class="mb-3">
                            <label for="reason_category" class="form-label">Reason Category <span class="text-danger">*</span></label>
                            <select name="reason_category" id="reason_category" class="form-select @error('reason_category') is-invalid @enderror" required>
                                <option value="">-- Select Category --</option>
                                <option value="damage" {{ old('reason_category') == 'damage' ? 'selected' : '' }}>Damage/Broken Items</option>
                                <option value="loss" {{ old('reason_category') == 'loss' ? 'selected' : '' }}>Loss/Missing Items</option>
                                <option value="found" {{ old('reason_category') == 'found' ? 'selected' : '' }}>Found Items (Stock Count)</option>
                                <option value="correction" {{ old('reason_category') == 'correction' ? 'selected' : '' }}>Manual Correction</option>
                                <option value="opname_result" {{ old('reason_category') == 'opname_result' ? 'selected' : '' }}>Stock Opname Result</option>
                                <option value="other" {{ old('reason_category') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('reason_category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Detailed Reason --}}
                        <div class="mb-3">
                            <label for="reason" class="form-label">Detailed Reason <span class="text-danger">*</span></label>
                            <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror"
                                rows="4" required placeholder="Please provide detailed explanation for this adjustment...">{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Provide clear details about why this adjustment is needed</small>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Adjustment
                            </button>
                            <a href="{{ route('admin.spareparts.adjustments') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Information</h6>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">Stock Adjustment Guidelines:</h6>
                    <ul class="small">
                        <li><strong>Add:</strong> Use positive number (+) to add items (found items, corrections for undercount)</li>
                        <li><strong>Subtract:</strong> Use negative number (-) to remove items (damaged, lost, expired items, corrections for overcount)</li>
                        <li><strong>Correction:</strong> Use for manual adjustments based on physical count</li>
                        <li>Always provide detailed reason explaining the adjustment</li>
                        <li>All adjustments are logged and tracked for audit purposes</li>
                    </ul>

                    <hr>

                    <h6 class="fw-bold">Common Scenarios:</h6>
                    <div class="small">
                        <strong>Damaged Items:</strong> Type: Subtract, Category: Damage, use negative qty<br>
                        <strong>Found Items:</strong> Type: Add, Category: Found, use positive qty<br>
                        <strong>Stock Opname:</strong> Type: Correction, Category: Opname Result
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- Choices.js -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script>
// Initialize Choices.js for searchable dropdown
document.addEventListener('DOMContentLoaded', function() {
    const itemSelect = document.getElementById('item_id');

    // Initialize Choices.js with search enabled
    const choices = new Choices(itemSelect, {
        searchEnabled: true,
        searchPlaceholderValue: 'Type to search...',
        itemSelectText: 'Click to select',
        shouldSort: false,
        placeholder: true,
        placeholderValue: '-- Select Sparepart --',
        removeItemButton: true
    });

    const adjustmentTypeSelect = document.getElementById('adjustment_type');
    const adjustmentQtyInput = document.getElementById('adjustment_qty');
    const currentStockDisplay = document.getElementById('current-stock-display');
    const newStockPreview = document.getElementById('new-stock-preview');
    const qtyHint = document.getElementById('qty-hint');

    // Auto-adjust quantity sign based on adjustment type
    function adjustQuantitySign() {
        const adjustmentType = adjustmentTypeSelect.value;
        const currentValue = parseFloat(adjustmentQtyInput.value) || 0;
        const absoluteValue = Math.abs(currentValue);

        if (adjustmentType === 'subtract') {
            // For subtract, always make it negative
            if (currentValue >= 0) {
                adjustmentQtyInput.value = -absoluteValue;
            }
            qtyHint.textContent = 'Subtract mode: value will be automatically negative';
            qtyHint.className = 'text-danger';
        } else if (adjustmentType === 'add') {
            // For add, always make it positive
            if (currentValue < 0) {
                adjustmentQtyInput.value = absoluteValue;
            }
            qtyHint.textContent = 'Add mode: value will be automatically positive';
            qtyHint.className = 'text-success';
        } else {
            // For correction, allow both positive and negative
            qtyHint.textContent = 'Correction mode: use + for increase, - for decrease';
            qtyHint.className = 'text-muted';
        }

        updateStockPreview();
    }

    // Handle quantity input to auto-convert based on type
    function handleQuantityInput() {
        const adjustmentType = adjustmentTypeSelect.value;
        const currentValue = parseFloat(adjustmentQtyInput.value) || 0;
        const absoluteValue = Math.abs(currentValue);

        if (adjustmentType === 'subtract' && currentValue > 0) {
            adjustmentQtyInput.value = -absoluteValue;
        } else if (adjustmentType === 'add' && currentValue < 0) {
            adjustmentQtyInput.value = absoluteValue;
        }

        updateStockPreview();
    }

    // Update stock preview when any input changes
    function updateStockPreview() {
        const selectedOption = itemSelect.options[itemSelect.selectedIndex];

        if (!itemSelect.value) {
            currentStockDisplay.style.display = 'none';
            newStockPreview.style.display = 'none';
            return;
        }

        // Show current stock
        const currentStock = parseFloat(selectedOption.dataset.currentStock) || 0;
        const unit = selectedOption.dataset.unit || '';
        const minStock = parseFloat(selectedOption.dataset.minStock) || 0;

        currentStockDisplay.style.display = 'block';
        document.getElementById('current-stock-value').textContent = currentStock;
        document.getElementById('current-stock-unit').textContent = unit;

        // Show min stock warning if applicable
        if (minStock > 0) {
            document.getElementById('min-stock-warning').style.display = 'block';
            document.getElementById('min-stock-value').textContent = minStock + ' ' + unit;
        } else {
            document.getElementById('min-stock-warning').style.display = 'none';
        }

        // Calculate new stock if all fields are filled
        if (adjustmentQtyInput.value) {
            const adjustmentQty = parseFloat(adjustmentQtyInput.value) || 0;
            const newStock = currentStock + adjustmentQty;
            let calculation = `${currentStock} + (${adjustmentQty}) = <strong>${newStock}</strong> ${unit}`;

            // Show preview
            newStockPreview.style.display = 'block';
            document.getElementById('calculation-display').innerHTML = calculation;
            document.getElementById('new-stock-value').textContent = newStock;
            document.getElementById('new-stock-unit').textContent = unit;

            // Change color based on result
            const badge = document.getElementById('new-stock-value');
            if (newStock < 0) {
                badge.className = 'badge bg-danger fs-6 mt-2';
            } else if (newStock < minStock) {
                badge.className = 'badge bg-warning fs-6 mt-2';
            } else {
                badge.className = 'badge bg-success fs-6 mt-2';
            }

            // Validate for negative stock
            if (newStock < 0) {
                adjustmentQtyInput.setCustomValidity('This adjustment would result in negative stock');
            } else {
                adjustmentQtyInput.setCustomValidity('');
            }
        } else {
            newStockPreview.style.display = 'none';
        }
    }

    // Add event listeners
    // Choices.js change event
    itemSelect.addEventListener('change', function() {
        updateStockPreview();
    });

    adjustmentTypeSelect.addEventListener('change', adjustQuantitySign);
    adjustmentQtyInput.addEventListener('input', handleQuantityInput);

    // Restore preview if there are old values (validation failed)
    if (itemSelect.value) {
        updateStockPreview();
    }
    if (adjustmentTypeSelect.value) {
        adjustQuantitySign();
    }
});
</script>
@endpush
@endsection

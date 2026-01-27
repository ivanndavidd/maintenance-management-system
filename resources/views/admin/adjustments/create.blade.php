@extends('layouts.admin')

@section('page-title', 'Create Stock Adjustment')

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
                <li class="breadcrumb-item"><a href="{{ route('admin.adjustments.index') }}">Stock Adjustments</a></li>
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
                    <form action="{{ route('admin.adjustments.store') }}" method="POST">
                        @csrf

                        {{-- Item Type Selection --}}
                        <div class="mb-3">
                            <label for="item_type" class="form-label">Item Type <span class="text-danger">*</span></label>
                            <select name="item_type" id="item_type" class="form-select @error('item_type') is-invalid @enderror" required>
                                <option value="">-- Select Item Type --</option>
                                <option value="sparepart" {{ old('item_type') == 'sparepart' ? 'selected' : '' }}>Sparepart</option>
                                <option value="tool" {{ old('item_type') == 'tool' ? 'selected' : '' }}>Tool</option>
                            </select>
                            @error('item_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Sparepart Selection --}}
                        <div class="mb-3" id="sparepart-select-group" style="display: none;">
                            <label for="sparepart_id" class="form-label">Sparepart <span class="text-danger">*</span></label>
                            <select name="sparepart_id" id="sparepart_id" class="form-select @error('item_id') is-invalid @enderror">
                                <option value="">-- Select Sparepart --</option>
                                @foreach($spareparts as $sparepart)
                                <option value="{{ $sparepart->id }}"
                                    data-current-stock="{{ $sparepart->quantity }}"
                                    data-unit="{{ $sparepart->unit }}"
                                    data-min-stock="{{ $sparepart->minimum_stock }}"
                                    data-price="{{ $sparepart->parts_price }}"
                                    {{ old('item_id') == $sparepart->id && old('item_type') == 'sparepart' ? 'selected' : '' }}>
                                    {{ $sparepart->sparepart_name }} ({{ $sparepart->sparepart_id }}) - Stock: {{ $sparepart->quantity }} {{ $sparepart->unit }}
                                </option>
                                @endforeach
                            </select>
                            @error('item_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tool Selection --}}
                        <div class="mb-3" id="tool-select-group" style="display: none;">
                            <label for="tool_id" class="form-label">Tool <span class="text-danger">*</span></label>
                            <select name="tool_id" id="tool_id" class="form-select @error('item_id') is-invalid @enderror">
                                <option value="">-- Select Tool --</option>
                                @foreach($tools as $tool)
                                <option value="{{ $tool->id }}"
                                    data-current-stock="{{ $tool->quantity }}"
                                    data-unit="{{ $tool->unit }}"
                                    data-min-stock="{{ $tool->minimum_stock }}"
                                    data-price="{{ $tool->parts_price }}"
                                    {{ old('item_id') == $tool->id && old('item_type') == 'tool' ? 'selected' : '' }}>
                                    {{ $tool->sparepart_name }} ({{ $tool->sparepart_id }}) - Stock: {{ $tool->quantity }} {{ $tool->unit }}
                                </option>
                                @endforeach
                            </select>
                            @error('item_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Hidden field for item_id --}}
                        <input type="hidden" name="item_id" id="item_id" value="{{ old('item_id') }}">

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
                            <label for="adjustment_qty_input" class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" id="adjustment_qty_input" class="form-control @error('adjustment_qty') is-invalid @enderror"
                                value="{{ old('adjustment_qty') ? abs(old('adjustment_qty')) : '' }}" required min="0" step="1">
                            <input type="hidden" name="adjustment_qty" id="adjustment_qty" value="{{ old('adjustment_qty') }}">
                            @error('adjustment_qty')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted" id="qty-hint">Enter quantity amount (always use positive number)</small>
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
                            <a href="{{ route('admin.adjustments.index') }}" class="btn btn-secondary">
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
                        <li><strong>Add:</strong> Select "Add Stock" type, enter amount to add (e.g., add 5 items)</li>
                        <li><strong>Subtract:</strong> Select "Subtract Stock" type, enter amount to subtract (e.g., subtract 3 items for damaged goods)</li>
                        <li><strong>Correction:</strong> Enter the NEW TOTAL stock quantity after physical count (e.g., current stock 102 → enter 80 to set stock to 80)</li>
                        <li>Always use positive numbers - the system automatically calculates the difference</li>
                        <li>Always provide detailed reason explaining the adjustment</li>
                        <li>All adjustments are logged and tracked for audit purposes</li>
                    </ul>

                    <hr>

                    <h6 class="fw-bold">Example Scenarios:</h6>
                    <div class="small">
                        <strong>Damaged Items:</strong> Current: 102 → Type: Subtract, enter 5 → Result: 97 pcs<br>
                        <strong>Found Items:</strong> Current: 102 → Type: Add, enter 3 → Result: 105 pcs<br>
                        <strong>Stock Opname:</strong> Current: 102 → Type: Correction, enter 80 → Result: 80 pcs (adjusts -22)
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
document.addEventListener('DOMContentLoaded', function() {
    const itemTypeSelect = document.getElementById('item_type');
    const sparepartSelectGroup = document.getElementById('sparepart-select-group');
    const toolSelectGroup = document.getElementById('tool-select-group');
    const sparepartSelect = document.getElementById('sparepart_id');
    const toolSelect = document.getElementById('tool_id');
    const itemIdInput = document.getElementById('item_id');
    const adjustmentQtyInput = document.getElementById('adjustment_qty'); // hidden field
    const adjustmentQtyInputVisible = document.getElementById('adjustment_qty_input'); // visible input
    const adjustmentTypeSelect = document.getElementById('adjustment_type');
    const currentStockDisplay = document.getElementById('current-stock-display');
    const newStockPreview = document.getElementById('new-stock-preview');

    // Initialize Choices.js for searchable dropdowns
    const sparepartChoices = new Choices(sparepartSelect, {
        searchEnabled: true,
        searchPlaceholderValue: 'Type to search sparepart...',
        itemSelectText: 'Click to select',
        shouldSort: false,
        placeholder: true,
        placeholderValue: '-- Select Sparepart --',
        removeItemButton: true
    });

    const toolChoices = new Choices(toolSelect, {
        searchEnabled: true,
        searchPlaceholderValue: 'Type to search tool...',
        itemSelectText: 'Click to select',
        shouldSort: false,
        placeholder: true,
        placeholderValue: '-- Select Tool --',
        removeItemButton: true
    });

    // Show/hide item selects based on item type
    itemTypeSelect.addEventListener('change', function() {
        if (this.value === 'sparepart') {
            sparepartSelectGroup.style.display = 'block';
            toolSelectGroup.style.display = 'none';
            sparepartSelect.required = true;
            toolSelect.required = false;
            toolSelect.value = '';
            itemIdInput.value = sparepartSelect.value;
        } else if (this.value === 'tool') {
            sparepartSelectGroup.style.display = 'none';
            toolSelectGroup.style.display = 'block';
            sparepartSelect.required = false;
            toolSelect.required = true;
            sparepartSelect.value = '';
            itemIdInput.value = toolSelect.value;
        } else {
            sparepartSelectGroup.style.display = 'none';
            toolSelectGroup.style.display = 'none';
            sparepartSelect.required = false;
            toolSelect.required = false;
            itemIdInput.value = '';
        }
        updateStockPreview();
    });

    // Update hidden item_id when selection changes
    sparepartSelect.addEventListener('change', function() {
        itemIdInput.value = this.value;
        updateStockPreview();
    });

    toolSelect.addEventListener('change', function() {
        itemIdInput.value = this.value;
        updateStockPreview();
    });

    adjustmentQtyInputVisible.addEventListener('input', updateStockPreview);
    adjustmentTypeSelect.addEventListener('change', function() {
        updateHintText();
        updateStockPreview();
    });

    // Update hint text based on adjustment type
    function updateHintText() {
        const hintElement = document.getElementById('qty-hint');
        const labelElement = document.querySelector('label[for="adjustment_qty_input"]');

        if (adjustmentTypeSelect.value === 'correction') {
            hintElement.textContent = 'Enter the NEW TOTAL stock quantity (e.g., if current is 102 and you want 80, enter 80)';
            labelElement.innerHTML = 'New Stock Quantity <span class="text-danger">*</span>';
            adjustmentQtyInputVisible.min = '0';
        } else if (adjustmentTypeSelect.value === 'subtract') {
            hintElement.textContent = 'Enter amount to subtract (always use positive number, e.g., 5 to subtract 5 from stock)';
            labelElement.innerHTML = 'Quantity to Subtract <span class="text-danger">*</span>';
            adjustmentQtyInputVisible.min = '1';
        } else if (adjustmentTypeSelect.value === 'add') {
            hintElement.textContent = 'Enter amount to add (always use positive number, e.g., 10 to add 10 to stock)';
            labelElement.innerHTML = 'Quantity to Add <span class="text-danger">*</span>';
            adjustmentQtyInputVisible.min = '1';
        } else {
            hintElement.textContent = 'Enter quantity amount (always use positive number)';
            labelElement.innerHTML = 'Quantity <span class="text-danger">*</span>';
            adjustmentQtyInputVisible.min = '1';
        }
    }

    // Update stock preview
    function updateStockPreview() {
        let selectedOption;

        if (itemTypeSelect.value === 'sparepart' && sparepartSelect.value) {
            selectedOption = sparepartSelect.options[sparepartSelect.selectedIndex];
        } else if (itemTypeSelect.value === 'tool' && toolSelect.value) {
            selectedOption = toolSelect.options[toolSelect.selectedIndex];
        } else {
            currentStockDisplay.style.display = 'none';
            newStockPreview.style.display = 'none';
            return;
        }

        const currentStock = parseFloat(selectedOption.dataset.currentStock) || 0;
        const unit = selectedOption.dataset.unit || '';
        const minStock = parseFloat(selectedOption.dataset.minStock) || 0;

        currentStockDisplay.style.display = 'block';
        document.getElementById('current-stock-value').textContent = currentStock;
        document.getElementById('current-stock-unit').textContent = unit;

        if (minStock > 0) {
            document.getElementById('min-stock-warning').style.display = 'block';
            document.getElementById('min-stock-value').textContent = minStock + ' ' + unit;
        } else {
            document.getElementById('min-stock-warning').style.display = 'none';
        }

        if (adjustmentQtyInputVisible.value !== '') {
            let inputQty = Math.abs(parseFloat(adjustmentQtyInputVisible.value) || 0);
            let adjustmentQty = inputQty;
            let newStock;
            let calculation;

            // Apply logic based on adjustment type
            if (adjustmentTypeSelect.value === 'correction') {
                // For correction: input is the target stock, calculate the difference
                newStock = inputQty;
                adjustmentQty = newStock - currentStock;
                calculation = `${currentStock} → <strong>${newStock}</strong> ${unit} (${adjustmentQty >= 0 ? '+' : ''}${adjustmentQty})`;
            } else if (adjustmentTypeSelect.value === 'subtract') {
                // For subtract: input is amount to subtract
                adjustmentQty = -inputQty;
                newStock = currentStock + adjustmentQty;
                calculation = `${currentStock} + (${adjustmentQty}) = <strong>${newStock}</strong> ${unit}`;
            } else if (adjustmentTypeSelect.value === 'add') {
                // For add: input is amount to add
                adjustmentQty = inputQty;
                newStock = currentStock + adjustmentQty;
                calculation = `${currentStock} + (${adjustmentQty}) = <strong>${newStock}</strong> ${unit}`;
            } else {
                newStockPreview.style.display = 'none';
                return;
            }

            // Update hidden field with the correctly signed value
            adjustmentQtyInput.value = adjustmentQty;

            newStockPreview.style.display = 'block';
            document.getElementById('calculation-display').innerHTML = calculation;
            document.getElementById('new-stock-value').textContent = newStock;
            document.getElementById('new-stock-unit').textContent = unit;

            const badge = document.getElementById('new-stock-value');
            if (newStock < 0) {
                badge.className = 'badge bg-danger fs-6 mt-2';
                adjustmentQtyInputVisible.setCustomValidity('This adjustment would result in negative stock');
            } else if (newStock < minStock) {
                badge.className = 'badge bg-warning fs-6 mt-2';
                adjustmentQtyInputVisible.setCustomValidity('');
            } else {
                badge.className = 'badge bg-success fs-6 mt-2';
                adjustmentQtyInputVisible.setCustomValidity('');
            }
        } else {
            newStockPreview.style.display = 'none';
            adjustmentQtyInput.value = '';
        }
    }

    // Restore state if there are old values
    if (itemTypeSelect.value) {
        itemTypeSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@endsection

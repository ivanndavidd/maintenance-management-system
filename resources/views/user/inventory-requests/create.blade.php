@extends('layouts.user')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('user.inventory-requests.index') }}">Inventory Requests</a>
                </li>
                <li class="breadcrumb-item active">New Request</li>
            </ol>
        </nav>
        <h2><i class="fas fa-plus-circle"></i> New Inventory Request</h2>
        <p class="text-muted">Request parts or inventory for your work</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-file-alt"></i> Request Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('user.inventory-requests.store') }}" method="POST">
                        @csrf

                        <!-- Request Code (Auto-generated) -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Request Code</label>
                            <input type="text" name="request_code" class="form-control bg-light"
                                   value="{{ $requestCode }}" readonly>
                            <small class="text-muted">Auto-generated request code</small>
                        </div>

                        <!-- Part Selection -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Part/Inventory <span class="text-danger">*</span>
                            </label>
                            <select name="part_id" id="part_id" class="form-select @error('part_id') is-invalid @enderror" required>
                                <option value="">-- Select Part --</option>
                                @foreach($parts as $part)
                                <option value="{{ $part->id }}"
                                        data-quantity="{{ $part->quantity }}"
                                        data-unit="{{ $part->unit }}"
                                        {{ old('part_id') == $part->id ? 'selected' : '' }}>
                                    {{ $part->name }} ({{ $part->part_number }}) - Available: {{ $part->quantity }} {{ $part->unit }}
                                </option>
                                @endforeach
                            </select>
                            @error('part_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Available Stock Display -->
                        <div class="mb-3" id="stock-info" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Available Stock:</strong>
                                <span id="available-stock"></span>
                                <span id="stock-unit"></span>
                            </div>
                        </div>

                        <!-- Quantity Requested -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Quantity Requested <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="quantity_requested" id="quantity_requested"
                                   class="form-control @error('quantity_requested') is-invalid @enderror"
                                   value="{{ old('quantity_requested') }}"
                                   min="1" required>
                            @error('quantity_requested')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Reason -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Reason <span class="text-danger">*</span>
                            </label>
                            <select name="reason" class="form-select @error('reason') is-invalid @enderror" required>
                                <option value="">-- Select Reason --</option>
                                <option value="Maintenance Work" {{ old('reason') == 'Maintenance Work' ? 'selected' : '' }}>
                                    Maintenance Work
                                </option>
                                <option value="Repair Job" {{ old('reason') == 'Repair Job' ? 'selected' : '' }}>
                                    Repair Job
                                </option>
                                <option value="Preventive Maintenance" {{ old('reason') == 'Preventive Maintenance' ? 'selected' : '' }}>
                                    Preventive Maintenance
                                </option>
                                <option value="Breakdown Repair" {{ old('reason') == 'Breakdown Repair' ? 'selected' : '' }}>
                                    Breakdown Repair
                                </option>
                                <option value="Stock Replacement" {{ old('reason') == 'Stock Replacement' ? 'selected' : '' }}>
                                    Stock Replacement
                                </option>
                                <option value="Other" {{ old('reason') == 'Other' ? 'selected' : '' }}>
                                    Other
                                </option>
                            </select>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Usage Description -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Usage Description</label>
                            <textarea name="usage_description" class="form-control @error('usage_description') is-invalid @enderror"
                                      rows="4"
                                      placeholder="Provide detailed information about how you will use this inventory...">{{ old('usage_description') }}</textarea>
                            @error('usage_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Optional: Provide more details about the usage</small>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('user.inventory-requests.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Help Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Request Guidelines</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Select the part you need from the available inventory
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Specify the exact quantity required
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Provide a clear reason for the request
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success"></i>
                            Add detailed usage description for faster approval
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-info-circle text-info"></i>
                            Your request will be reviewed by admin
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-info-circle text-info"></i>
                            You'll be notified once approved or rejected
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Important Notes</h6>
                </div>
                <div class="card-body">
                    <ul class="small mb-0">
                        <li class="mb-2">Only parts with available stock are shown</li>
                        <li class="mb-2">Requested quantity cannot exceed available stock</li>
                        <li class="mb-2">You can cancel pending requests anytime</li>
                        <li class="mb-2">Approved requests will automatically deduct inventory</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Show available stock when part is selected
    document.getElementById('part_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const stockInfo = document.getElementById('stock-info');
        const availableStock = document.getElementById('available-stock');
        const stockUnit = document.getElementById('stock-unit');
        const quantityInput = document.getElementById('quantity_requested');

        if (this.value) {
            const quantity = selectedOption.getAttribute('data-quantity');
            const unit = selectedOption.getAttribute('data-unit');

            availableStock.textContent = quantity;
            stockUnit.textContent = unit;
            stockInfo.style.display = 'block';

            // Set max attribute for quantity input
            quantityInput.setAttribute('max', quantity);
        } else {
            stockInfo.style.display = 'none';
            quantityInput.removeAttribute('max');
        }
    });

    // Validate quantity on input
    document.getElementById('quantity_requested').addEventListener('input', function() {
        const max = parseInt(this.getAttribute('max'));
        const value = parseInt(this.value);

        if (max && value > max) {
            this.setCustomValidity('Requested quantity exceeds available stock (' + max + ')');
        } else if (value < 1) {
            this.setCustomValidity('Quantity must be at least 1');
        } else {
            this.setCustomValidity('');
        }
    });
</script>
@endpush
@endsection

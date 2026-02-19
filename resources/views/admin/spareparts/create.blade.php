@extends('layouts.admin')

@section('page-title', 'Add New Sparepart')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Add New Sparepart</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route($routePrefix.'.spareparts.index') }}">Spareparts</a></li>
                <li class="breadcrumb-item active">Add New</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Sparepart Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route($routePrefix.'.spareparts.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="from_po_item" id="from_po_item_id" value="{{ request('from_po_item') }}">

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Material Code will be auto-generated (Format: SPR + YYYYMMDD + 001)
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="equipment_type" class="form-label">Equipment Type <span class="text-danger">*</span></label>
                                <input type="text" name="equipment_type" id="equipment_type"
                                    class="form-control @error('equipment_type') is-invalid @enderror"
                                    value="{{ old('equipment_type') }}"
                                    placeholder="e.g., CBS, Singulator, Belt Conveyor, Tools">
                                <small class="text-muted">Enter equipment type (e.g., CBS, Belt Conveyor, Panel, Tools)</small>
                                @error('equipment_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="sparepart_name" class="form-label">Sparepart Name <span class="text-danger">*</span></label>
                                <input type="text" name="sparepart_name" id="sparepart_name"
                                    class="form-control @error('sparepart_name') is-invalid @enderror"
                                    value="{{ old('sparepart_name') }}" required>
                                @error('sparepart_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="brand" class="form-label">Brand</label>
                                <input type="text" name="brand" id="brand"
                                    class="form-control @error('brand') is-invalid @enderror"
                                    value="{{ old('brand') }}">
                                @error('brand')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="model" class="form-label">Model</label>
                                <input type="text" name="model" id="model"
                                    class="form-control @error('model') is-invalid @enderror"
                                    value="{{ old('model') }}">
                                @error('model')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="quantity" class="form-label">Initial Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" id="quantity"
                                    class="form-control @error('quantity') is-invalid @enderror"
                                    value="{{ old('quantity', 0) }}" min="0" required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="minimum_stock" class="form-label">Minimum Stock <span class="text-danger">*</span></label>
                                <input type="number" name="minimum_stock" id="minimum_stock"
                                    class="form-control @error('minimum_stock') is-invalid @enderror"
                                    value="{{ old('minimum_stock', 5) }}" min="0" required>
                                @error('minimum_stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="unit" class="form-label">Unit <span class="text-danger">*</span></label>
                                <select name="unit" id="unit" class="form-select @error('unit') is-invalid @enderror" required>
                                    <option value="">Select Unit</option>
                                    <option value="pcs" {{ old('unit') == 'pcs' ? 'selected' : '' }}>Pcs</option>
                                    <option value="unit" {{ old('unit') == 'unit' ? 'selected' : '' }}>Unit</option>
                                    <option value="set" {{ old('unit') == 'set' ? 'selected' : '' }}>Set</option>
                                    <option value="box" {{ old('unit') == 'box' ? 'selected' : '' }}>Box</option>
                                    <option value="pack" {{ old('unit') == 'pack' ? 'selected' : '' }}>Pack</option>
                                    <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>Kg</option>
                                    <option value="liter" {{ old('unit') == 'liter' ? 'selected' : '' }}>Liter</option>
                                    <option value="meter" {{ old('unit') == 'meter' ? 'selected' : '' }}>Meter</option>
                                </select>
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="parts_price" class="form-label">Unit Price (Rp) <span class="text-danger">*</span></label>
                                <input type="number" name="parts_price" id="parts_price"
                                    class="form-control @error('parts_price') is-invalid @enderror"
                                    value="{{ old('parts_price', 0) }}" min="0" step="0.01" required>
                                @error('parts_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="vulnerability" class="form-label">Vulnerability Level</label>
                                <select name="vulnerability" id="vulnerability" class="form-select @error('vulnerability') is-invalid @enderror">
                                    <option value="">Not Specified</option>
                                    <option value="low" {{ old('vulnerability') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ old('vulnerability') == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ old('vulnerability') == 'high' ? 'selected' : '' }}>High</option>
                                    <option value="critical" {{ old('vulnerability') == 'critical' ? 'selected' : '' }}>Critical</option>
                                </select>
                                @error('vulnerability')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label">Storage Location</label>
                            <input type="text" name="location" id="location"
                                class="form-control @error('location') is-invalid @enderror"
                                value="{{ old('location') }}"
                                placeholder="e.g., Warehouse A - Rack 3 - Shelf 2">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="path" class="form-label">Image/Document</label>
                            <input type="file" name="path" id="path"
                                class="form-control @error('path') is-invalid @enderror"
                                accept="image/*,.pdf">
                            <small class="text-muted">Accepted: Images (JPG, PNG) or PDF. Max 2MB</small>
                            @error('path')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route($routePrefix.'.spareparts.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Sparepart
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Tips</h5>
                </div>
                <div class="card-body">
                    <h6><i class="fas fa-lightbulb text-warning"></i> Quick Tips:</h6>
                    <ul class="small">
                        <li>Material code will be auto-generated when you save</li>
                        <li>Set minimum stock to trigger low stock alerts</li>
                        <li>Vulnerability level helps prioritize critical items</li>
                        <li>Use clear storage locations for easy finding</li>
                        <li>Upload images to help identify parts quickly</li>
                    </ul>

                    <hr>

                    <h6><i class="fas fa-file-excel text-success"></i> Bulk Import:</h6>
                    <p class="small">Need to import many items at once?</p>
                    <a href="{{ route($routePrefix.'.spareparts.import') }}" class="btn btn-success btn-sm w-100">
                        <i class="fas fa-upload"></i> Import from Excel
                    </a>
                </div>
            </div>

            @if(isset($pendingPoItems) && $pendingPoItems->count() > 0)
            <div class="card mt-3 border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> From Purchase Orders
                        <span class="badge bg-dark ms-1">{{ $pendingPoItems->count() }}</span>
                    </h5>
                </div>
                <div class="card-body p-2">
                    <p class="small text-muted mb-2">New sparepart items from received POs. Click to auto-fill the form.</p>
                    @foreach($pendingPoItems as $poItem)
                    <div class="card mb-2 {{ isset($selectedPoItem) && $selectedPoItem->id === $poItem->id ? 'border-primary shadow-sm' : 'border-light' }}"
                         style="cursor:pointer; transition: box-shadow 0.2s;"
                         onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.15)'"
                         onmouseout="this.style.boxShadow=''"
                         onclick="fillFromPo({{ json_encode([
                             'id'       => $poItem->id,
                             'name'     => $poItem->unlisted_item_name,
                             'specs'    => $poItem->unlisted_item_specs ?? '',
                             'unit'     => $poItem->unit,
                             'price'    => (float) $poItem->unit_price,
                             'quantity' => (int) $poItem->quantity_received,
                         ]) }})">
                        <div class="card-body py-2 px-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong class="small">{{ $poItem->unlisted_item_name }}</strong>
                                    @if($poItem->unlisted_item_description)
                                        <br><span class="text-muted" style="font-size:11px;">{{ Str::limit($poItem->unlisted_item_description, 55) }}</span>
                                    @endif
                                    <br>
                                    <span class="badge bg-secondary" style="font-size:10px;">{{ $poItem->purchaseOrder->po_number }}</span>
                                    <span class="text-muted small ms-1">{{ $poItem->quantity_received }} {{ $poItem->unit }}</span>
                                </div>
                                <i class="fas fa-arrow-right text-primary mt-1 ms-2 flex-shrink-0"></i>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function fillFromPo(data) {
    if (data.id)       document.getElementById('from_po_item_id').value = data.id;
    if (data.name)     document.getElementById('sparepart_name').value = data.name;
    if (data.specs)    document.getElementById('model').value = data.specs;
    if (data.price)    document.getElementById('parts_price').value = data.price;
    if (data.quantity) document.getElementById('quantity').value = data.quantity;

    // Set unit select if it matches an option
    if (data.unit) {
        const unitSelect = document.getElementById('unit');
        for (let i = 0; i < unitSelect.options.length; i++) {
            if (unitSelect.options[i].value.toLowerCase() === data.unit.toLowerCase()) {
                unitSelect.selectedIndex = i;
                break;
            }
        }
    }

    // Scroll to form
    document.getElementById('sparepart_name').scrollIntoView({ behavior: 'smooth', block: 'center' });
    document.getElementById('sparepart_name').focus();
}

// Auto-fill if a specific PO item was passed via URL
@if(isset($selectedPoItem) && $selectedPoItem)
window.addEventListener('DOMContentLoaded', function() {
    fillFromPo({{ json_encode([
        'name'     => $selectedPoItem->unlisted_item_name,
        'specs'    => $selectedPoItem->unlisted_item_specs ?? '',
        'unit'     => $selectedPoItem->unit,
        'price'    => (float) $selectedPoItem->unit_price,
        'quantity' => (int) $selectedPoItem->quantity_received,
    ]) }});
});
@endif
</script>
@endpush

@endsection

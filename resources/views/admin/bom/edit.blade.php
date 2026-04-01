@extends('layouts.admin')

@section('page-title', 'Edit BOM ' . $bom->bom_id)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-edit me-2"></i>Edit BOM {{ $bom->bom_id }}</h4>
            <p class="text-muted mb-0">{{ $bom->description ?? 'No description' }}</p>
        </div>
        <a href="{{ route($routePrefix . '.bom-management.show', $bom) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route($routePrefix . '.bom-management.update', $bom) }}" method="POST" id="bomForm">
        @csrf @method('PUT')
        <div class="row">
            <div class="col-lg-8">
                <!-- BOM Info -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">BOM Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">BOM ID <span class="text-danger">*</span></label>
                                <input type="text" name="bom_id" class="form-control text-uppercase"
                                       value="{{ old('bom_id', $bom->bom_id) }}" required maxlength="20"
                                       style="text-transform:uppercase;">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Description</label>
                                <input type="text" name="description" class="form-control"
                                       value="{{ old('description', $bom->description) }}"
                                       placeholder="Optional description">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">BOM Items</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow()">
                            <i class="fas fa-plus me-1"></i> Add Row
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="4%">No</th>
                                        <th width="16%">No. Material</th>
                                        <th>Material Description <span class="text-danger">*</span></th>
                                        <th width="8%">Qty <span class="text-danger">*</span></th>
                                        <th width="8%">Unit <span class="text-danger">*</span></th>
                                        <th width="13%">Price Unit</th>
                                        <th width="13%">Price</th>
                                        <th width="5%"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <!-- rows pre-filled by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <small class="text-muted me-3">Total Price: <strong id="totalPrice">Rp 0</strong></small>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow()">
                            <i class="fas fa-plus me-1"></i> Add Row
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-save me-1"></i> Update BOM
                        </button>
                        <a href="{{ route($routePrefix . '.bom-management.show', $bom) }}" class="btn btn-secondary w-100">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
let rowIndex = 0;

const existingItems = @json($bom->items->map(fn($i) => [
    'material_code' => $i->material_code,
    'material_description' => $i->material_description,
    'qty' => $i->qty,
    'unit' => $i->unit,
    'price_unit' => $i->price_unit,
    'price' => $i->price,
]));

function addRow(data) {
    const tbody = document.getElementById('itemsBody');
    const no = tbody.rows.length + 1;
    const d = data || {};
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="row-no text-center">${no}</td>
        <td><input type="text" name="items[${rowIndex}][material_code]" class="form-control form-control-sm" value="${d.material_code||''}" placeholder="130317..."></td>
        <td><input type="text" name="items[${rowIndex}][material_description]" class="form-control form-control-sm" value="${d.material_description||''}" required placeholder="Material name"></td>
        <td><input type="number" name="items[${rowIndex}][qty]" class="form-control form-control-sm qty-input" value="${d.qty!=null?d.qty:1}" min="0" step="0.01" required></td>
        <td><input type="text" name="items[${rowIndex}][unit]" class="form-control form-control-sm" value="${d.unit||'Pcs'}" required></td>
        <td><input type="number" name="items[${rowIndex}][price_unit]" class="form-control form-control-sm price-input" value="${d.price_unit!=null?d.price_unit:''}" min="0" step="1" placeholder="0"></td>
        <td><input type="number" name="items[${rowIndex}][price]" class="form-control form-control-sm price-input" value="${d.price!=null?d.price:''}" min="0" step="1" placeholder="0"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)"><i class="fas fa-times"></i></button></td>
    `;
    tbody.appendChild(tr);
    rowIndex++;
    renumberRows();
    bindPriceEvents(tr);
    updateTotal();
}

function removeRow(btn) {
    btn.closest('tr').remove();
    renumberRows();
    updateTotal();
}

function renumberRows() {
    document.querySelectorAll('#itemsBody tr').forEach((tr, i) => {
        tr.querySelector('.row-no').textContent = i + 1;
    });
}

function bindPriceEvents(tr) {
    tr.querySelectorAll('.price-input').forEach(el => {
        el.addEventListener('input', updateTotal);
    });
}

function updateTotal() {
    let total = 0;
    document.querySelectorAll('#itemsBody tr').forEach(tr => {
        const inputs = tr.querySelectorAll('.price-input');
        if (inputs.length >= 2) {
            total += parseFloat(inputs[1].value) || 0;
        }
    });
    document.getElementById('totalPrice').textContent = 'Rp ' + total.toLocaleString('id-ID');
}

// Pre-fill existing items
existingItems.forEach(item => addRow(item));
if (existingItems.length === 0) addRow();

document.getElementById('bomForm').addEventListener('submit', function(e) {
    if (document.querySelectorAll('#itemsBody tr').length === 0) {
        e.preventDefault();
        alert('Please add at least one item.');
    }
});
</script>
@endpush

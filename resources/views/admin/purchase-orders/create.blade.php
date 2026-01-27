@extends('layouts.admin')

@section('page-title', 'Create Purchase Order')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2>Create Purchase Order</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.purchase-orders.index') }}">Purchase Orders</a></li>
                <li class="breadcrumb-item active">Create</li>
            </ol>
        </nav>
    </div>

    @if($lowStockSpareparts->count() > 0 || $lowStockTools->count() > 0)
    <div class="alert alert-warning mb-3">
        <strong><i class="fas fa-exclamation-triangle"></i> Low Stock Alert:</strong>
        {{ $lowStockSpareparts->count() }} sparepart(s) and {{ $lowStockTools->count() }} tool(s) are low in stock.
    </div>
    @endif

    <div class="row">
        <!-- Shopping Cart -->
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> Shopping Cart (<span id="cart_count">0</span> items)</h5>
                </div>
                <div class="card-body">
                    <!-- Add Item Section -->
                    <div class="border rounded p-3 mb-3 bg-light">
                        <h6 class="mb-3">Add Item to Cart</h6>

                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label class="form-label">Search Item</label>
                                <select id="item_select" class="form-select" onchange="onItemSelect()">
                                    <option value="">-- Select Sparepart or Tool --</option>
                                    @if($lowStockSpareparts->count() > 0)
                                    <optgroup label="⚠️ LOW STOCK - Spareparts">
                                        @foreach($lowStockSpareparts as $sp)
                                        <option value="sparepart-{{ $sp->id }}"
                                            data-type="sparepart"
                                            data-id="{{ $sp->id }}"
                                            data-name="{{ $sp->sparepart_name }}"
                                            data-code="{{ $sp->sparepart_id }}"
                                            data-price="{{ $sp->parts_price }}"
                                            data-unit="{{ $sp->unit }}"
                                            data-brand="{{ $sp->brand }}"
                                            data-model="{{ $sp->model }}">
                                            [SPAREPART] {{ $sp->sparepart_name }} ({{ $sp->sparepart_id }})@if($sp->model) - {{ $sp->model }}@endif
                                        </option>
                                        @endforeach
                                    </optgroup>
                                    @endif

                                    @if($lowStockTools->count() > 0)
                                    <optgroup label="⚠️ LOW STOCK - Tools">
                                        @foreach($lowStockTools as $tool)
                                        <option value="tool-{{ $tool->id }}"
                                            data-type="tool"
                                            data-id="{{ $tool->id }}"
                                            data-name="{{ $tool->sparepart_name }}"
                                            data-code="{{ $tool->tool_id }}"
                                            data-price="{{ $tool->parts_price }}"
                                            data-unit="{{ $tool->unit }}"
                                            data-brand="{{ $tool->brand }}"
                                            data-model="{{ $tool->model }}">
                                            [TOOL] {{ $tool->sparepart_name }} ({{ $tool->tool_id }})@if($tool->model) - {{ $tool->model }}@endif
                                        </option>
                                        @endforeach
                                    </optgroup>
                                    @endif

                                    <optgroup label="All Spareparts">
                                        @foreach($allSpareparts as $sp)
                                        <option value="sparepart-{{ $sp->id }}"
                                            data-type="sparepart"
                                            data-id="{{ $sp->id }}"
                                            data-name="{{ $sp->sparepart_name }}"
                                            data-code="{{ $sp->sparepart_id }}"
                                            data-price="{{ $sp->parts_price }}"
                                            data-unit="{{ $sp->unit }}"
                                            data-brand="{{ $sp->brand }}"
                                            data-model="{{ $sp->model }}">
                                            [SPAREPART] {{ $sp->sparepart_name }} ({{ $sp->sparepart_id }})@if($sp->model) - {{ $sp->model }}@endif
                                        </option>
                                        @endforeach
                                    </optgroup>

                                    <optgroup label="All Tools">
                                        @foreach($allTools as $tool)
                                        <option value="tool-{{ $tool->id }}"
                                            data-type="tool"
                                            data-id="{{ $tool->id }}"
                                            data-name="{{ $tool->sparepart_name }}"
                                            data-code="{{ $tool->tool_id }}"
                                            data-price="{{ $tool->parts_price }}"
                                            data-unit="{{ $tool->unit }}"
                                            data-brand="{{ $tool->brand }}"
                                            data-model="{{ $tool->model }}">
                                            [TOOL] {{ $tool->sparepart_name }} ({{ $tool->tool_id }})@if($tool->model) - {{ $tool->model }}@endif
                                        </option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Supplier</label>
                                <input type="text" id="item_supplier" class="form-control" placeholder="e.g., Siemens">
                            </div>

                            <div class="col-md-2 mb-3">
                                <label class="form-label">Qty</label>
                                <input type="number" id="item_quantity" class="form-control" min="1" value="1">
                            </div>

                            <div class="col-md-2 mb-3">
                                <label class="form-label">Price (Rp)</label>
                                <input type="number" id="item_price" class="form-control" min="0" step="0.01" readonly>
                                <small class="text-muted">From master data</small>
                            </div>
                        </div>

                        <!-- Hidden unit field - auto-filled from master data -->
                        <input type="hidden" id="item_unit" value="pcs">

                        <button type="button" class="btn btn-success w-100 mt-3" onclick="addToCart()">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </div>

                    <!-- Cart Items Table -->
                    <h6 class="mb-3">Items in Cart</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th width="3%">#</th>
                                    <th width="20%">Item Name</th>
                                    <th width="10%">Code</th>
                                    <th width="12%">Supplier</th>
                                    <th width="10%">Type</th>
                                    <th width="7%">Qty</th>
                                    <th width="7%">Unit</th>
                                    <th width="12%">Price</th>
                                    <th width="12%">Subtotal</th>
                                    <th width="3%"></th>
                                </tr>
                            </thead>
                            <tbody id="cart_tbody">
                                <tr id="empty_cart">
                                    <td colspan="10" class="text-center text-muted py-4">
                                        <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                                        <p>Cart is empty. Add items from above.</p>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot id="cart_footer" style="display:none;">
                                <tr class="table-light">
                                    <th colspan="8" class="text-end">GRAND TOTAL:</th>
                                    <th colspan="2" id="grand_total">Rp 0</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Form -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Purchase Order Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.purchase-orders.store') }}" method="POST" id="po_form">
                        @csrf
                        <div id="hidden_cart_items"></div>

                        <div class="mb-3">
                            <label class="form-label">PO Number</label>
                            <input type="text" class="form-control" value="{{ $poNumber }}" readonly>
                            <small class="text-muted">Auto-generated</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Order Date <span class="text-danger">*</span></label>
                            <input type="date" name="order_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Expected Delivery</label>
                            <input type="date" name="expected_delivery_date" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Approver <span class="text-danger">*</span></label>
                            <select name="approver_id" class="form-select" required>
                                <option value="">-- Select Admin --</option>
                                @foreach($adminUsers as $admin)
                                    <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="submit_btn" disabled>
                                <i class="fas fa-paper-plane"></i> Create Purchase Order
                            </button>
                            <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Unlisted items feature has been disabled. All items must be added to master data first. -->

<script>
let cart = [];
let cartIndex = 0;

function onItemSelect() {
    const select = document.getElementById('item_select');
    const selected = select.options[select.selectedIndex];

    if (!selected.value) return;

    document.getElementById('item_price').value = selected.getAttribute('data-price') || '';
    document.getElementById('item_unit').value = selected.getAttribute('data-unit') || 'pcs';
}

function addToCart() {
    const select = document.getElementById('item_select');
    const selected = select.options[select.selectedIndex];

    if (!selected.value) {
        alert('Please select an item!');
        return;
    }

    const supplier = document.getElementById('item_supplier').value.trim();
    const quantity = parseInt(document.getElementById('item_quantity').value);
    const price = parseFloat(document.getElementById('item_price').value);
    const unit = document.getElementById('item_unit').value;

    if (!supplier) {
        alert('Please enter supplier name!');
        return;
    }

    if (!quantity || quantity < 1) {
        alert('Please enter valid quantity!');
        return;
    }

    if (!price || price < 0) {
        alert('Please enter valid price!');
        return;
    }

    const item = {
        index: cartIndex++,
        type: selected.getAttribute('data-type'),
        item_id: selected.getAttribute('data-id'),
        name: selected.getAttribute('data-name'),
        code: selected.getAttribute('data-code'),
        supplier: supplier,
        quantity: quantity,
        unit: unit,
        unit_price: price,
        subtotal: quantity * price,
        is_unlisted: false
    };

    cart.push(item);
    renderCart();
    resetForm();
}

// addUnlistedToCart function has been removed - unlisted items feature disabled

function renderCart() {
    const tbody = document.getElementById('cart_tbody');
    tbody.innerHTML = '';

    if (cart.length === 0) {
        tbody.innerHTML = `
            <tr id="empty_cart">
                <td colspan="10" class="text-center text-muted py-4">
                    <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                    <p>Cart is empty. Add items from above.</p>
                </td>
            </tr>
        `;
        document.getElementById('cart_footer').style.display = 'none';
        document.getElementById('submit_btn').disabled = true;
        document.getElementById('cart_count').textContent = '0';
        return;
    }

    let grandTotal = 0;

    cart.forEach((item, idx) => {
        grandTotal += item.subtotal;

        let typeBadge = '';
        if (item.type === 'sparepart') {
            typeBadge = '<span class="badge bg-info">Sparepart</span>';
        } else if (item.type === 'tool') {
            typeBadge = '<span class="badge bg-success">Tool</span>';
        }

        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="text-center">${idx + 1}</td>
            <td><strong>${item.name}</strong></td>
            <td><small class="text-muted">${item.code}</small></td>
            <td>${item.supplier}</td>
            <td>${typeBadge}</td>
            <td class="text-center">${item.quantity}</td>
            <td>${item.unit}</td>
            <td class="text-end">Rp ${formatNumber(item.unit_price)}</td>
            <td class="text-end"><strong>Rp ${formatNumber(item.subtotal)}</strong></td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger" onclick="removeFromCart(${item.index})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });

    document.getElementById('cart_count').textContent = cart.length;
    document.getElementById('grand_total').textContent = 'Rp ' + formatNumber(grandTotal);
    document.getElementById('cart_footer').style.display = '';
    document.getElementById('submit_btn').disabled = false;

    updateHiddenFields();
}

function removeFromCart(index) {
    cart = cart.filter(item => item.index !== index);
    renderCart();
}

function resetForm() {
    // Reset Select2 dropdown
    if (typeof $ !== 'undefined' && $('#item_select').data('select2')) {
        $('#item_select').val(null).trigger('change');
    } else {
        document.getElementById('item_select').value = '';
    }

    document.getElementById('item_supplier').value = '';
    document.getElementById('item_quantity').value = 1;
    document.getElementById('item_unit').value = 'pcs';
    document.getElementById('item_price').value = '';
}

function updateHiddenFields() {
    const container = document.getElementById('hidden_cart_items');
    container.innerHTML = '';

    // Add dummy supplier (required by backend but not used per-item)
    const supplierInput = document.createElement('input');
    supplierInput.type = 'hidden';
    supplierInput.name = 'supplier';
    supplierInput.value = 'Multiple Suppliers';
    container.appendChild(supplierInput);

    cart.forEach((item, idx) => {
        const fields = [];

        if (item.is_unlisted) {
            fields.push(['type', 'unlisted']);
            fields.push(['unlisted_name', item.unlisted_name]);
            fields.push(['unlisted_description', item.unlisted_description || '']);
            fields.push(['unlisted_specs', item.unlisted_specs || '']);
        } else {
            fields.push(['type', item.type]);
            fields.push(['item_id', item.item_id]);
        }

        fields.push(['quantity', item.quantity]);
        fields.push(['unit', item.unit]);
        fields.push(['unit_price', item.unit_price]);
        fields.push(['supplier', item.supplier]);

        fields.forEach(([key, value]) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `items[${idx}][${key}]`;
            input.value = value;
            container.appendChild(input);
        });
    });
}

function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

// Form validation
document.getElementById('po_form').addEventListener('submit', function(e) {
    if (cart.length === 0) {
        e.preventDefault();
        alert('Cart is empty! Please add at least one item.');
        return false;
    }
});
</script>
@endsection

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
/* Fix Select2 dropdown position when sidebar expands */
.select2-container {
    z-index: 1060 !important;
}

.select2-dropdown {
    z-index: 1061 !important;
}
</style>
@endpush

@push('scripts')
<!-- jQuery (required for Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
// Initialize Select2 for searchable dropdown
$(document).ready(function() {
    $('#item_select').select2({
        placeholder: "-- Search Sparepart or Tool --",
        allowClear: true,
        width: '100%',
        theme: 'bootstrap-5'
    });

    // Trigger onItemSelect when selection changes
    $('#item_select').on('select2:select', function (e) {
        onItemSelect();
    });

    // Also trigger on clear
    $('#item_select').on('select2:clear', function (e) {
        resetForm();
    });

    // Reposition dropdown when sidebar transitions
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.addEventListener('transitionend', function() {
            if ($('#item_select').hasClass('select2-hidden-accessible')) {
                $('#item_select').select2('close');
            }
        });
    }
});
</script>
@endpush

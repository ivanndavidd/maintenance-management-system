{{-- Sparepart Usage Section - for non-lift categories --}}
@if(!in_array($ticket->problem_category, ['lift_merah', 'lift_kuning']))
<div class="mb-3">
    <label class="form-label fw-bold">Sparepart Usage <span class="text-danger">*</span></label>
    <div class="d-flex gap-3">
        <div class="form-check">
            <input class="form-check-input" type="radio" name="sparepart_usage" id="spUsageNo_{{ $formId }}" value="no" required checked>
            <label class="form-check-label" for="spUsageNo_{{ $formId }}">No</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="sparepart_usage" id="spUsageYes_{{ $formId }}" value="yes">
            <label class="form-check-label" for="spUsageYes_{{ $formId }}">Yes</label>
        </div>
    </div>
</div>

<div id="sparepartUsageSection_{{ $formId }}" style="display:none;">
    <div class="border rounded p-3 bg-light mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-bold"><i class="fas fa-boxes me-1"></i>Spareparts Used</span>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addSparepartRow_{{ $formId }}()">
                <i class="fas fa-plus me-1"></i> Add Sparepart
            </button>
        </div>
        <div id="sparepartRows_{{ $formId }}">
            {{-- rows injected by JS --}}
        </div>
        <small class="text-muted">Add all spareparts used during this repair.</small>
    </div>
</div>

{{-- Out of Stock Reporter --}}
<div class="mb-2">
    <button type="button" class="btn btn-sm btn-outline-danger" onclick="document.getElementById('outOfStockPanel_{{ $formId }}').classList.toggle('d-none')">
        <i class="fas fa-exclamation-triangle me-1"></i> Report Out of Stock Sparepart
    </button>
    <div id="outOfStockPanel_{{ $formId }}" class="d-none mt-2 border rounded p-3 bg-light">
        <p class="mb-2 text-muted small">Select a sparepart that is out of stock to notify Supervisor & Admin:</p>
        <div class="d-flex gap-2 align-items-center">
            <select id="outOfStockSelect_{{ $formId }}" class="form-select form-select-sm">
                <option value="">-- Select Sparepart --</option>
                @foreach($spareparts->where('quantity', 0) as $sp)
                    <option value="{{ $sp->id }}" data-name="{{ $sp->sparepart_name }}">
                        {{ $sp->sparepart_name }} {{ $sp->material_code ? '('.$sp->material_code.')' : '' }} — Stock: 0 {{ $sp->unit }}
                    </option>
                @endforeach
            </select>
            <form method="POST" id="outOfStockForm_{{ $formId }}" style="display:inline;">
                @csrf
                <button type="button" class="btn btn-sm btn-danger text-nowrap"
                        onclick="submitOutOfStock_{{ $formId }}()">
                    <i class="fas fa-bell me-1"></i> Report to SPV
                </button>
            </form>
        </div>
    </div>
</div>

<script>
(function(){
    const formId = '{{ $formId }}';
    const noRadio = document.getElementById('spUsageNo_' + formId);
    const yesRadio = document.getElementById('spUsageYes_' + formId);
    const section = document.getElementById('sparepartUsageSection_' + formId);
    let rowIndex = 0;

    const spareparts = @json($spareparts->map(function($sp) {
        return [
            'id' => $sp->id,
            'name' => $sp->sparepart_name,
            'material_code' => $sp->material_code,
            'unit' => $sp->unit,
            'quantity' => $sp->quantity,
            'minimum_stock' => $sp->minimum_stock,
        ];
    }));

    function buildOptions() {
        return spareparts.map(sp => {
            const outOfStock = sp.quantity <= 0;
            return `<option value="${sp.id}" data-unit="${sp.unit}" data-stock="${sp.quantity}" data-min="${sp.minimum_stock}" ${outOfStock ? 'class="text-danger"' : ''}>
                ${sp.name}${sp.material_code ? ' ('+sp.material_code+')' : ''} — Stock: ${sp.quantity} ${sp.unit}${outOfStock ? ' ⚠ OUT OF STOCK' : ''}
            </option>`;
        }).join('');
    }

    window['addSparepartRow_' + formId] = function() {
        const container = document.getElementById('sparepartRows_' + formId);
        const idx = rowIndex++;
        const div = document.createElement('div');
        div.className = 'row g-2 mb-2 align-items-start sp-row';
        div.id = 'spRow_' + formId + '_' + idx;
        div.innerHTML = `
            <div class="col-md-6">
                <select name="spareparts[${idx}][sparepart_id]" class="form-select form-select-sm sp-select" required onchange="onSpSelect_${formId}(this, ${idx})">
                    <option value="">-- Select Sparepart --</option>
                    ${buildOptions()}
                </select>
            </div>
            <div class="col-md-3">
                <div class="input-group input-group-sm">
                    <input type="number" name="spareparts[${idx}][quantity_used]" class="form-control sp-qty" min="1" placeholder="Qty" required>
                    <span class="input-group-text sp-unit-label">-</span>
                </div>
                <small class="sp-stock-info text-muted"></small>
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="document.getElementById('spRow_${formId}_${idx}').remove()">
                    <i class="fas fa-trash"></i>
                </button>
            </div>`;
        container.appendChild(div);
    };

    window['onSpSelect_' + formId] = function(select, idx) {
        const opt = select.options[select.selectedIndex];
        const row = document.getElementById('spRow_' + formId + '_' + idx);
        const qtyInput = row.querySelector('.sp-qty');
        const unitLabel = row.querySelector('.sp-unit-label');
        const stockInfo = row.querySelector('.sp-stock-info');
        const stock = parseInt(opt.dataset.stock || 0);
        const unit = opt.dataset.unit || '-';
        unitLabel.textContent = unit;
        qtyInput.max = stock > 0 ? stock : 0;
        if (stock <= 0) {
            stockInfo.innerHTML = '<span class="text-danger">Out of stock — cannot use</span>';
            qtyInput.disabled = true;
            qtyInput.value = '';
        } else {
            stockInfo.textContent = 'Stock: ' + stock + ' ' + unit;
            qtyInput.disabled = false;
        }
    };

    [noRadio, yesRadio].forEach(r => r && r.addEventListener('change', function() {
        section.style.display = yesRadio.checked ? 'block' : 'none';
        if (yesRadio.checked && document.getElementById('sparepartRows_' + formId).children.length === 0) {
            window['addSparepartRow_' + formId]();
        }
    }));

    window['submitOutOfStock_' + formId] = function() {
        const select = document.getElementById('outOfStockSelect_' + formId);
        const spId = select.value;
        if (!spId) { alert('Please select a sparepart first.'); return; }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ url($reportOutOfStockBaseUrl) }}/' + spId;
        form.innerHTML = `@csrf`;
        document.body.appendChild(form);
        form.submit();
    };
})();
</script>
@endif

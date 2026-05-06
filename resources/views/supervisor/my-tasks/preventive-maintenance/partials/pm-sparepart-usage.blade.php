{{-- PM Sparepart Usage Section --}}
<div class="mb-3">
    <label class="form-label fw-semibold">Menggunakan Sparepart?</label>
    <div class="d-flex gap-3">
        <div class="form-check">
            <input class="form-check-input" type="radio" name="sparepart_usage" id="spUsageNo_{{ $formId }}" value="no" checked>
            <label class="form-check-label" for="spUsageNo_{{ $formId }}">Tidak</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="sparepart_usage" id="spUsageYes_{{ $formId }}" value="yes">
            <label class="form-check-label" for="spUsageYes_{{ $formId }}">Ya</label>
        </div>
    </div>
</div>

<div id="sparepartUsageSection_{{ $formId }}" style="display:none;">
    <div class="border rounded p-3 bg-light mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold"><i class="fas fa-boxes me-1"></i>Sparepart Digunakan</span>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addSpRow_{{ $formId }}()">
                <i class="fas fa-plus me-1"></i> Tambah Sparepart
            </button>
        </div>
        <div id="sparepartRows_{{ $formId }}"></div>
        <small class="text-muted">Tambahkan semua sparepart yang digunakan.</small>
    </div>
</div>

@php
$sparepartsJson = json_encode($spareparts->map(fn($sp) => [
    'id'            => $sp->id,
    'name'          => $sp->sparepart_name,
    'material_code' => $sp->material_code,
    'unit'          => $sp->unit,
    'quantity'      => $sp->quantity,
    'minimum_stock' => $sp->minimum_stock,
])->values());
@endphp
<script>
(function(){
    const fId     = '{{ $formId }}';
    const noRadio = document.getElementById('spUsageNo_'  + fId);
    const yesRadio= document.getElementById('spUsageYes_' + fId);
    const section = document.getElementById('sparepartUsageSection_' + fId);
    let rowIdx = 0;

    const spareparts = {!! $sparepartsJson !!};

    function buildOptions() {
        return spareparts.map(sp => {
            const oos = sp.quantity <= 0;
            return `<option value="${sp.id}" data-unit="${sp.unit}" data-stock="${sp.quantity}">
                ${sp.name}${sp.material_code ? ' ('+sp.material_code+')' : ''} — Stock: ${sp.quantity} ${sp.unit}${oos ? ' ⚠ OUT OF STOCK' : ''}
            </option>`;
        }).join('');
    }

    window['addSpRow_' + fId] = function() {
        const container = document.getElementById('sparepartRows_' + fId);
        const idx = rowIdx++;
        const div = document.createElement('div');
        div.className = 'row g-2 mb-2 align-items-start sp-row';
        div.id = 'spRow_' + fId + '_' + idx;
        div.innerHTML = `
            <div class="col-md-7">
                <select name="spareparts[${idx}][sparepart_id]" class="form-select form-select-sm sp-select" required
                        onchange="onSpChange_${fId}(this, ${idx})">
                    <option value="">-- Pilih Sparepart --</option>
                    ${buildOptions()}
                </select>
            </div>
            <div class="col-md-3">
                <div class="input-group input-group-sm">
                    <input type="number" name="spareparts[${idx}][quantity_used]" class="form-control sp-qty"
                           min="1" placeholder="Qty" required>
                    <span class="input-group-text sp-unit">-</span>
                </div>
                <small class="sp-stock-info text-muted d-block"></small>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-outline-danger"
                        onclick="document.getElementById('spRow_${fId}_${idx}').remove()">
                    <i class="fas fa-trash"></i>
                </button>
            </div>`;
        container.appendChild(div);
    };

    window['onSpChange_' + fId] = function(select, idx) {
        const opt   = select.options[select.selectedIndex];
        const row   = document.getElementById('spRow_' + fId + '_' + idx);
        const qty   = row.querySelector('.sp-qty');
        const unit  = row.querySelector('.sp-unit');
        const info  = row.querySelector('.sp-stock-info');
        const stock = parseInt(opt.dataset.stock || 0);

        unit.textContent = opt.dataset.unit || '-';

        if (!opt.value) { info.textContent = ''; qty.disabled = false; qty.removeAttribute('max'); return; }

        if (stock <= 0) {
            info.innerHTML = '<span class="text-danger">Out of stock</span>';
            qty.disabled = true; qty.value = ''; qty.removeAttribute('required');
        } else {
            info.textContent = 'Stock: ' + stock + ' ' + (opt.dataset.unit || '');
            qty.disabled = false; qty.max = stock; qty.setAttribute('required', 'required');
        }
    };

    [noRadio, yesRadio].forEach(r => r && r.addEventListener('change', function() {
        section.style.display = yesRadio.checked ? 'block' : 'none';
        if (yesRadio.checked && document.getElementById('sparepartRows_' + fId).children.length === 0) {
            window['addSpRow_' + fId]();
        }
    }));

    // Block submit if any out-of-stock row
    const form = document.getElementById('reportForm_' + fId);
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!yesRadio || !yesRadio.checked) return;
            const hasOos = Array.from(document.querySelectorAll('#sparepartRows_' + fId + ' .sp-qty'))
                .some(q => q.disabled);
            if (hasOos) { e.preventDefault(); alert('Hapus sparepart yang out of stock sebelum submit.'); }

            // Re-index to avoid gaps
            document.querySelectorAll('#sparepartRows_' + fId + ' .sp-row').forEach((row, i) => {
                const sel = row.querySelector('.sp-select');
                const qty = row.querySelector('.sp-qty');
                if (sel) sel.name = `spareparts[${i}][sparepart_id]`;
                if (qty) qty.name = `spareparts[${i}][quantity_used]`;
            });
        });
    }
})();
</script>

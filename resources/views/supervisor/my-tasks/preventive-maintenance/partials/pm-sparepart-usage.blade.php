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

{{-- Choices.js (load once; guard with window flag) --}}
@once
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<style>
    .choices__inner { min-height: 34px; padding: 4px 10px; font-size: 13px; }
    .choices__list--dropdown .choices__item--selectable { padding: 8px 10px; font-size: 13px; }
    .choices[data-type*=select-one] .choices__inner { padding-bottom: 4px; }
</style>
@endonce

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
// Load Choices.js once
if (!window._choicesJsLoaded) {
    window._choicesJsLoaded = true;
    const s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js';
    document.head.appendChild(s);
}

(function(){
    const fId      = '{{ $formId }}';
    const noRadio  = document.getElementById('spUsageNo_'  + fId);
    const yesRadio = document.getElementById('spUsageYes_' + fId);
    const section  = document.getElementById('sparepartUsageSection_' + fId);
    let rowIdx = 0;

    const spareparts = {!! $sparepartsJson !!};

    // Build Choices.js option items
    function buildChoiceItems() {
        return spareparts.map(sp => {
            const oos   = sp.quantity <= 0;
            const label = sp.name
                + (sp.material_code ? ' (' + sp.material_code + ')' : '')
                + ' — Stock: ' + sp.quantity + ' ' + sp.unit
                + (oos ? ' ⚠ OUT OF STOCK' : '');
            return {
                value:    String(sp.id),
                label:    label,
                disabled: false,
                customProperties: {
                    unit:     sp.unit,
                    stock:    sp.quantity,
                    oos:      oos,
                },
            };
        });
    }

    function initChoices(selectEl, idx) {
        // Wait for Choices.js to be available
        function tryInit() {
            if (typeof Choices === 'undefined') { setTimeout(tryInit, 80); return; }

            const c = new Choices(selectEl, {
                searchEnabled:        true,
                searchPlaceholderValue:'Ketik untuk cari sparepart...',
                itemSelectText:       '',
                shouldSort:           false,
                placeholder:          true,
                placeholderValue:     '-- Pilih Sparepart --',
                removeItemButton:     false,
                choices:              buildChoiceItems(),
            });

            // Store instance so we can destroy later
            selectEl._choicesInstance = c;

            // Handle change
            selectEl.addEventListener('change', function() {
                onSpChange_internal(selectEl, idx);
            });
        }
        tryInit();
    }

    function onSpChange_internal(select, idx) {
        const val   = select.value;
        const row   = document.getElementById('spRow_' + fId + '_' + idx);
        if (!row) return;
        const qty   = row.querySelector('.sp-qty');
        const unit  = row.querySelector('.sp-unit');
        const info  = row.querySelector('.sp-stock-info');

        if (!val) { info.textContent = ''; qty.disabled = false; qty.removeAttribute('max'); unit.textContent = '-'; return; }

        const sp = spareparts.find(s => String(s.id) === String(val));
        if (!sp) return;

        unit.textContent = sp.unit || '-';

        if (sp.quantity <= 0) {
            info.innerHTML = '<span class="text-danger">Out of stock</span>';
            qty.disabled = true; qty.value = ''; qty.removeAttribute('required');
        } else {
            info.textContent = 'Stock: ' + sp.quantity + ' ' + (sp.unit || '');
            qty.disabled = false; qty.max = sp.quantity; qty.setAttribute('required', 'required');
        }
    }

    window['addSpRow_' + fId] = function() {
        const container = document.getElementById('sparepartRows_' + fId);
        const idx = rowIdx++;
        const div = document.createElement('div');
        div.className = 'row g-2 mb-2 align-items-start sp-row';
        div.id = 'spRow_' + fId + '_' + idx;
        div.innerHTML = `
            <div class="col-md-7">
                <select name="spareparts[${idx}][sparepart_id]" class="form-select form-select-sm sp-select" required>
                    <option value="">-- Pilih Sparepart --</option>
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
                        onclick="removeSpRow_${fId}(${idx})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>`;
        container.appendChild(div);

        const selectEl = div.querySelector('.sp-select');
        initChoices(selectEl, idx);
    };

    window['removeSpRow_' + fId] = function(idx) {
        const row = document.getElementById('spRow_' + fId + '_' + idx);
        if (!row) return;
        const sel = row.querySelector('.sp-select');
        if (sel && sel._choicesInstance) sel._choicesInstance.destroy();
        row.remove();
    };

    [noRadio, yesRadio].forEach(r => r && r.addEventListener('change', function() {
        section.style.display = yesRadio.checked ? 'block' : 'none';
        if (yesRadio.checked && document.getElementById('sparepartRows_' + fId).children.length === 0) {
            window['addSpRow_' + fId]();
        }
    }));

    // Block submit if any out-of-stock row, re-index fields
    const form = document.getElementById('reportForm_' + fId);
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!yesRadio || !yesRadio.checked) return;
            const hasOos = Array.from(document.querySelectorAll('#sparepartRows_' + fId + ' .sp-qty'))
                .some(q => q.disabled);
            if (hasOos) { e.preventDefault(); alert('Hapus sparepart yang out of stock sebelum submit.'); return; }

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

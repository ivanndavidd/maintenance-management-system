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

@php
$sparepartsJson = json_encode($spareparts->map(function($sp) {
    return [
        'id' => $sp->id,
        'name' => $sp->sparepart_name,
        'material_code' => $sp->material_code,
        'unit' => $sp->unit,
        'quantity' => $sp->quantity,
        'minimum_stock' => $sp->minimum_stock,
    ];
})->values());
@endphp
<script>
(function(){
    const formId = '{{ $formId }}';
    const noRadio = document.getElementById('spUsageNo_' + formId);
    const yesRadio = document.getElementById('spUsageYes_' + formId);
    const section = document.getElementById('sparepartUsageSection_' + formId);
    const outOfStockUrl = '{{ url($reportOutOfStockBaseUrl) }}';
    let rowIndex = 0;

    const spareparts = {!! $sparepartsJson !!};

    function buildOptions() {
        return spareparts.map(sp => {
            const outOfStock = sp.quantity <= 0;
            return `<option value="${sp.id}" data-unit="${sp.unit}" data-stock="${sp.quantity}" data-min="${sp.minimum_stock}">
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
            <div class="col-md-3 d-flex gap-1 align-items-center">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="document.getElementById('spRow_${formId}_${idx}').remove()">
                    <i class="fas fa-trash"></i>
                </button>
                <button type="button" class="btn btn-sm btn-danger sp-report-btn d-none" title="Report out of stock to SPV"
                        onclick="reportOutOfStock_${formId}(${idx})">
                    <i class="fas fa-bell me-1"></i> Report to SPV
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
        const reportBtn = row.querySelector('.sp-report-btn');
        const stock = parseInt(opt.dataset.stock || 0);
        const unit = opt.dataset.unit || '-';
        const spId = opt.value;

        unitLabel.textContent = unit;
        reportBtn.classList.add('d-none');
        reportBtn.dataset.spId = spId;

        if (!spId) {
            stockInfo.textContent = '';
            qtyInput.disabled = false;
            qtyInput.removeAttribute('max');
            return;
        }

        if (stock <= 0) {
            stockInfo.innerHTML = '<span class="text-danger">Out of stock — please remove this row or report to SPV</span>';
            qtyInput.disabled = true;
            qtyInput.value = '';
            qtyInput.removeAttribute('required');
            reportBtn.classList.remove('d-none');
        } else {
            stockInfo.textContent = 'Stock: ' + stock + ' ' + unit;
            qtyInput.disabled = false;
            qtyInput.max = stock;
            qtyInput.setAttribute('required', 'required');
        }
    };

    window['reportOutOfStock_' + formId] = function(idx) {
        const row = document.getElementById('spRow_' + formId + '_' + idx);
        const btn = row.querySelector('.sp-report-btn');
        const spId = btn.dataset.spId;
        if (!spId) return;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';

        fetch(outOfStockUrl + '/' + spId, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        })
        .then(() => {
            btn.innerHTML = '<i class="fas fa-check me-1"></i> Reported';
            btn.classList.remove('btn-danger');
            btn.classList.add('btn-success');
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-bell me-1"></i> Report to SPV';
            alert('Failed to send notification. Please try again.');
        });
    };

    [noRadio, yesRadio].forEach(r => r && r.addEventListener('change', function() {
        section.style.display = yesRadio.checked ? 'block' : 'none';
        if (yesRadio.checked && document.getElementById('sparepartRows_' + formId).children.length === 0) {
            window['addSparepartRow_' + formId]();
        }
    }));

    // Intercept form submit — block if any out-of-stock row still exists
    const modalForm = document.querySelector('#submitReportModal form');
    if (modalForm) {
        modalForm.addEventListener('submit', function(e) {
            if (!yesRadio || !yesRadio.checked) return;

            const container = document.getElementById('sparepartRows_' + formId);
            const rows = Array.from(container.querySelectorAll('.sp-row'));

            // Check if any row is out of stock (qty disabled)
            const hasOutOfStock = rows.some(row => {
                const qty = row.querySelector('.sp-qty');
                return qty && qty.disabled;
            });

            if (hasOutOfStock) {
                e.preventDefault();
                alert('Please remove out-of-stock spareparts from the list before submitting.');
                return;
            }

            // Re-index to avoid array gaps
            rows.forEach((row, i) => {
                const spSelect = row.querySelector('.sp-select');
                const qtyInput = row.querySelector('.sp-qty');
                if (spSelect) spSelect.name = `spareparts[${i}][sparepart_id]`;
                if (qtyInput) qtyInput.name = `spareparts[${i}][quantity_used]`;
            });
        });
    }
})();
</script>
@endif

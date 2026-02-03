@extends('layouts.admin')

@section('page-title', 'Stock Opname Details')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h4><i class="fas fa-clipboard-check"></i> Stock Opname Details</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('supervisor.my-tasks.stock-opname') }}">My Assignments</a></li>
                <li class="breadcrumb-item active">{{ $schedule->schedule_code }}</li>
            </ol>
        </nav>
    </div>

    {{-- Schedule Info & Statistics --}}
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header {{ $schedule->isOverdue() ? 'bg-danger text-white' : 'bg-primary text-white' }}">
                    <h5 class="mb-0">{{ $schedule->schedule_code }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Execution Date</th>
                                    <td>{{ $schedule->execution_date->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if($schedule->status == 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($schedule->isOverdue())
                                            <span class="badge bg-danger">Overdue</span>
                                        @else
                                            <span class="badge bg-primary">Active</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created By</th>
                                    <td>{{ $schedule->createdByUser->name ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Item Types</th>
                                    <td>
                                        @if($schedule->include_spareparts)
                                            <span class="badge bg-info me-1">Spareparts</span>
                                        @endif
                                        @if($schedule->include_tools)
                                            <span class="badge bg-warning me-1">Tools</span>
                                        @endif
                                        @if($schedule->include_assets)
                                            <span class="badge bg-success me-1">Assets</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Assigned Users</th>
                                    <td>{{ $schedule->userAssignments->count() }} users</td>
                                </tr>
                                <tr>
                                    <th>Days Remaining</th>
                                    <td>
                                        @if($schedule->getDaysRemaining() > 0)
                                            <span class="text-success">{{ $schedule->getDaysRemaining() }} days</span>
                                        @elseif($schedule->getDaysRemaining() == 0)
                                            <span class="text-warning">Last day!</span>
                                        @else
                                            <span class="text-danger">{{ abs($schedule->getDaysRemaining()) }} days overdue</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($schedule->notes)
                    <div class="alert alert-info">
                        <strong>Notes:</strong> {{ $schedule->notes }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Statistics Card --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-chart-pie"></i> Progress</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <th>Total Items</th>
                            <td class="text-end">{{ $stats['total_items'] }}</td>
                        </tr>
                        <tr>
                            <th>Completed</th>
                            <td class="text-end text-success">{{ $stats['completed_items'] }}</td>
                        </tr>
                        <tr>
                            <th>Pending</th>
                            <td class="text-end text-warning">{{ $stats['pending_items'] }}</td>
                        </tr>
                        @if($stats['cancelled_items'] > 0)
                        <tr>
                            <th>Cancelled</th>
                            <td class="text-end text-danger">{{ $stats['cancelled_items'] }}</td>
                        </tr>
                        @endif
                    </table>

                    <div class="progress mt-3" style="height: 25px;">
                        <div class="progress-bar bg-success"
                            style="width: {{ $stats['progress_percentage'] }}%"
                            role="progressbar">
                            {{ $stats['progress_percentage'] }}%
                        </div>
                    </div>
                    <small class="text-muted d-block text-center mt-1">Overall Progress</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Items Table with Filters --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-box"></i> Scheduled Items ({{ $schedule->total_items }})</h5>
            <small class="text-muted">Showing {{ $scheduleItems->firstItem() ?? 0 }}-{{ $scheduleItems->lastItem() ?? 0 }} of {{ $scheduleItems->total() }}</small>
        </div>
        <div class="card-body">
            {{-- Filter Form --}}
            <form method="GET" action="{{ route('supervisor.my-tasks.stock-opname.show', $schedule->id) }}" class="mb-3">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Item Type</label>
                        <select name="item_type" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            <option value="sparepart" {{ request('item_type') == 'sparepart' ? 'selected' : '' }}>Sparepart</option>
                            <option value="tool" {{ request('item_type') == 'tool' ? 'selected' : '' }}>Tool</option>
                            <option value="asset" {{ request('item_type') == 'asset' ? 'selected' : '' }}>Asset</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm me-2">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('supervisor.my-tasks.stock-opname.show', $schedule->id) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            {{-- Batch Save Button & Excel Import/Export --}}
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-success" id="saveAllBtn" style="display: none;">
                        <i class="fas fa-save"></i> Save All Changes (<span id="changedCount">0</span>)
                    </button>
                    <button type="button" class="btn btn-secondary" id="cancelAllBtn" style="display: none;">
                        <i class="fas fa-times"></i> Cancel All
                    </button>

                    {{-- Excel Export/Import Buttons --}}
                    <div class="btn-group">
                        <a href="{{ route('supervisor.my-tasks.stock-opname.export-template', $schedule->id) }}" class="btn btn-primary">
                            <i class="fas fa-download"></i> Download Template
                        </a>
                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="fas fa-upload"></i> Import Excel
                        </button>
                    </div>
                </div>
                <div>
                    <small class="text-muted" id="editInfo"></small>
                </div>
            </div>

            {{-- Items Table --}}
            @if($scheduleItems->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover" id="opnameTable">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="10%">Type</th>
                                <th width="15%">Item Code</th>
                                <th width="25%">Item Name</th>
                                <th width="12%">Physical Qty</th>
                                <th width="8%">Discrepancy</th>
                                <th width="10%">Status</th>
                                <th width="15%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($scheduleItems as $index => $scheduleItem)
                            <tr data-item-id="{{ $scheduleItem->id }}" data-status="{{ $scheduleItem->execution_status }}">
                                <td>{{ $scheduleItems->firstItem() + $index }}</td>
                                <td>
                                    @if($scheduleItem->item_type == 'sparepart')
                                        <span class="badge bg-info">Sparepart</span>
                                    @elseif($scheduleItem->item_type == 'tool')
                                        <span class="badge bg-warning">Tool</span>
                                    @else
                                        <span class="badge bg-success">Asset</span>
                                    @endif
                                </td>
                                <td>{{ $scheduleItem->getItemCode() }}</td>
                                <td>{{ $scheduleItem->getItemName() }}</td>
                                <td class="physical-qty-cell">
                                    @if($scheduleItem->execution_status == 'completed')
                                        {{-- Show value with conditional edit for negative discrepancy --}}
                                        @if($scheduleItem->discrepancy_qty < 0)
                                            <span class="display-value" style="display: none;">{{ $scheduleItem->physical_quantity }}</span>
                                            <input type="number"
                                                class="form-control form-control-sm edit-input"
                                                min="0"
                                                step="1"
                                                value="{{ $scheduleItem->physical_quantity }}"
                                                placeholder="Enter qty">
                                        @else
                                            <span class="display-value">{{ $scheduleItem->physical_quantity }}</span>
                                        @endif
                                    @else
                                        {{-- Pending items: Show input directly --}}
                                        <span class="display-value" style="display: none;">-</span>
                                        <input type="number"
                                            class="form-control form-control-sm edit-input"
                                            min="0"
                                            step="1"
                                            placeholder="Enter qty">
                                    @endif
                                </td>
                                <td class="discrepancy-cell">
                                    @if($scheduleItem->execution_status == 'completed')
                                        @if($scheduleItem->discrepancy_qty > 0)
                                            <span class="text-success">+{{ $scheduleItem->discrepancy_qty }}</span>
                                        @elseif($scheduleItem->discrepancy_qty < 0)
                                            <span class="text-danger">{{ $scheduleItem->discrepancy_qty }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="status-cell">
                                    @if($scheduleItem->execution_status == 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($scheduleItem->execution_status == 'in_progress')
                                        <span class="badge bg-info">In Progress</span>
                                    @elseif($scheduleItem->execution_status == 'completed')
                                        <span class="badge bg-success">Completed</span>
                                    @else
                                        <span class="badge bg-danger">Cancelled</span>
                                    @endif
                                </td>
                                <td class="action-cell">
                                    @if($scheduleItem->execution_status == 'completed')
                                        @if($scheduleItem->discrepancy_qty < 0)
                                            {{-- Negative discrepancy: show clear button --}}
                                            <button type="button" class="btn btn-sm btn-secondary clear-btn" title="Clear">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @else
                                            {{-- Positive or zero: locked --}}
                                            <i class="fas fa-check-circle text-success"></i>
                                        @endif
                                    @else
                                        {{-- Pending: show clear button --}}
                                        <button type="button" class="btn btn-sm btn-secondary clear-btn" title="Clear">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Links --}}
                @if($scheduleItems->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <small class="text-muted">
                                Showing {{ $scheduleItems->firstItem() }} to {{ $scheduleItems->lastItem() }} of {{ $scheduleItems->total() }} items
                            </small>
                        </div>
                        <div>
                            {{ $scheduleItems->links() }}
                        </div>
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No items found with current filters.</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Import Excel Modal --}}
<div class="modal fade" id="importModal" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="importExcelForm" enctype="multipart/form-data" data-no-loading="true">
                @csrf
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-upload"></i> Import Stock Opname dari Excel
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h6 class="alert-heading"><i class="fas fa-info-circle"></i> Cara Import:</h6>
                        <ol class="mb-0 small">
                            <li>Download template dengan klik "Download Template"</li>
                            <li>Isi kolom <strong>"Physical Qty"</strong> dengan jumlah fisik yang dihitung</li>
                            <li>Isi kolom <strong>"Notes"</strong> jika ada catatan (opsional)</li>
                            <li>Upload file Excel yang sudah diisi</li>
                            <li>Klik <strong>"Save All Changes"</strong> untuk menyimpan</li>
                        </ol>
                    </div>

                    <div class="mb-3">
                        <label for="excel_file" class="form-label">Pilih File Excel <span class="text-danger">*</span></label>
                        <input type="file" class="form-control @error('excel_file') is-invalid @enderror"
                               id="excel_file" name="excel_file" accept=".xlsx,.xls" required>
                        @error('excel_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Format: .xlsx atau .xls, Max 10MB</small>
                    </div>

                    <div id="importResult" class="d-none"></div>

                    <div class="alert alert-warning small mb-0">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Perhatian:</strong> Data dari Excel akan mengisi form. Klik "Save All Changes" untuk menyimpan.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-info" id="importBtn">
                        <i class="fas fa-upload"></i> Upload & Isi Form
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const scheduleId = {{ $schedule->id }};
    const storageKey = `opname_inputs_${scheduleId}`;
    const changedItems = new Map(); // Store changed items: itemId => {physical_quantity, notes}

    // Load saved inputs from localStorage
    function loadSavedInputs() {
        const saved = localStorage.getItem(storageKey);
        if (saved) {
            try {
                const savedData = JSON.parse(saved);

                // Load ALL saved data into changedItems (not just current page)
                Object.entries(savedData).forEach(([itemId, data]) => {
                    changedItems.set(itemId, data);

                    // If item exists on current page, populate the input
                    const row = document.querySelector(`tr[data-item-id="${itemId}"]`);
                    if (row) {
                        const editInput = row.querySelector('.edit-input');
                        if (editInput && data.physical_quantity !== null) {
                            editInput.value = data.physical_quantity;
                            row.classList.add('table-info');
                        }
                    }
                });

                updateBatchButtons();
            } catch (e) {
                console.error('Error loading saved inputs:', e);
            }
        }
    }

    // Save inputs to localStorage
    function saveToLocalStorage() {
        const dataToSave = {};
        changedItems.forEach((data, itemId) => {
            dataToSave[itemId] = data;
        });
        localStorage.setItem(storageKey, JSON.stringify(dataToSave));
    }

    // Clear localStorage for this schedule
    function clearLocalStorage() {
        localStorage.removeItem(storageKey);
    }

    // Update batch button visibility
    function updateBatchButtons() {
        const count = changedItems.size;
        const saveAllBtn = document.getElementById('saveAllBtn');
        const cancelAllBtn = document.getElementById('cancelAllBtn');
        const changedCount = document.getElementById('changedCount');
        const editInfo = document.getElementById('editInfo');

        if (count > 0) {
            saveAllBtn.style.display = 'inline-block';
            cancelAllBtn.style.display = 'inline-block';
            changedCount.textContent = count;
            editInfo.textContent = `${count} item(s) ready to save`;
        } else {
            saveAllBtn.style.display = 'none';
            cancelAllBtn.style.display = 'none';
            editInfo.textContent = '';
        }
    }

    // Load saved inputs on page load
    loadSavedInputs();

    // Auto-detect input changes on all edit-input fields
    document.querySelectorAll('.edit-input').forEach(input => {
        // On input change
        input.addEventListener('input', function() {
            const row = this.closest('tr');
            const itemId = row.dataset.itemId;
            const physicalQty = parseFloat(this.value);

            // Only add to batch if value is valid
            if (this.value && physicalQty >= 0) {
                // Store changed item
                changedItems.set(itemId, {
                    physical_quantity: physicalQty,
                    notes: ''
                });

                // Mark row as changed
                row.classList.add('table-info');
                updateBatchButtons();

                // Save to localStorage
                saveToLocalStorage();
            } else {
                // Remove from batch if invalid
                if (changedItems.has(itemId)) {
                    changedItems.delete(itemId);
                    row.classList.remove('table-info');
                    updateBatchButtons();

                    // Update localStorage
                    saveToLocalStorage();
                }
            }
        });
    });

    // Clear button click (reset input)
    document.querySelectorAll('.clear-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const itemId = row.dataset.itemId;
            const editInput = row.querySelector('.edit-input');

            // Reset input
            editInput.value = '';

            // Remove from batch queue
            if (changedItems.has(itemId)) {
                changedItems.delete(itemId);
                row.classList.remove('table-info');
                updateBatchButtons();

                // Update localStorage
                saveToLocalStorage();
            }

            editInput.focus();
        });
    });

    // Save All button
    document.getElementById('saveAllBtn').addEventListener('click', function() {
        if (changedItems.size === 0) {
            alert('No changes to save');
            return;
        }

        const confirmMsg = `Are you sure you want to save ${changedItems.size} item(s)?\n\nThis will update the physical quantity and calculate discrepancies.`;
        if (!confirm(confirmMsg)) {
            return;
        }

        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        // Prepare batch data
        const batchData = Array.from(changedItems.entries()).map(([itemId, data]) => ({
            item_id: itemId,
            ...data
        }));

        console.log('Sending batch data:', batchData);

        // Send batch request
        fetch('/supervisor/my-tasks/stock-opname/execute-batch', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ items: batchData })
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                // Clear localStorage before reload
                clearLocalStorage();
                // Reload page to show updated data
                window.location.reload();
            } else {
                console.error('Errors:', data.errors);
                let errorMsg = data.message || 'Failed to save items';
                if (data.errors && data.errors.length > 0) {
                    errorMsg += '\n\nDetails:\n' + data.errors.join('\n');
                }
                alert(errorMsg);
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-save"></i> Save All Changes (<span id="changedCount">' + changedItems.size + '</span>)';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.\n\nError: ' + error.message);
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-save"></i> Save All Changes (<span id="changedCount">' + changedItems.size + '</span>)';
        });
    });

    // Cancel All button
    document.getElementById('cancelAllBtn').addEventListener('click', function() {
        if (!confirm('Cancel all changes?')) {
            return;
        }

        // Reset all changed rows
        changedItems.forEach((data, itemId) => {
            const row = document.querySelector(`tr[data-item-id="${itemId}"]`);
            if (row) {
                const editInput = row.querySelector('.edit-input');
                editInput.value = '';
                row.classList.remove('table-info');
            }
        });

        changedItems.clear();
        updateBatchButtons();

        // Clear localStorage
        clearLocalStorage();
    });

    // Allow Enter key to move to next input
    document.querySelectorAll('.edit-input').forEach((input, index, inputs) => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                // Move to next input field
                if (index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            }
        });
    });

    // Handle Excel Import via AJAX
    const importForm = document.getElementById('importExcelForm');
    const importBtn = document.getElementById('importBtn');
    const importResult = document.getElementById('importResult');

    importForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const fileInput = document.getElementById('excel_file');
        if (!fileInput.files.length) {
            alert('Pilih file Excel terlebih dahulu');
            return;
        }

        // Disable button and show loading
        importBtn.disabled = true;
        importBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        importResult.classList.add('d-none');

        const formData = new FormData();
        formData.append('excel_file', fileInput.files[0]);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        fetch('{{ route('supervisor.my-tasks.stock-opname.import-excel', $schedule->id) }}', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Fill form fields with imported data
                let filledCount = 0;
                data.data.forEach(item => {
                    // Add to changedItems Map
                    changedItems.set(String(item.item_id), {
                        physical_quantity: item.physical_quantity,
                        notes: item.notes || ''
                    });

                    // Try to fill the input on current page
                    const row = document.querySelector(`tr[data-item-id="${item.item_id}"]`);
                    if (row) {
                        const editInput = row.querySelector('.edit-input');
                        if (editInput) {
                            editInput.value = item.physical_quantity;
                            row.classList.add('table-info');
                            filledCount++;
                        }
                    }
                });

                // Save to localStorage
                saveToLocalStorage();
                updateBatchButtons();

                // Show success message
                importResult.classList.remove('d-none');
                importResult.className = 'alert alert-success';
                importResult.innerHTML = `
                    <i class="fas fa-check-circle"></i>
                    <strong>Berhasil!</strong> ${data.success_count} item telah dibaca dari Excel.
                    ${filledCount} item terisi di halaman ini.
                    ${data.error_count > 0 ? `<br><small class="text-danger">${data.error_count} error ditemukan.</small>` : ''}
                    <br><small>Klik <strong>"Save All Changes"</strong> untuk menyimpan.</small>
                `;

                // Close modal after 2 seconds
                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('importModal'));
                    if (modal) {
                        modal.hide();
                    }
                    // Reset file input
                    fileInput.value = '';
                }, 2000);

            } else {
                importResult.classList.remove('d-none');
                importResult.className = 'alert alert-danger';
                importResult.innerHTML = `<i class="fas fa-times-circle"></i> <strong>Error:</strong> ${data.message}`;
            }

            // Reset button
            importBtn.disabled = false;
            importBtn.innerHTML = '<i class="fas fa-upload"></i> Upload & Isi Form';
        })
        .catch(error => {
            console.error('Import error:', error);
            importResult.classList.remove('d-none');
            importResult.className = 'alert alert-danger';
            importResult.innerHTML = `<i class="fas fa-times-circle"></i> <strong>Error:</strong> ${error.message}`;

            importBtn.disabled = false;
            importBtn.innerHTML = '<i class="fas fa-upload"></i> Upload & Isi Form';
        });
    });
});
</script>
@endpush
@endsection

@extends('layouts.user')

@section('page-title', 'Execute Stock Opname')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2><i class="fas fa-clipboard-check"></i> Execute Stock Opname</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('user.stock-opname.index') }}">My Assignments</a></li>
                <li class="breadcrumb-item"><a href="{{ route('user.stock-opname.show', $schedule->id) }}">{{ $schedule->schedule_code }}</a></li>
                <li class="breadcrumb-item active">Execute</li>
            </ol>
        </nav>
    </div>

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-clipboard-check"></i> Item Information</h5>
                </div>
                <div class="card-body">
                    @if($itemData)
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%" class="bg-light">Item Type</th>
                                <td>
                                    @if($item->item_type == 'sparepart')
                                        <span class="badge bg-info">Sparepart</span>
                                    @elseif($item->item_type == 'tool')
                                        <span class="badge bg-warning">Tool</span>
                                    @else
                                        <span class="badge bg-success">Asset</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Item Code</th>
                                <td><strong>{{ $item->getItemCode() }}</strong></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Item Name</th>
                                <td><strong>{{ $item->getItemName() }}</strong></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Location</th>
                                <td>{{ $item->getItemLocation() }}</td>
                            </tr>
                            @if($item->item_type == 'sparepart')
                                <tr>
                                    <th class="bg-light">Current System Quantity</th>
                                    <td class="fs-4 fw-bold text-primary">{{ $itemData->quantity }} {{ $itemData->unit }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Unit Price</th>
                                    <td>Rp {{ number_format($itemData->price, 0, ',', '.') }}</td>
                                </tr>
                            @elseif($item->item_type == 'tool')
                                <tr>
                                    <th class="bg-light">Current System Quantity</th>
                                    <td class="fs-4 fw-bold text-primary">{{ $itemData->quantity }} {{ $itemData->unit }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Unit Price</th>
                                    <td>Rp {{ number_format($itemData->price, 0, ',', '.') }}</td>
                                </tr>
                            @else
                                <tr>
                                    <th class="bg-light">Quantity</th>
                                    <td class="fs-4 fw-bold text-primary">1 unit</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Purchase Price</th>
                                    <td>Rp {{ number_format($itemData->purchase_price ?? 0, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Condition</th>
                                    <td>{{ ucfirst($itemData->condition ?? '-') }}</td>
                                </tr>
                            @endif
                        </table>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> Item data not found.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Execution Form --}}
            <div class="card mt-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Input Physical Count</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('user.stock-opname.execute', [$schedule->id, $item->id]) }}" method="POST" id="executeForm">
                        @csrf

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Please count the physical quantity of this item and input the result below.
                        </div>

                        {{-- Physical Quantity --}}
                        <div class="mb-4">
                            <label for="physical_quantity" class="form-label fs-5">
                                Physical Quantity <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                name="physical_quantity"
                                id="physical_quantity"
                                class="form-control form-control-lg @error('physical_quantity') is-invalid @enderror"
                                value="{{ old('physical_quantity') }}"
                                min="0"
                                required
                                autofocus>
                            @error('physical_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Enter the actual quantity counted</small>
                        </div>

                        {{-- Discrepancy Preview --}}
                        <div id="discrepancyPreview" style="display: none;" class="mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <strong>Discrepancy Preview</strong>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <small class="text-muted">System Qty</small>
                                            <div class="fs-5 fw-bold" id="systemQty">
                                                @if($item->item_type == 'sparepart' || $item->item_type == 'tool')
                                                    {{ $itemData->quantity }}
                                                @else
                                                    1
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">Physical Qty</small>
                                            <div class="fs-5 fw-bold text-primary" id="physicalQty">0</div>
                                        </div>
                                        <div class="col-md-4">
                                            <small class="text-muted">Discrepancy</small>
                                            <div class="fs-5 fw-bold" id="discrepancyQty">0</div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="text-center">
                                        <small class="text-muted">Estimated Discrepancy Value</small>
                                        <div class="fs-4 fw-bold" id="discrepancyValue">Rp 0</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="mb-4">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea name="notes"
                                id="notes"
                                class="form-control @error('notes') is-invalid @enderror"
                                rows="3"
                                placeholder="Add any notes or observations...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">e.g., condition, location details, reasons for discrepancy</small>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check"></i> Submit Opname
                            </button>
                            <a href="{{ route('user.stock-opname.show', $schedule->id) }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Instructions Sidebar --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Instructions</h6>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">How to Execute:</h6>
                    <ol class="small">
                        <li>Go to the item's physical location</li>
                        <li>Count the actual quantity carefully</li>
                        <li>Input the counted quantity in the form</li>
                        <li>Add notes if needed (especially for discrepancies)</li>
                        <li>Submit the form</li>
                    </ol>

                    <hr>

                    <h6 class="fw-bold">Important Notes:</h6>
                    <ul class="small">
                        <li>Double-check your count before submitting</li>
                        <li>If there's a discrepancy, add notes explaining why</li>
                        <li>System will automatically calculate discrepancy value</li>
                        <li>Once submitted, you cannot change it</li>
                        <li>Item will be marked as completed for all users</li>
                    </ul>

                    <hr>

                    <h6 class="fw-bold">Discrepancy:</h6>
                    <div class="small">
                        <p><strong>Positive (+):</strong> Physical quantity is more than system (surplus)</p>
                        <p><strong>Negative (-):</strong> Physical quantity is less than system (shortage)</p>
                        <p><strong>Zero (0):</strong> Physical matches system (accurate)</p>
                    </div>
                </div>
            </div>

            {{-- Schedule Info --}}
            <div class="card mt-3">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-calendar"></i> Schedule Info</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <th>Code</th>
                            <td>{{ $schedule->schedule_code }}</td>
                        </tr>
                        <tr>
                            <th>Execution Date</th>
                            <td>{{ $schedule->execution_date->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <th>Days Left</th>
                            <td>
                                @if($schedule->getDaysRemaining() > 0)
                                    <span class="text-success">{{ $schedule->getDaysRemaining() }} days</span>
                                @elseif($schedule->getDaysRemaining() == 0)
                                    <span class="text-warning">Last day!</span>
                                @else
                                    <span class="text-danger">Overdue</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const physicalQtyInput = document.getElementById('physical_quantity');
    const discrepancyPreview = document.getElementById('discrepancyPreview');
    const systemQtyDisplay = document.getElementById('systemQty');
    const physicalQtyDisplay = document.getElementById('physicalQty');
    const discrepancyQtyDisplay = document.getElementById('discrepancyQty');
    const discrepancyValueDisplay = document.getElementById('discrepancyValue');

    const systemQty = {{ $item->item_type == 'asset' ? 1 : ($itemData->quantity ?? 0) }};
    const itemPrice = {{ $item->item_type == 'sparepart' ? ($itemData->price ?? 0) : ($item->item_type == 'tool' ? ($itemData->price ?? 0) : ($itemData->purchase_price ?? 0)) }};

    physicalQtyInput.addEventListener('input', function() {
        const physicalQty = parseInt(this.value) || 0;

        if (this.value !== '') {
            discrepancyPreview.style.display = 'block';

            const discrepancyQty = physicalQty - systemQty;
            const discrepancyValue = discrepancyQty * itemPrice;

            physicalQtyDisplay.textContent = physicalQty;
            discrepancyQtyDisplay.textContent = (discrepancyQty > 0 ? '+' : '') + discrepancyQty;

            // Color coding for discrepancy
            if (discrepancyQty > 0) {
                discrepancyQtyDisplay.classList.remove('text-danger');
                discrepancyQtyDisplay.classList.add('text-success');
                discrepancyValueDisplay.classList.remove('text-danger');
                discrepancyValueDisplay.classList.add('text-success');
            } else if (discrepancyQty < 0) {
                discrepancyQtyDisplay.classList.remove('text-success');
                discrepancyQtyDisplay.classList.add('text-danger');
                discrepancyValueDisplay.classList.remove('text-success');
                discrepancyValueDisplay.classList.add('text-danger');
            } else {
                discrepancyQtyDisplay.classList.remove('text-success', 'text-danger');
                discrepancyValueDisplay.classList.remove('text-success', 'text-danger');
            }

            // Format currency
            const formattedValue = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(Math.abs(discrepancyValue));

            discrepancyValueDisplay.textContent = (discrepancyValue < 0 ? '-' : (discrepancyValue > 0 ? '+' : '')) + formattedValue;
        } else {
            discrepancyPreview.style.display = 'none';
        }
    });

    // Form validation
    const executeForm = document.getElementById('executeForm');
    executeForm.addEventListener('submit', function(e) {
        const physicalQty = parseInt(physicalQtyInput.value);

        if (isNaN(physicalQty) || physicalQty < 0) {
            e.preventDefault();
            alert('Please enter a valid physical quantity (0 or greater).');
            physicalQtyInput.focus();
            return false;
        }

        // Confirmation for large discrepancies
        const discrepancyQty = Math.abs(physicalQty - systemQty);
        const discrepancyPercent = systemQty > 0 ? (discrepancyQty / systemQty) * 100 : 0;

        if (discrepancyPercent > 20) {
            const confirmed = confirm(
                'Warning: Large discrepancy detected!\n\n' +
                'System Quantity: ' + systemQty + '\n' +
                'Physical Quantity: ' + physicalQty + '\n' +
                'Discrepancy: ' + (physicalQty - systemQty) + ' (' + discrepancyPercent.toFixed(1) + '%)\n\n' +
                'Are you sure this count is correct?'
            );

            if (!confirmed) {
                e.preventDefault();
                return false;
            }
        }
    });
});
</script>
@endpush
@endsection

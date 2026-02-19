<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sparepart;
use App\Models\PurchaseOrder;
use App\Models\StockOpnameSchedule;
use App\Models\StockOpnameExecution;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SparepartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Sparepart::with(['addedByUser', 'opnameUser', 'verifiedUser']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('sparepart_name', 'like', "%{$search}%")
                  ->orWhere('sparepart_id', 'like', "%{$search}%")
                  ->orWhere('material_code', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        // Equipment type filter
        if ($request->filled('equipment_type')) {
            $query->where('equipment_type', $request->equipment_type);
        }

        // Stock status filter
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'low':
                    $query->whereColumn('quantity', '<=', 'minimum_stock')
                          ->where('quantity', '>', 0);
                    break;
                case 'normal':
                    $query->whereColumn('quantity', '>', 'minimum_stock');
                    break;
                case 'out':
                    $query->where('quantity', '<=', 0);
                    break;
            }
        }

        // Location filter
        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        $spareparts = $query->latest()->paginate(15)->appends($request->except('page'));

        // Get unique equipment types from database
        $equipmentTypes = Sparepart::whereNotNull('equipment_type')
            ->distinct()
            ->pluck('equipment_type')
            ->sort()
            ->values();

        // Get unique locations from database
        $locations = Sparepart::whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->pluck('location')
            ->sort()
            ->values();

        // Calculate total statistics from all data (not just current page)
        $allSpareparts = Sparepart::all();
        $stats = [
            'total' => $allSpareparts->count(),
            'low_stock' => $allSpareparts->filter(function($sp) {
                return $sp->quantity > 0 && $sp->quantity <= $sp->minimum_stock;
            })->count(),
            'out_of_stock' => $allSpareparts->where('quantity', '<=', 0)->count(),
            'total_value' => $allSpareparts->sum(function($sp) {
                return $sp->quantity * $sp->parts_price;
            })
        ];

        return view('admin.spareparts.index', compact('spareparts', 'equipmentTypes', 'locations', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Generate sparepart ID
        $sparepartId = Sparepart::generateSparepartId();

        // Get pending unlisted sparepart items from received POs that haven't been registered yet
        $pendingPoItems = \App\Models\PurchaseOrderItem::with('purchaseOrder')
            ->where('is_unlisted', true)
            ->where('item_type', \App\Models\Sparepart::class)
            ->where('compliance_status', 'compliant')
            ->whereNull('registered_to_master_at')
            ->whereHas('purchaseOrder', fn($q) => $q->where('status', 'received'))
            ->get();

        // If coming from a specific PO item, pre-select it
        $selectedPoItem = null;
        if ($request->filled('from_po_item')) {
            $selectedPoItem = $pendingPoItems->firstWhere('id', $request->from_po_item);
        }

        return view('admin.spareparts.create', compact('sparepartId', 'pendingPoItems', 'selectedPoItem'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipment_type' => 'nullable|string|max:255',
            'sparepart_name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'minimum_stock' => 'required|integer|min:0',
            'vulnerability' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'parts_price' => 'required|numeric|min:0',
            'item_type' => 'nullable|string|max:255',
            'path' => 'nullable|string|max:255',
        ]);

        // Generate sparepart ID
        $validated['sparepart_id'] = Sparepart::generateSparepartId();
        $validated['add_part_by'] = auth()->id();

        Sparepart::create($validated);

        // If created from a PO unlisted item, mark that item as registered
        if ($request->filled('from_po_item')) {
            \App\Models\PurchaseOrderItem::where('id', $request->from_po_item)
                ->where('is_unlisted', true)
                ->whereNull('registered_to_master_at')
                ->update(['registered_to_master_at' => now()]);
        }

        return redirect()
            ->route('admin.spareparts.index')
            ->with('success', 'Sparepart created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Sparepart $sparepart)
    {
        $sparepart->load(['addedByUser', 'opnameUser', 'verifiedUser']);

        return view('admin.spareparts.show', compact('sparepart'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sparepart $sparepart)
    {
        return view('admin.spareparts.edit', compact('sparepart'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sparepart $sparepart)
    {
        $validated = $request->validate([
            'equipment_type' => 'nullable|string|max:255',
            'sparepart_name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'minimum_stock' => 'required|integer|min:0',
            'vulnerability' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'parts_price' => 'required|numeric|min:0',
            'item_type' => 'nullable|string|max:255',
            'path' => 'nullable|string|max:255',
        ]);

        $sparepart->update($validated);

        return redirect()
            ->route('admin.spareparts.index')
            ->with('success', 'Sparepart updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sparepart $sparepart)
    {
        $sparepart->delete();

        return redirect()
            ->route('admin.spareparts.index')
            ->with('success', 'Sparepart deleted successfully!');
    }

    // ===== PURCHASE ORDER MANAGEMENT =====
    public function purchaseOrders()
    {
        $purchaseOrders = PurchaseOrder::with(['item', 'orderedByUser', 'receivedByUser'])
            ->where('item_type', Sparepart::class)
            ->latest()
            ->paginate(15);

        return view('admin.spareparts.purchase-orders', compact('purchaseOrders'));
    }

    public function createPurchaseOrder()
    {
        $spareparts = Sparepart::where('quantity', '<=', \DB::raw('minimum_stock'))
            ->orWhereRaw('quantity < minimum_stock * 1.5')
            ->get();

        $allSpareparts = Sparepart::orderBy('sparepart_name')->get();
        $poNumber = PurchaseOrder::generatePONumber();

        return view('admin.spareparts.create-purchase-order', compact('spareparts', 'allSpareparts', 'poNumber'));
    }

    public function storePurchaseOrder(Request $request)
    {
        $validated = $request->validate([
            'sparepart_id' => 'required|exists:spareparts,id',
            'quantity_ordered' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'supplier' => 'required|string|max:255',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string',
        ]);

        $validated['po_number'] = PurchaseOrder::generatePONumber();
        $validated['item_id'] = $validated['sparepart_id'];
        $validated['item_type'] = Sparepart::class;
        $validated['total_price'] = $validated['quantity_ordered'] * $validated['unit_price'];
        $validated['ordered_by'] = auth()->id();
        $validated['status'] = 'ordered';

        unset($validated['sparepart_id']);

        PurchaseOrder::create($validated);

        return redirect()
            ->route('admin.spareparts.purchase-orders')
            ->with('success', 'Purchase Order created successfully!');
    }

    public function receivePurchaseOrder(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'quantity_received' => 'required|integer|min:1|max:' . $purchaseOrder->getRemainingQuantity(),
            'add_to_stock' => 'nullable|boolean',
        ]);

        $addToStock = $request->has('add_to_stock') && $request->add_to_stock == '1';

        $purchaseOrder->receiveItems($validated['quantity_received'], auth()->id(), $addToStock);

        $message = 'Items received successfully!';
        if ($addToStock) {
            $message .= ' Stock inventory has been updated.';
        } else {
            $message .= ' Stock inventory was NOT updated.';
        }

        return redirect()
            ->route('admin.spareparts.purchase-orders')
            ->with('success', $message);
    }

    // ===== STOCK OPNAME MANAGEMENT =====
    public function opnameDashboard()
    {
        $stats = [
            'total_schedules' => StockOpnameSchedule::where('status', 'active')->where('item_type', 'sparepart')->count(),
            'overdue_schedules' => StockOpnameSchedule::where('status', 'active')
                ->where('item_type', 'sparepart')
                ->whereDate('scheduled_date', '<', now())
                ->count(),
            'total_executions' => StockOpnameExecution::where('item_type', 'sparepart')->count(),
            'missed_executions' => StockOpnameExecution::where('item_type', 'sparepart')->where('is_missed', true)->count(),
        ];

        $recentExecutions = StockOpnameExecution::with(['executedByUser', 'verifiedByUser'])
            ->where('item_type', 'sparepart')
            ->latest('execution_date')
            ->limit(10)
            ->get();

        $upcomingSchedules = StockOpnameSchedule::with(['assignedUser'])
            ->where('status', 'active')
            ->where('item_type', 'sparepart')
            ->orderBy('scheduled_date')
            ->limit(10)
            ->get();

        // Calculate overall accuracy
        $executions = StockOpnameExecution::where('item_type', 'sparepart')->get();
        $totalAccuracy = 0;
        foreach ($executions as $execution) {
            $totalAccuracy += $execution->getAccuracyPercentage();
        }
        $stats['average_accuracy'] = $executions->count() > 0 ? round($totalAccuracy / $executions->count(), 2) : 100;

        return view('admin.spareparts.opname-dashboard', compact('stats', 'recentExecutions', 'upcomingSchedules'));
    }

    public function opnameSchedules()
    {
        $schedules = StockOpnameSchedule::with(['assignedUser', 'createdByUser'])
            ->where('item_type', 'sparepart')
            ->latest()
            ->paginate(15);

        return view('admin.spareparts.opname-schedules', compact('schedules'));
    }

    public function createOpnameSchedule()
    {
        $staffMaintenances = User::role('staff_maintenance')->get();
        $scheduleCode = StockOpnameSchedule::generateScheduleCode();

        return view('admin.spareparts.create-opname-schedule', compact('staffMaintenances', 'scheduleCode'));
    }

    public function storeOpnameSchedule(Request $request)
    {
        $validated = $request->validate([
            'frequency' => 'required|in:monthly,semesterly,annually',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'scheduled_time' => 'nullable|date_format:H:i',
            'assigned_to' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $validated['item_type'] = 'sparepart';
        $validated['schedule_code'] = StockOpnameSchedule::generateScheduleCode();
        $validated['created_by'] = auth()->id();
        $validated['status'] = 'active';

        StockOpnameSchedule::create($validated);

        return redirect()
            ->route('admin.spareparts.opname-schedules')
            ->with('success', 'Opname schedule created successfully!');
    }

    public function opnameExecutions()
    {
        $executions = StockOpnameExecution::with(['schedule', 'executedByUser', 'verifiedByUser'])
            ->where('item_type', 'sparepart')
            ->latest('execution_date')
            ->paginate(15);

        return view('admin.spareparts.opname-executions', compact('executions'));
    }

    public function createOpnameExecution()
    {
        $schedules = StockOpnameSchedule::where('status', 'active')->where('item_type', 'sparepart')->get();
        $spareparts = Sparepart::orderBy('sparepart_name')->get();
        $executionCode = StockOpnameExecution::generateExecutionCode();

        return view('admin.spareparts.create-opname-execution', compact('schedules', 'spareparts', 'executionCode'));
    }

    public function storeOpnameExecution(Request $request)
    {
        $validated = $request->validate([
            'schedule_id' => 'nullable|exists:stock_opname_schedules,id',
            'item_id' => 'required|exists:spareparts,id',
            'execution_date' => 'required|date',
            'physical_quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string',
            'discrepancy_notes' => 'nullable|string',
        ]);

        $item = Sparepart::findOrFail($validated['item_id']);

        $validated['item_type'] = 'sparepart';
        $validated['execution_code'] = StockOpnameExecution::generateExecutionCode();
        $validated['system_quantity'] = $item->quantity;
        $validated['executed_by'] = auth()->id();

        if ($validated['schedule_id']) {
            $schedule = StockOpnameSchedule::find($validated['schedule_id']);
            $validated['scheduled_date'] = $schedule->scheduled_date;
        }

        $execution = StockOpnameExecution::create($validated);

        $execution->calculateDiscrepancy($item->parts_price);
        $execution->calculateComplianceStatus();

        if ($execution->schedule_id) {
            $schedule = $execution->schedule;
            if ($execution->is_missed) {
                $schedule->incrementMissedCount();
            }
            $schedule->incrementExecutionCount();
        }

        $item->physical_quantity = $execution->physical_quantity;
        $item->discrepancy_qty = $execution->discrepancy_qty;
        $item->discrepancy_value = $execution->discrepancy_value;
        $item->opname_status = 'completed';
        $item->opname_date = $execution->execution_date;
        $item->opname_by = $execution->executed_by;
        $item->last_opname_at = now();
        $item->save();

        return redirect()
            ->route('admin.spareparts.opname-executions')
            ->with('success', 'Opname execution recorded successfully!');
    }

    public function opnameComplianceReport()
    {
        $executions = StockOpnameExecution::with(['schedule', 'executedByUser'])
            ->where('item_type', 'sparepart')
            ->orderBy('execution_date', 'desc')
            ->paginate(20);

        $stats = [
            'on_time' => StockOpnameExecution::where('item_type', 'sparepart')->where('status', 'on_time')->count(),
            'late' => StockOpnameExecution::where('item_type', 'sparepart')->where('status', 'late')->count(),
            'early' => StockOpnameExecution::where('item_type', 'sparepart')->where('status', 'early')->count(),
            'missed' => StockOpnameExecution::where('item_type', 'sparepart')->where('is_missed', true)->count(),
        ];

        return view('admin.spareparts.opname-compliance-report', compact('executions', 'stats'));
    }

    public function opnameAccuracyReport()
    {
        $executions = StockOpnameExecution::with(['executedByUser'])
            ->where('item_type', 'sparepart')
            ->orderBy('execution_date', 'desc')
            ->paginate(20);

        $totalAccuracy = 0;
        $totalDiscrepancyValue = 0;
        foreach ($executions as $execution) {
            $totalAccuracy += $execution->getAccuracyPercentage();
            $totalDiscrepancyValue += abs($execution->discrepancy_value);
        }

        $stats = [
            'average_accuracy' => $executions->count() > 0 ? round($totalAccuracy / $executions->count(), 2) : 100,
            'total_discrepancy_value' => $totalDiscrepancyValue,
            'items_with_discrepancy' => StockOpnameExecution::where('item_type', 'sparepart')->where('discrepancy_qty', '!=', 0)->count(),
        ];

        return view('admin.spareparts.opname-accuracy-report', compact('executions', 'stats'));
    }

    // ===== STOCK ADJUSTMENT =====
    public function adjustments()
    {
        $adjustments = StockAdjustment::with(['adjustedByUser', 'approvedByUser'])
            ->where('item_type', 'sparepart')
            ->latest()
            ->paginate(15);

        return view('admin.spareparts.adjustments', compact('adjustments'));
    }

    public function createAdjustment(Request $request)
    {
        $spareparts = Sparepart::orderBy('sparepart_name')->get();
        $adjustmentCode = StockAdjustment::generateAdjustmentCode();

        // Get selected sparepart from query parameter
        $selectedSparepartId = $request->query('sparepart_id');
        $selectedSparepart = null;

        if ($selectedSparepartId) {
            $selectedSparepart = Sparepart::find($selectedSparepartId);
        }

        return view('admin.spareparts.create-adjustment', compact('spareparts', 'adjustmentCode', 'selectedSparepart'));
    }

    public function storeAdjustment(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:spareparts,id',
            'adjustment_qty' => 'required|integer|not_in:0',
            'adjustment_type' => 'required|in:add,subtract,correction',
            'reason_category' => 'required|in:damage,loss,found,correction,opname_result,other',
            'reason' => 'required|string',
        ]);

        $item = Sparepart::findOrFail($validated['item_id']);

        $validated['item_type'] = 'sparepart';
        $validated['adjustment_code'] = StockAdjustment::generateAdjustmentCode();
        $validated['quantity_before'] = $item->quantity;
        $validated['quantity_after'] = $item->quantity + $validated['adjustment_qty'];
        $validated['adjusted_by'] = auth()->id();
        $validated['status'] = 'approved';

        if ($validated['quantity_after'] < 0) {
            return back()->withErrors(['adjustment_qty' => 'Adjustment would result in negative quantity.'])->withInput();
        }

        $adjustment = StockAdjustment::create($validated);
        $adjustment->calculateValueImpact($item->parts_price);
        $adjustment->applyAdjustment();

        return redirect()
            ->route('admin.spareparts.adjustments')
            ->with('success', 'Stock adjustment created and applied successfully!');
    }

    // ===== EXCEL IMPORT =====
    public function showImportForm()
    {
        return view('admin.spareparts.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048',
        ]);

        try {
            $file = $request->file('file');
            $path = $file->getRealPath();

            $successCount = 0;
            $errorMessages = [];
            $rowNumber = 0;

            DB::beginTransaction();

            if (($handle = fopen($path, 'r')) !== false) {
                // Skip header row
                $header = fgetcsv($handle);

                // Validate header
                $expectedHeaders = ['equipment_type', 'sparepart_name', 'brand', 'model', 'quantity', 'minimum_stock', 'unit', 'parts_price', 'vulnerability', 'location'];

                while (($row = fgetcsv($handle)) !== false) {
                    $rowNumber++;

                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    try {
                        // Clean and trim all values
                        $row = array_map(function($value) {
                            return trim($value);
                        }, $row);

                        // Get equipment type directly from CSV (no mapping)
                        // Column 1: Equipment (CBS, Singulator, Belt Conveyor, Roller Conveyor, Consumable, Panel, Tools, etc)
                        $equipmentType = !empty($row[1]) ? trim($row[1]) : null;

                        // Get quantity and unit (separate columns)
                        $quantity = !empty($row[6]) ? intval($row[6]) : 0;
                        $rawUnit = !empty($row[7]) ? strtolower(trim($row[7])) : 'pcs';

                        // Map unit to valid values
                        $validUnits = ['pcs', 'unit', 'set', 'box', 'pack', 'kg', 'liter', 'meter', 'piece', 'lot', 'roll', 'pair'];
                        if (!in_array($rawUnit, $validUnits)) {
                            // Try to map common variations
                            if (in_array($rawUnit, ['pc', 'piece', 'pieces'])) {
                                $unit = 'pcs';
                            } elseif (in_array($rawUnit, ['units', 'ea'])) {
                                $unit = 'unit';
                            } elseif (in_array($rawUnit, ['sets'])) {
                                $unit = 'set';
                            } elseif (in_array($rawUnit, ['boxes'])) {
                                $unit = 'box';
                            } elseif (in_array($rawUnit, ['packs'])) {
                                $unit = 'pack';
                            } else {
                                // Default to pcs for unknown units
                                $unit = 'pcs';
                            }
                        } else {
                            $unit = $rawUnit;
                        }

                        // Map vulnerability
                        $vulnerability = !empty($row[8]) ? strtolower(trim($row[8])) : null;

                        // Map CSV data to array
                        // Format: No | Equipment | Material Code | Sparepart Name | Brand | Model | Quantity | Unit | Vulnerability | Location | Parts Price | Minimum | Item Type | Path
                        // Index:  0  | 1         | 2             | 3              | 4     | 5     | 6        | 7    | 8             | 9        | 10          | 11      | 12        | 13
                        $data = [
                            'equipment_type' => $equipmentType,
                            'sparepart_name' => !empty($row[3]) ? $row[3] : null,
                            'brand' => !empty($row[4]) ? $row[4] : null,
                            'model' => !empty($row[5]) ? $row[5] : null,
                            'quantity' => $quantity,
                            'minimum_stock' => !empty($row[11]) ? intval($row[11]) : 1,
                            'unit' => $unit,
                            'parts_price' => !empty($row[10]) ? floatval($row[10]) : 0,
                            'vulnerability' => $vulnerability,
                            'location' => !empty($row[9]) ? $row[9] : null,
                        ];

                        // Validate data
                        $validator = Validator::make($data, [
                            'equipment_type' => 'nullable|string|max:255',
                            'sparepart_name' => 'required|string|max:255',
                            'brand' => 'nullable|string|max:255',
                            'model' => 'nullable|string|max:255',
                            'quantity' => 'required|integer|min:0',
                            'minimum_stock' => 'required|integer|min:0',
                            'unit' => 'required|in:pcs,unit,set,box,pack,kg,liter,meter,piece,lot,roll,pair',
                            'parts_price' => 'required|numeric|min:0',
                            'vulnerability' => 'nullable|in:low,medium,high,critical',
                            'location' => 'nullable|string|max:255',
                        ]);

                        if ($validator->fails()) {
                            $errorMessages[] = "Row " . ($rowNumber + 1) . ": " . implode(', ', $validator->errors()->all());
                            continue;
                        }

                        // Create sparepart
                        Sparepart::create([
                            'sparepart_id' => Sparepart::generateSparepartId(),
                            'material_code' => !empty($row[2]) ? $row[2] : null,
                            'equipment_type' => $data['equipment_type'],
                            'sparepart_name' => $data['sparepart_name'],
                            'brand' => $data['brand'],
                            'model' => $data['model'],
                            'quantity' => intval($data['quantity']),
                            'minimum_stock' => intval($data['minimum_stock']),
                            'unit' => $data['unit'],
                            'parts_price' => floatval($data['parts_price']),
                            'vulnerability' => $data['vulnerability'],
                            'location' => $data['location'],
                            'item_type' => 'sparepart',
                            'add_part_by' => auth()->id(),
                        ]);

                        $successCount++;

                    } catch (\Exception $e) {
                        $errorMessages[] = "Row " . ($rowNumber + 1) . ": " . $e->getMessage();
                    }
                }

                fclose($handle);
            }

            DB::commit();

            if (count($errorMessages) > 0) {
                return redirect()
                    ->route('admin.spareparts.import')
                    ->with('warning', "Imported {$successCount} items with some errors:")
                    ->with('errors', $errorMessages);
            }

            return redirect()
                ->route('admin.spareparts.index')
                ->with('success', "Successfully imported {$successCount} sparepart items!");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('admin.spareparts.import')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'equipment_type',
            'sparepart_name',
            'brand',
            'model',
            'quantity',
            'minimum_stock',
            'unit',
            'parts_price',
            'vulnerability',
            'location'
        ];

        $exampleData = [
            [
                'electrical',
                'Motor Listrik 3 Phase',
                'Siemens',
                'XYZ-123',
                '10',
                '5',
                'pcs',
                '150000',
                'high',
                'Warehouse A - Rack 3'
            ],
            [
                'mechanical',
                'Bearing 6204',
                'SKF',
                'SKF-6204',
                '20',
                '10',
                'pcs',
                '25000',
                'medium',
                'Warehouse B - Shelf 2'
            ],
            [
                'pneumatic',
                'Air Cylinder 40mm',
                'SMC',
                'CDJ2B16-40',
                '5',
                '2',
                'unit',
                '350000',
                'high',
                'Warehouse A - Rack 5'
            ],
        ];

        $filename = 'spareparts_import_template_' . date('Y-m-d') . '.csv';

        $handle = fopen('php://output', 'w');
        ob_start();

        // Add UTF-8 BOM for Excel compatibility
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

        // Add headers
        fputcsv($handle, $headers);

        // Add example data
        foreach ($exampleData as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);
        $content = ob_get_clean();

        return response($content)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}

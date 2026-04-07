<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tool;
use Illuminate\Http\Request;
use App\Imports\ToolsImport;
use Maatwebsite\Excel\Facades\Excel;

class ToolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Tool::with(['addedByUser', 'opnameUser', 'verifiedUser']);

        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sparepart_name', 'LIKE', "%{$search}%")
                    ->orWhere('tool_id', 'LIKE', "%{$search}%")
                    ->orWhere('material_code', 'LIKE', "%{$search}%")
                    ->orWhere('brand', 'LIKE', "%{$search}%")
                    ->orWhere('model', 'LIKE', "%{$search}%");
            });
        }

        // Filter by location
        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        // Filter by status (from card clicks)
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'in_stock':
                    $query->where('quantity', '>', 0)
                        ->whereColumn('quantity', '>', 'minimum_stock');
                    break;
                case 'low_stock':
                    $query->where('quantity', '>', 0)
                        ->whereColumn('quantity', '<=', 'minimum_stock');
                    break;
                case 'out_of_stock':
                    $query->where('quantity', '<=', 0);
                    break;
            }
        }

        $tools = $query->latest()->paginate(15)->appends($request->except('page'));

        // Calculate inventory statistics
        $totalTools = Tool::count();
        $lowStock = Tool::where('quantity', '>', 0)
            ->whereColumn('quantity', '<=', 'minimum_stock')
            ->count();
        $outOfStock = Tool::where('quantity', '<=', 0)->count();
        $totalValue = Tool::sum(\DB::raw('quantity * parts_price'));

        return view('admin.tools.index', compact('tools', 'totalTools', 'lowStock', 'outOfStock', 'totalValue'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Generate tool ID
        $toolId = Tool::generateToolId();

        // Get pending unlisted tool items from received POs that haven't been registered yet
        $pendingPoItems = \App\Models\PurchaseOrderItem::with('purchaseOrder')
            ->where('is_unlisted', true)
            ->where('item_type', Tool::class)
            ->where('compliance_status', 'compliant')
            ->whereNull('registered_to_master_at')
            ->whereHas('purchaseOrder', fn($q) => $q->where('status', 'received'))
            ->get();

        // If coming from a specific PO item, pre-select it
        $selectedPoItem = null;
        if ($request->filled('from_po_item')) {
            $selectedPoItem = $pendingPoItems->firstWhere('id', $request->from_po_item);
        }

        return view('admin.tools.create', compact('toolId', 'pendingPoItems', 'selectedPoItem'));
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

        // Generate tool ID
        $validated['tool_id'] = Tool::generateToolId();
        $validated['item_type'] = 'tool';
        $validated['add_part_by'] = auth()->id();

        Tool::create($validated);

        // If created from a PO unlisted item, mark that item as registered
        if ($request->filled('from_po_item')) {
            \App\Models\PurchaseOrderItem::where('id', $request->from_po_item)
                ->where('is_unlisted', true)
                ->whereNull('registered_to_master_at')
                ->update(['registered_to_master_at' => now()]);
        }

        return redirect()
            ->route('admin.tools.index')
            ->with('success', 'Tool created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tool $tool)
    {
        $tool->load(['addedByUser', 'opnameUser', 'verifiedUser']);

        return view('admin.tools.show', compact('tool'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tool $tool)
    {
        return view('admin.tools.edit', compact('tool'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tool $tool)
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

        $tool->update($validated);

        return redirect()
            ->route('admin.tools.index')
            ->with('success', 'Tool updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tool $tool)
    {
        $tool->delete();

        return redirect()
            ->route('admin.tools.index')
            ->with('success', 'Tool deleted successfully!');
    }

    /**
     * Import tools from CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:10240',
        ]);

        try {
            $file = $request->file('file');
            $path = $file->getRealPath();

            $successCount = 0;
            $errorMessages = [];
            $rowNumber = 0;

            \DB::beginTransaction();

            if (($handle = fopen($path, 'r')) !== false) {
                // Strip UTF-8 BOM if present
                $bom = fread($handle, 3);
                if ($bom !== "\xEF\xBB\xBF") {
                    rewind($handle);
                }

                // Skip header row
                $header = fgetcsv($handle);

                while (($row = fgetcsv($handle)) !== false) {
                    $rowNumber++;

                    // Skip empty rows or rows without sparepart name (col3)
                    if (empty(array_filter($row))) {
                        continue;
                    }
                    if (empty(trim($row[3] ?? ''))) {
                        continue;
                    }

                    try {
                        // Clean and trim all values
                        $row = array_map(function($value) {
                            return trim($value);
                        }, $row);

                        // Skip rows where col0 is not a number (malformed rows)
                        if (!empty($row[0]) && !is_numeric($row[0])) {
                            continue;
                        }

                        // Format: No | Equipment Type | Material Code | Spare Parts Name | Brand | Model | Quantity | Unit | Vulnerability | Location | Parts Price | Minimum Stock | Item Type | Path
                        // Index:  0  | 1              | 2             | 3                | 4     | 5     | 6        | 7    | 8             | 9        | 10          | 11            | 12        | 13
                        $unitMap = [
                            'pcs' => 'pcs', 'pc' => 'pcs', 'piece' => 'pcs', 'pieces' => 'pcs',
                            'unit' => 'unit', 'units' => 'unit', 'ea' => 'unit',
                            'set' => 'set', 'sets' => 'set',
                            'box' => 'box', 'boxes' => 'box',
                            'pack' => 'pack', 'packs' => 'pack',
                            'kg' => 'kg', 'liter' => 'liter', 'ltr' => 'liter',
                            'meter' => 'meter', 'lot' => 'lot', 'roll' => 'roll', 'pair' => 'pair',
                        ];
                        $rawUnit = isset($row[7]) && trim($row[7]) !== '' ? strtolower(trim($row[7])) : 'pcs';
                        $unit = $unitMap[$rawUnit] ?? 'pcs';

                        $vulnerability = isset($row[8]) && trim($row[8]) !== '' ? strtolower(trim($row[8])) : null;
                        if (!in_array($vulnerability, ['low', 'medium', 'high', 'critical'])) {
                            $vulnerability = null;
                        }

                        $data = [
                            'equipment_type' => !empty($row[1]) ? $row[1] : 'Tools',
                            'material_code' => !empty($row[2]) ? $row[2] : null,
                            'tool_name' => $row[3],
                            'brand' => !empty($row[4]) ? $row[4] : null,
                            'model' => !empty($row[5]) ? $row[5] : null,
                            'quantity' => isset($row[6]) && trim($row[6]) !== '' ? intval($row[6]) : 0,
                            'unit' => $unit,
                            'vulnerability' => $vulnerability,
                            'location' => !empty($row[9]) ? $row[9] : null,
                            'parts_price' => isset($row[10]) && trim($row[10]) !== '' ? floatval(preg_replace('/[^0-9.]/', '', $row[10])) : 0,
                            'minimum_stock' => isset($row[11]) && trim($row[11]) !== '' ? intval($row[11]) : 0,
                        ];

                        // Validate data
                        $validator = \Validator::make($data, [
                            'equipment_type' => 'nullable|string|max:255',
                            'tool_name' => 'required|string|max:255',
                            'brand' => 'nullable|string|max:255',
                            'model' => 'nullable|string|max:255',
                            'quantity' => 'required|integer|min:0',
                            'unit' => 'required|in:pcs,unit,set,box,pack,kg,liter,meter,lot,roll,pair',
                            'vulnerability' => 'nullable|in:low,medium,high,critical',
                            'minimum_stock' => 'required|integer|min:0',
                            'location' => 'nullable|string|max:255',
                            'parts_price' => 'required|numeric|min:0',
                            'material_code' => 'nullable|string|max:255',
                        ]);

                        if ($validator->fails()) {
                            $errorMessages[] = "Row " . ($rowNumber + 1) . ": " . implode(', ', $validator->errors()->all());
                            continue;
                        }

                        // Prepare tool data
                        $toolData = [
                            'tool_id' => Tool::generateToolId(),
                            'equipment_type' => $data['equipment_type'],
                            'sparepart_name' => $data['tool_name'],
                            'brand' => $data['brand'],
                            'model' => $data['model'],
                            'quantity' => intval($data['quantity']),
                            'unit' => $data['unit'],
                            'vulnerability' => $data['vulnerability'],
                            'minimum_stock' => intval($data['minimum_stock']),
                            'location' => $data['location'],
                            'parts_price' => floatval($data['parts_price']),
                            'item_type' => 'tool',
                            'add_part_by' => auth()->id(),
                        ];

                        // Only add material_code if not empty
                        if (!empty($data['material_code'])) {
                            $toolData['material_code'] = $data['material_code'];
                        }

                        // Create tool
                        Tool::create($toolData);

                        $successCount++;

                    } catch (\Exception $e) {
                        $errorMessages[] = "Row " . ($rowNumber + 1) . ": " . $e->getMessage();
                    }
                }

                fclose($handle);
            }

            \DB::commit();

            if (count($errorMessages) > 0) {
                return redirect()
                    ->route('admin.tools.create')
                    ->with('warning', "Imported {$successCount} tools with some errors:")
                    ->with('import_errors', $errorMessages);
            }

            return redirect()
                ->route('admin.tools.index')
                ->with('success', "Successfully imported {$successCount} tool items!");

        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()
                ->route('admin.tools.create')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}

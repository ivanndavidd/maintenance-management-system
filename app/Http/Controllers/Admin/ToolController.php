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
            'file' => 'required|mimes:csv,txt|max:2048',
        ]);

        try {
            $file = $request->file('file');
            $path = $file->getRealPath();

            $successCount = 0;
            $errorMessages = [];
            $rowNumber = 0;

            \DB::beginTransaction();

            if (($handle = fopen($path, 'r')) !== false) {
                // Skip header row
                $header = fgetcsv($handle);

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

                        // Map CSV data to array
                        // Format: Equipment Type | Material Code | Sparepart Name | Brand | Model | Quantity | Unit | Vulnerability | Location | Parts Price | Minimum | Item Type | Path
                        // Index:  0               | 1             | 2              | 3     | 4     | 5        | 6    | 7             | 8        | 9           | 10      | 11        | 12
                        $data = [
                            'equipment_type' => !empty($row[0]) ? $row[0] : 'Tools',
                            'material_code' => !empty($row[1]) ? $row[1] : null,
                            'tool_name' => !empty($row[2]) ? $row[2] : null,
                            'brand' => !empty($row[3]) ? $row[3] : null,
                            'model' => !empty($row[4]) ? $row[4] : null,
                            'quantity' => !empty($row[5]) ? intval($row[5]) : 0,
                            'unit' => !empty($row[6]) ? strtolower(trim($row[6])) : 'pcs',
                            'vulnerability' => !empty($row[7]) ? strtolower(trim($row[7])) : null,
                            'location' => !empty($row[8]) ? $row[8] : null,
                            'parts_price' => !empty($row[9]) ? floatval($row[9]) : 0,
                            'minimum_stock' => !empty($row[10]) ? intval($row[10]) : 1,
                        ];

                        // Map unit to valid values
                        $validUnits = ['pcs', 'unit', 'set', 'box', 'pack'];
                        if (!in_array($data['unit'], $validUnits)) {
                            // Try to map common variations
                            if (in_array($data['unit'], ['pc', 'piece', 'pieces'])) {
                                $data['unit'] = 'pcs';
                            } elseif (in_array($data['unit'], ['units', 'ea'])) {
                                $data['unit'] = 'unit';
                            } elseif (in_array($data['unit'], ['sets'])) {
                                $data['unit'] = 'set';
                            } elseif (in_array($data['unit'], ['boxes'])) {
                                $data['unit'] = 'box';
                            } elseif (in_array($data['unit'], ['packs'])) {
                                $data['unit'] = 'pack';
                            } else {
                                // Default to pcs for unknown units
                                $data['unit'] = 'pcs';
                            }
                        }

                        // Normalize vulnerability value
                        if (!empty($data['vulnerability']) && !in_array($data['vulnerability'], ['low', 'medium', 'high', 'critical'])) {
                            // Set to null if invalid
                            $data['vulnerability'] = null;
                        }

                        // Validate data
                        $validator = \Validator::make($data, [
                            'equipment_type' => 'nullable|string|max:255',
                            'tool_name' => 'required|string|max:255',
                            'brand' => 'nullable|string|max:255',
                            'model' => 'nullable|string|max:255',
                            'quantity' => 'required|integer|min:0',
                            'unit' => 'required|in:pcs,unit,set,box,pack',
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

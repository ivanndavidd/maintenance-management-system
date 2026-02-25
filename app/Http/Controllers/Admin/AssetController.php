<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Imports\AssetMasterImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssetController extends Controller
{
    /**
     * Display a listing of assets
     */
    public function index(Request $request)
    {
        $query = Asset::with(['creator', 'updater']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('equipment_id', 'like', "%{$search}%")
                    ->orWhere('asset_name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $assets = $query->orderBy('created_at', 'desc')->paginate(20)->appends($request->except('page'));

        return view('admin.assets.index', compact('assets'));
    }

    /**
     * Show the import form
     */
    public function showImport()
    {
        return view('admin.assets.import');
    }

    /**
     * Import assets from CSV file (columns: EquipmentID, AssetName, BOMID, GroupID)
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file     = $request->file('csv_file');
        $handle   = fopen($file->getRealPath(), 'r');
        $imported = 0;
        $errors   = [];
        $row      = 0;

        // Read UTF-8 BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Read header row
        $headers = fgetcsv($handle);
        if (!$headers) {
            return back()->with('error', 'CSV file is empty or invalid.');
        }

        // Normalize headers: lowercase, trim
        $headers = array_map(fn($h) => strtolower(trim($h)), $headers);

        $colEquipmentId = array_search('equipmentid', $headers);
        $colAssetName   = array_search('assetname', $headers);
        $colBomId       = array_search('bomid', $headers);
        $colGroupId     = array_search('groupid', $headers);

        if ($colEquipmentId === false || $colAssetName === false) {
            fclose($handle);
            return back()->with('error', 'CSV must have EquipmentID and AssetName columns.');
        }

        $userId = auth()->check() ? auth()->id() : null;

        while (($data = fgetcsv($handle)) !== false) {
            $row++;
            $equipmentId = isset($data[$colEquipmentId]) ? trim($data[$colEquipmentId]) : null;
            $assetName   = isset($data[$colAssetName])   ? trim($data[$colAssetName])   : null;
            $bomId       = ($colBomId !== false && isset($data[$colBomId]))     ? trim($data[$colBomId])     : null;
            $groupId     = ($colGroupId !== false && isset($data[$colGroupId])) ? trim($data[$colGroupId])   : null;

            if (empty($assetName)) continue;

            $equipmentId = $equipmentId ?: null;
            $bomId       = $bomId       ?: null;
            $groupId     = $groupId     ?: null;

            try {
                // Upsert by equipment_id; if no equipment_id, always insert
                if ($equipmentId) {
                    $existing = Asset::withTrashed()->where('equipment_id', $equipmentId)->first();
                    if ($existing) {
                        if ($existing->trashed()) $existing->restore();
                        $existing->update([
                            'asset_name'  => $assetName,
                            'bom_id'      => $bomId,
                            'group_id'    => $groupId,
                            'updated_by'  => $userId,
                        ]);
                    } else {
                        Asset::create([
                            'equipment_id' => $equipmentId,
                            'asset_name'   => $assetName,
                            'bom_id'       => $bomId,
                            'group_id'     => $groupId,
                            'status'       => 'active',
                        ]);
                    }
                } else {
                    Asset::create([
                        'asset_name' => $assetName,
                        'bom_id'     => $bomId,
                        'group_id'   => $groupId,
                        'status'     => 'active',
                    ]);
                }
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$row} ({$equipmentId}): " . $e->getMessage();
            }
        }

        fclose($handle);

        $message = "Successfully imported {$imported} assets.";
        if (!empty($errors)) {
            $message .= ' With ' . count($errors) . ' errors.';
        }

        return redirect()->route('admin.assets.index')
            ->with('success', $message)
            ->with('import_errors', $errors);
    }

    /**
     * Show the form for creating a new asset
     */
    public function create()
    {
        return view('admin.assets.create');
    }

    /**
     * Store a newly created asset
     */
    public function store(Request $request)
    {
        $request->validate([
            'equipment_id' => 'nullable|string|max:100',
            'asset_name'   => 'required|string|max:255',
            'status'       => 'required|in:active,inactive,maintenance,disposed',
            'notes'        => 'nullable|string',
        ]);

        Asset::create($request->only(['equipment_id', 'asset_name', 'status', 'notes']));

        return redirect()->route('admin.assets.index')
            ->with('success', 'Asset created successfully.');
    }

    /**
     * Display the specified asset
     */
    public function show(Asset $asset)
    {
        $asset->load(['creator', 'updater']);
        return view('admin.assets.show', compact('asset'));
    }

    /**
     * Show the form for editing the specified asset
     */
    public function edit(Asset $asset)
    {
        return view('admin.assets.edit', compact('asset'));
    }

    /**
     * Update the specified asset
     */
    public function update(Request $request, Asset $asset)
    {
        $request->validate([
            'equipment_id' => 'nullable|string|max:100',
            'asset_name'   => 'required|string|max:255',
            'status'       => 'required|in:active,inactive,maintenance,disposed',
            'notes'        => 'nullable|string',
        ]);

        $asset->update($request->only(['equipment_id', 'asset_name', 'status', 'notes']));

        return redirect()->route('admin.assets.index')
            ->with('success', 'Asset updated successfully.');
    }

    /**
     * Remove the specified asset
     */
    public function destroy(Asset $asset)
    {
        $asset->delete();

        return redirect()->route('admin.assets.index')
            ->with('success', 'Asset deleted successfully.');
    }

    /**
     * Download template Excel file
     */
    public function downloadTemplate()
    {
        $templatePath = storage_path('app/templates/asset_import_template.xlsx');

        if (file_exists($templatePath)) {
            return response()->download($templatePath, 'asset_import_template.xlsx');
        }

        return back()->with('error', 'Template file not found.');
    }

    /**
     * Update BOM ID inline via AJAX
     */
    public function updateBom(Request $request, Asset $asset)
    {
        $request->validate([
            'bom_id' => 'nullable|string|max:20',
        ]);

        $asset->update(['bom_id' => $request->bom_id ?: null]);

        return response()->json(['success' => true, 'bom_id' => $asset->bom_id]);
    }

    /**
     * Search assets for Select2 AJAX
     */
    public function search(Request $request)
    {
        $q = $request->get('q', '');

        $query = Asset::where('status', 'active');

        if ($q !== '') {
            $query->where(function ($qb) use ($q) {
                $qb->where('asset_name', 'like', "%{$q}%")
                    ->orWhere('equipment_id', 'like', "%{$q}%");
            });
        }

        $assets = $query->orderBy('equipment_id')
        ->get()
        ->map(function ($asset) {
            return [
                'id' => $asset->id,
                'text' => ($asset->equipment_id ?? '-') . ' - ' . $asset->asset_name,
            ];
        });

        return response()->json(['results' => $assets]);
    }
}

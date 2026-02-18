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
                $q->where('asset_id', 'like', "%{$search}%")
                    ->orWhere('equipment_id', 'like', "%{$search}%")
                    ->orWhere('asset_name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('equipment_type', 'like', "%{$search}%");
            });
        }

        // Filter by equipment type
        if ($request->has('equipment_type') && $request->equipment_type != '') {
            $query->where('equipment_type', $request->equipment_type);
        }

        // Filter by location
        if ($request->has('location') && $request->location != '') {
            $query->where('location', $request->location);
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $assets = $query->orderBy('created_at', 'desc')->paginate(20)->appends($request->except('page'));

        // Get unique values for filters
        $equipmentTypes = Asset::select('equipment_type')->distinct()->pluck('equipment_type');
        $locations = Asset::select('location')->distinct()->pluck('location');

        return view('admin.assets.index', compact('assets', 'equipmentTypes', 'locations'));
    }

    /**
     * Show the import form
     */
    public function showImport()
    {
        return view('admin.assets.import');
    }

    /**
     * Import assets from Excel file
     */
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240', // Max 10MB
        ]);

        try {
            $file = $request->file('excel_file');
            $filePath = $file->getRealPath();

            // Process import
            $importer = new AssetMasterImport();
            $result = $importer->importFromFile($filePath);

            if ($result['success']) {
                $message = "Successfully imported {$result['imported']} assets.";

                if (!empty($result['errors'])) {
                    $message .= " With " . count($result['errors']) . " errors.";
                }

                return redirect()->route('admin.assets.index')
                    ->with('success', $message)
                    ->with('import_errors', $result['errors']);
            } else {
                return back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error importing file: ' . $e->getMessage());
        }
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
            'asset_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'equipment_type' => 'required|string|max:255',
            'status' => 'required|in:active,inactive,maintenance,disposed',
            'notes' => 'nullable|string',
        ]);

        Asset::create($request->all());

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
            'asset_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'equipment_type' => 'required|string|max:255',
            'status' => 'required|in:active,inactive,maintenance,disposed',
            'notes' => 'nullable|string',
        ]);

        $asset->update($request->all());

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
     * Search assets for Select2 AJAX
     */
    public function search(Request $request)
    {
        $q = $request->get('q', '');

        $query = Asset::where('status', 'active');

        if ($q !== '') {
            $query->where(function ($qb) use ($q) {
                $qb->where('asset_name', 'like', "%{$q}%")
                    ->orWhere('equipment_id', 'like', "%{$q}%")
                    ->orWhere('location', 'like', "%{$q}%");
            });
        }

        $assets = $query->orderBy('equipment_id')
        ->get()
        ->map(function ($asset) {
            return [
                'id' => $asset->id,
                'text' => ($asset->equipment_id ?? '-') . ' - ' . $asset->asset_name . ' (' . ($asset->location ?? '-') . ')',
            ];
        });

        return response()->json(['results' => $assets]);
    }
}

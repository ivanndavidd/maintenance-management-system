<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bom;
use App\Models\BomItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BomController extends Controller
{
    private function getRoutePrefix(): string
    {
        return auth()->user()->hasRole('supervisor_maintenance') ? 'supervisor' : 'admin';
    }

    public function index(Request $request)
    {
        $query = Bom::withCount(['items', 'assets']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('bom_id', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $boms = $query->orderBy('bom_id')->paginate(20)->appends($request->except('page'));

        return view('admin.bom.index', compact('boms'));
    }

    public function create()
    {
        return view('admin.bom.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'bom_id'                         => 'required|string|max:20|unique:boms,bom_id',
            'description'                    => 'nullable|string|max:255',
            'items'                          => 'required|array|min:1',
            'items.*.material_description'   => 'required|string|max:255',
            'items.*.qty'                    => 'required|numeric|min:0',
            'items.*.unit'                   => 'required|string|max:20',
            'items.*.material_code'          => 'nullable|string|max:50',
            'items.*.price_unit'             => 'nullable|numeric|min:0',
            'items.*.price'                  => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $bom = Bom::create([
                'bom_id'      => strtoupper(trim($request->bom_id)),
                'description' => $request->description,
            ]);

            foreach ($request->items as $index => $item) {
                $bom->items()->create([
                    'no'                   => $index + 1,
                    'material_code'        => $item['material_code'] ?? null,
                    'material_description' => $item['material_description'],
                    'qty'                  => $item['qty'],
                    'unit'                 => $item['unit'],
                    'price_unit'           => isset($item['price_unit']) && $item['price_unit'] !== '' ? $item['price_unit'] : null,
                    'price'                => isset($item['price']) && $item['price'] !== '' ? $item['price'] : null,
                ]);
            }
        });

        return redirect()->route($this->getRoutePrefix() . '.bom-management.index')
            ->with('success', 'BOM ' . strtoupper($request->bom_id) . ' created successfully.');
    }

    public function show(Bom $bomManagement)
    {
        $bomManagement->load(['items', 'assets', 'creator', 'updater']);
        $totalPrice = $bomManagement->items->sum('price');

        return view('admin.bom.show', [
            'bom'        => $bomManagement,
            'totalPrice' => $totalPrice,
        ]);
    }

    public function edit(Bom $bomManagement)
    {
        $bomManagement->load('items');

        return view('admin.bom.edit', ['bom' => $bomManagement]);
    }

    public function update(Request $request, Bom $bomManagement)
    {
        $request->validate([
            'bom_id'                         => 'required|string|max:20|unique:boms,bom_id,' . $bomManagement->id,
            'description'                    => 'nullable|string|max:255',
            'items'                          => 'required|array|min:1',
            'items.*.material_description'   => 'required|string|max:255',
            'items.*.qty'                    => 'required|numeric|min:0',
            'items.*.unit'                   => 'required|string|max:20',
            'items.*.material_code'          => 'nullable|string|max:50',
            'items.*.price_unit'             => 'nullable|numeric|min:0',
            'items.*.price'                  => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $bomManagement) {
            $bomManagement->update([
                'bom_id'      => strtoupper(trim($request->bom_id)),
                'description' => $request->description,
            ]);

            // Delete and recreate items
            $bomManagement->items()->delete();

            foreach ($request->items as $index => $item) {
                $bomManagement->items()->create([
                    'no'                   => $index + 1,
                    'material_code'        => $item['material_code'] ?? null,
                    'material_description' => $item['material_description'],
                    'qty'                  => $item['qty'],
                    'unit'                 => $item['unit'],
                    'price_unit'           => isset($item['price_unit']) && $item['price_unit'] !== '' ? $item['price_unit'] : null,
                    'price'                => isset($item['price']) && $item['price'] !== '' ? $item['price'] : null,
                ]);
            }
        });

        return redirect()->route($this->getRoutePrefix() . '.bom-management.show', $bomManagement)
            ->with('success', 'BOM updated successfully.');
    }

    public function destroy(Bom $bomManagement)
    {
        $bomManagement->delete();

        return redirect()->route($this->getRoutePrefix() . '.bom-management.index')
            ->with('success', 'BOM deleted successfully.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file   = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        // Skip header row
        $header = fgetcsv($handle);

        $rows    = [];
        $skipped = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 4) {
                $skipped++;
                continue;
            }
            [$bomId, $no, $materialCode, $materialDescription] = array_pad($row, 8, null);
            $qty       = isset($row[4]) && $row[4] !== '' ? (float) $row[4] : 1;
            $unit      = isset($row[5]) && $row[5] !== '' ? trim($row[5]) : 'Pcs';
            $priceUnit = isset($row[6]) && $row[6] !== '' ? (float) str_replace([',', ' '], '', $row[6]) : null;
            $price     = isset($row[7]) && $row[7] !== '' ? (float) str_replace([',', ' '], '', $row[7]) : null;

            $bomId = strtoupper(trim($bomId));
            if (empty($bomId) || empty($materialDescription)) {
                $skipped++;
                continue;
            }

            $rows[$bomId][] = [
                'no'                   => (int) $no,
                'material_code'        => trim($materialCode) ?: null,
                'material_description' => trim($materialDescription),
                'qty'                  => $qty,
                'unit'                 => $unit,
                'price_unit'           => $priceUnit,
                'price'                => $price,
            ];
        }
        fclose($handle);

        $imported = 0;
        DB::transaction(function () use ($rows, &$imported) {
            foreach ($rows as $bomId => $items) {
                $bom = Bom::updateOrCreate(
                    ['bom_id' => $bomId],
                    ['bom_id' => $bomId]
                );

                // Replace all items
                $bom->items()->delete();
                foreach ($items as $index => $item) {
                    $bom->items()->create(array_merge($item, [
                        'no' => $item['no'] ?: ($index + 1),
                    ]));
                }
                $imported++;
            }
        });

        return redirect()->route($this->getRoutePrefix() . '.bom-management.index')
            ->with('success', "Imported {$imported} BOM(s) successfully." . ($skipped ? " {$skipped} row(s) skipped." : ''));
    }
}

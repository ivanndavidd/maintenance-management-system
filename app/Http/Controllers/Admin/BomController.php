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

    private function parsePrice(string $value): ?float
    {
        $value = trim($value);
        if ($value === '' || strtolower($value) === 'rp-' || $value === '-') {
            return null;
        }
        // Remove Rp prefix, spaces, dots as thousand separator, then parse
        $value = preg_replace('/[Rp\s]/i', '', $value);
        $value = str_replace('.', '', $value);  // remove thousand dots
        $value = str_replace(',', '.', $value); // convert decimal comma to dot
        $value = preg_replace('/[^0-9.]/', '', $value);
        return $value !== '' ? (float) $value : null;
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

        // Skip first header row
        fgetcsv($handle);

        $rows          = [];
        $skipped       = 0;
        $currentBomId  = null;

        while (($row = fgetcsv($handle)) !== false) {
            // Skip empty rows
            if (count(array_filter($row, fn($v) => trim($v) !== '')) === 0) {
                continue;
            }

            $col0 = trim($row[0] ?? '');
            $col1 = trim($row[1] ?? '');

            // Detect repeated header rows (e.g. "No.,No. Material,...")
            if (strtolower($col1) === 'no. material' || strtolower($col0) === 'no.') {
                continue;
            }

            // If col0 looks like a BOM ID (e.g. R07, R08), update current BOM
            if (preg_match('/^[A-Za-z]\d+$/', $col0)) {
                $currentBomId = strtoupper($col0);

                // If col1 is "No." this row is just a BOM header line, skip to next row
                if (strtolower($col1) === 'no.') {
                    continue;
                }

                // col1 is now the line No.
                $no = is_numeric($col1) ? (int) $col1 : 0;
                $materialCode        = trim($row[2] ?? '');
                $materialDescription = trim($row[3] ?? '');
                $qty                 = isset($row[4]) && trim($row[4]) !== '' ? (float) trim($row[4]) : 1;
                $unit                = isset($row[5]) && trim($row[5]) !== '' ? trim($row[5]) : 'Pcs';
                $priceUnit           = $this->parsePrice($row[6] ?? '');
                $price               = $this->parsePrice($row[7] ?? '');
            } else {
                // Continuation row — col0 is empty, col1=no, col2=material_code, col3=description...
                if (!$currentBomId) { $skipped++; continue; }
                $no                  = is_numeric($col1) ? (int) $col1 : 0;
                $materialCode        = trim($row[2] ?? '');
                $materialDescription = trim($row[3] ?? '');
                $qty                 = isset($row[4]) && trim($row[4]) !== '' ? (float) trim($row[4]) : 1;
                $unit                = isset($row[5]) && trim($row[5]) !== '' ? trim($row[5]) : 'Pcs';
                $priceUnit           = $this->parsePrice($row[6] ?? '');
                $price               = $this->parsePrice($row[7] ?? '');
            }

            if (empty($materialDescription)) {
                $skipped++;
                continue;
            }

            $rows[$currentBomId][] = [
                'no'                   => $no,
                'material_code'        => $materialCode ?: null,
                'material_description' => $materialDescription,
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
                $bom = Bom::withTrashed()->where('bom_id', $bomId)->first();
                if ($bom) {
                    if ($bom->trashed()) {
                        $bom->restore();
                    }
                } else {
                    $bom = Bom::create(['bom_id' => $bomId]);
                }

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

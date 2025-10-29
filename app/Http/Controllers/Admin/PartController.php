<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Part;
use Illuminate\Http\Request;

class PartController extends Controller
{
    public function index()
    {
        $parts = Part::paginate(10);
        
        // Low stock parts
        $lowStockParts = Part::whereColumn('stock_quantity', '<=', 'minimum_stock')
                            ->orderBy('stock_quantity')
                            ->get();
        
        $stats = [
            'total_parts' => Part::count(),
            'low_stock' => $lowStockParts->count(),
            'out_of_stock' => Part::where('stock_quantity', 0)->count(),
            'total_value' => Part::selectRaw('SUM(stock_quantity * unit_cost) as total')->first()->total ?? 0,
        ];
        
        return view('admin.parts.index', compact('parts', 'lowStockParts', 'stats'));
    }

    public function create()
    {
        return view('admin.parts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:parts',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:20',
            'stock_quantity' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        Part::create($validated);

        return redirect()->route('admin.parts.index')
                        ->with('success', 'Part added successfully!');
    }

    public function edit(Part $part)
    {
        return view('admin.parts.edit', compact('part'));
    }

    public function update(Request $request, Part $part)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:parts,code,' . $part->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:20',
            'stock_quantity' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        $part->update($validated);

        return redirect()->route('admin.parts.index')
                        ->with('success', 'Part updated successfully!');
    }

    public function destroy(Part $part)
    {
        $part->delete();
        return redirect()->route('admin.parts.index')
                        ->with('success', 'Part deleted successfully!');
    }
}
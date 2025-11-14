<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Part;
use Illuminate\Http\Request;

class PartController extends Controller
{
    public function index(Request $request)
    {
        $query = Part::query();

        // Search filter
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('supplier', 'like', "%{$search}%");
            });
        }

        // Stock status filter
        if ($request->has('stock_status') && $request->stock_status != '') {
            switch ($request->stock_status) {
                case 'out_of_stock':
                    $query->where('stock_quantity', 0);
                    break;
                case 'low_stock':
                    $query->whereColumn('stock_quantity', '<=', 'minimum_stock')
                          ->where('stock_quantity', '>', 0);
                    break;
                case 'available':
                    $query->whereColumn('stock_quantity', '>', 'minimum_stock');
                    break;
            }
        }

        // Location filter
        if ($request->has('location') && $request->location != '') {
            $query->where('location', 'like', "%{$request->location}%");
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // Validate sort columns
        $allowedSortColumns = [
            'code',
            'name',
            'unit',
            'stock_quantity',
            'minimum_stock',
            'unit_cost',
            'location',
            'created_at'
        ];

        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);

        $parts = $query->paginate(15);

        // Low stock parts
        $lowStockParts = Part::whereColumn('stock_quantity', '<=', 'minimum_stock')
                            ->orderBy('stock_quantity')
                            ->get();

        // Get unique locations for filter
        $locations = Part::whereNotNull('location')
                        ->where('location', '!=', '')
                        ->distinct()
                        ->pluck('location')
                        ->sort();

        $stats = [
            'total_parts' => Part::count(),
            'low_stock' => Part::whereColumn('stock_quantity', '<=', 'minimum_stock')
                              ->where('stock_quantity', '>', 0)->count(),
            'out_of_stock' => Part::where('stock_quantity', 0)->count(),
            'total_value' => Part::selectRaw('SUM(stock_quantity * unit_cost) as total')->first()->total ?? 0,
        ];

        return view('admin.parts.index', compact('parts', 'lowStockParts', 'stats', 'locations'));
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
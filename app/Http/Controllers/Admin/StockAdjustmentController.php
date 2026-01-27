<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockAdjustment;
use App\Models\Sparepart;
use App\Models\Tool;
use Illuminate\Http\Request;

class StockAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $adjustments = StockAdjustment::with(['adjustedByUser', 'approvedByUser'])
            ->latest()
            ->paginate(15)->appends($request->except('page'));

        return view('admin.adjustments.index', compact('adjustments'));
    }

    public function create()
    {
        $spareparts = Sparepart::orderBy('sparepart_name')->get();
        $tools = Tool::orderBy('sparepart_name')->get();
        $adjustmentCode = StockAdjustment::generateAdjustmentCode();

        return view('admin.adjustments.create', compact('spareparts', 'tools', 'adjustmentCode'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_type' => 'required|in:sparepart,tool',
            'item_id' => 'required|integer',
            'adjustment_qty' => 'required|integer|not_in:0',
            'adjustment_type' => 'required|in:add,subtract,correction',
            'reason_category' => 'required|in:damage,loss,found,correction,opname_result,other',
            'reason' => 'required|string',
        ]);

        // Get item
        if ($validated['item_type'] === 'sparepart') {
            $item = Sparepart::findOrFail($validated['item_id']);
        } else {
            $item = Tool::findOrFail($validated['item_id']);
        }

        $validated['adjustment_code'] = StockAdjustment::generateAdjustmentCode();
        $validated['quantity_before'] = $item->quantity;
        $validated['quantity_after'] = $item->quantity + $validated['adjustment_qty'];
        $validated['adjusted_by'] = auth()->id();
        $validated['status'] = 'approved'; // Auto-approve for admin

        // Ensure quantity doesn't go negative
        if ($validated['quantity_after'] < 0) {
            return back()->withErrors(['adjustment_qty' => 'Adjustment would result in negative quantity.'])->withInput();
        }

        $adjustment = StockAdjustment::create($validated);

        // Calculate value impact
        $adjustment->calculateValueImpact($item->parts_price);

        // Apply adjustment
        $adjustment->applyAdjustment();

        return redirect()
            ->route('admin.adjustments.index')
            ->with('success', 'Stock adjustment created and applied successfully!');
    }

    public function show(StockAdjustment $adjustment)
    {
        $adjustment->load(['adjustedByUser', 'approvedByUser', 'relatedOpnameExecution']);

        // Load item based on type
        if ($adjustment->item_type === 'sparepart') {
            $item = Sparepart::find($adjustment->item_id);
        } else {
            $item = Tool::find($adjustment->item_id);
        }

        return view('admin.adjustments.show', compact('adjustment', 'item'));
    }

    public function approve(StockAdjustment $adjustment)
    {
        if ($adjustment->status !== 'pending') {
            return back()->with('error', 'Adjustment has already been processed.');
        }

        $adjustment->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Apply adjustment
        $adjustment->applyAdjustment();

        return redirect()
            ->route('admin.adjustments.show', $adjustment)
            ->with('success', 'Adjustment approved and applied successfully!');
    }

    public function reject(Request $request, StockAdjustment $adjustment)
    {
        $validated = $request->validate([
            'approval_notes' => 'required|string',
        ]);

        if ($adjustment->status !== 'pending') {
            return back()->with('error', 'Adjustment has already been processed.');
        }

        $adjustment->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_notes' => $validated['approval_notes'],
        ]);

        return redirect()
            ->route('admin.adjustments.show', $adjustment)
            ->with('success', 'Adjustment rejected successfully!');
    }
}

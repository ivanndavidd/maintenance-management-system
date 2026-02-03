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

        // Auto-approve only for admin, pending for others (supervisor, etc)
        if (auth()->user()->hasRole('admin')) {
            $validated['status'] = 'approved';
            $validated['approved_by'] = auth()->id();
            $validated['approved_at'] = now();
        } else {
            $validated['status'] = 'pending';
        }

        // Ensure quantity doesn't go negative
        if ($validated['quantity_after'] < 0) {
            return back()->withErrors(['adjustment_qty' => 'Adjustment would result in negative quantity.'])->withInput();
        }

        $adjustment = StockAdjustment::create($validated);

        // Calculate value impact
        $adjustment->calculateValueImpact($item->parts_price);

        // Apply adjustment only if approved (admin creates it)
        if ($adjustment->status === 'approved') {
            $adjustment->applyAdjustment();
        }

        // Refresh adjustment to get latest data with relationships
        $adjustment->refresh();
        $adjustment->load('adjustedByUser');

        // Send notification based on status
        try {
            if ($adjustment->status === 'pending') {
                // Notify all admins for approval
                $admins = \App\Models\User::role('admin')->get();

                foreach ($admins as $admin) {
                    \Mail::to($admin->email)->send(new \App\Mail\StockAdjustmentCreated($adjustment));
                }

                \Log::info('Stock adjustment approval request sent to admins', [
                    'adjustment_code' => $adjustment->adjustment_code,
                    'created_by' => auth()->user()->name,
                    'admins_count' => $admins->count(),
                ]);
            } else {
                // Notify other admins (informational, already approved)
                $admins = \App\Models\User::role('admin')->where('id', '!=', auth()->id())->get();

                foreach ($admins as $admin) {
                    \Mail::to($admin->email)->send(new \App\Mail\StockAdjustmentCreated($adjustment));
                }

                \Log::info('Stock adjustment notifications sent to admins', [
                    'adjustment_code' => $adjustment->adjustment_code,
                    'item_type' => $adjustment->item_type,
                    'adjustment_qty' => $adjustment->adjustment_qty,
                    'admins_count' => $admins->count(),
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send stock adjustment notification: ' . $e->getMessage(), [
                'adjustment_id' => $adjustment->id,
            ]);
        }

        $message = $adjustment->status === 'approved'
            ? 'Stock adjustment created and applied successfully!'
            : 'Stock adjustment created and waiting for admin approval.';

        return redirect()
            ->route('admin.adjustments.index')
            ->with('success', $message);
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

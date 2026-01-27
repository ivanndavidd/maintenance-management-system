<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryRequest;
use App\Models\Part;
use Illuminate\Http\Request;

class InventoryRequestController extends Controller
{
    /**
     * Display all inventory requests for admin approval
     */
    public function index(Request $request)
    {
        $query = InventoryRequest::with(['part', 'user', 'approver']);

        // Status filter
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Search filter
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('request_code', 'like', "%{$search}%")
                    ->orWhereHas('part', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('part_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(15)->appends($request->except('page'));

        // Count pending requests for badge
        $pendingCount = InventoryRequest::pending()->count();

        return view('admin.inventory-requests.index', compact('requests', 'pendingCount'));
    }

    /**
     * Show request details
     */
    public function show(InventoryRequest $inventoryRequest)
    {
        $inventoryRequest->load(['part', 'user', 'approver']);

        return view('admin.inventory-requests.show', compact('inventoryRequest'));
    }

    /**
     * Approve inventory request
     */
    public function approve(Request $request, InventoryRequest $inventoryRequest)
    {
        if (!$inventoryRequest->isPending()) {
            return back()->with('error', 'Only pending requests can be approved.');
        }

        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string'],
        ]);

        // Check if part has enough stock
        $part = $inventoryRequest->part;
        if ($part->quantity < $inventoryRequest->quantity_requested) {
            return back()->with('error', 'Insufficient stock. Available: ' . $part->quantity);
        }

        // Update part quantity
        $part->decrement('quantity', $inventoryRequest->quantity_requested);

        // Update request
        $inventoryRequest->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        return redirect()
            ->route('admin.inventory-requests.index')
            ->with('success', 'Request approved successfully! Inventory has been deducted.');
    }

    /**
     * Reject inventory request
     */
    public function reject(Request $request, InventoryRequest $inventoryRequest)
    {
        if (!$inventoryRequest->isPending()) {
            return back()->with('error', 'Only pending requests can be rejected.');
        }

        $validated = $request->validate([
            'admin_notes' => ['required', 'string'],
        ]);

        $inventoryRequest->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'admin_notes' => $validated['admin_notes'],
        ]);

        return redirect()
            ->route('admin.inventory-requests.index')
            ->with('success', 'Request rejected.');
    }
}

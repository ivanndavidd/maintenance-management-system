<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\InventoryRequest;
use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InventoryRequestController extends Controller
{
    /**
     * Display user's inventory requests
     */
    public function index(Request $request)
    {
        $query = InventoryRequest::where('user_id', auth()->id())
            ->with(['part', 'approver']);

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
                    });
            });
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('user.inventory-requests.index', compact('requests'));
    }

    /**
     * Show form to create new request
     */
    public function create()
    {
        $parts = Part::where('quantity', '>', 0)
            ->orderBy('name')
            ->get();

        // Generate request code
        $requestCode = 'REQ-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        return view('user.inventory-requests.create', compact('parts', 'requestCode'));
    }

    /**
     * Store new inventory request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_code' => ['required', 'string', 'max:50', 'unique:inventory_requests'],
            'part_id' => ['required', 'exists:parts,id'],
            'quantity_requested' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string'],
            'usage_description' => ['nullable', 'string'],
        ]);

        // Check if requested quantity is available
        $part = Part::findOrFail($validated['part_id']);
        if ($part->quantity < $validated['quantity_requested']) {
            return back()
                ->withInput()
                ->with('error', 'Requested quantity exceeds available stock. Available: ' . $part->quantity);
        }

        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';

        InventoryRequest::create($validated);

        return redirect()
            ->route('user.inventory-requests.index')
            ->with('success', 'Inventory request submitted successfully! Waiting for admin approval.');
    }

    /**
     * Show request details
     */
    public function show(InventoryRequest $inventoryRequest)
    {
        // Ensure user can only view their own requests
        if ($inventoryRequest->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $inventoryRequest->load(['part', 'user', 'approver']);

        return view('user.inventory-requests.show', compact('inventoryRequest'));
    }

    /**
     * Cancel pending request
     */
    public function destroy(InventoryRequest $inventoryRequest)
    {
        // Ensure user can only cancel their own requests
        if ($inventoryRequest->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        // Only pending requests can be cancelled
        if (!$inventoryRequest->isPending()) {
            return back()->with('error', 'Only pending requests can be cancelled.');
        }

        $inventoryRequest->delete();

        return redirect()
            ->route('user.inventory-requests.index')
            ->with('success', 'Request cancelled successfully.');
    }
}

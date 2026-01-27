<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Sparepart;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['items.item', 'orderedByUser', 'receivedByUser', 'approver', 'approvedByUser']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by approval status
        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        // Filter by has unlisted items
        if ($request->filled('has_unlisted')) {
            $query->where('has_unlisted_items', true);
        }

        $purchaseOrders = $query->latest()->paginate(15)->appends($request->except('page'));

        return view('admin.purchase-orders.index', compact('purchaseOrders'));
    }

    public function create()
    {
        // Get low stock spareparts
        $lowStockSpareparts = Sparepart::where('quantity', '<=', \DB::raw('minimum_stock'))
            ->orWhereRaw('quantity < minimum_stock * 1.5')
            ->get();

        // Get low stock tools
        $lowStockTools = Tool::where('quantity', '<=', \DB::raw('minimum_stock'))
            ->orWhereRaw('quantity < minimum_stock * 1.5')
            ->get();

        // Get all spareparts and tools
        $allSpareparts = Sparepart::orderBy('sparepart_name')->get();
        $allTools = Tool::orderBy('sparepart_name')->get();

        // Get all admin users for approval tagging
        $adminUsers = User::role('admin')->orderBy('name')->get();

        $poNumber = PurchaseOrder::generatePONumber();

        return view('admin.purchase-orders.create', compact(
            'lowStockSpareparts',
            'lowStockTools',
            'allSpareparts',
            'allTools',
            'adminUsers',
            'poNumber'
        ));
    }

    public function store(Request $request)
    {
        // Validate cart items
        $validated = $request->validate([
            'supplier' => 'required|string|max:255',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'approver_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',

            // Cart items (array) - UNLISTED ITEMS NOT ALLOWED
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:sparepart,tool',
            'items.*.item_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.unit' => 'required|string|max:50',
            'items.*.supplier' => 'required|string|max:255',
        ]);

        // Create Purchase Order
        $purchaseOrder = PurchaseOrder::create([
            'po_number' => PurchaseOrder::generatePONumber(),
            'supplier' => $validated['supplier'],
            'order_date' => $validated['order_date'],
            'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
            'approver_id' => $validated['approver_id'],
            'notes' => $validated['notes'] ?? null,
            'ordered_by' => auth()->id(),
            'status' => 'pending',
            'approval_status' => 'pending',
            'total_items' => 0,
            'total_quantity' => 0,
            'total_price' => 0,
            'has_unlisted_items' => false,
        ]);

        // Add items to cart (only listed items allowed)
        foreach ($validated['items'] as $itemData) {
            $subtotal = $itemData['quantity'] * $itemData['unit_price'];

            $poItemData = [
                'purchase_order_id' => $purchaseOrder->id,
                'quantity_ordered' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'subtotal' => $subtotal,
                'unit' => $itemData['unit'],
                'supplier' => $itemData['supplier'],
                'status' => 'pending',
                'is_unlisted' => false,
            ];

            // Set item type and ID
            if ($itemData['type'] === 'sparepart') {
                $poItemData['item_type'] = Sparepart::class;
                $poItemData['item_id'] = $itemData['item_id'];
            } elseif ($itemData['type'] === 'tool') {
                $poItemData['item_type'] = Tool::class;
                $poItemData['item_id'] = $itemData['item_id'];
            }

            PurchaseOrderItem::create($poItemData);
        }

        // Update PO summary
        $purchaseOrder->has_unlisted_items = false;
        $purchaseOrder->updateSummary();

        return redirect()
            ->route('admin.purchase-orders.index')
            ->with('success', 'Purchase Order created with ' . $purchaseOrder->total_items . ' items! Sent for approval.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'items.item',
            'orderedByUser',
            'receivedByUser',
            'approver',
            'approvedByUser',
            'cancelledByUser'
        ]);

        return view('admin.purchase-orders.show', compact('purchaseOrder'));
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        // Check if user can approve
        if (!$purchaseOrder->canBeApprovedBy(auth()->id())) {
            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder)
                ->with('error', 'You are not authorized to approve this Purchase Order.');
        }

        // Check if already approved or rejected
        if ($purchaseOrder->approval_status !== 'pending') {
            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder)
                ->with('error', 'This Purchase Order has already been ' . $purchaseOrder->approval_status . '.');
        }

        $purchaseOrder->approve(auth()->id());
        $purchaseOrder->update(['status' => 'ordered']);

        return redirect()
            ->route('admin.purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase Order approved successfully!');
    }

    public function reject(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Check if user can reject
        if (!$purchaseOrder->canBeApprovedBy(auth()->id())) {
            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder)
                ->with('error', 'You are not authorized to reject this Purchase Order.');
        }

        // Check if already approved or rejected
        if ($purchaseOrder->approval_status !== 'pending') {
            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder)
                ->with('error', 'This Purchase Order has already been ' . $purchaseOrder->approval_status . '.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $purchaseOrder->reject(auth()->id(), $validated['rejection_reason']);
        $purchaseOrder->update(['status' => 'cancelled']);

        return redirect()
            ->route('admin.purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase Order rejected.');
    }

    // Show goods receiving form (for all items)
    public function showReceiveForm(PurchaseOrder $purchaseOrder)
    {
        // Check if PO is approved
        if ($purchaseOrder->approval_status !== 'approved') {
            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder)
                ->with('error', 'Can only receive goods for approved Purchase Orders.');
        }

        // Check if already fully received
        if ($purchaseOrder->isFullyReceived()) {
            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder)
                ->with('error', 'This Purchase Order has already been fully received.');
        }

        $purchaseOrder->load('items.item');

        return view('admin.purchase-orders.receive', compact('purchaseOrder'));
    }

    // Receive specific item
    public function receiveItem(Request $request, PurchaseOrder $purchaseOrder, PurchaseOrderItem $item)
    {
        $validated = $request->validate([
            'quantity_received' => 'required|integer|min:1|max:' . $item->getRemainingQuantity(),
            'actual_delivery_date' => 'required|date|before_or_equal:today',
        ]);

        $item->receiveItems($validated['quantity_received']);

        // Update PO delivery date if not set
        if (!$purchaseOrder->actual_delivery_date) {
            $purchaseOrder->actual_delivery_date = $validated['actual_delivery_date'];
            $purchaseOrder->received_by = auth()->id();
            $purchaseOrder->save();
        }

        // Update PO status
        if ($purchaseOrder->isFullyReceived()) {
            $purchaseOrder->status = 'received';
        } else {
            $purchaseOrder->status = 'partial_received';
        }
        $purchaseOrder->save();

        return redirect()
            ->route('admin.purchase-orders.show', $purchaseOrder)
            ->with('success', 'Item received! Please verify quality.');
    }

    // Batch receive multiple items
    public function batchReceive(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'actual_delivery_date' => 'required|date|before_or_equal:today',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity' => 'required|integer|min:0',
        ]);

        $purchaseOrder = PurchaseOrder::findOrFail($validated['purchase_order_id']);
        $receivedCount = 0;

        foreach ($validated['items'] as $itemData) {
            $quantity = (int) $itemData['quantity'];

            // Skip if quantity is 0
            if ($quantity <= 0) {
                continue;
            }

            $poItem = PurchaseOrderItem::findOrFail($itemData['item_id']);

            // Validate quantity does not exceed remaining
            $remaining = $poItem->getRemainingQuantity();
            if ($quantity > $remaining) {
                return redirect()
                    ->back()
                    ->with('error', "Quantity for {$poItem->getItemName()} exceeds remaining quantity ({$remaining})");
            }

            // Receive the item
            $poItem->receiveItems($quantity);
            $receivedCount++;
        }

        // Update PO delivery date if not set
        if (!$purchaseOrder->actual_delivery_date) {
            $purchaseOrder->actual_delivery_date = $validated['actual_delivery_date'];
            $purchaseOrder->received_by = auth()->id();
            $purchaseOrder->save();
        }

        // Update PO status
        if ($purchaseOrder->isFullyReceived()) {
            $purchaseOrder->status = 'received';
        } else {
            $purchaseOrder->status = 'partial_received';
        }
        $purchaseOrder->save();

        if ($receivedCount === 0) {
            return redirect()
                ->back()
                ->with('error', 'No items were received. Please enter quantity for at least one item.');
        }

        return redirect()
            ->route('admin.purchase-orders.show', $purchaseOrder)
            ->with('success', "Successfully received {$receivedCount} item(s)! Please verify quality.");
    }

    // Mark item compliance
    public function markItemCompliance(Request $request, PurchaseOrder $purchaseOrder, PurchaseOrderItem $item)
    {
        $validated = $request->validate([
            'compliance_status' => 'required|in:compliant,non_compliant',
            'compliance_notes' => 'nullable|string|required_if:compliance_status,non_compliant',
        ]);

        if ($validated['compliance_status'] === 'compliant') {
            $item->markAsCompliant($validated['compliance_notes'] ?? null);
            $message = 'Item marked as compliant!';
        } else {
            $item->markAsNonCompliant($validated['compliance_notes']);
            $message = 'Item marked as NON-COMPLIANT.';
        }

        return redirect()
            ->route('admin.purchase-orders.show', $purchaseOrder)
            ->with('success', $message);
    }

    // Add single item to stock
    public function addItemToStock(PurchaseOrder $purchaseOrder, PurchaseOrderItem $item)
    {
        if (!$item->canAddToStock()) {
            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder)
                ->with('error', 'Cannot add item to stock. Item must be received and marked as compliant first.');
        }

        try {
            $item->addToStock();

            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder)
                ->with('success', 'Item "' . $item->getItemName() . '" added to stock successfully!');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder)
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // Add all compliant items to stock
    public function addAllCompliantToStock(PurchaseOrder $purchaseOrder)
    {
        $addedCount = 0;
        $errors = [];

        foreach ($purchaseOrder->items as $item) {
            if ($item->canAddToStock()) {
                try {
                    $item->addToStock();
                    $addedCount++;
                } catch (\Exception $e) {
                    $errors[] = $item->getItemName() . ': ' . $e->getMessage();
                }
            }
        }

        if ($addedCount > 0) {
            $message = $addedCount . ' item(s) added to stock successfully!';
            if (count($errors) > 0) {
                $message .= ' Some items failed: ' . implode(', ', $errors);
            }
            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder)
                ->with('success', $message);
        }

        return redirect()
            ->route('admin.purchase-orders.show', $purchaseOrder)
            ->with('error', 'No items could be added to stock. ' . implode(', ', $errors));
    }

    public function cancel(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Check if PO can be cancelled
        if (!$purchaseOrder->canBeCancelled()) {
            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder)
                ->with('error', 'Cannot cancel this Purchase Order. Items may have already been added to stock.');
        }

        $validated = $request->validate([
            'cancellation_type' => 'required|in:cancelled_by_vendor,cancelled_by_user,internal_issue,budget_constraint,duplicate_order,item_unavailable,other',
            'cancellation_reason' => 'required|string|max:1000',
        ]);

        try {
            $purchaseOrder->cancel(
                auth()->id(),
                $validated['cancellation_type'],
                $validated['cancellation_reason']
            );

            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder)
                ->with('success', 'Purchase Order cancelled successfully. Cancellation has been recorded in history.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder)
                ->with('error', $e->getMessage());
        }
    }

    // Delete PO permanently (Admin only)
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        // Check if user is admin
        if (!auth()->user()->hasRole('admin')) {
            return redirect()
                ->route('admin.purchase-orders.index')
                ->with('error', 'Only admin can delete Purchase Orders.');
        }

        // Prevent deletion if items have been added to stock
        $hasStockItems = $purchaseOrder->items()->where('added_to_stock', true)->exists();
        if ($hasStockItems) {
            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder)
                ->with('error', 'Cannot delete PO: Some items have already been added to stock. Please reverse the process first.');
        }

        $poNumber = $purchaseOrder->po_number;

        // Delete all items first
        $purchaseOrder->items()->delete();

        // Delete the PO
        $purchaseOrder->delete();

        return redirect()
            ->route('admin.purchase-orders.index')
            ->with('success', "Purchase Order {$poNumber} has been permanently deleted.");
    }

    // Mark item as compliant
    public function markCompliant(Request $request, PurchaseOrder $purchaseOrder, PurchaseOrderItem $item)
    {
        $validated = $request->validate([
            'compliance_notes' => 'nullable|string',
        ]);

        $item->markAsCompliant($validated['compliance_notes'] ?? null);

        return redirect()
            ->route('admin.purchase-orders.show', $purchaseOrder)
            ->with('success', 'Item marked as compliant!');
    }

    // Mark item as non-compliant
    public function markNonCompliant(Request $request, PurchaseOrder $purchaseOrder, PurchaseOrderItem $item)
    {
        $validated = $request->validate([
            'compliance_notes' => 'required|string',
        ]);

        $item->markAsNonCompliant($validated['compliance_notes']);

        return redirect()
            ->route('admin.purchase-orders.show', $purchaseOrder)
            ->with('warning', 'Item marked as NON-COMPLIANT. This item cannot be added to stock.');
    }

    // Reverse compliance status (from non-compliant back to pending)
    // This resets the ENTIRE PO back to pending approval status
    public function reverseCompliance(PurchaseOrder $purchaseOrder, PurchaseOrderItem $item)
    {
        if ($item->compliance_status !== 'non_compliant') {
            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder)
                ->with('error', 'Only non-compliant items can be reversed.');
        }

        // IMPORTANT: First, remove all items from stock that were already added
        // This must happen BEFORE resetting status to preserve quantity_received data
        foreach ($purchaseOrder->items as $poItem) {
            if ($poItem->added_to_stock) {
                $poItem->removeFromStock();
            }
        }

        // Reset ALL items' compliance status and received status
        foreach ($purchaseOrder->items as $poItem) {
            $poItem->update([
                'compliance_status' => null,
                'compliance_notes' => null,
                'quantity_received' => 0,
                'added_to_stock' => false,
                'stock_added_at' => null,
                'status' => 'pending',
            ]);
        }

        // Reset the entire PO back to pending approval
        $purchaseOrder->update([
            'status' => 'pending',
            'approval_status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
            'rejected_at' => null,
            'actual_delivery_date' => null,
            'received_by' => null,
        ]);

        // Refresh the purchase order to ensure fresh data
        $purchaseOrder->refresh();
        $purchaseOrder->load('items.item');

        return redirect()
            ->route('admin.purchase-orders.show', $purchaseOrder)
            ->with('warning', 'PO has been reversed and sent back for re-approval. Stock quantities have been adjusted. PO Number remains: ' . $purchaseOrder->po_number . '. All items must be re-received and re-checked after approval.');
    }

    // Return goods and create reorder (copies all items)
    public function returnAndReorder(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Check if PO has non-compliant items
        if (!$purchaseOrder->hasNonCompliantItems()) {
            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder)
                ->with('error', 'Can only return purchase orders with non-compliant items.');
        }

        // Check if already returned
        if ($purchaseOrder->returned_at) {
            return redirect()
                ->route('admin.purchase-orders.show', $purchaseOrder)
                ->with('error', 'This Purchase Order has already been returned.');
        }

        $validated = $request->validate([
            'return_reason' => 'required|string|max:1000',
        ]);

        // Create reorder (this will copy all items)
        $newPO = $purchaseOrder->returnAndReorder(auth()->id(), $validated['return_reason']);

        return redirect()
            ->route('admin.purchase-orders.show', $newPO)
            ->with('success', 'Items returned! New PO created: ' . $newPO->po_number . ' with ' . $newPO->total_items . ' items.');
    }
}

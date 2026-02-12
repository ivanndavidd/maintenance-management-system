<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends TenantModels
{
    protected $fillable = [
        'purchase_order_id',
        'item_type',
        'item_id',
        'is_unlisted',
        'unlisted_item_name',
        'unlisted_item_description',
        'unlisted_item_specs',
        'quantity_ordered',
        'quantity_received',
        'unit_price',
        'subtotal',
        'unit',
        'supplier',
        'status',
        'compliance_status',
        'compliance_notes',
        'added_to_stock',
        'stock_added_at',
        'notes',
    ];

    protected $casts = [
        'is_unlisted' => 'boolean',
        'quantity_ordered' => 'integer',
        'quantity_received' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'added_to_stock' => 'boolean',
        'stock_added_at' => 'datetime',
    ];

    // Purchase Order relationship
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    // Polymorphic relationship to item (Sparepart or Tool)
    public function item()
    {
        return $this->morphTo();
    }

    // Sparepart relationship helper
    public function sparepart()
    {
        return $this->belongsTo(Sparepart::class, 'item_id')->where('item_type', Sparepart::class);
    }

    // Tool relationship helper
    public function tool()
    {
        return $this->belongsTo(Tool::class, 'item_id')->where('item_type', Tool::class);
    }

    // Calculate remaining quantity to receive
    public function getRemainingQuantity()
    {
        return $this->quantity_ordered - $this->quantity_received;
    }

    // Check if item is fully received
    public function isFullyReceived()
    {
        return $this->quantity_received >= $this->quantity_ordered;
    }

    // Check if item is partially received
    public function isPartiallyReceived()
    {
        return $this->quantity_received > 0 && $this->quantity_received < $this->quantity_ordered;
    }

    // Get item name (from master data or unlisted)
    public function getItemName()
    {
        if ($this->is_unlisted) {
            return $this->unlisted_item_name;
        }

        $item = $this->item;
        if (!$item) {
            return 'Unknown Item';
        }

        if ($item instanceof Sparepart) {
            return $item->sparepart_name;
        } elseif ($item instanceof Tool) {
            return $item->sparepart_name;
        }

        return 'Unknown Item';
    }

    // Get item code (from master data or unlisted)
    public function getItemCode()
    {
        if ($this->is_unlisted) {
            return 'UNLISTED';
        }

        $item = $this->item;
        if (!$item) {
            return '-';
        }

        if ($item instanceof Sparepart) {
            return $item->sparepart_id ?? '-';
        } elseif ($item instanceof Tool) {
            return $item->tool_id ?? '-';
        }

        return '-';
    }

    // Mark as compliant
    public function markAsCompliant($notes = null)
    {
        $this->compliance_status = 'compliant';
        $this->compliance_notes = $notes;
        $this->save();
    }

    // Mark as non-compliant
    public function markAsNonCompliant($notes)
    {
        $this->compliance_status = 'non_compliant';
        $this->compliance_notes = $notes;
        $this->save();
    }

    // Add to stock (only for compliant, listed items)
    public function addToStock()
    {
        if ($this->is_unlisted) {
            throw new \Exception(
                'Cannot add unlisted item to stock. Please add to master data first.',
            );
        }

        if ($this->compliance_status !== 'compliant') {
            throw new \Exception('Cannot add to stock: Item is not marked as compliant');
        }

        if ($this->added_to_stock) {
            throw new \Exception('Item has already been added to stock');
        }

        $item = $this->item;
        if ($item) {
            $item->quantity += $this->quantity_received;
            $item->save();

            $this->added_to_stock = true;
            $this->stock_added_at = now();
            $this->save();

            return true;
        }

        return false;
    }

    // Remove from stock (reverse process when PO is reversed)
    public function removeFromStock()
    {
        if (!$this->added_to_stock) {
            return false; // Already not in stock, nothing to remove
        }

        if ($this->is_unlisted) {
            return false; // Unlisted items don't affect stock
        }

        $item = $this->item;
        if ($item) {
            // Reduce stock quantity
            $item->quantity -= $this->quantity_received;

            // Prevent negative stock
            if ($item->quantity < 0) {
                $item->quantity = 0;
            }

            $item->save();
            return true;
        }

        return false;
    }

    // Check if can add to stock
    public function canAddToStock()
    {
        return !$this->is_unlisted &&
            $this->status === 'received' &&
            $this->compliance_status === 'compliant' &&
            !$this->added_to_stock;
    }

    // Update status based on quantity received
    public function updateStatus()
    {
        if ($this->quantity_received >= $this->quantity_ordered) {
            $this->status = 'received';
        } elseif ($this->quantity_received > 0) {
            $this->status = 'partial_received';
        } else {
            $this->status = 'pending';
        }

        $this->save();
    }

    // Receive items
    public function receiveItems($quantity)
    {
        $this->quantity_received += $quantity;
        $this->updateStatus();
    }
}

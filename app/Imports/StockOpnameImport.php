<?php

namespace App\Imports;

use App\Models\StockOpnameScheduleItem;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;

class StockOpnameImport
{
    protected $userId;
    protected $scheduleId;
    protected $successCount = 0;
    protected $errorCount = 0;
    protected $errors = [];
    protected $importedData = []; // Store imported data for UI preview

    public function __construct($userId, $scheduleId)
    {
        $this->userId = $userId;
        $this->scheduleId = $scheduleId;
    }

    /**
     * Import from uploaded file - only parse and return data, don't save
     */
    public function import($filePath)
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();

            // Start from row 2 (skip header)
            for ($row = 2; $row <= $highestRow; $row++) {
                try {
                    // Read cell values
                    // Format: Item Type (A), Item Code (B), Item Name (C), Location (D), Physical Qty (E), Notes (F)
                    $itemCode = $sheet->getCell('B' . $row)->getValue();
                    $physicalQty = $sheet->getCell('E' . $row)->getValue();
                    $notes = $sheet->getCell('F' . $row)->getValue();

                    // Skip if no item code or physical quantity provided
                    if (empty($itemCode) || $physicalQty === null || $physicalQty === '') {
                        continue;
                    }

                    // Validate physical quantity is numeric
                    if (!is_numeric($physicalQty)) {
                        $this->errorCount++;
                        $this->errors[] = "Row {$row}: Physical Qty must be a number";
                        continue;
                    }

                    // Find the item by item code (material_code for sparepart/tool, equipment_id for asset)
                    $item = StockOpnameScheduleItem::where('schedule_id', $this->scheduleId)
                        ->where(function($query) use ($itemCode) {
                            $query->whereHas('sparepart', function($q) use ($itemCode) {
                                $q->where('material_code', $itemCode);
                            })
                            ->orWhereHas('tool', function($q) use ($itemCode) {
                                $q->where('material_code', $itemCode);
                            })
                            ->orWhereHas('asset', function($q) use ($itemCode) {
                                $q->where('equipment_id', $itemCode);
                            });
                        })
                        ->first();

                    if (!$item) {
                        $this->errorCount++;
                        $this->errors[] = "Row {$row}: Item Code {$itemCode} not found in this schedule";
                        continue;
                    }

                    // Check if already executed
                    if ($item->execution_status !== 'pending') {
                        $this->errorCount++;
                        $this->errors[] = "Row {$row}: Item already executed";
                        continue;
                    }

                    // Store data for UI (don't save to database yet)
                    $this->importedData[] = [
                        'item_id' => $item->id,
                        'physical_quantity' => (int) $physicalQty,
                        'notes' => $notes,
                    ];

                    $this->successCount++;

                } catch (\Exception $e) {
                    $this->errorCount++;
                    $this->errors[] = "Row {$row}: " . $e->getMessage();
                    Log::error("Stock Opname Import Row Error", [
                        'row' => $row,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

        } catch (\Exception $e) {
            $this->errorCount++;
            $this->errors[] = "File reading error: " . $e->getMessage();
            Log::error("Stock Opname Import File Error", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get success count
     */
    public function getSuccessCount()
    {
        return $this->successCount;
    }

    /**
     * Get error count
     */
    public function getErrorCount()
    {
        return $this->errorCount;
    }

    /**
     * Get errors
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get imported data for UI preview
     */
    public function getImportedData()
    {
        return $this->importedData;
    }
}

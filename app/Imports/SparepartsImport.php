<?php

namespace App\Imports;

use App\Models\Sparepart;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Validators\Failure;
use Throwable;

class SparepartsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure, WithBatchInserts, WithChunkReading
{
    use Importable;

    protected $errors = [];
    protected $failures = [];
    protected $successCount = 0;

    public function model(array $row)
    {
        $this->successCount++;

        return new Sparepart([
            'sparepart_id' => Sparepart::generateSparepartId(),
            'material_code' => !empty($row['material_code']) ? trim($row['material_code']) : null,
            'equipment_type' => strtolower(trim($row['equipment_type'])),
            'sparepart_name' => trim($row['sparepart_name']),
            'brand' => !empty($row['brand']) ? trim($row['brand']) : null,
            'model' => !empty($row['model']) ? trim($row['model']) : null,
            'quantity' => intval($row['quantity']),
            'minimum_stock' => intval($row['minimum_stock']),
            'unit' => strtolower(trim($row['unit'])),
            'parts_price' => floatval($row['parts_price']),
            'vulnerability' => !empty($row['vulnerability']) ? strtolower(trim($row['vulnerability'])) : null,
            'location' => !empty($row['location']) ? trim($row['location']) : null,
            'item_type' => 'sparepart',
            'add_part_by' => auth()->id(),
        ]);
    }

    public function rules(): array
    {
        return [
            'equipment_type' => 'nullable|string|max:255',
            'sparepart_name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'unit' => 'required|in:pcs,unit,set,box,pack,kg,liter,meter',
            'parts_price' => 'required|numeric|min:0',
            'vulnerability' => 'nullable|in:low,medium,high,critical',
            'location' => 'nullable|string|max:255',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'equipment_type.required' => 'Equipment type is required',
            'equipment_type.in' => 'Equipment type must be: electrical, mechanical, pneumatic, hydraulic, electronic, or other',
            'sparepart_name.required' => 'Sparepart name is required',
            'quantity.required' => 'Quantity is required',
            'quantity.integer' => 'Quantity must be a number',
            'quantity.min' => 'Quantity cannot be negative',
            'minimum_stock.required' => 'Minimum stock is required',
            'minimum_stock.integer' => 'Minimum stock must be a number',
            'minimum_stock.min' => 'Minimum stock cannot be negative',
            'unit.required' => 'Unit is required',
            'unit.in' => 'Unit must be: pcs, unit, set, box, pack, kg, liter, or meter',
            'parts_price.required' => 'Parts price is required',
            'parts_price.numeric' => 'Parts price must be a number',
            'parts_price.min' => 'Parts price cannot be negative',
            'vulnerability.in' => 'Vulnerability must be: low, medium, high, or critical',
        ];
    }

    public function onError(Throwable $e)
    {
        $this->errors[] = $e->getMessage();
    }

    public function onFailure(Failure ...$failures)
    {
        $this->failures = array_merge($this->failures, $failures);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getFailures()
    {
        return $this->failures;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}

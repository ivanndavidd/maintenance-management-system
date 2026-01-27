<?php

namespace App\Imports;

use App\Models\Tool;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\Log;

class ToolsImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, SkipsOnError
{
    use SkipsErrors;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Check if tool already exists
        $existingTool = Tool::where('tool_id', $row['tool_id'])->first();

        if ($existingTool) {
            // Update existing tool
            $existingTool->update([
                'sparepart_name' => $row['tool_name'] ?? $existingTool->sparepart_name,
                'material_code' => $row['material_code'] ?? $existingTool->material_code,
                'equipment_type' => 'Tools',
                'brand' => $row['brand'] ?? $existingTool->brand,
                'model' => $row['model'] ?? $existingTool->model,
                'quantity' => $row['quantity'] ?? $existingTool->quantity,
                'unit' => $row['unit'] ?? $existingTool->unit,
                'minimum_stock' => $row['minimum_stock'] ?? $existingTool->minimum_stock,
                'location' => $row['location'] ?? $existingTool->location,
                'parts_price' => $row['parts_price'] ?? $existingTool->parts_price,
            ]);

            return null;
        }

        // Create new tool
        return new Tool([
            'tool_id' => $row['tool_id'],
            'sparepart_name' => $row['tool_name'],
            'material_code' => $row['material_code'] ?? null,
            'equipment_type' => 'Tools',
            'brand' => $row['brand'] ?? null,
            'model' => $row['model'] ?? null,
            'quantity' => $row['quantity'] ?? 0,
            'unit' => $row['unit'] ?? 'pcs',
            'minimum_stock' => $row['minimum_stock'] ?? 0,
            'location' => $row['location'] ?? null,
            'parts_price' => $row['parts_price'] ?? 0,
        ]);
    }

    public function rules(): array
    {
        return [
            'tool_id' => 'required|string',
            'tool_name' => 'required|string',
            'quantity' => 'nullable|numeric',
            'unit' => 'nullable|string',
            'parts_price' => 'nullable|numeric',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'tool_id.required' => 'Tool ID is required',
            'tool_name.required' => 'Tool Name is required',
        ];
    }
}

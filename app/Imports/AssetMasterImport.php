<?php

namespace App\Imports;

use App\Models\Asset;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AssetMasterImport
{
    protected $importedCount = 0;
    protected $errors = [];
    protected $currentSequence = null;

    /**
     * Import assets from Excel file with multiple sheets
     *
     * @param string $filePath Full path to uploaded Excel file
     * @return array ['success' => bool, 'imported' => count, 'errors' => array]
     */
    public function importFromFile($filePath)
    {
        try {
            // Load the Excel file using PhpSpreadsheet
            $spreadsheet = IOFactory::load($filePath);

            // Initialize sequence number ONCE for entire import
            $date = now()->format('Ymd');
            $prefix = 'AST' . $date;

            $latest = Asset::where('asset_id', 'like', $prefix . '%')
                ->orderBy('asset_id', 'desc')
                ->first();

            $this->currentSequence = $latest ? ((int)substr($latest->asset_id, -3)) + 1 : 1;

            // Get all sheet names
            $sheetNames = $spreadsheet->getSheetNames();

            foreach ($sheetNames as $sheetIndex => $sheetName) {
                $this->processSheet($spreadsheet, $sheetName, $sheetIndex, $prefix);
            }

            return [
                'success' => true,
                'imported' => $this->importedCount,
                'errors' => $this->errors
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error importing file: ' . $e->getMessage(),
                'errors' => $this->errors
            ];
        }
    }

    /**
     * Process a single sheet
     *
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
     * @param string $sheetName
     * @param int $sheetIndex
     * @param string $prefix
     */
    protected function processSheet($spreadsheet, $sheetName, $sheetIndex, $prefix)
    {
        try {
            $sheet = $spreadsheet->getSheetByName($sheetName);

            // Get location from cell A1
            $location = $sheet->getCell('A1')->getValue();

            // Equipment type is the sheet name
            $equipmentType = $sheetName;

            // Get data starting from row 2 (after headers)
            $highestRow = $sheet->getHighestRow();

            for ($row = 2; $row <= $highestRow; $row++) {
                try {
                    // Get Equipment ID from column B
                    $equipmentId = $sheet->getCell('B' . $row)->getValue();

                    // Get Asset Name (Description) from column C
                    $assetName = $sheet->getCell('C' . $row)->getValue();

                    // Skip if asset name is empty
                    if (empty($assetName)) {
                        continue;
                    }

                    // Generate Asset ID using class-level sequence counter
                    $assetId = $prefix . str_pad($this->currentSequence, 3, '0', STR_PAD_LEFT);
                    $this->currentSequence++;

                    // Use direct DB insert to completely bypass Eloquent model events
                    \DB::table('assets_master')->insert([
                        'asset_id' => $assetId,
                        'equipment_id' => $equipmentId,
                        'asset_name' => $assetName,
                        'location' => $location,
                        'equipment_type' => $equipmentType,
                        'status' => 'active',
                        'created_by' => auth()->check() ? auth()->id() : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $this->importedCount++;
                } catch (\Exception $e) {
                    $this->errors[] = "Sheet '{$sheetName}', Row {$row}: " . $e->getMessage();
                }
            }
        } catch (\Exception $e) {
            $this->errors[] = "Sheet '{$sheetName}': " . $e->getMessage();
        }
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}

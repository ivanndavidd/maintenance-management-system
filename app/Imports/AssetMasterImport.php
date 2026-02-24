<?php

namespace App\Imports;

use PhpOffice\PhpSpreadsheet\IOFactory;

class AssetMasterImport
{
    protected $importedCount = 0;
    protected $errors = [];

    public function importFromFile($filePath)
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheetNames  = $spreadsheet->getSheetNames();

            foreach ($sheetNames as $sheetIndex => $sheetName) {
                $this->processSheet($spreadsheet, $sheetName);
            }

            return [
                'success'  => true,
                'imported' => $this->importedCount,
                'errors'   => $this->errors,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error importing file: ' . $e->getMessage(),
                'errors'  => $this->errors,
            ];
        }
    }

    protected function processSheet($spreadsheet, $sheetName)
    {
        try {
            $sheet         = $spreadsheet->getSheetByName($sheetName);
            $location      = $sheet->getCell('A1')->getValue();
            $equipmentType = $sheetName;
            $highestRow    = $sheet->getHighestRow();

            for ($row = 2; $row <= $highestRow; $row++) {
                try {
                    $equipmentId = $sheet->getCell('B' . $row)->getValue();
                    $assetName   = $sheet->getCell('C' . $row)->getValue();

                    if (empty($assetName)) continue;

                    \DB::table('assets_master')->insert([
                        'equipment_id'   => $equipmentId,
                        'asset_name'     => $assetName,
                        'location'       => $location,
                        'equipment_type' => $equipmentType,
                        'status'         => 'active',
                        'created_by'     => auth()->check() ? auth()->id() : null,
                        'created_at'     => now(),
                        'updated_at'     => now(),
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

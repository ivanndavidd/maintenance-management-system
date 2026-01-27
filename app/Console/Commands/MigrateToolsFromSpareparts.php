<?php

namespace App\Console\Commands;

use App\Models\Sparepart;
use App\Models\Tool;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateToolsFromSpareparts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spareparts:migrate-tools {--delete : Delete spareparts after migration} {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate spareparts with equipment_type "Tools" to tools table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of Tools from Spareparts...');
        $this->newLine();

        // Get all spareparts with equipment_type = 'Tools'
        $toolSpareparts = Sparepart::where('equipment_type', 'Tools')->get();

        if ($toolSpareparts->isEmpty()) {
            $this->warn('No spareparts found with equipment_type = "Tools"');
            return 0;
        }

        $this->info("Found {$toolSpareparts->count()} items to migrate.");
        $this->newLine();

        if (!$this->option('force') && !$this->confirm('Do you want to continue?')) {
            $this->info('Migration cancelled.');
            return 0;
        }

        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($toolSpareparts as $index => $sparepart) {
                try {
                    // Generate tool_id
                    $toolId = Tool::generateToolId();

                    // Create tool from sparepart data
                    Tool::create([
                        'tool_id' => $toolId,
                        'equipment_type' => $sparepart->equipment_type,
                        'material_code' => $sparepart->material_code,
                        'sparepart_name' => $sparepart->sparepart_name,
                        'brand' => $sparepart->brand,
                        'model' => $sparepart->model,
                        'quantity' => $sparepart->quantity,
                        'unit' => $sparepart->unit,
                        'minimum_stock' => $sparepart->minimum_stock,
                        'vulnerability' => $sparepart->vulnerability,
                        'location' => $sparepart->location,
                        'parts_price' => $sparepart->parts_price,
                        'item_type' => 'tool',
                        'path' => $sparepart->path,
                        'physical_quantity' => $sparepart->physical_quantity,
                        'discrepancy_qty' => $sparepart->discrepancy_qty,
                        'discrepancy_value' => $sparepart->discrepancy_value,
                        'opname_status' => $sparepart->opname_status,
                        'opname_date' => $sparepart->opname_date,
                        'opname_by' => $sparepart->opname_by,
                        'verified_by' => $sparepart->verified_by,
                        'adjustment_qty' => $sparepart->adjustment_qty,
                        'adjustment_reason' => $sparepart->adjustment_reason,
                        'last_opname_at' => $sparepart->last_opname_at,
                        'add_part_by' => $sparepart->add_part_by,
                        'created_at' => $sparepart->created_at,
                        'updated_at' => $sparepart->updated_at,
                    ]);

                    $successCount++;
                    $this->info("[" . ($index + 1) . "/{$toolSpareparts->count()}] Migrated: {$sparepart->sparepart_name} → {$toolId}");

                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Failed to migrate {$sparepart->sparepart_name}: " . $e->getMessage();
                    $this->error("[" . ($index + 1) . "/{$toolSpareparts->count()}] Error: {$sparepart->sparepart_name}");
                }
            }

            // If --delete flag is set, delete the migrated spareparts
            if ($this->option('delete')) {
                $this->newLine();
                if ($this->option('force') || $this->confirm('Delete migrated spareparts from spareparts table?')) {
                    $deleteCount = Sparepart::where('equipment_type', 'Tools')->delete();
                    $this->info("Deleted {$deleteCount} spareparts.");
                }
            }

            DB::commit();

            $this->newLine();
            $this->info('===== Migration Summary =====');
            $this->info("Successfully migrated: {$successCount}");
            if ($errorCount > 0) {
                $this->warn("Failed: {$errorCount}");
                $this->newLine();
                $this->error('Errors:');
                foreach ($errors as $error) {
                    $this->error("- {$error}");
                }
            }

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Migration failed: ' . $e->getMessage());
            return 1;
        }
    }
}

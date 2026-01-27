<?php

namespace App\Console\Commands;

use App\Models\Tool;
use Illuminate\Console\Command;

class UpdateToolIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tools:update-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate tool_id for all tools that have NULL tool_id';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting tool_id update...');
        $this->newLine();

        $toolsWithoutId = Tool::whereNull('tool_id')->get();

        if ($toolsWithoutId->isEmpty()) {
            $this->warn('No tools found without tool_id.');
            return 0;
        }

        $this->info("Found {$toolsWithoutId->count()} tools without tool_id.");
        $this->newLine();

        $successCount = 0;
        $errorCount = 0;

        foreach ($toolsWithoutId as $index => $tool) {
            try {
                $toolId = Tool::generateToolId();
                $tool->tool_id = $toolId;
                $tool->save();

                $successCount++;
                $this->info("[" . ($index + 1) . "/{$toolsWithoutId->count()}] Updated: {$tool->sparepart_name} → {$toolId}");
            } catch (\Exception $e) {
                $errorCount++;
                $this->error("[" . ($index + 1) . "/{$toolsWithoutId->count()}] Error: {$tool->sparepart_name} - " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info('===== Update Summary =====');
        $this->info("Successfully updated: {$successCount}");
        if ($errorCount > 0) {
            $this->warn("Failed: {$errorCount}");
        }

        return 0;
    }
}

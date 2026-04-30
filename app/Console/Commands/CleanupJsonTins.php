<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;

class CleanupJsonTins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'company:cleanup-json-tins {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up TIN numbers stored as JSON objects and extract the actual TIN value';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }

        $this->info('Scanning for TIN numbers stored as JSON objects...');

        $companies = Company::all();
        $updatedCount = 0;

        foreach ($companies as $company) {
            $currentTin = $company->tin_number;

            // Check if TIN is stored as JSON
            if (is_string($currentTin) && str_starts_with($currentTin, '{') && str_ends_with($currentTin, '}')) {
                $decoded = json_decode($currentTin, true);

                if (json_last_error() === JSON_ERROR_NONE && isset($decoded['tin'])) {
                    $extractedTin = $decoded['tin'];

                    $this->line("Company: {$company->name} (ID: {$company->id})");
                    $this->line("  Current TIN: {$currentTin}");
                    $this->line("  Extracted TIN: {$extractedTin}");

                    if (!$dryRun) {
                        $company->update(['tin_number' => $extractedTin]);
                        $this->info("  ✅ Updated");
                    } else {
                        $this->info("  🔍 Would update");
                    }

                    $updatedCount++;
                } else {
                    $this->warn("  ⚠️  Could not parse JSON: {$currentTin}");
                }
            }
        }

        if ($updatedCount === 0) {
            $this->info('No JSON TIN numbers found.');
        } else {
            if ($dryRun) {
                $this->info("Found {$updatedCount} companies with JSON TIN numbers that would be cleaned up.");
            } else {
                $this->info("Successfully cleaned up {$updatedCount} TIN numbers.");
            }
        }
    }
}

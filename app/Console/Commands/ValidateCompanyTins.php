<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\TinValidationService;
use Illuminate\Console\Command;

class ValidateCompanyTins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'company:validate-tins {--company-id= : Validate specific company ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate TIN for companies with active LHDN credentials';

    public function __construct(
        private TinValidationService $tinService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $companyId = $this->option('company-id');

        if ($companyId) {
            $companies = Company::where('id', $companyId)->get();
        } else {
            $companies = Company::whereHas('lhdnCredential', function($query) {
                $query->where('status', 'active');
            })->get();
        }

        if ($companies->isEmpty()) {
            $this->info('No companies found with active LHDN credentials.');
            return;
        }

        $this->info("Found {$companies->count()} companies with active LHDN credentials.");

        $bar = $this->output->createProgressBar($companies->count());
        $bar->start();

        $validated = 0;
        $failed = 0;

        foreach ($companies as $company) {
            try {
                $result = $this->tinService->validateCompanyTin($company);
                if ($result['success']) {
                    $validated++;
                    $this->line("\n✓ Company {$company->id} ({$company->name}): {$result['message']}");
                } else {
                    $failed++;
                    $this->line("\n✗ Company {$company->id} ({$company->name}): {$result['message']}");
                }
            } catch (\Exception $e) {
                $failed++;
                $this->line("\n✗ Company {$company->id} ({$company->name}): Error - {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Validation complete:");
        $this->info("✓ Validated: {$validated}");
        $this->info("✗ Failed: {$failed}");
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\MyInvoisSdkService;
use Illuminate\Console\Command;

class UpdateInvoiceStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-invoice-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update LHDN statuses for invoices from accepted/submitted to their actual validation status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting invoice status update...');

        $invoices = Invoice::whereNotNull('lhdn_uuid')
            ->whereIn('lhdn_status', ['accepted', 'submitted'])
            ->get();

        $this->info("Found {$invoices->count()} invoices to update");

        $updated = 0;
        $errors = 0;

        foreach ($invoices as $invoice) {
            try {
                $this->line("Updating invoice {$invoice->id} ({$invoice->invoice_number})");

                $statusResponse = app(MyInvoisSdkService::class)->getDocumentStatus($invoice);

                if ($statusResponse && isset($statusResponse['status'])) {
                    $this->info("  ✓ Updated to: {$statusResponse['status']}");
                    $updated++;
                } else {
                    $this->warn("  ⚠ No status returned for invoice {$invoice->id}");
                }

                // Add a small delay to avoid overwhelming the API
                sleep(1);

            } catch (\Exception $e) {
                $this->error("  ✗ Failed to update invoice {$invoice->id}: {$e->getMessage()}");
                $errors++;
            }
        }

        $this->info("Completed: {$updated} updated, {$errors} errors");
    }
}

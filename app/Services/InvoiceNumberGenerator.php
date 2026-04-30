<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Invoice;
use Carbon\Carbon;

class InvoiceNumberGenerator
{
    /**
     * Generate the next invoice number for a company based on their format.
     */
    public function generate(Company $company, ?Carbon $invoiceDate = null): string
    {
        $format = $company->invoice_prefix ?? 'INV-{####}';
        $date = $invoiceDate ?? now();

        // Get the next sequence number
        $sequence = $this->getNextSequence($company, $format, $date);

        // Replace placeholders
        return $this->replacePlaceholders($format, $date, $sequence);
    }

    /**
     * Get the next sequence number based on the format and date.
     */
    protected function getNextSequence(Company $company, string $format, Carbon $date): int
    {
        // Determine if the format includes date-based placeholders
        $hasYear = str_contains($format, '{YYYY}') || str_contains($format, '{YY}');
        $hasMonth = str_contains($format, '{MM}');
        $hasDay = str_contains($format, '{DD}');

        // Build the query to find the last invoice
        $query = Invoice::where('company_id', $company->id);

        // If format includes year, only count invoices from the same year
        if ($hasYear) {
            $query->whereYear('invoice_date', $date->year);
        }

        // If format includes month, only count invoices from the same month
        if ($hasMonth) {
            $query->whereMonth('invoice_date', $date->month);
        }

        // If format includes day, only count invoices from the same day
        if ($hasDay) {
            $query->whereDate('invoice_date', $date->toDateString());
        }

        // Get the count and increment
        $count = $query->count();

        return $count + 1;
    }

    /**
     * Replace placeholders in the format with actual values.
     */
    protected function replacePlaceholders(string $format, Carbon $date, int $sequence): string
    {
        // Determine the padding length from the format (e.g., #### = 4 digits)
        preg_match('/{(#+)}/', $format, $matches);
        $padding = isset($matches[1]) ? strlen($matches[1]) : 4;

        $replacements = [
            '{YYYY}' => $date->format('Y'),
            '{YY}' => $date->format('y'),
            '{MM}' => $date->format('m'),
            '{DD}' => $date->format('d'),
            '{####}' => str_pad($sequence, $padding, '0', STR_PAD_LEFT),
            '{###}' => str_pad($sequence, 3, '0', STR_PAD_LEFT),
            '{##}' => str_pad($sequence, 2, '0', STR_PAD_LEFT),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $format);
    }

    /**
     * Preview what the next invoice number would be without saving.
     */
    public function preview(Company $company, ?Carbon $invoiceDate = null): string
    {
        return $this->generate($company, $invoiceDate);
    }
}

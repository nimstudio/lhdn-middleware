<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BulkInvoiceImport implements WithMultipleSheets
{
    public $invoiceData = [];

    public $lineItemData = [];

    public function sheets(): array
    {
        return [
            0 => new InvoiceDetailsSheetImport($this),
            1 => new LineItemsSheetImport($this),
        ];
    }

    public function getInvoiceData()
    {
        return $this->invoiceData;
    }

    public function getLineItemData()
    {
        return $this->lineItemData;
    }
}

// Sheet import for Invoice Details (Sheet 1)
class InvoiceDetailsSheetImport implements ToCollection
{
    protected $parent;

    public function __construct($parent)
    {
        $this->parent = $parent;
    }

    public function collection(Collection $rows)
    {
        $headers = [];
        foreach ($rows as $index => $row) {
            if ($index === 0) {
                // Skip instruction row
                continue;
            } elseif ($index === 1) {
                // Second row contains actual headers
                $headers = $row->toArray();

                continue;
            }

            // Skip empty rows
            $rowData = $row->toArray();
            if (empty(array_filter($rowData))) {
                continue;
            }

            // Map row data to associative array using headers
            $data = [];
            foreach ($headers as $colIndex => $header) {
                $data[$header] = $rowData[$colIndex] ?? null;
            }

            $this->parent->invoiceData[] = $data;
        }
    }
}

// Sheet import for Line Item Details (Sheet 2)
class LineItemsSheetImport implements ToCollection
{
    protected $parent;

    public function __construct($parent)
    {
        $this->parent = $parent;
    }

    public function collection(Collection $rows)
    {
        $headers = [];
        foreach ($rows as $index => $row) {
            if ($index === 0) {
                // Skip instruction row
                continue;
            } elseif ($index === 1) {
                // Second row contains actual headers
                $headers = $row->toArray();

                continue;
            }

            // Skip empty rows
            $rowData = $row->toArray();
            if (empty(array_filter($rowData))) {
                continue;
            }

            // Map row data to associative array using headers
            $data = [];
            foreach ($headers as $colIndex => $header) {
                $data[$header] = $rowData[$colIndex] ?? null;
            }

            $this->parent->lineItemData[] = $data;
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class InvoiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $invoiceTypes = [
            ['code' => '01', 'description' => 'Invoice', 'sort_order' => 1],
            ['code' => '02', 'description' => 'Credit Note', 'sort_order' => 2],
            ['code' => '03', 'description' => 'Debit Note', 'sort_order' => 3],
            ['code' => '04', 'description' => 'Refund Note', 'sort_order' => 4],
            ['code' => '11', 'description' => 'Self-billed Invoice', 'sort_order' => 5],
            ['code' => '12', 'description' => 'Self-billed Credit Note', 'sort_order' => 6],
            ['code' => '13', 'description' => 'Self-billed Debit Note', 'sort_order' => 7],
            ['code' => '14', 'description' => 'Self-billed Refund Note', 'sort_order' => 8],
        ];

        foreach ($invoiceTypes as $invoiceType) {
            \DB::table('invoice_types')->updateOrInsert(
                ['code' => $invoiceType['code']],
                array_merge($invoiceType, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
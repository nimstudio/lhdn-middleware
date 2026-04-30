<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TaxTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxTypes = [
            ['code' => '01', 'description' => 'Sales Tax', 'sort_order' => 1],
            ['code' => '02', 'description' => 'Service Tax', 'sort_order' => 2],
            ['code' => '03', 'description' => 'Tourism Tax', 'sort_order' => 3],
            ['code' => '04', 'description' => 'High-Value Goods Tax', 'sort_order' => 4],
            ['code' => '05', 'description' => 'Sales Tax on Low Value Goods', 'sort_order' => 5],
            ['code' => '06', 'description' => 'Not Applicable', 'sort_order' => 6],
            ['code' => 'E', 'description' => 'Tax exemption (where applicable)', 'sort_order' => 7],
        ];

        foreach ($taxTypes as $taxType) {
            \DB::table('tax_types')->updateOrInsert(
                ['code' => $taxType['code']],
                array_merge($taxType, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}

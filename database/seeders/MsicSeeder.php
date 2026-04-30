<?php

namespace Database\Seeders;

use App\Models\Msic;
use Illuminate\Database\Seeder;

class MsicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = base_path('.cursor/Malaysia Standard Industrial Classification.json');

        if (! file_exists($jsonPath)) {
            $this->command->error('MSIC JSON file not found at: '.$jsonPath);

            return;
        }

        $jsonContent = file_get_contents($jsonPath);
        $msicData = json_decode($jsonContent, true);

        if (! $msicData) {
            $this->command->error('Failed to parse MSIC JSON file');

            return;
        }

        $this->command->info('Seeding '.count($msicData).' MSIC records...');

        $batch = [];
        $batchSize = 500;
        $seenCodes = [];

        foreach ($msicData as $index => $item) {
            $code = $item['Code'];

            // Skip duplicates
            if (isset($seenCodes[$code])) {
                $this->command->warn('Skipping duplicate code: '.$code);

                continue;
            }

            $seenCodes[$code] = true;

            $batch[] = [
                'code' => $code,
                'description' => $item['Description'],
                'category' => $item['MSIC Category Reference'] ?: null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($batch) >= $batchSize) {
                Msic::insert($batch);
                $batch = [];
                $this->command->info('Seeded '.($index + 1).' records...');
            }
        }

        if (! empty($batch)) {
            Msic::insert($batch);
        }

        $this->command->info('MSIC seeding completed successfully!');
    }
}

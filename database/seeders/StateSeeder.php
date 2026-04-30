<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $states = [
            ['name' => 'Johor', 'code' => 'JHR', 'lhdn_code' => '01'],
            ['name' => 'Kedah', 'code' => 'KDH', 'lhdn_code' => '02'],
            ['name' => 'Kelantan', 'code' => 'KTN', 'lhdn_code' => '03'],
            ['name' => 'Melaka', 'code' => 'MLK', 'lhdn_code' => '04'],
            ['name' => 'Negeri Sembilan', 'code' => 'NSN', 'lhdn_code' => '05'],
            ['name' => 'Pahang', 'code' => 'PHG', 'lhdn_code' => '06'],
            ['name' => 'Pulau Pinang', 'code' => 'PNG', 'lhdn_code' => '07'],
            ['name' => 'Perak', 'code' => 'PRK', 'lhdn_code' => '08'],
            ['name' => 'Perlis', 'code' => 'PLS', 'lhdn_code' => '09'],
            ['name' => 'Selangor', 'code' => 'SGR', 'lhdn_code' => '10'],
            ['name' => 'Terengganu', 'code' => 'TRG', 'lhdn_code' => '11'],
            ['name' => 'Sabah', 'code' => 'SBH', 'lhdn_code' => '12'],
            ['name' => 'Sarawak', 'code' => 'SWK', 'lhdn_code' => '13'],
            ['name' => 'Wilayah Persekutuan Kuala Lumpur', 'code' => 'KUL', 'lhdn_code' => '14'],
            ['name' => 'Wilayah Persekutuan Labuan', 'code' => 'LBN', 'lhdn_code' => '15'],
            ['name' => 'Wilayah Persekutuan Putrajaya', 'code' => 'PJY', 'lhdn_code' => '16'],
            ['name' => 'Not Applicable', 'code' => 'NA', 'lhdn_code' => '17'],
        ];

        foreach ($states as $state) {
            \DB::table('states')->updateOrInsert(
                ['code' => $state['code']],
                array_merge($state, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have a state (use Selangor as a realistic default)
        $stateId = DB::table('states')->where('code', 'SGR')->value('id');
        if (! $stateId) {
            $stateId = DB::table('states')->value('id');
        }

        // Ensure we have a default item classification (Others - code 022)
        $defaultClassificationId = DB::table('item_classifications')->where('code', '022')->value('id');
        if (! $defaultClassificationId) {
            throw new \Exception('Default item classification (022 - Others) not found. Please ensure ItemClassificationSeeder runs first.');
        }

        // Create or update a realistic company
        $companyRegistrationNumber = '202501234567'; // SSM number-like
        $companyTinNumber = 'C12345678901'; // TIN-like

        $companyUuid = (string) Str::uuid();

        DB::table('companies')->updateOrInsert(
            ['registration_number' => $companyRegistrationNumber],
            [
                'uuid' => $companyUuid,
                'name' => 'Acme Solutions Sdn Bhd',
                'registration_number' => $companyRegistrationNumber,
                'tin_number' => $companyTinNumber,
                'email' => 'accounts@acme.test',
                'phone' => '+60312345678',
                'address_line_1' => 'Unit 12-3, Menara Business Park',
                'address_line_2' => 'Jalan Pusat Bandar 1',
                'city' => 'Shah Alam',
                'state_id' => $stateId,
                'postcode' => '40100',
                'country' => 'Malaysia',
                'status' => 'active',
                'onboarding_completed' => true,
                'subscription_plan_id' => 1,
                'subscription_status' => 'active',
                'subscription_starts_at' => now()->subMonth(),
                'subscription_ends_at' => now()->addYear(),
                'subscription_payment_proof' => null,
                'subscription_approved_by' => null,
                'subscription_approved_at' => now(),
                'invoice_prefix' => 'ACME-{####}',
                'default_tax_rates' => json_encode([
                    [
                        'value' => 0,
                        'label' => 'No Tax',
                        'tax_type_id' => DB::table('tax_types')->where('code', '06')->value('id'),
                        'tax_type_code' => '06'
                    ],
                    [
                        'value' => 6,
                        'label' => 'Sales Tax (6%)',
                        'tax_type_id' => DB::table('tax_types')->where('code', '01')->value('id'),
                        'tax_type_code' => '01'
                    ],
                    [
                        'value' => 8,
                        'label' => 'Service Tax (8%)',
                        'tax_type_id' => DB::table('tax_types')->where('code', '02')->value('id'),
                        'tax_type_code' => '02'
                    ],
                ]),
                'default_item_classification_id' => $defaultClassificationId,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $companyId = DB::table('companies')->where('registration_number', $companyRegistrationNumber)->value('id');

        // Create a test user with approved subscription and link to company
        DB::table('users')->updateOrInsert(
            ['email' => 'user@test.com'],
            [
                'name' => 'Test User',
                'email' => 'user@test.com',
                'phone' => '+60123456789',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'company_id' => $companyId,
                'subscription_plan_id' => 1, // Plan A
                'subscription_status' => 'active',
                'is_super_admin' => false,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Perfect for small businesses',
                'price_annually' => 99.00,
                'invoice_limit_monthly' => 50,
                'features' => json_encode([
                    'Up to 50 invoices per month',
                    'LHDN submission',
                    'Email support',
                    'Activity log',
                ]),
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'description' => 'For growing companies',
                'price_annually' => 299.00,
                'invoice_limit_monthly' => 200,
                'features' => json_encode([
                    'Up to 200 invoices per month',
                    'LHDN submission',
                    'Priority email support',
                    'Activity log',
                    'Advanced analytics',
                ]),
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Unlimited invoice submission',
                'price_annually' => 999.00,
                'invoice_limit_monthly' => 999999, // Unlimited
                'features' => json_encode([
                    'Unlimited invoices',
                    'LHDN submission',
                    '24/7 priority support',
                    'Activity log',
                    'Advanced analytics',
                    'Dedicated account manager',
                    'Custom integrations',
                ]),
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            DB::table('subscription_plans')->updateOrInsert(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}

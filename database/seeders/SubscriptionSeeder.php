<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $northsails = Tenant::where('slug', 'northsails')->first();
        $pivotlab   = Tenant::where('slug', 'pivotlab')->first();

        $growthPlan = Plan::where('slug', 'growth')->first();
        $scalePlan  = Plan::where('slug', 'scale')->first();

        // NorthSails — active Stripe Growth subscription
        Subscription::create([
            'tenant_id'            => $northsails->id,
            'plan_id'              => $growthPlan->id,
            'provider'             => 'stripe',
            'provider_id'          => 'sub_' . Str::random(24),
            'status'               => 'active',
            'current_period_start' => now()->subDays(15),
            'current_period_end'   => now()->addDays(15),
            'cancel_at'            => null,
        ]);

        // PIVOTLAB — active Scale subscription
        Subscription::create([
            'tenant_id'            => $pivotlab->id,
            'plan_id'              => $scalePlan->id,
            'provider'             => 'stripe',
            'provider_id'          => 'sub_' . Str::random(24),
            'status'               => 'active',
            'current_period_start' => now()->subDays(5),
            'current_period_end'   => now()->addDays(25),
            'cancel_at'            => null,
        ]);

        $this->command->info('✓ Subscriptions seeded');
    }
}

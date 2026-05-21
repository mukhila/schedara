<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $growthPlan  = Plan::where('slug', 'growth')->first();
        $starterPlan = Plan::where('slug', 'starter')->first();
        $scalePlan   = Plan::where('slug', 'scale')->first();

        // ── NorthSails — active Growth tenant ────────────────────────
        $northsails = Tenant::create([
            'name'          => 'NorthSails Apparel',
            'slug'          => 'northsails',
            'plan_id'       => $growthPlan->id,
            'trial_ends_at' => null,
            'status'        => 'active',
            'settings'      => [
                'brand_color'  => '#65a1d8',
                'timezone'     => 'Asia/Kolkata',
                'language'     => 'en',
                'week_starts'  => 'monday',
                'posting_hours' => ['09:00', '12:00', '18:00'],
            ],
        ]);

        // ── Vesper — trialing Starter tenant ─────────────────────────
        $vesper = Tenant::create([
            'name'          => 'Vesper Co',
            'slug'          => 'vesper',
            'plan_id'       => $starterPlan->id,
            'trial_ends_at' => now()->addDays(10),
            'status'        => 'trialing',
            'settings'      => [
                'brand_color' => '#0a2748',
                'timezone'    => 'Europe/London',
                'language'    => 'en',
            ],
        ]);

        // ── PIVOTLAB — Scale (agency) tenant ─────────────────────────
        $pivotlab = Tenant::create([
            'name'          => 'PIVOTLAB Agency',
            'slug'          => 'pivotlab',
            'plan_id'       => $scalePlan->id,
            'trial_ends_at' => null,
            'status'        => 'active',
            'settings'      => [
                'brand_color' => '#021b2e',
                'timezone'    => 'America/New_York',
                'language'    => 'en',
                'white_label' => true,
            ],
        ]);

        // ── 4 random tenants ─────────────────────────────────────────
        Tenant::factory(4)->active()->create();

        // ── Assign users to NorthSails ───────────────────────────────
        $priya  = User::where('email', 'priya@northsails.dev')->first();
        $mateo  = User::where('email', 'mateo@northsails.dev')->first();
        $admin  = User::where('email', 'admin@schedara.dev')->first();

        TenantUser::create([
            'tenant_id'   => $northsails->id,
            'user_id'     => $priya->id,
            'role'        => 'owner',
            'permissions' => null,
            'invited_at'  => now()->subDays(60),
            'joined_at'   => now()->subDays(60),
        ]);

        TenantUser::create([
            'tenant_id'   => $northsails->id,
            'user_id'     => $mateo->id,
            'role'        => 'member',
            'permissions' => ['post:create', 'post:edit'],
            'invited_at'  => now()->subDays(58),
            'joined_at'   => now()->subDays(57),
        ]);

        // ── Assign Jordan to Vesper ───────────────────────────────────
        $jordan = User::where('email', 'jordan@vesper.dev')->first();

        TenantUser::create([
            'tenant_id'  => $vesper->id,
            'user_id'    => $jordan->id,
            'role'       => 'owner',
            'invited_at' => now()->subDays(5),
            'joined_at'  => now()->subDays(5),
        ]);

        $this->command->info('✓ Tenants seeded (3 fixed + 4 random) with memberships');
    }
}

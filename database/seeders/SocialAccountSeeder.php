<?php

namespace Database\Seeders;

use App\Models\SocialAccount;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class SocialAccountSeeder extends Seeder
{
    public function run(): void
    {
        $northsails = Tenant::where('slug', 'northsails')->first();
        $vesper     = Tenant::where('slug', 'vesper')->first();

        // NorthSails — 4 connected platforms
        $northsailsAccounts = [
            ['platform' => 'instagram', 'account_name' => '@northsails_apparel', 'account_id' => '1234567890'],
            ['platform' => 'facebook',  'account_name' => 'NorthSails Apparel',  'account_id' => '9876543210'],
            ['platform' => 'linkedin',  'account_name' => 'NorthSails Apparel',  'account_id' => '5432167890'],
            ['platform' => 'twitter',   'account_name' => '@northsails',         'account_id' => '1122334455'],
        ];

        foreach ($northsailsAccounts as $acc) {
            SocialAccount::factory()->create(array_merge($acc, [
                'tenant_id' => $northsails->id,
                'status'    => 'active',
                'scopes'    => ['read', 'write', 'publish'],
                'expires_at'=> now()->addDays(60),
            ]));
        }

        // Vesper — 2 connected platforms
        SocialAccount::factory()->create([
            'tenant_id'    => $vesper->id,
            'platform'     => 'instagram',
            'account_name' => '@vesper.co',
            'account_id'   => '6677889900',
        ]);

        SocialAccount::factory()->create([
            'tenant_id'    => $vesper->id,
            'platform'     => 'pinterest',
            'account_name' => 'Vesper Co',
            'account_id'   => '1122998877',
        ]);

        // Random accounts for other tenants
        $otherTenants = Tenant::whereNotIn('slug', ['northsails', 'vesper'])->get();

        foreach ($otherTenants as $tenant) {
            SocialAccount::factory(rand(1, 3))->create(['tenant_id' => $tenant->id]);
        }

        $this->command->info('✓ Social accounts seeded');
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Fixed dev accounts ────────────────────────────────────────
        $fixed = [
            [
                'name'              => 'Super Admin',
                'email'             => 'admin@schedara.dev',
                'timezone'          => 'UTC',
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Priya Anand',
                'email'             => 'priya@northsails.dev',
                'timezone'          => 'Asia/Kolkata',
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Mateo Reyes',
                'email'             => 'mateo@northsails.dev',
                'timezone'          => 'America/New_York',
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Jordan Blake',
                'email'             => 'jordan@vesper.dev',
                'timezone'          => 'Europe/London',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($fixed as $i => $attrs) {
            $factory = User::factory();
            if ($i === 0) {
                $factory = $factory->superAdmin();
            }
            $factory->create($attrs);
        }

        // ── Random dev users ─────────────────────────────────────────
        User::factory(16)->create();

        $this->command->info('✓ Users seeded (4 fixed + 16 random)');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PlanSeeder::class,
            UserSeeder::class,
            TenantSeeder::class,
            SocialPlatformSeeder::class,
            SocialAccountSeeder::class,
            PostSeeder::class,
            InboxSeeder::class,
            SubscriptionSeeder::class,
            AiSeeder::class,
            TeamCollaborationSeeder::class,
        ]);
    }
}

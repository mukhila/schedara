<?php

namespace Database\Seeders;

use App\Models\InboxMessage;
use App\Models\SocialAccount;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class InboxSeeder extends Seeder
{
    public function run(): void
    {
        $northsails = Tenant::where('slug', 'northsails')->first();
        $mateo      = User::where('email', 'mateo@northsails.dev')->first();
        $igAccount  = SocialAccount::where('tenant_id', $northsails->id)
                                   ->where('platform', 'instagram')
                                   ->first();

        if (! $igAccount) {
            $this->command->warn('No Instagram account found — skipping InboxSeeder');
            return;
        }

        $fixtures = [
            [
                'external_id' => 'cm_' . uniqid(),
                'type'        => 'comment',
                'from_user'   => ['id' => '111', 'name' => 'Maya Hansen', 'username' => '@maya.cph', 'avatar' => null],
                'content'     => 'Loved the new drop! Do you ship to Denmark? 🇩🇰',
                'sentiment'   => 'positive',
                'status'      => 'unread',
            ],
            [
                'external_id' => 'dm_' . uniqid(),
                'type'        => 'dm',
                'from_user'   => ['id' => '222', 'name' => 'Jordan Blake', 'username' => '@jordan_b', 'avatar' => null],
                'content'     => 'Where can I find the linen pant in size M?',
                'sentiment'   => 'neutral',
                'status'      => 'unread',
                'assigned_to' => $mateo->id,
            ],
            [
                'external_id' => 'mn_' . uniqid(),
                'type'        => 'mention',
                'from_user'   => ['id' => '333', 'name' => 'Sara V.', 'username' => '@sara.styles', 'avatar' => null],
                'content'     => '@northsails_apparel just got my order and WOW the quality 🔥',
                'sentiment'   => 'positive',
                'status'      => 'read',
            ],
            [
                'external_id' => 'cm_' . uniqid(),
                'type'        => 'comment',
                'from_user'   => ['id' => '444', 'name' => 'Ravi K.', 'username' => '@ravi.k', 'avatar' => null],
                'content'     => 'Price seems way too high for what you get. Disappointed.',
                'sentiment'   => 'negative',
                'status'      => 'unread',
            ],
        ];

        foreach ($fixtures as $msg) {
            InboxMessage::create(array_merge($msg, [
                'tenant_id'         => $northsails->id,
                'social_account_id' => $igAccount->id,
                'platform'          => 'instagram',
                'tags'              => null,
                'received_at'       => now()->subMinutes(rand(5, 1440)),
            ]));
        }

        $this->command->info('✓ Inbox messages seeded (4 fixtures)');
    }
}

<?php

namespace Database\Seeders;

use App\Models\CollaborationTask;
use App\Models\Post;
use App\Models\PostApproval;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TeamCollaborationSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'northsails')->first() ?? Tenant::first();

        if (!$tenant) {
            $this->command->warn('No tenant found — skipping TeamCollaborationSeeder.');
            return;
        }

        $members = TenantUser::where('tenant_id', $tenant->id)
            ->whereNotNull('joined_at')
            ->with('user')
            ->get();

        if ($members->isEmpty()) {
            $this->command->warn('No team members found — skipping tasks/approvals seeding.');
            return;
        }

        $owner   = $members->where('role', 'owner')->first()?->user   ?? $members->first()->user;
        $manager = $members->where('role', 'manager')->first()?->user  ?? $owner;
        $creator = $members->where('role', 'creator')->first()?->user  ?? $owner;

        // ── Seed sample tasks ─────────────────────────────────────────
        $tasks = [
            ['title' => 'Design Instagram story templates',        'priority' => 'high',   'status' => 'pending',     'assigned_to' => $creator->id],
            ['title' => 'Write 10 captions for product launch',    'priority' => 'urgent', 'status' => 'in_progress', 'assigned_to' => $creator->id],
            ['title' => 'Research competitor hashtag strategies',  'priority' => 'medium', 'status' => 'pending',     'assigned_to' => $manager->id],
            ['title' => 'Compile Q2 content calendar',             'priority' => 'high',   'status' => 'review',      'assigned_to' => $creator->id],
            ['title' => 'Set up LinkedIn company page',            'priority' => 'medium', 'status' => 'completed',   'assigned_to' => $manager->id],
            ['title' => 'Brief agency on brand guidelines',        'priority' => 'low',    'status' => 'pending',     'assigned_to' => $owner->id],
        ];

        foreach ($tasks as $i => $t) {
            CollaborationTask::firstOrCreate(
                ['tenant_id' => $tenant->id, 'title' => $t['title']],
                array_merge($t, [
                    'uuid'        => Str::uuid(),
                    'tenant_id'   => $tenant->id,
                    'assigned_by' => $owner->id,
                    'sort_order'  => $i,
                    'due_date'    => now()->addDays(rand(3, 21)),
                ])
            );
        }

        // ── Seed sample post approval ─────────────────────────────────
        $post = Post::where('tenant_id', $tenant->id)
            ->where('status', Post::STATUS_DRAFT)
            ->first();

        if ($post) {
            PostApproval::firstOrCreate(
                ['post_id' => $post->id, 'status' => 'pending'],
                [
                    'uuid'            => Str::uuid(),
                    'tenant_id'       => $tenant->id,
                    'requested_by'    => $creator->id,
                    'request_comment' => 'Please review before scheduling.',
                ]
            );

            $post->update(['status' => Post::STATUS_PENDING_APPROVAL]);
        }

        $this->command->info('✓ Team collaboration tasks and approvals seeded');
    }
}

<?php

namespace App\Services;

use App\Enums\TenantRole;
use App\Models\Plan;
use App\Models\TeamInvitation;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use App\Notifications\TeamInvitationNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class TenantService
{
    /** Create a personal workspace for a new user and make them Owner. */
    public function createForUser(User $user): Tenant
    {
        $plan = Plan::where('slug', 'starter')->first();

        $tenant = Tenant::create([
            'name'          => $user->name . "'s Workspace",
            'slug'          => $this->uniqueSlug($user->name ?: $user->email),
            'plan_id'       => $plan?->id,
            'status'        => 'trialing',
            'trial_ends_at' => now()->addDays(14),
            'settings'      => [
                'timezone'  => $user->timezone ?? 'UTC',
                'language'  => 'en',
            ],
        ]);

        TenantUser::create([
            'tenant_id'  => $tenant->id,
            'user_id'    => $user->id,
            'role'       => TenantRole::Owner->value,
            'invited_at' => now(),
            'joined_at'  => now(),
        ]);

        return $tenant;
    }

    /** Send an invitation email with a 7-day signed URL. */
    public function invite(
        Tenant $tenant,
        User   $inviter,
        string $email,
        string $role,
        ?string $message = null,
    ): TeamInvitation {
        // Cancel any existing pending invitation for this email in this tenant
        TeamInvitation::where('tenant_id', $tenant->id)
            ->where('email', $email)
            ->pending()
            ->delete();

        $invitation = TeamInvitation::create([
            'tenant_id'  => $tenant->id,
            'invited_by' => $inviter->id,
            'email'      => $email,
            'role'       => $role,
            'token'      => Str::uuid()->toString(),
            'message'    => $message,
            'expires_at' => now()->addDays(7),
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'invitation.show',
            now()->addDays(7),
            ['token' => $invitation->token],
        );

        Notification::route('mail', $email)
            ->notify(new TeamInvitationNotification($invitation, $inviter, $tenant, $signedUrl));

        return $invitation;
    }

    private function uniqueSlug(string $base): string
    {
        $slug = Str::slug($base) ?: 'workspace';
        $root = $slug;
        $i    = 1;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = "{$root}-{$i}";
            $i++;
        }

        return $slug;
    }
}

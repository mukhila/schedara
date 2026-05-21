<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Per-tenant analytics private channel — any authenticated member of the tenant can listen
Broadcast::channel('tenant.{tenantId}.analytics', function ($user, int $tenantId) {
    return $user->tenants()->where('tenants.id', $tenantId)->exists();
});

// Per-user notifications private channel
Broadcast::channel('user.{userId}.notifications', function ($user, int $userId) {
    return (int) $user->id === $userId;
});

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

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Kitchen channel - only authenticated users from same outlet
Broadcast::channel('kitchen.{outletId}', function ($user, $outletId) {
    return $user->outlet_id === $outletId;
});

// Dashboard channel - only authenticated users from same outlet
Broadcast::channel('dashboard.{outletId}', function ($user, $outletId) {
    return $user->outlet_id === $outletId;
});

// Tenant outlet channel - only authenticated users from same tenant and outlet
Broadcast::channel('tenant.{tenantId}.outlet.{outletId}', function ($user, $tenantId, $outletId) {
    return $user->tenant_id === $tenantId && $user->outlet_id === $outletId;
});
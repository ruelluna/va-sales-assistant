<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('call-session.{callSessionId}', function ($user, $callSessionId) {
    $callSession = \App\Models\CallSession::find($callSessionId);

    if (! $callSession) {
        return false;
    }

    return (int) $user->id === (int) $callSession->va_user_id;
});

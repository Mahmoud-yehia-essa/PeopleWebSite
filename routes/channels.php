<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

Broadcast::routes(['middleware' => ['web', 'auth:sanctum']]);

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});



Broadcast::channel('chat.{receiverId}', function ($user = null, $receiverId = null) {
    return true;
});

Broadcast::channel('private-chat.{receiverId}', function ($user = null, $receiverId = null) {
    return true;
});

Broadcast::channel('group.{groupId}', function ($user = null, $groupId = null) {
    return true;
});

Broadcast::channel('chat.group.{groupId}', function ($user = null, $groupId = null) {
    return true;
});

Broadcast::channel('online', function ($user = null) {
    $u = $user ?? auth()->user();
    $id = $u ? $u->id : (int)request('user_id', 0);
    return [
        'id' => (int)$id,
        'name' => $u ? ($u->name ?? $u->first_name ?? 'User') : 'User',
        'avatar' => $u ? ($u->avatar_url ?? $u->profile_picture ?? null) : null,
    ];
});

Broadcast::channel('presence-online', function ($user = null) {
    $u = $user ?? auth()->user();
    $id = $u ? $u->id : (int)request('user_id', 0);
    return [
        'id' => (int)$id,
        'name' => $u ? ($u->name ?? $u->first_name ?? 'User') : 'User',
        'avatar' => $u ? ($u->avatar_url ?? $u->profile_picture ?? null) : null,
    ];
});

Broadcast::channel('chat-presence', function ($user = null) {
    $u = $user ?? auth()->user();
    $id = $u ? $u->id : (int)request('user_id', 0);
    return [
        'id' => (int)$id,
        'name' => $u ? ($u->name ?? $u->first_name ?? 'User') : 'User',
        'avatar' => $u ? ($u->avatar_url ?? $u->profile_picture ?? null) : null,
    ];
});

Broadcast::channel('presence-chat-presence', function ($user = null) {
    $u = $user ?? auth()->user();
    $id = $u ? $u->id : (int)request('user_id', 0);
    return [
        'id' => (int)$id,
        'name' => $u ? ($u->name ?? $u->first_name ?? 'User') : 'User',
        'avatar' => $u ? ($u->avatar_url ?? $u->profile_picture ?? null) : null,
    ];
});
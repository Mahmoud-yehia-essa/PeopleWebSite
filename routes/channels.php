<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

Broadcast::routes(['middleware' => ['web', 'auth:sanctum']]);

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});



Broadcast::channel('chat.{receiverId}', function (User $user, int $receiverId) {
    return (int) $user->id === (int) $receiverId || auth()->check() || $user !== null;
});

Broadcast::channel('private-chat.{receiverId}', function (User $user, int $receiverId) {
    return (int) $user->id === (int) $receiverId || auth()->check() || $user !== null;
});

Broadcast::channel('group.{groupId}', function (User $user, int $groupId) {
    return auth()->check() || $user !== null;
});

Broadcast::channel('chat.group.{groupId}', function (User $user, int $groupId) {
    return auth()->check() || $user !== null;
});

Broadcast::channel('online', function ($user = null) {
    $u = $user ?? auth()->user();
    if ($u) {
        return [
            'id' => $u->id,
            'name' => $u->name ?? $u->first_name ?? 'User',
            'avatar' => $u->profile_picture ?? null,
        ];
    }
    return [
        'id' => request('user_id', 0),
        'name' => 'User',
    ];
});

Broadcast::channel('presence-online', function ($user = null) {
    $u = $user ?? auth()->user();
    if ($u) {
        return [
            'id' => $u->id,
            'name' => $u->name ?? $u->first_name ?? 'User',
            'avatar' => $u->profile_picture ?? null,
        ];
    }
    return [
        'id' => request('user_id', 0),
        'name' => 'User',
    ];
});

Broadcast::channel('chat-presence', function ($user = null) {
    $u = $user ?? auth()->user();
    return [
        'id' => $u ? $u->id : request('user_id', 0),
        'name' => $u ? ($u->name ?? $u->first_name ?? 'User') : 'User',
        'avatar' => $u ? ($u->avatar_url ?? $u->profile_picture ?? null) : null,
    ];
});
<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});



Broadcast::channel('chat.{receiverId}', function (User $user, int $receiverId) {
    // يسمح للمستخدم بالاتصال بقناته الخاصة، أو إذا كان يراسل هذا المستخدم
    // لتسهيل عمل ميزة يكتب الآن (Whisper) بين الطرفين، نسمح بالاشتراك لجميع المستخدمين المصادقين
    return (int) $user->id === (int) $receiverId || auth()->check();
});

Broadcast::channel('chat-presence', function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'avatar' => $user->avatar_url,
    ];
});
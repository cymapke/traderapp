<?php
// routes/channels.php
Broadcast::channel('user.{userId}.profile', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('user.{userId}.orders', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('trades', function ($user) {
    return true; // All authenticated users can listen to trades
});

<?php
// routes/channels.php
Broadcast::channel('user.{userId}.profile', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

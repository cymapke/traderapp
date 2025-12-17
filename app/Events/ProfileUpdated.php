<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProfileUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $profileData;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->profileData = $user->getProfileData();
    }

    public function broadcastOn()
    {
        // Private channel for specific user
        return new Channel('user.' . $this->user->id . '.profile');
    }

    public function broadcastAs()
    {
        return 'profile.updated';
    }

    public function broadcastWith()
    {
        return [
            'data' => $this->profileData,
            'updated_at' => now()->toISOString(),
        ];
    }
}

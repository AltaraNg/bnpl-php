<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VendorRegisteredEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User|Builder|array $vendor;
    public string $otp;
    public string $url;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User|Builder|array $vendor, string $otp, string $url)
    {
        $this->otp = $otp;
        $this->url = $url;
        $this->vendor = $vendor;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|PrivateChannel|array
     */
    public function broadcastOn(): Channel|PrivateChannel|array
    {
        return new PrivateChannel('channel-name');
    }
}

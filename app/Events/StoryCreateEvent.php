<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StoryCreateEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $contents;

    /**
     * Create a new event instance.
     */
    public function __construct($contents)
    {
        $this->contents = $contents;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    // BERTUGAS UNTUK MEMBUAT SEBUAH CHANNEL UNTUK NOTIFIKASI
    public function broadcastOn()
    {
        return [
            new Channel('storytime-ch')
        ];
    }

    // BERTUGAS UNTUK MEMBUAT NAMA EVENT YANG BISA DIPAKAI OLEH FRONTEND
    public function broadcastAs()
    {
        return 'storytime-bc';
    }

    public function broadcastWith()
    {
        return [
            'contents' => $this->contents
        ];
    }
}

<?php

namespace App\Events;

use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;
    public string $channel;
    /**
     * Create a new event instance.
     */
    public function __construct(Message $message, string $channel)
    {
        //
        $this->message = $message;
        $this->channel = $channel;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel.'. $this->channel),
        ];
    }

    public function broadcastAs(){
        return 'chat';
    }

    public function broadcastWith(){
        return [
            'messageText' => $this->message->message,
            'senderId' => $this->message->user_id,
            'receiverId' => $this->message->receiver_id,
            'type' => $this->message->type,
            'timestamp' => Carbon::parse($this->message->created_at)->timestamp,
            'imageUrl' => $this->message->image_url,
            'channel' => $this->message->channel
        ];
    }
}

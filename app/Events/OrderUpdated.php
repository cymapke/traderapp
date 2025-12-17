<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;
    public $action; // created, updated, deleted, filled, cancelled

    public function __construct(Order $order, string $action = 'updated')
    {
        $this->order = $order;
        $this->action = $action;
    }

    public function broadcastOn()
    {
        return new Channel('user.' . $this->order->user_id . '.orders');
    }

    public function broadcastAs()
    {
        return 'order.updated';
    }

    public function broadcastWith()
    {
        return [
            'action' => $this->action,
            'order' => [
                'id' => $this->order->id,
                'symbol' => $this->order->ticker->symbol,
                'side' => $this->order->side,
                'side_icon' => $this->order->side_icon,
                'amount' => (float) $this->order->amount,
                'price' => (float) $this->order->price,
                'total' => (float) $this->order->total,
                'status' => $this->order->status,
                'status_text' => $this->order->status_text,
                'created_at' => $this->order->created_at->toISOString(),
            ],
            'timestamp' => now()->toISOString(),
        ];
    }
}

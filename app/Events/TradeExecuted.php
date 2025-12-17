<?php

namespace App\Events;

use App\Models\Trade;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TradeExecuted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The trade that was executed.
     */
    public $trade;

    /**
     * Create a new event instance.
     */
    public function __construct(Trade $trade)
    {
        $this->trade = $trade;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            // Public channel for all users to see trades
            new Channel('trades'),
            // Private channel for the buyer
            new PrivateChannel('users.' . $this->trade->buyOrder->user_id),
            // Private channel for the seller
            new PrivateChannel('users.' . $this->trade->sellOrder->user_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'trade.executed';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->trade->id,
            'symbol' => $this->trade->buyOrder->ticker->symbol,
            'price' => (float) $$this->trade->buyOrder->price,
            'quantity' => (float) $this->trade->buyOrder->amount,
            'total' => (float) $this->trade->buyOrder->amount * $this->trade->buyOrder->price,
            'buy_commission' => (float) $this->trade->buy_commission,
            'sell_commission' => (float) $this->trade->sell_commission,
            'formatted' => [
                'price' => $this->trade->buyOrder->price,
                'quantity' => $this->trade->buyOrder->amount,
                'total' => $this->trade->buyOrder->amount * $this->trade->buyOrder->price,
                'buy_commission' => $this->trade->formatted_buy_commission,
                'sell_commission' => $this->trade->formatted_sell_commission,
            ],
            'buy_order' => [
                'id' => $this->trade->buy_order_id,
                'user_id' => $this->trade->buyOrder->user_id,
                'side' => 'BUY',
            ],
            'sell_order' => [
                'id' => $this->trade->sell_order_id,
                'user_id' => $this->trade->sellOrder->user_id,
                'side' => 'SELL',
            ],
        ];
    }

    /**
     * Determine if this event should broadcast.
     */
    public function broadcastWhen(): bool
    {
        // Only broadcast if we have both orders loaded
        return $this->trade->relationLoaded('buyOrder') && 
               $this->trade->relationLoaded('sellOrder') &&
               $this->trade->buyOrder->relationLoaded('ticker') &&
               $this->trade->sellOrder->relationLoaded('ticker');
    }
}

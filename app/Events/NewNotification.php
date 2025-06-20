<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use App\Models\User; // Import User model
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Product $product; // The product related to the notification
    public User $user; // The user this notification is intended for

    /**
     * Create a new event instance.
     *
     * @param Product $product
     * @param User $user The user to whom this notification is targeted
     */
    public function __construct(Product $product, User $user)
    {
        $this->product = $product;
        $this->user = $user;
    }

    
    public function broadcastAs(): string
    {
        return 'NewNotification'; // Or any simple, descriptive name you like
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Use the user passed to the constructor
        Log::info("NewNotification broadcastWith: \$notifiable object loaded successfully. on channel 'App.Models.User.' . $this->user->id");
        log::info($this->user->id);
        return [
            new PrivateChannel('App.Models.User.' . $this->user->id)

        ];
    }

    /**
     * Define the data to broadcast.
     * By default, all public properties are broadcast.
     * If you want to customize, add a broadcastWith() method.
     * For this example, product and user (or parts of user) will be broadcast.
     * Let's be explicit with what we send for the notification payload.
     */
    public function broadcastWith(): array
    {
        $eventData = [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'url' => route('cart.index'), // Or a product-specific URLproduct_id' => $this->product->id,
            'message' => (app()->getLocale() === 'ar')
                ? " تم  ضافة منتج '{$this->product->name}'  السلة."
                : "A new product '{$this->product->name}' was added to cart.",
    ];
        return $eventData;
    }
}
<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use App\Models\User; // Import User
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\NewNotification; // Your custom event
// use Illuminate\Notifications\Messages\MailMessage;

class ProductAddedToCartNotification extends Notification 
{
    use Queueable;

    public Product $product;

    /**
     * Create a new notification instance.
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  object  $notifiable The entity being notified (e.g., User)
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // This notification is now only for the database.
        // The broadcasting is handled by the NewNotification event.
        return ['database'];
    }

    /**
     * Get the array representation of the notification for the database.
     *
     * @param  object  $notifiable The entity being notified (e.g., User)
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        // Dispatch your custom event for broadcasting
        // Ensure $notifiable is a User instance before passing
        if ($notifiable instanceof User) {
            broadcast(new NewNotification($this->product, $notifiable));
            Log::info("NewNotification broadcastWith: \$notifiable object loaded successfully.");
        } else {
            // Handle case where $notifiable is not a User, if necessary
            // Maybe log an error or don't broadcast
        }

        // Data for the database record
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name, // Good to have
            'message' => "A new product '{$this->product->name}' was added to cart.",
            'url' => route('cart.index'),
        ];
    }
}
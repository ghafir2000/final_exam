<?php

use App\Models\Chat;
use App\Models\User; // Make sure User model is imported
use App\Models\AI;   // Make sure AI model is imported
use Illuminate\Support\Facades\Log; // Optional: Add for debugging
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

// Ensure this line is uncommented and the middleware is correct for your auth setup
// 'auth:web' assumes you are using standard Laravel session-based authentication.
Broadcast::routes(['middleware' => ['web', 'auth:web']]); // Added 'web' group for session/CSRF, keep 'auth:web'

// Authorize users to listen to the chat channel
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    // $user is the authenticated user trying to listen (instance of App\Models\User)
    // $chatId is the ID from the channel name (e.g., 'chat.2' -> $chatId = 2)

    // Log::info("Attempting to authorize user {$user->id} for chat channel {$chatId}"); // Optional Debugging

    // Ensure chatId is a valid integer
    if (!ctype_digit((string)$chatId)) {
         // Log::warning("Invalid chatId format received: " . $chatId); // Optional Debugging
        return false;
    }
    $chatId = (int) $chatId;

    // Find the chat by ID
    $chat = Chat::find($chatId);

    // Check if the chat exists AND if the authenticated user is the 'customer' of this chat
    if ($chat) {
        // Compare the authenticated user's ID with the customer_id associated with the chat.
        // Make sure the ID comparison is correct. If your `customer_id` stores the ID
        // from a related `Customer` model, and $user->id is the `User` model ID, you might need
        // to access the user's related customer ID (e.g., $user->user$user_id if using morph relation,
        // or $user->customer->id if a direct relationship exists).
        // Assuming $user->id directly maps to the chat's customer_id for simplicity here:
        $isParticipant = (int) $chat->user_id === (int) $user->id ||
                        ((int) $chat->chatable_id === (int) $user->id && $chat->chatable_type === get_class($user)); // Strict comparison after casting to int

        // Log::info("Chat {$chatId} found. Customer ID: {$chat->customer_id}. User ID: {$user->id}. Is participant: " . ($isParticipant ? 'Yes' : 'No')); // Optional Debugging
        return $isParticipant;
    } else {
        Log::warning("Chat {$chatId} not found."); // Optional Debugging
        return false; // Chat not found, deny access
    }
});


// This channel is typically for individual user notifications (keep if needed)
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
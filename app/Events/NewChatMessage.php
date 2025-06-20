<?php

namespace App\Events;

use App\Models\Message;
use App\Models\User; // Make sure User is imported
use App\Models\AI;   // Make sure AI is imported
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log; // For debugging

class NewChatMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $messageInstance;
    public $chatId;

    public function __construct(Message $message, int $chatId)
    {
        $this->messageInstance = $message;
        $this->chatId = $chatId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . $this->chatId);
    }

    public function broadcastWith()
    {
        $this->messageInstance->loadMissing('sender'); // Ensure 'sender' polymorphic relationship is loaded
        $message = $this->messageInstance;
        $sender = $message->sender; // This should be the User or AI model instance

        // ---- START DEBUGGING $sender ----
        if (!$sender) {
            Log::error("NewChatMessage broadcastWith: \$sender is NULL for message. This is a critical issue.", [
                'message_id' => $message->id,
                'db_sender_id' => $message->sender_id,    // Value from database
                'db_sender_type' => $message->sender_type  // Value from database
            ]);
        } else {
            Log::info("NewChatMessage broadcastWith: \$sender object loaded successfully.", [
                'message_id' => $message->id,
                'sender_class' => get_class($sender),
                'sender_id_property' => $sender->id,
                'sender_name_property' => $sender->name ?? 'N/A'
            ]);
        }
        // ---- END DEBUGGING $sender ----

        $senderData = null;

        if($this->messageInstance->chat->chatable_type == AI::class)
        {
            if ($sender) { // If the sender model was successfully loaded via the relationship
                $isUserModel = $sender instanceof \App\Models\User;
                $isAIModel = $sender instanceof \App\Models\AI;

                $profilePic = asset('images/upload_default.jpg'); // A very generic fallback
                $senderName = 'Unknown Sender';

                if ($isUserModel) {
                    $senderName = $sender->name ?? 'User';
                    // Check if the User model uses Spatie Media Library or a direct path
                    $profilePic = method_exists($sender, 'getFirstMediaUrl')
                                ? ($sender->getFirstMediaUrl('profile_pictures') ?: asset('images/upload_default.jpg'))
                                : ($sender->profile_image_path ?? asset('images/upload_default.jpg')); // Example: if you have a 'profile_image_path' column
                } elseif ($isAIModel) {
                    $senderName = $sender->name ?? 'Dr.Pet-er (AI)';
                    // Assuming AI model has a 'profile_picture' attribute that stores a direct path or URL
                    $profilePic = $sender->profile_picture ?? asset('images/veterinarian_AI.jpg');
                } else {
                    // This case indicates the sender is loaded, but it's neither User nor AI
                    Log::warning("NewChatMessage broadcastWith: Sender is an unexpected type.", [
                        'message_id' => $message->id, 'sender_class' => get_class($sender)
                    ]);
                }

                $senderData = [
                    'id' => $sender->id,
                    'type' => get_class($sender), // e.g., "App\Models\User" or "App\Models\AI"
                    'name' => $senderName,
                    'profile_picture' => $profilePic,
                    'image_url' => $profilePic, // Keep image_url for consistency if JS uses it
                ];
            } else {
                // This block is hit if $sender was null (i.e., the relationship failed to load)
                // The debug log above this 'if' block would have already indicated this.
                // Create a fallback $senderData so the payload doesn't completely break.
                Log::error("NewChatMessage broadcastWith: Using fallback senderData because \$sender was null.", ['message_id' => $message->id]);
                $senderData = [
                    'id' => $message->sender_id ?? 'fallback_sender_id', // Use DB value if available
                    'type' => $message->sender_type ?? 'FallbackSenderType', // Use DB value
                    'name' => 'Sender (Error)',
                    'profile_picture' => asset('images/upload_default.jpg'),
                    'image_url' => asset('images/upload_default.jpg'),
                ];
            }

            // **** Construct the FINAL payload to be broadcast ****
            // The JavaScript client (.listen() callback) expects an object `e`
            // where `e.message` contains the actual message data.
            $payload = [
                'message' => [ // This outer 'message' key is what your JS expects: `e.message`
                    'id' => $message->id,
                    'chat_id' => $message->chat_id,
                    'message' => $message->message,
                    'created_at' => $message->created_at->toISOString(), // Use ISO string for JS Date compatibility
                    'user' => $senderData, // This nested 'user' key contains sender info, as expected by JS `renderMessage`
                    // Optionally include raw sender_id and sender_type from DB if needed for other JS logic,
                    // but message.user.id and message.user.type should be primary.
                    // 'db_sender_id' => $message->sender_id,
                    // 'db_sender_type' => $message->sender_type,
                ],
            ];

            Log::info('Broadcasting NewChatMessage with FINAL payload structure:', $payload);
        }
        else 
        {
            $senderDisplayName = 'Unknown Sender';
            $senderDisplayAvatar = asset('images/upload_default.jpg'); // Default avatar
            $senderRole = null; // Default role

            if ($sender) {
                // Determine display name
                // If sender is App\Models\User, it might have a 'name' property.
                // If sender is App\Models\AI, it might also have a 'name' property.
                // You might have a common interface or rely on a 'name' attribute.
                $senderDisplayName = $sender->name ?? ($sender instanceof AI ? 'Dr. Pets AI' : 'Chat Participant');

                // Determine avatar URL
                // This logic should mirror how you get avatars in openChat or for the User model itself.
                // Example: using a 'profile_picture_url' accessor or property.
                if (method_exists($sender, 'getProfilePictureUrlAttribute') || property_exists($sender, 'profile_picture_url')) {
                    $senderDisplayAvatar = $sender->profile_picture_url ?: $senderDisplayAvatar;
                } elseif (method_exists($sender, 'getFirstMediaUrl')) { // Example for Spatie Media Library
                    $senderDisplayAvatar = $sender->getFirstMediaUrl('profile_picture') ?: $senderDisplayAvatar;
                } elseif (property_exists($sender, 'image_url')) { // Common for AI or simpler models
                    $senderDisplayAvatar = $sender->image_url ?: $senderDisplayAvatar;
                }
                // Ensure it's a full URL if needed, or use your buildFullAssetUrl logic equivalent on client-side
                // For broadcast, it's often better to send full URLs if possible, or consistent relative paths.
                // If $senderDisplayAvatar is a relative path from public:
                if ($senderDisplayAvatar && !str_starts_with($senderDisplayAvatar, 'http')) {
                    $senderDisplayAvatar = asset($senderDisplayAvatar);
                }


                // Determine role (example)
                // if ($sender instanceof User && method_exists($sender, 'getRoleNameAttribute')) {
                //    $senderRole = $sender->role_name;
                // } elseif ($sender instanceof AI) {
                //    $senderRole = 'AI Assistant';
                // }
            }

            // Determine 'is_auth_user_message'
            // This flag, in the context of a broadcast received by OTHERS, should be FALSE,
            // because the message was NOT sent by the user receiving the broadcast.
            // The client-side JavaScript (`renderMessage`) will use this flag to style the message.
            $isAuthUserMessage = false;

            // If you needed to check against the *currently authenticated user* initiating the broadcast
            // (which is usually not relevant for the payload sent to *others*):
            // $auth = Auth::user();
            // if ($auth && $sender && $sender->id == $auth->id && get_class($sender) == get_class($auth)) {
            //     $isAuthUserMessageForSenderContext = true;
            // }


            $payload =  [
                'message' => [
                    'id' => $message->id,
                    'content' => $message->message, // Using 'message' as per your Message model
                    'created_at_raw' => $message->created_at->toIso8601String(),
                    'is_auth_user_message' => $isAuthUserMessage, // This will be false for recipients
                    'sender_id' => $sender ? $sender->id : null,
                    'sender_type' => $sender ? get_class($sender) : null,
                    'sender_name' => $senderDisplayName,
                    'sender_avatar_url' => $senderDisplayAvatar,
                    'sender_role_name' => $senderRole,
                ]
            ];

            Log::info('Broadcasting NewChatMessage with INITIAL payload structure:', $payload);
        }
        return $payload; // Return the correctly structured payload
    }
}
<?php

namespace App\Http\Controllers\web;

use App\Models\AI;
use App\Models\Chat;
use App\Models\User;
use App\Models\Message;
use App\Enums\ChatEnums;
use Illuminate\Http\Request;
use App\Events\NewChatMessage;
use App\Jobs\ProcessAIMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UpdateChatRequest;
use Illuminate\Database\Eloquent\Model; // For type hinting
use App\Services\ChatService; // Assuming you still use these?
use App\Services\UserService;   // Assuming you still use these?
use App\Http\Requests\OpenChatRequest; // Keep if used for openChat

class ChatController extends Controller
{
    protected $userService, $chatService;
    public function __construct(UserService $userService, ChatService $chatService) 
    { 
        $this->userService = $userService;
        $this->chatService = $chatService;
    }

    /**
     * Placeholder for listing chats (adjust as needed)
     */

    
     
    public function index($id = null)
    {
        // Example: Get chats where the user is either the `user_id` or the `chatable`
        $chats = $this->chatService->all();

        $selectedChat = ($id) ? $id : null;  
        

        return view('web.auth.user.chat-index', compact('chats','selectedChat')); // Or return JSON
    }

    public function store(OpenChatRequest $request)
    {

        $validated = $request->validated();
        
        $chat = $this->chatService->firstOrCreate($validated);

        return redirect()->route('chat.index', ['chat_id' => $chat->id]);

    }

    public function openChat($id) // $id is the chat_id
    {
        // Ensure your chatService->find can eager load these relationships effectively
        $chat = $this->chatService->find($id, false, ['chatable', 'user', 'messages.sender']);

        if (!$chat) {
            return response()->json(['error' => 'Chat not found.'], 404);
        }

        $authUserId = Auth::id();
        $authUser = $this->userService->find(Auth::id()); // Get the authenticated user object using UserService

        if (!($chat->user_id == $authUserId || ($chat->chatable_id == $authUserId && $chat->chatable_type === get_class($authUser)))) {
            return response()->json(['error' => 'Access denied to this chat.'], 403);
        }

        // --- Determine Chat Partner ---
        $chatPartner = null;
        if ($chat->user_id == $authUserId) {
            $chatPartner = $chat->chatable;
        } elseif ($chat->chatable_id == $authUserId && $chat->chatable_type === get_class($authUser)) {
            $chatPartner = $chat->user;
        }

        if (!$chatPartner) {
            Log::error("Could not determine chat partner for chat {$id} and user {$authUserId}. Chat User: {$chat->user_id}, Chatable: {$chat->chatable_id} ({$chat->chatable_type})");
            return response()->json(['error' => 'Could not determine chat partner.'], 500);
        }

        $chatPartnerName = $chatPartner->name ?? 'Chat Partner';
        // Assuming Spatie Media Library or similar for getFirstMediaUrl
        $chatPartnerImageUrl = method_exists($chatPartner, 'getFirstMediaUrl') ?
                               (asset($chatPartner->getFirstMediaUrl('profile_picture')) ?: asset('images/upload_default.jpg')) :
                               ($chatPartner->profile_picture_url ?? asset('images/upload_default.jpg'));


        // --- Fetch and Format Messages ---
        // 'messages.sender' should be eager loaded by $this->chatService->find
        $rawMessages = $chat->messages()->orderBy('created_at', 'asc')->get();

        $formattedMessages = $rawMessages->map(function ($message) use ($authUserId, $authUser, $chatPartner, $chatPartnerName, $chatPartnerImageUrl) {
            $sender = $message->sender; // This is the loaded sender model (User, Admin, etc.)
            $isAuthUserMessage = false;
            $senderDisplayName = 'Unknown';
            $senderDisplayAvatar = asset('images/upload_default.jpg');
            $senderRole = null; // Example

            if ($sender) {
                $isAuthUserMessage = ($sender->id == $authUserId);

                if ($isAuthUserMessage) {
                    $senderDisplayName = 'You'; // Or $authUser->name
                    $senderDisplayAvatar = method_exists($authUser, 'getFirstMediaUrl') ?
                                           (asset($authUser->getFirstMediaUrl('profile_picture')) ?: asset('images/upload_default.jpg')) :
                                           ($authUser->profile_picture_url ?? asset('images/upload_default.jpg'));
                } elseif ($chatPartner && $sender->id == $chatPartner->id && get_class($sender) == get_class($chatPartner)) {
                    $senderDisplayName = $chatPartnerName;
                    $senderDisplayAvatar = $chatPartnerImageUrl;
                } else { // Some other sender or if sender info is directly on message
                    $senderDisplayName = $sender->name ?? 'Participant';
                     $senderDisplayAvatar = method_exists($sender, 'getFirstMediaUrl') ?
                                       (asset($sender->getFirstMediaUrl('profile_picture')) ?: asset('images/upload_default.jpg')) :
                                       ($sender->profile_picture_url ?? asset('images/upload_default.jpg'));
                }
                // if (method_exists($sender, 'getRoleNameAttribute')) { // Example
                //    $senderRole = $sender->role_name;
                // }
            }

            return [
                'id' => $message->id,
                'content' => $message->message, // Make sure your Message model has 'content' attribute
                'created_at_raw' => $message->created_at->toIso8601String(), // For JS Date parsing
                'is_auth_user_message' => $isAuthUserMessage,
                'sender_id' => $sender ? $sender->id : null,
                'sender_type' => $sender ? get_class($sender) : null,
                'sender_name' => $senderDisplayName,
                'sender_avatar_url' => $senderDisplayAvatar,
                'sender_role_name' => $senderRole,
            ];
        });

        // Authenticated user's image
        $authUserImageUrl = method_exists($authUser, 'getFirstMediaUrl') ?
                            (asset($authUser->getFirstMediaUrl('profile_picture')) ?: asset('images/upload_default.jpg')) :
                            ($authUser->profile_picture_url ?? asset('images/upload_default.jpg'));

        // Optional: Update chat status (ensure ChatEnums::OPENED is defined)
        // $this->chatService->update(['id' => $chat->id, 'status' => ChatEnums::OPENED]);

        return response()->json([
            'chat_id' => $chat->id,
            'messages' => $formattedMessages,
            'picture_url' => asset($authUserImageUrl), // Current user's image for "You"
            'user_id' => $authUserId,
            'user_model_class' => get_class($authUser),
            'chat_partner' => [
                'id' => $chatPartner->id,
                'type' => get_class($chatPartner),
                'name' => $chatPartnerName,
                'partner_image' => asset($chatPartnerImageUrl),
            ],
        ]);
    }



     public function update(UpdateChatRequest $request)
    {
        $data = $request->validated();
        // dd($data);        

        $this->chatService->update($data);
        // dd($data);
        return redirect()->route('booking.show',$data['id']);
    }
    


    /**
     * Opens the specific chat between the authenticated user and the AI.
     */
    public function openAIChat(Request $request)
    {
        
        $prompt = file_get_contents(public_path('prompts/Dr.Pet-er.txt'));
        Log::info('AI Chat: Prompt content loaded successfully.');

        $authenticatedUser = $request->user();

        // --- THIS IS THE CORRECTED CODE ---
        $ai = AI::firstOrCreate(
            // --- Argument 1: The attributes to FIND the record by ---
            [
                'name' => 'Dr.Pet-er'
            ],
            // --- Argument 2: The attributes to use if CREATING a new record ---
            [
                'model'       => config('services.gemini.model', env('GEMINI_MODEL_NAME')), // Using config is best, env() is a fallback
                'description' => $prompt
            ]
        );

        Log::info('AI Chat: Successfully found or created AI record. ID: ' . $ai->id);

        // ... rest of your controller logic
        if ($ai && $authenticatedUser) {
            Log::info('openAIChat: AI entity found: ' . $ai->id);
            Log::info('openAIChat: Authenticated User: ' . $authenticatedUser->id);

            $aiDescription = $ai->description;
            $replaceText = '{{user}}';
            $replaceWith = $authenticatedUser->name;
            $ai->description = str_replace($replaceText, $replaceWith, $aiDescription);
            $ai->save();

            Log::info('openAIChat: Updated AI description with user name: ' . $replaceWith);
        } else {
            Log::error("openAIChat: Unable to find or create AI entity and/or authenticated user.");
        }

        if (!$ai) {
            Log::critical("Failed to find or create AI entity in openAIChat.");
            return response()->json(['error' => 'AI entity configuration error.'], 500);
        }

        $chat = null; // Initialize to null
        $wasRecentlyCreated = false;
        $transactionError = null; // Variable to store potential error

        try {
            // Use transaction for atomic check/create
            DB::transaction(function () use ($authenticatedUser, $ai, &$chat, &$wasRecentlyCreated) {
                $chat = Chat::firstOrCreate(
                    [ // Attributes to find by
                        'user_id' => $authenticatedUser->id,
                        'chatable_id' => $ai->id,
                        'chatable_type' => get_class($ai),
                    ]
                );
                // Check if the model was just created in this request
                $wasRecentlyCreated = $chat->wasRecentlyCreated;
                Log::info("Inside transaction: Chat ID {$chat->id}, WasRecentlyCreated: " . ($wasRecentlyCreated ? 'Yes' : 'No'));
            });
        } catch (\Exception $e) {
             // Catch any error during the transaction
             $transactionError = $e;
             Log::error("Error during openAIChat transaction: " . $e->getMessage(), ['exception' => $e]);
        }

        // **** ADD CHECK HERE ****
        // Check if the transaction failed OR if $chat is still null for some reason
        if ($transactionError || !$chat instanceof Chat) {
            Log::error("Failed to find or create chat within transaction.", ['user_id' => $authenticatedUser->id, 'ai_id' => $ai->id]);
            return response()->json(['error' => 'Could not establish chat session.'], 500);
        }
        // **** END CHECK ****

        // --- Proceed only if $chat is a valid Chat object ---
        Log::info("openAIChat: Proceeding with Chat ID {$chat->id}");

        // Use exists() for a more efficient check if messages exist
        $hasExistingMessages = $chat->messages()->exists();

        // --- Trigger Initial AI Message if Chat is New AND Empty ---
        if (($wasRecentlyCreated || !$hasExistingMessages) && !empty($ai->description)) {
            Log::info("openAIChat: Chat {$chat->id} is new or empty. Dispatching initial AI prompt job.");
            ProcessAIMessage::dispatch($chat->id, null, $authenticatedUser->id);
        } else {
            Log::info("openAIChat: Chat {$chat->id} has existing messages or AI has no description. Not dispatching initial AI job.");
        }
        // --- End Initial AI Message Trigger ---

        // Fetch messages for the response
        $rawMessages = $chat->messages()->with('sender')->orderBy('created_at', 'asc')->take(50)->get();
        $formattedMessages = $this->formatMessagesForFrontend($rawMessages, $authenticatedUser);

        return response()->json([
            'chat_id' => $chat->id,
            'messages' => $formattedMessages,
            'chat_partner' => [
                'id' => $ai->id,
                'type' => get_class($ai),
                'description' => null, // Frontend doesn't need this anymore
                'name' => $ai->name ?? "Dr.Pet-er (AI)",
                'image_url' => $ai->profile_picture ?? asset('images/veterinarian_AI.jpg'),
            ],
        ]);
    }

    /**
     * Send a message in a chat.
     */
    public function sendMessage(Request $request)
    {
        $data = $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'message' => 'required|string',
        ]);

        Log::info("sendMessage: Received message data:", $data);

        $authenticatedUser = Auth::user(); // The logged-in App\Models\User
        $chatId = $data['chat_id'];
        $messageText = $data['message'];

        // Fetch the chat instance, load participants to check permission
        $chat = Chat::with('user', 'chatable')->find($chatId);

        if (!$chat) {
            return response()->json(['error' => 'Chat not found'], 404);
        }

        // Security Check: Is the authenticatedUser the `user` of the chat OR the `chatable`?
        $isParticipant = ($chat->user_id == $authenticatedUser->id) ||
                        ($chat->chatable_id == $authenticatedUser->id && $chat->chatable_type == get_class($authenticatedUser));

        if (!$isParticipant) {
            Log::warning("Unauthorized message attempt.", [ /* ... logging data ... */ ]);
            return response()->json(['error' => 'You are not a participant in this chat.'], 403);
        }
        Log::info("sendMessage: Chat {$chat->id} found and User {$authenticatedUser->id} is participant.");

        // Create a new message, sender is always the authenticatedUser
        $message = $chat->messages()->create([
            'sender_id' => $authenticatedUser->id,
            'sender_type' => get_class($authenticatedUser),
            'message' => $messageText,
        ]);
        Log::info("sendMessage: User message created.", ['message_id' => $message->id]);


        // Broadcast the new USER message
        broadcast(new NewChatMessage($message, $chatId))->toOthers(); // Exclude sender

        // --- AI Integration Logic ---
        // Check if the *other* party in the chat is an AI
        $otherParty = null;

        if ($chat->user_id == $authenticatedUser->id) {
             // If auth user is the primary user_id, the chatable is the other party
            $otherParty = $chat->chatable;
        } else if ($chat->chatable_id == $authenticatedUser->id && $chat->chatable_type == get_class($authenticatedUser)) {
             // If auth user is the chatable entity, the user_id is the other party
            $otherParty = $chat->user; // Load the user model if needed: $chat->loadMissing('user')->user;
        }

        // Dispatch the job ONLY if the other party is an AI instance
        if ($otherParty instanceof AI) {
            Log::info("Dispatching ProcessAIMessage for chat {$chatId} as other party is AI.");
            // Job needs: chatId, the user's message text, and the user's ID
            ProcessAIMessage::dispatch($chatId, $messageText, $authenticatedUser->id);
        }
        // --- End AI Integration Logic ---
        $user = $this->userService->find($authenticatedUser->id);

        $profile_picture = asset($user->getFirstMediaUrl('profile_picture')) ?: asset('images/default_user_avatar.png'); 

        // Return the sent message (optionally load sender for the response)
        return response()->json(['status' => 'Message sent!', 'message_data' => $message->load('sender'), 'picture_url' => $profile_picture]);
    }

    /**
     * Clear messages for a given chat.
     */
    public function clearMessages($chat_id) // Parameter name matches route {chat_id}
    {
        $chat = Chat::find($chat_id);
        $user = Auth::user();

        if (!$chat) {
            Log::error("ClearMessages Error: Chat with ID {$chat_id} not found.");
            return response()->json(['error' => 'Chat not found'], 404);
        }

        // Authorization: Check if the authenticated user is the chat owner (user_id)
        // OR if they are the chatable entity. Allows either participant to clear.
        $isParticipant = ($chat->user_id == $user->id) ||
                         ($chat->chatable_id == $user->id && $chat->chatable_type == get_class($user));

        if (!$isParticipant) {
            Log::warning("ClearMessages: Unauthorized attempt by User {$user->id} on Chat {$chat_id}.");
            return response()->json(['error' => 'Unauthorized to clear this chat'], 403);
        }

        try {
            $deletedCount = $chat->messages()->delete(); // Deletes all related messages
            Log::info("Messages cleared for Chat {$chat_id}. Count: {$deletedCount}");
            return response()->json(['status' => 'Messages cleared!']);
        } catch (\Exception $e) {
            Log::error("Error deleting messages for Chat {$chat_id}: " . $e->getMessage());
            return response()->json(['error' => 'Failed to clear messages due to a server error.'], 500);
        }
    }

    /**
     * Helper function to format messages for frontend response.
     */
    protected function formatMessagesForFrontend($messages, User $authenticatedUser)
    {
        return $messages->map(function ($message) use ($authenticatedUser) {
            $sender = $message->sender; // Polymorphic sender model (User or AI or Other)
            $senderData = null;

            if ($sender) {
                 $isAuthUser = ($sender instanceof User && $sender->id == $authenticatedUser->id);
                 $isAI = $sender instanceof AI;

                 $profilePic = asset('images/default_user_avatar.jpg'); // Default fallback
                 $senderName = 'Unknown';

                 if ($isAuthUser) {
                     $senderName = $sender->name ?? 'You';
                     $profilePic = method_exists($sender, 'getFirstMediaUrl')
                                     ? ($sender->getFirstMediaUrl('profile_pictures') ?: $profilePic)
                                     : ($sender->profile_picture ?? $profilePic); // Check direct attribute too
                 } elseif ($isAI) {
                     $senderName = $sender->name ?? 'Dr.Pet-er (AI)';
                     $profilePic = $sender->profile_picture ?? asset('images/veterinarian_AI.jpg');
                 } else { // Handle other potential sender types if needed
                     $senderName = $sender->name ?? class_basename($sender);
                     $profilePic = method_exists($sender, 'getFirstMediaUrl')
                                     ? ($sender->getFirstMediaUrl('profile_picture') ?: $profilePic)
                                     : ($sender->profile_picture ?? $profilePic);
                 }

                $senderData = [
                    'id' => $sender->id,
                    'type' => get_class($sender),
                    'name' => $senderName,
                    'profile_picture' => $profilePic,
                    'image_url' => $profilePic, // Consistent key for JS
                ];
            } else {
                 // Log if sender is missing, indicates data integrity issue
                 Log::warning("formatMessagesForFrontend: Message {$message->id} missing sender relationship.", [
                     'sender_id' => $message->sender_id, 'sender_type' => $message->sender_type
                 ]);
                 $senderData = [ /* ... minimal fallback ... */ ];
            }

            return [
                'id' => $message->id,
                'chat_id' => $message->chat_id,
                'message' => $message->message,
                'created_at' => $message->created_at->toISOString(),
                'user' => $senderData, // Key JS expects
            ];
        });
    }
}
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Chat;
use App\Models\AI;       // Ensure AI model is imported
use App\Models\User;      // Ensure User model is imported
use App\Models\Message;
use App\Events\NewChatMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessAIMessage implements ShouldQueue
{
    // ... (traits) ...
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chatId;
    protected $userMessageText; // Can be null if it's an initiation call
    protected $triggeringUserId;

    // Constructor signature allows nullable message text
    public function __construct(int $chatId, ?string $userMessageText, int $userId)
    {
        $this->chatId = $chatId;
        $this->userMessageText = $userMessageText;
        $this->triggeringUserId = $userId;
        Log::info("ProcessAIMessage Job Queued.", [
            'chat_id' => $this->chatId,
            'user_id' => $this->triggeringUserId,
            'is_initiation' => is_null($userMessageText) // Log if it's an initiation
        ]);
    }

    public function handle()
    {
        $isInitiationCall = is_null($this->userMessageText);
        $apiResultPrefix = 'API_RESPONSE_VISIBLE_TO_DR_PETER:';
        $isApiResultFeed = !$isInitiationCall && str_starts_with($this->userMessageText ?? '', $apiResultPrefix);

        Log::info("ProcessAIMessage Job started.", [/* ... logging ... */]);

        $chat = null; $ai = null;

        try {
            $chat = Chat::with(['messages.sender', 'chatable'])->find($this->chatId);
            if (!$chat || !($chat->chatable instanceof AI)) { /* ... error handling ... */ return; }
            $ai = $chat->chatable;
            Log::info("ProcessAIMessage: AI participant confirmed: " . $ai->name);

            // --- Prepare API Contents ---
            $apiContents = [];
            $aiSystemPrompt = trim($ai->description ?? '');

            if ($isInitiationCall) {
                // --- Handle Initial AI Greeting ---
                Log::info("Handling initiation call.", ['chat_id' => $this->chatId]);
                if (!empty($aiSystemPrompt)) {
                    // Send ONLY the system prompt as the first 'user' turn to generate the greeting
                    $apiContents[] = ['role' => 'user', 'parts' => [['text' => $aiSystemPrompt]]];
                    // Gemini should respond with the greeting after this
                    Log::info("Prepared API contents from system prompt for initiation.");
                } else {
                    Log::warning("Initiation call, AI has no description.", ['chat_id' => $this->chatId]);
                    $this->saveAndBroadcastAIMessage("Hello! How can I help?", $chat, $ai); // Generic greeting
                    return;
                }

            } elseif ($isApiResultFeed) {
                // --- Handle Feed of API Result ---
                Log::info("Handling API result feed.", ['chat_id' => $this->chatId]);
                // Get history (which includes the user message and the AI's CALL_API message)
                $historyIncludingApiCall = $chat->messages->sortBy('created_at')->map(function ($msg) use ($ai) {
                     $role = ($msg->sender_type === User::class) ? 'user' : 'model';
                     return ['role' => $role, 'parts' => [['text' => $msg->message]]];
                 })->values()->toArray();

                $apiContents = $historyIncludingApiCall;
                // Add the API result back as a 'user' turn for context
                $apiContents[] = ['role' => 'user', 'parts' => [['text' => $this->userMessageText]]]; // Contains "API_RESPONSE_VISIBLE..."
                Log::info("Prepared API contents including API result feed.");

            } else {
                // --- Handle Regular User Message ---
                Log::info("Handling user message.", ['chat_id' => $this->chatId]);
                $conversationHistory = $chat->messages->sortBy('created_at')->map(function ($msg) use ($ai) {
                     $role = ($msg->sender_type === User::class) ? 'user' : 'model';
                     return ['role' => $role, 'parts' => [['text' => $msg->message]]];
                 })->values()->toArray();

                 // Optional: Prepend system prompt for context if needed (e.g., first *real* user message)
                 $shouldPrependPromptContext = false;
                 if (!empty($aiSystemPrompt)) {
                    // Example: Only add if history only contains AI greeting + this user message
                     $isFirstActualUserMessage = count($conversationHistory) === 2 && $conversationHistory[0]['role'] === 'model' && $conversationHistory[1]['role'] === 'user';
                     if ($isFirstActualUserMessage) {
                         $shouldPrependPromptContext = true;
                         Log::info("Including system prompt context for first user message.");
                     }
                 }

                 if ($shouldPrependPromptContext) {
                    $apiContents[] = ['role' => 'user', 'parts' => [['text' => $aiSystemPrompt]]];
                    $apiContents = array_merge($apiContents, $conversationHistory);
                 } else {
                     $apiContents = $conversationHistory;
                 }
            }
            // --- End API Content Preparation ---

            // --- Check, Prepare API Call, Execute, Handle Response ---
            if (empty($apiContents)) {
                Log::warning("ProcessAIMessage: No API contents generated.", [/* ... */]);
                return;
            }

            $apiKey = env('GEMINI_API_KEY'); /* ... */
            $modelName = env('GEMINI_MODEL_NAME', 'gemini-1.5-flash-latest'); /* ... */
            if (!$apiKey || !$modelName) { /* ... */ return; }
            $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key=" . $apiKey;
            $requestBody = [
                'contents' => $apiContents,
                'generationConfig' => ['temperature' => 0.9, 'maxOutputTokens' => 1000], // Example
            'safetySettings' => [ /* ... */ ],
            ];
            Log::info("Sending request to Gemini API.", ['url' => $apiUrl, 'content_turns_sent' => count($apiContents)]);
            // Log::debug('Gemini Request Body:', ['body' => json_encode($requestBody)]);

            $response = Http::withHeaders(['Content-Type'=>'application/json', 'Accept'=>'application/json'])
                           ->timeout(60)->post($apiUrl, $requestBody);

            if ($response->successful()) {
                // ... (Extract $aiResponseText as before) ...
                 $aiResponseText = $response->json('candidates.0.content.parts.0.text'); // Shortcut using dot notation

                 if (!$aiResponseText) { /* ... handle empty/blocked/stopped responses ... */ }

                 if ($aiResponseText) {
                      // Save and broadcast the AI's response.
                      // The observer will check *this* message for CALL_API.
                      $this->saveAndBroadcastAIMessage($aiResponseText, $chat, $ai);
                 }
            } else {
                 // ... (Log API error and send user-facing error message as before) ...
                 $statusCode = $response->status();
                 Log::error('Gemini API request failed', ['status'=>$statusCode, 'body'=>$response->body(), /*...*/]);
                 $errorMessage = "AI service error ({$statusCode}). Please try again.";
                 if($statusCode == 503) $errorMessage = "AI service busy. Please try again.";
                 $this->sendErrorMessageToChat($errorMessage, $chat, $ai);
            }

        } catch (\Exception $e) {
            // ... (Log critical errors and send user-facing error message) ...
             Log::critical('Exception in ProcessAIMessage handle: '.$e->getMessage(), [/*...*/]);
             if($chat && $ai) $this->sendErrorMessageToChat("System error processing request.", $chat, $ai);
        } finally {
            Log::info("ProcessAIMessage Job finished attempt.", ['chat_id' => $this->chatId]);
        }
    } // end handle
    /**
     * Saves the AI's message and broadcasts it.
     */
    protected function saveAndBroadcastAIMessage(string $messageText, Chat $chat, AI $ai)
    {
        // Ensure we don't save an empty message
        if(trim($messageText) === '') {
            Log::warning("Attempted to save an empty AI message.", ['chat_id' => $chat->id]);
            return;
        }

        $aiMessage = $chat->messages()->create([
            'sender_id' => $ai->id,
            'sender_type' => get_class($ai),
            'message' => $messageText,
        ]);
        Log::info("AI message saved and broadcasting.", ['chat_id' => $chat->id, 'message_id' => $aiMessage->id]);

        // Important: Pass the newly created $aiMessage to the event
        broadcast(new NewChatMessage($aiMessage, $chat->id));
    }

    /**
     * Saves and broadcasts an error message as the AI.
     */
    protected function sendErrorMessageToChat(string $errorMessageText, Chat $chat, AI $ai)
    {
        // Note: This also calls saveAndBroadcastAIMessage
        if ($chat && $ai) {
            $this->saveAndBroadcastAIMessage($errorMessageText, $chat, $ai);
            Log::info("Sent error message to chat as AI.", ['chat_id' => $chat->id, 'error_message' => $errorMessageText]);
        } else {
            Log::error("Could not send error message to chat - Chat or AI object missing.", ['chat_id' => $this->chatId]);
        }
    }

    // Retry logic
    public $tries = 2; // Reduce retries for API calls unless it's specifically a connection issue
    public $backoff = [30, 60]; // Shorter backoff: 30s, then 60s
    public function retryUntil() { return now()->addMinutes(3); }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessAIMessage Job FAILED permanently.', [
             'chat_id' => $this->chatId,
             'exception_message' => $exception->getMessage(),
             'exception_class' => get_class($exception),
        ]);
        // Optionally, notify the user via a different mechanism that the AI response failed after retries
        // Or save a specific "failed" message in the chat attributed to the AI
        $chat = Chat::find($this->chatId);
        $ai = $chat ? $chat->chatable : null; // Try to get AI
        if ($chat && $ai instanceof AI) {
             $this->sendErrorMessageToChat("I encountered an issue processing your request after a few tries. Please try again later or ask something different.", $chat, $ai);
        }
    }
}
<?php

namespace App\Observers;

use App\Models\Message;
use App\Models\AI;
use App\Models\Chat;
use App\Events\NewChatMessage;
use App\Jobs\ProcessAIMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

class MessageObserver
{
    public function created(Message $message)
    {
        $message->loadMissing('sender');
        if (!($message->sender instanceof AI)) { return; } // Only process AI messages

        $triggerPhrase = 'CALL_API(';
        if (strpos($message->message, $triggerPhrase) !== false) {
            Log::info("MessageObserver: Trigger phrase found in AI message {$message->id}. Processing...");
            $this->processApiCallCommand($message, $triggerPhrase);
        }
    }

    protected function processApiCallCommand(Message $message, string $triggerPhrase)
    {
        // Regex to capture FunctionName and optional parameters
        $pattern = '/' . preg_quote($triggerPhrase, '/') . '\s*([a-zA-Z0-9_]+)\s*(?:,\s*(.*?))?\s*\)/s';

        if (preg_match($pattern, $message->message, $matches)) {
            $functionName = trim($matches[1]);
            $rawParams = trim($matches[2] ?? '');
            Log::info("MessageObserver: Matched Function: [{$functionName}], Raw Params: [{$rawParams}]");

            // Parse parameters (JSON preferred, fallback to string)
            $params = json_decode($rawParams, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $trimmedParam = trim($rawParams, " '\"");
                $params = (strcasecmp($trimmedParam, 'NO_Attributes') === 0) ? 'NO_Attributes' : $trimmedParam;
            }
            Log::info("MessageObserver: Parsed Params:", is_array($params) ? $params : ['value' => $params]);

            // Execute based on function name
            $this->executeInternalFunction($functionName, $params, $message);

        } else {
             Log::warning("MessageObserver: Failed to parse API command in message {$message->id}.", ['content' => $message->message]);
        }
    }

    /**
     * Executes the requested internal function or API call.
     */
    protected function executeInternalFunction(string $functionName, $params, Message $originalAiMessage)
    {
        // REMOVED 'acknowledged' case - now handles only real API calls
        switch (strtolower($functionName)) { // Use strtolower for case-insensitive matching
            case 'getservices':
                 $this->callInternalHttpApi('GetServices', $params, $originalAiMessage);
                 break;

            case 'showserviceowner':
                 $this->callInternalHttpApi('showServiceOwner', $params, $originalAiMessage);
                 break;

            default:
                 Log::error("MessageObserver: Unknown function call requested by AI: {$functionName}", ['message_id' => $originalAiMessage->id]);
                 $this->createApiResultMessage($originalAiMessage->chat_id, $originalAiMessage->sender_id, "[System Error: AI requested an unknown action '{$functionName}'.]");
                 break;
        }
    }

    /**
     * Calls the internal HTTP API endpoints.
     * (Implementation remains the same as previous version)
     */
    protected function callInternalHttpApi(string $functionName, $params, Message $originalAiMessage)
    {
        $apiUrl = null;
        $method = 'GET';
        $payload = [];

        try {
            // Determine API details based on function name
            switch (strtolower($functionName)) {
                case 'getservices':
                    $apiUrl = route('internal.ai.getServices');
                    $method = 'GET';
                    // Prepare query parameters for GET request
                    $payload = ($params === 'NO_Attributes' || empty($params)) ? [] : (is_array($params) ? $params : ['query' => $params]);
                    break;
                case 'showserviceowner':
                    $apiUrl = route('internal.ai.getServiceOwner');
                    $method = 'POST';
                    // Ensure parameters are valid for POST request body
                    if (!is_array($params) || empty($params['servicable_id']) || empty($params['servicable_type'])) {
                        throw new \InvalidArgumentException("Missing required parameters for showServiceOwner.");
                    }
                    $payload = $params; // Use params as request body
                    break;
                default:
                     // This case should ideally be caught in executeInternalFunction, but added defensively
                     throw new \InvalidArgumentException("Cannot call internal API for unknown function: {$functionName}");
            }

            // --- Retrieve the Internal API Key ---
            $internalApiKey = config('app.internal_api_key');
            if (empty($internalApiKey)) { // Use empty() for better check
                Log::critical("MessageObserver: INTERNAL_API_SECRET_KEY is not configured in .env or config/app.php!");
                throw new \RuntimeException("Internal API Key configuration is missing.");
            }
            // --- End Key Retrieval ---


            Log::info("MessageObserver: Calling internal HTTP API.", [
                'method' => $method,
                'url' => $apiUrl,
                'payload_keys' => is_array($payload) ? array_keys($payload) : null
            ]);

            // --- Build and Send Request with API Key Header ---
            $client = Http::withHeaders([
                            'Accept' => 'application/json',
                            'X-Internal-Api-Key' => $internalApiKey // <<< ADD THE KEY HEADER
                        ])
                        ->timeout(15); // Timeout for the internal call

            $response = null;
            if ($method === 'GET') {
                $response = $client->get($apiUrl, $payload); // Send payload as query string
            } elseif ($method === 'POST') {
                $response = $client->post($apiUrl, $payload); // Send payload as JSON body
            }
            // --- End Request Sending ---


            // --- Handle Response ---
            if ($response && $response->successful()) {
                 Log::info("MessageObserver: Internal API call successful for {$functionName}.");
                 // Trigger continuation job with the JSON result
                 $this->triggerAiContinuation($originalAiMessage, $response->json());
            } elseif ($response) {
                 // Internal API call failed (e.g., 4xx, 5xx error from your API controller)
                 $errorDetail = $response->json('error', 'Internal API Error (' . $response->status() . ')');
                 Log::error("MessageObserver: Internal API call failed.", [
                     'function' => $functionName,
                     'status' => $response->status(),
                     'response_body' => $response->body() // Log the actual error response
                 ]);
                 // Send a message back to the chat indicating the internal failure
                 $this->createApiResultMessage($originalAiMessage->chat_id, $originalAiMessage->sender_id, "[System Error: Failed to retrieve data for '{$functionName}'. Details: {$errorDetail}]");
            } else {
                 // This happens if the Http::get/post call itself failed without returning a response object
                 throw new \RuntimeException("Internal API call for {$functionName} failed without a response object.");
            }
        // --- End Response Handling ---

        } catch (\InvalidArgumentException $e) {
             // Catch specific errors for invalid parameters/function names
             Log::error("MessageObserver: Invalid argument for internal API call.", ['function'=>$functionName, 'error'=>$e->getMessage(), 'params' => $params]);
             $this->createApiResultMessage($originalAiMessage->chat_id, $originalAiMessage->sender_id, "[System Error: Invalid parameters for internal request '{$functionName}'.]");
        } catch (\Exception $e) {
             // Catch any other exceptions during the process
             Log::error("MessageObserver: Exception during internal API call for {$functionName}: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]); // Log trace for deeper errors
             $this->createApiResultMessage($originalAiMessage->chat_id, $originalAiMessage->sender_id, "[System Error: Could not process internal request '{$functionName}'.]");
        }
    } // End callInternalHttpApi


    /**
      * Triggers the ProcessAIMessage job again with API results.
      * (Implementation remains the same as previous version)
      */
    protected function triggerAiContinuation(Message $lastAiMessage, array $resultData)
    {
        // ... (Format $apiResultSummary = "API_RESPONSE_VISIBLE..." ) ...
        $apiResultSummary = "API_RESPONSE_VISIBLE_TO_DR_PETER: \n" . json_encode($resultData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        // ... (Dispatch ProcessAIMessage job) ...
        ProcessAIMessage::dispatch( $lastAiMessage->chat_id, $apiResultSummary, $lastAiMessage->sender_id );
    }

    /**
     * Helper to create and broadcast a new message from the AI (e.g., for errors)
     * (Implementation remains the same as previous version)
     */
    protected function createApiResultMessage(int $chatId, int $aiSenderId, string $resultText)
    {
        // ... (Find chat, create message, broadcast) ...
         if (trim($resultText) === '') return;
         $chat = Chat::find($chatId);
         if (!$chat) { Log::error("Observer cannot find Chat {$chatId}"); return; }
         $msg = $chat->messages()->create([ 'sender_id' => $aiSenderId, 'sender_type' => AI::class, 'message' => $resultText ]);
         Log::info("Observer created result/error message {$msg->id}.");
         broadcast(new NewChatMessage($msg, $chatId));
    }
}
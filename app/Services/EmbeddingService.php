<?php

declare(strict_types=1);

namespace App\Services; // Your chosen namespace

// Assuming this Client and TextPart are from the library you are successfully using
use GeminiAPI\Client;
use App\Models\Embedding;
use GeminiAPI\Resources\Parts\TextPart; // You used this in your `embedContent` call

// The following 'use' statements for response types (like \Gemini\Responses\Embeddings\EmbedContentResponse)
// belong to a different library ('gemini-api-php/client').
// If your $this->client is an instance of \GeminiAPI\Client, these are likely INCORRECT.
// You will need to find the correct response type classes from the 'GeminiAPI' library's documentation
// to properly type-hint $response objects. For now, specific type hints for $response will be omitted
// where they would have referred to \Gemini\Responses\* types.

class EmbeddingService
{
    private Client $client; // This should be \GeminiAPI\Client
    private string $embeddingModel;

    /**
     * Constructor.
     *
     * @param string $apiKey Your Gemini API Key.
     * @param string $modelName The embedding model to use.
     */
    public function __construct(string $apiKey, string $modelName = 'text-embedding-004')
    {
        // This instantiation is based on your previous usage with \GeminiAPI\Client
        $this->client = new Client($apiKey);
        $this->embeddingModel = $modelName;
    }

    /**
     * Generates an embedding for a single piece of text.
     *
     * @param string $text The text to embed.
     * @return array<float>|null The embedding vector, or null on failure.
     */
    public function generateEmbedding(string $text): ?array
    {
        $trimmedText = trim($text);
        if (empty($trimmedText)) {
            error_log("EmbeddingService: Input text cannot be empty.");
            return null;
        }

        try {
            // This call is based on your use of \GeminiAPI\Client
            // and its embedContent method taking a TextPart.
            $response = $this->client->embeddingModel($this->embeddingModel)
                ->embedContent(new TextPart($trimmedText)); // Using TextPart as per your example

            // CRITICAL: The structure of the $response object and how to extract
            // the embedding values (e.g., $response->embedding->values) is entirely
            // dependent on the 'GeminiAPI' library you are using.
            // You MUST verify this with its documentation.
            // The following attempts to access 'embedding->values' is a common pattern
            // but might need adjustment for your specific library.
            if (is_object($response) && isset($response->embedding) && is_object($response->embedding) && isset($response->embedding->values) && is_array($response->embedding->values)) {
                return $response->embedding->values;
            } else {
                // Log the actual response to help understand its structure
                error_log("EmbeddingService: Unexpected response structure for single embedding. Response: " . print_r($response, true));
                return null;
            }
        } catch (\Throwable $e) {
            error_log("Gemini API Error (single embedding for '{$trimmedText}'): " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Generates embeddings for multiple texts by calling embedContent for each one.
     * Note: This is less efficient than a true batch embedding API call if the
     * underlying service supports batching and the library exposed it.
     *
     * @param array<string> $texts An array of texts to embed.
     * @return array<array<float>> An array of embedding vectors, in the same order as input.
     *                             An empty array is returned for texts that failed or were initially empty.
     */
    public function generateEmbeddings(array $texts): array
    {
        if (empty($texts)) {
            return [];
        }

        $allEmbeddings = [];
        foreach ($texts as $text) {
            // Reuse the single embedding logic
            $embedding = $this->generateEmbedding($text); // generateEmbedding handles trimming and empty checks

            if ($embedding !== null) {
                $allEmbeddings[] = $embedding;
            } else {
                // If generateEmbedding returned null (due to error or empty input after trim),
                // add an empty array to maintain the structure relative to the input array.
                $allEmbeddings[] = [];
            }
        }
        return $allEmbeddings;
    }

    /**
     * Sets a different embedding model.
     *
     * @param string $modelName
     * @return void
     */
    public function setModel(string $modelName): void
    {
        $this->embeddingModel = $modelName;
    }

    /**
     * Gets the current embedding model name.
     *
     * @return string
     */
    public function getModel(): string
    {
        return $this->embeddingModel;
    }


    public function FilterEmbeddings(string $prompt)
    // send embeduing type 
    {
        $embeding = Embedding::all();
    }
}
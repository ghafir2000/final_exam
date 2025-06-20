<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\Product;
use App\Models\Embedding;
use App\Services\EmbeddingService; // Import the service
use Illuminate\Support\Facades\Log; // For logging
use Illuminate\Support\Facades\Config; // To get API key

class EmbeddingSeeder extends Seeder
{
    private EmbeddingService $embeddingService;

    public function __construct()
    {
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            $message = "Gemini API key is not configured. Please set GEMINI_API_KEY in your .env file and services.gemini.api_key in config/services.php.";
            Log::error($message);
            // Optionally, throw an exception to stop the seeder if the API key is critical
            // throw new \Exception($message);
            // For now, we'll let it proceed, but embedding generation will fail.
            // Or initialize a "null" service if that makes sense for your error handling.
            // For this implementation, we'll assume an exception should halt or it will try with a null key.
            // A better approach might be to check if $apiKey is null within the run method before proceeding.
        }
        // Assuming your EmbeddingService constructor takes the API key
        // and optionally the model name.
        // You might want to make the model name configurable as well.
        $this->embeddingService = new EmbeddingService($apiKey);
    }

    private function prepareTextForLlm(array $parts): string
    {
        $filteredParts = array_filter($parts, fn($part) => !empty(trim((string) $part))); // Ensure parts are strings
        return implode(". ", $filteredParts) . ".";
    }

    public function run(): void
    {
        $apiKey = env('GEMINI_API_KEY');
        if (!$apiKey) {
            Log::error("EmbeddingSeeder: Gemini API key is not configured. Skipping embedding generation.");
            $this->command->getOutput()->writeln("<error>EmbeddingSeeder: Gemini API key is not configured. Skipping embedding generation.</error>"); // Keep if running via command
            echo "EmbeddingSeeder: Gemini API key is not configured. Skipping embedding generation.\n"; // For direct seeder runs
            return;
        }

        Log::info("Starting LLM embedding generation seeder...");

        $services = Service::with('breeds')->get();
        $products = Product::with('categories')->get();

        Log::info("Processing services for embeddings...");
        foreach ($services as $service) {
            $textParts = [
                "Type: Service",
                "Name: " . trim($service->name),
                "Description: " . trim($service->description),
                "Price: " . $service->price,
            ];

            if ($service->breeds && $service->breeds->isNotEmpty()) {
                $breedsNames = $service->breeds->pluck('name')->implode(', ');
                $textParts[] = "Applicable Breeds: " . trim($breedsNames);
            }

            $textForLlm = $this->prepareTextForLlm($textParts);
            // echo($textForLlm);

            if (!empty($textForLlm) && strlen($textForLlm) > 10) {
                $embeddingVector = $this->embeddingService->generateEmbedding($textForLlm);

                if ($embeddingVector) {
                    Embedding::factory()
                        ->withTextForLlmEmbedding($textForLlm)
                        ->withEmbeddableService($service)
                        ->create([
                            'embedding_vector' => $embeddingVector, // Pass the generated vector here
                        ]);
                    Log::info("Generated and saved embedding for service: {$service->name}");
                } else {
                    Log::error("Failed to generate embedding for service: {$service->name}. Text: " . $textForLlm);
                    // Optionally create the embedding record with a null vector if that's desired behavior on API failure
                    // Embedding::factory()
                    //     ->withTextForLlmEmbedding($textForLlm)
                    //     ->withEmbeddableService($service)
                    //     ->create([
                    //         'embedding_vector' => null, // Explicitly null on failure
                    //     ]);
                }
            } else {
                Log::warning("Skipping service {$service->name} due to insufficient text for LLM.");
            }
        }

        Log::info("Processing products for embeddings...");
        foreach ($products as $product) {
            $textParts = [
                "Type: Product",
                "Name: " . trim($product->name),
                "Description: " . trim($product->description),
                "Price: " . $product->price,
            ];

            if ($product->categories && $product->categories->isNotEmpty()) {
                $categoriesNames = $product->categories->pluck('name')->implode(', ');
                $textParts[] = "Applicable Categories: " . trim($categoriesNames);
            }
            $textForLlm = $this->prepareTextForLlm($textParts);

            if (!empty($textForLlm) && strlen($textForLlm) > 10) {
                $embeddingVector = $this->embeddingService->generateEmbedding($textForLlm);

                if ($embeddingVector) {
                    Embedding::factory()
                        ->withTextForLlmEmbedding($textForLlm)
                        ->withEmbeddableProduct($product)
                        ->create([
                            'embedding_vector' => $embeddingVector, // Pass the generated vector here
                        ]);
                    Log::info("Generated and saved embedding for product: {$product->name}");
                } else {
                    Log::error("Failed to generate embedding for product: {$product->name}. Text: " . $textForLlm);
                    // Optionally create with null vector on failure (see service loop comment)
                }
            } else {
                Log::warning("Skipping product {$product->name} due to insufficient text for LLM.");
            }
        }
        Log::info("LLM embedding generation seeder complete.");
    }
}
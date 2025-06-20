<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Service;
use App\Models\Embedding;

use function PHPUnit\Framework\returnSelf;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class EmbeddingFactory extends Factory
{

    protected $model = Embedding::class; // Explicitly define the model
    protected ?string $textForLlmEmbeddingContent = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function withTextForLlmEmbedding(string $text): self
    {
        return $this->state(function (array $attributes) use ($text) {
            return [
                'text_for_llm_embedding' => $text,
            ];
        });
    }

    public function withEmbeddableService(Service $service)
    {
        return $this->state(function (array $attributes) use ($service) {
            return [
                'embeddable_id' => $service->id,
                'embeddable_type' => Service::class,
            ];
        });
    }

    public function withEmbeddableProduct(Product $product)
    {
        return $this->state(function (array $attributes) use ($product) {
            return [
                'embeddable_id' => $product->id,
                'embeddable_type' => Product::class,
            ];
        });
    }


    public function definition(): array
    {

        // IMPORTANT: Reset the state for the next factory usage if instances are somehow reused (defensive)
        // However, for standard `Model::factory()` calls, each call should be a new instance.
        // $this->textForLlmEmbeddingContent = null; // Let's keep this commented for now to not mask the root cause

        return [
            'embedding_vector' => null, // Will be populated by create()
        ];
    }
}

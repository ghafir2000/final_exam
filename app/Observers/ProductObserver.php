<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    public function created(Product $product): void
    {
        $seeder = new \Database\Seeders\EmbeddingSeeder();
        $parts = [
            $product->name,
            $product->description, // Assuming description is available
        ];

        $product->load('categories');
        if ($product->categories && !$product->categories->isEmpty()) {
            $parts = array_merge($parts, $product->categories->pluck('name')->toArray());
        }

        $rawText = implode(' ', array_filter($parts));
        $normalizedText = $seeder->normalizeAndStem($rawText);

        if ($normalizedText) {
            $product->embeddiable()->create([
                'value' => $normalizedText
            ]);
        }
    }

    public function updated(Product $product): void
    {
        $seeder = new \Database\Seeders\EmbeddingSeeder();
        $parts = [
            $product->name,
            $product->description, // Assuming description is available
        ];

        $product->load('categories');
        if ($product->categories && !$product->categories->isEmpty()) {
            $parts = array_merge($parts, $product->categories->pluck('name')->toArray());
        }

        $rawText = implode(' ', array_filter($parts));
        $normalizedText = $seeder->normalizeAndStem($rawText);

        if ($normalizedText) {
            $product->embeddiable()->update([
                'value' => $normalizedText
            ]);
        }
    }

    public function deleted(Product $product): void
    {
        $product->embeddiable()->delete();
    }

    public function restored(Product $product): void
    {
        $product->embeddiable()->restore();
    }

    public function forceDeleted(Product $product): void
    {
        $product->embeddiable()->forceDelete();
    }
}


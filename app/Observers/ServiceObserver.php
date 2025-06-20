<?php

namespace App\Observers;

use App\Models\Service;

class ServiceObserver
{


    public function created(Service $service): void
    {
        $seeder = new \Database\Seeders\EmbeddingSeeder();
        $parts = [
            $service->name,
            $service->description, // Assuming description is available
        ];

        $service->load('breed');
        if ($service->breed) {
            $parts[] = $service->breed->name;
        }

        $rawText = implode(' ', array_filter($parts));
        $normalizedText = $seeder->normalizeAndStem($rawText);

        if ($normalizedText) {
            $service->embeddiable()->create([
                'value' => $normalizedText
            ]);
        }
    }

    public function updated(Service $service): void
    {
        $seeder = new \Database\Seeders\EmbeddingSeeder();
        $parts = [
            $service->name,
            $service->description, // Assuming description is available
        ];

        $service->load('breed');
        if ($service->breed) {
            $parts[] = $service->breed->name;
        }

        $rawText = implode(' ', array_filter($parts));
        $normalizedText = $seeder->normalizeAndStem($rawText);

        if ($normalizedText) {
            $service->embeddiable()->update([
                'value' => $normalizedText
            ]);
        }
    }


    public function deleted(Service $Service): void
    {
        $Service->embeddiable()->delete();
    }


    public function restored(Service $Service): void
    {
        $Service->embeddiable()->restore();
    }

    public function forceDeleted(Service $Service): void
    {
        $Service->embeddiable()->forceDelete();
    }
}

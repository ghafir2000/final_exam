<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\HasMedia;

class ImageService
{
    /**
     * Store an image and associate it with a model.
     *
     * @param HasMedia $model The model implementing HasMedia.
     * @param mixed $image The uploaded image file.
     * @param string $collection The media collection name (default: 'default').
     * @return void
     * @throws \Exception
     */
    public function store(HasMedia $model, $image, $collection = 'default')
    {
        try {
            // Validate and assign the image to the media collection
            $model->addMedia($image)->toMediaCollection($collection);
        } catch (\Exception $exception) {
            Log::error("Error storing image: " . $exception->getMessage());
            throw new \Exception("Failed to store image.");
        }
    }

    /**
     * Update the image associated with a model.
     *
     * @param HasMedia $model The model implementing HasMedia.
     * @param mixed $newImage The new uploaded image file.
     * @param string $collection The media collection name (default: 'default').
     * @return void
     * @throws \Exception
     */
    public function update(HasMedia $model, $image, $collection = 'default')
    {
        try {
            // Clear the existing media in the collection
            $model->clearMediaCollection($collection);

            // Add the new image
            $model->addMedia($image)->toMediaCollection($collection);
        } catch (\Exception $exception) {
            Log::error("Error updating image: " . $exception->getMessage());
            throw new \Exception("Failed to update image.");
        }
    }
}
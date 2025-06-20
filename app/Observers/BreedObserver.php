<?php

namespace App\Observers;
use App\Models\Breed;
use App\Models\Animal;

class BreedObserver
{
    public function created(Breed $breed)
    {
        $this->updateAnimalBreedCount($breed);
    }

    public function updated(Breed $breed)
    {
        $this->updateAnimalBreedCount($breed);
    }

    public function deleted(Breed $breed)
    {
        $this->updateAnimalBreedCount($breed);
    }

    private function updateAnimalBreedCount(Breed $breed)
    {
        $animal = $breed->animal;
        if ($animal) {
            $animal->number_of_breeds = $animal->breeds()->count();
            // dd($animal->number_of_breeds);

            $animal->save();
        }
    }
}

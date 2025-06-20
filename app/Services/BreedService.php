<?php

namespace App\Services;

use App\Models\Breed;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BreedService
{
    public function find($id, $withTrashed = false, $withes = [])
    {
        return Breed::with($withes)->withTrashed($withTrashed)->find($id);
    }

    public function storeMany($data, $animal_id) 
    {
        // Loop over all the data items and create one for each
        $Breeds = [];
        foreach ($data as $breed) {
            // Give the breed an id 'name' so it wont cause an error
            // Create Breed
            $breed['animal_id'] = $animal_id;
            $Breed = Breed::create($breed);
            $Breeds[] = $Breed;
        }
        return $Breeds;
    }

    public function store($data) 
    {
        // Create Breed
        $Breed = Breed::create($data);
        return $Breed;
    }




    public function updateMany($breeds)
    {
        $animal_id = $breeds[0]->animal_id;
        foreach ($breeds as $item) {
            // Find Breed by ID
            $breed = Breed::find($item['id']);

            if (!$breed) {
                // If Breed doesn't exist, create a new one
                $item['animal_id'] = $animal_id;
                $this->store($item);
            } else {
                // Update existing Breed, excluding the ID
                $breed->update(Arr::except($item, 'id'));
            }
        }
    }

    public function update($data,$id)
    {
        $breed = Breed::find($id);

        if (!$breed) {
            throw new \Exception("Breed not found");
        }

        $breed->update(Arr::except($data, 'id'));

        return $breed;

    }
    
    public function destroy($id)
    {
        // Find Breed with `withTrashed` to handle soft deletes
        $Breed = Breed::withTrashed()->find($id);
        if (!$Breed) {
            throw new \Exception("Breed not found");
        }
        $Breed->delete();
        return $Breed;
    }

    public function restore($id)
    {
        // Find the Breed with soft-deleted records
        $Breed = Breed::withTrashed()->find($id);

        if (!$Breed) {
            throw new \Exception("Breed not found");
        }

        // Restore the Breed
        $Breed->restore();
    }
    
}


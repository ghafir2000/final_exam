<?php

namespace App\Services;

use App\Models\Animal;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AnimalService
{
    public function all($data = [], $paginated = false, $withes = [])
    {
        $query = Animal::query()
        ->with($withes) // Load all specified relationships
        ->when(isset($data['breed_id']), function ($query) use ($data) {
            return $query->whereHas('breeds', function ($query) use ($data) {
                $query->where('id', $data['breed_id']);
            });

        })->when(isset($data['search']), function ($query) use ($data) {
            return $query->where('name', 'like', "%{$data['search']}%");
            })->latest();


        if ($paginated)
            return $query->paginate();
        return $query->get();
    }

    public function find($id, $withTrashed = false, $withes = [])
    {
        return Animal::with($withes)->withTrashed($withTrashed)->find($id);
    }

    public function store($data) {
        // Create Animal
        $Animal = Animal::create($data);
        return $Animal;
    }

    public function update($data)
    {
        // Find Animal and validate existence
        $Animal = Animal::find($data['id']);
        if (!$Animal) {
            throw new \Exception("Animal not found");
        }

        // Update Animal
        $data = Arr::except($data,'id');

        $Animal->update($data);
        return $Animal;
    }

    public function destroy($id)
    {
        // Find Animal with `withTrashed` to handle soft deletes
        $Animal = Animal::withTrashed()->find($id);
        if (!$Animal) {
            throw new \Exception("Animal not found");
        }
        $Animal->delete();
        return $Animal;
    }

    public function restore($id)
    {
        // Find the Animal with soft-deleted records
        $Animal = Animal::withTrashed()->find($id);

        if (!$Animal) {
            throw new \Exception("Animal not found");
        }

        // Restore the Animal
        $Animal->restore();
    }
    
    
}


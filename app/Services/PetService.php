<?php

namespace App\Services;

use App\Models\Pet;
use App\Models\Animal;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PetService
{
    public function all($data = [], $paginated = true, $withes = [])
    {
        // \ If 'breed' is in the withes array, ensure 'breed.animal' is also included
        if (in_array('breed', $withes)) {
            $withes[] = 'breed.animal';
        }
        $id = Auth::user()->id;
        $query = Pet::query()->where('customer_id', $id)
            ->with($withes) 
            ->when(isset($data['breed_id']), function ($query) use ($data) {
                return $query->whereHas('breed', function ($query) use ($data) {
                    $query->where('id', $data['breed_id']);
                })
            ->when(isset($data['animal_id']), function ($query) use ($data) {
                return $query->whereHas('breed.animal', function ($query) use ($data) {
                    $query->where('id', $data['animal_id']);
                });

            })->when(isset($data['search']), function ($query) use ($data) {
                return $query->where('name', 'like', "%{$data['search']}%");
                })->latest();
            });

        if ($paginated)
            return $query->paginate();
        return $query->get();
    }


    public function find($id, $withTrashed = false, $withes = [])
    {
        // \ If 'breed' is in the withes array, ensure 'breed.animal' is also included
        if (in_array('breed', $withes)) {
            $withes[] = 'breed.animal';
        }
        $pet = Pet::with($withes)
            ->withTrashed($withTrashed)
            ->find($id);
        $petServicable = $pet->records()->whereHas('booking.service', function ($query) {
            $query->where('servicable_type', get_class(Auth::user()->userable))
                ->where('servicable_id', Auth::user()->userable_id);
        })->exists();

        $petOwner = $pet->customer_id === Auth::user()->userable_id && Auth::user()->userable_type === 'App\Models\Customer';
        
        if (!$pet || (!$petOwner && !$petServicable)) {
            throw new \Exception("Pet not found or you are not the owner or the pet's doctor");
        }
        

        return $pet;
    }

    public function store($data) {
        if (Auth::user()->userable instanceof \App\Models\Customer) {
            $data['customer_id'] = Auth::user()->userable_id;
        }
        try {
            $Pet = Pet::create($data);
            return $Pet;
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'customer_id cannot be null')) {
                throw new \Exception('You are not a customer, please register as one first.');
            }
            throw $e;
        }
    }

    
    public function update($id, $data)
    {
        // \ dd($id);
        $Pet = Pet::find($id);
        if (!$Pet || $Pet->customer_id !== Auth::user()->userable_id) {
            throw new \Exception("Pet not found or you are not the owner");
        }
        // \ dd($Pet);

        // \ Update Pet
        $Pet->update($data);
        return $Pet;
    }


    public function destroy($id)
    {
        // \ Find Pet with `withTrashed` to handle soft deletes
        $Pet = Pet::withTrashed()->find($id);
        if (!$Pet) {
            throw new \Exception("Pet not found");
        }
        if ($Pet->customer_id !== Auth::user()->userable_id) {
            throw new \Exception("Unauthorized action");
        }
        $Pet->delete();
        return $Pet;
    }


    public function restore($id)
    {
        // \ Find the Pet with soft-deleted records
        $Pet = Pet::withTrashed()->find($id);

        if (!$Pet) {
            throw new \Exception("Pet not found");
        }

        // \ Restore the Pet
        $Pet->restore();
    }
    

    
}

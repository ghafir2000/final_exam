<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserService
{
    public function all($data = [], $paginated = false)
    {
        $query = User::query()
        ->with('userable') // Eager load the polymorphic userable relationship
        ->when(isset($data['role']), function ($query) use ($data) {
            $query->where('userable_type', $data['role']);

        })->when(isset($data['search']), function ($query) use ($data) {
            return $query->where('name', 'like', "%{$data['search']}%")->latest();
        })->when(isset($data['email']), function ($query) use ($data) {
            return $query->Where('email', 'like', "%{$data['email']}%")->latest();
            });

        if ($paginated)
            return $query->paginate();
        return $query->get();
    }

    public function find($id, $withTrashed = false, $withes = [])
    {
        return User::with($withes)->with('userable')->withTrashed($withTrashed)->find($id);
    }

    public function store($data) {
        // Ensure userable type exists and is valid
        if (!class_exists($data['userable_type'])) {
            throw new \InvalidArgumentException("Invalid userable type: {$data['userable_type']}");
        }
    
        return DB::transaction(function () use ($data) {
            // Hash the password for security
            $data['password'] = bcrypt($data['password']);
    
            // Dynamically resolve userable fields
            $userableClass = $data['userable_type'];
            $userableFields = (new $userableClass())->getFillable();
            $userableData = Arr::only($data, $userableFields);
    
            // Create Userable
            $userable = $userableClass::create($userableData);
    
            // If userable class is customer give it a unique customer code
            if ($userableClass === \App\Models\Customer::class) {
                $userable->customer_code = (string) Str::uuid();
                $userable->save();
            }
    
            // Create User
            $user = User::create(Arr::except($data, ['userable_type', 'userable_id', ...$userableFields]));
    
            // Associate the userable with the user
            $user->userable()->associate($userable);
            if ($userableClass !== \App\Models\Customer::class) {
                $user->assignRole('provider');
            }
            // Save the user with the association
            $user->save();
    
            return $user;
        });
    }

    public function update($data, $id)
    {
        // Find User and validate existence
        $user = User::find($id);
        if (!$user) {
            throw new \Exception("User not found");
        }
        // Dynamically resolve userable fields
        $userableClass = $user->userable_type; // Get the userable type from the existing user
        $userableFields = (new $userableClass())->getFillable();
        $userableData = Arr::only($data, $userableFields);

        // Update User
        $user->update(Arr::except($data, $userableData));

        // Update Userable
        if ($user->userable) {
            $user->userable->update($userableData);
        }

        return $user;
    }

    public function destroy($id)
    {
        // Find User with `withTrashed` to handle soft deletes
        $user = User::withTrashed()->find($id);

        if (!$user || ($user->role !== 'admin' && $user->id !== Auth()->id())) {
            throw new \Exception("Unauthorized action");
        }
        $user->userable->delete();
        $user->delete();
        return $user;
    }

    public function restore($id)
    {
        // Find the User with soft-deleted records
        $user = User::withTrashed()->find($id);

        if (!$user) {
            throw new \Exception("User not found");
        }

        // Restore the User
        $user->restore();
        $user->userable->restore();
    }
    
    public function login($data)
    {
        if (Auth::attempt($data)) {
            return true;
        }
        return false;
    }
}


<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginUserRequest;
use Illuminate\Database\Eloquent\Model;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;

class UserController extends Controller
{

    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getServiceOwnerForAI(Request $request)
    {
        $validated = $request->validate([
            'servicable_id' => 'required|integer',
            'servicable_type' => 'required|string',
        ]);

        $servicableId = $validated['servicable_id'];
        $servicableType = $validated['servicable_type']; // e.g., App\Models\Vetrinarian

        Log::info("Internal API: getServiceOwnerForAI called.", $validated);

        // Validate type
        if (!class_exists($servicableType) || !is_subclass_of($servicableType, Model::class)) {
             Log::error("Internal API Error: Invalid serviceable_type.", $validated);
             return response()->json(['error' => 'Invalid owner type.'], 400);
        }

        try {
            // Find the User associated with the given Vetrinarian/Partner ID
            // Assuming your Vetrinarian/Partner model has a `user()` relationship OR
            // your User model has a `userable()` relationship back to Vet/Partner.
            // Let's assume User has userable():
            $ownerUser = User::where('userable_id', $servicableId)
                             ->where('userable_type', $servicableType)
                             ->first();

            if (!$ownerUser) {
                Log::error("Internal API Error: User for serviceable not found.", $validated);
                return response()->json(['error' => 'Service owner not found.'], 404);
            }

            // Format the response similar to your example
            $formattedOwner = [
                 "id" => $ownerUser->id,
                 "created_at" => $ownerUser->created_at->toISOString(),
                 "updated_at" => $ownerUser->updated_at->toISOString(),
                 "name" => $ownerUser->name,
                 "email" => $ownerUser->email,
                 "phone" => $ownerUser->phone,
                 "email_verified_at" => $ownerUser->email_verified_at ? $ownerUser->email_verified_at->toISOString() : null,
                 "address" => $ownerUser->address,
                 "country" => $ownerUser->country,
                 "deleted_at" => $ownerUser->deleted_at ? $ownerUser->deleted_at->toISOString() : null,
                 "userable_id" => $ownerUser->userable_id,
                 "userable_type" => $ownerUser->userable_type,
                 // Use Spatie media library or direct attribute for profile image
                  "profile_image_url" => method_exists($ownerUser, 'getFirstMediaUrl')
                     ? ($ownerUser->getFirstMediaUrl('profile_pictures') ?: null) // Get URL or null
                     : ($ownerUser->profile_picture ?? null), // Fallback to direct attribute
                  "url" => url('/user/' . $ownerUser->id) // Generate profile URL dynamically
            ];

            // Handle potential null profile image URL
             if (empty($formattedOwner['profile_image_url'])) {
                  Log::warning("User {$ownerUser->id} has no profile image set.");
                  // You might want to provide a default image URL here if the AI needs one
                   $formattedOwner['profile_image_url'] = asset('images/default_user_avatar.png');
             }


            return response()->json(['api_response' => $formattedOwner]);

        } catch (\Exception $e) {
            Log::error("Internal API Error in getServiceOwnerForAI: " . $e->getMessage(), $validated);
            return response()->json(['error' => 'Failed to retrieve service owner details.'], 500);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = $request->only(['role' , 'search' , 'email']);
        $users = $this->userService->All($data);
        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function login(LoginUserRequest $request)
    {
        $data = $request->validated();
        $is_loggedIn = $this->userService->login($data);

        if ($is_loggedIn) {
            $id = Auth::id();
            $user = $this->userService->find($id);
            $token = $user->createToken('barrer_token')->plainTextToken;
            return response()->json(['barrer_token' => $token]);
        }

        return response()->json(['error' => 'Invalid login credentials'], 401);
    }

    public function store(CreateUserRequest $request)
    {
        $data = $request->validated();
        $user = $this->userService->store($data);
        return response()->json(['message' => 'User registered successfully', 'user' => $user]);
    }

    public function show($id)
    {
        $user = $this->userService->find($id, true, ['userable']);
        return response()->json($user);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function update(UpdateUserRequest $request)
    {
        $data = $request->validated();
        $user = $this->userService->update($data, Auth()->id());
        return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
    }

    public function destroy($id)
    {
        $user = $this->userService->destroy($id);
        return response()->json(['message' => 'User deleted successfully', 'user' => $user]);
    }
}


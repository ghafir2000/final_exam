<?php

namespace App\Services;

use App\Models\AI;
use App\Models\Chat;
use App\Models\User;
use App\Enums\ChatEnums;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ChatService
{

    public function all($data = [])
    {
        $data['id'] = Auth()->id();
        $chats = Chat::query()
            ->with(['chatable', 'user', 'messages'])
            ->where('chatable_type', '!=', 'App\Models\AI')
            ->when(isset($data['search']), function ($query) use ($data) {
                $query->where('name', 'like', "%{$data['search']}%")->latest();
            })
            ->when(isset($data['email']), function ($query) use ($data) {
                $query->where('email', 'like', "%{$data['email']}%")->latest();
            })
            ->when(isset($data['id']), function ($query) use ($data) {
                $query->where('user_id', $data['id'])
                ->orwhere(function ($query) use ($data) {
                  $query->where('chatable_id', $data['id'])
                        ->where('chatable_type',User::class);
            });
        })
        ->latest()
        ->get();
        Log::info("the found chats are $chats");

        return $chats;
    }

    public function find($id, $withTrashed = false, $withes = [])
    {
        $user = Auth::user(); // Authenticated User
        
        $chat = Chat::with($withes)->with('chatable')->withTrashed($withTrashed)->find($id);
        if (!$chat) {
            return response()->json(['error' => 'Chat not found'], 404); // Return JSON response
        }

        $isParticipant = ($chat->user_id == $user->id) ||
                         ($chat->chatable_id == $user->id && $chat->chatable_type == get_class($user));

        if (!$isParticipant) {
            return response()->json(['error' => 'You are not a participant in this chat.'], 403);
        }
        return $chat;
    }

    public function firstOrCreate($data) {
        if (!class_exists($data['chatable_type']) || $data['chatable_type'] === 'App\Models\AI') {
            throw new \InvalidArgumentException("Invalid chatable type: {$data['chatable_type']}");
        }

        $user_id = Auth()->user()->id;
        $data['user_id'] = $user_id;
    
        $chat = Chat::firstOrCreate($data);

        return $chat;
    }

    public function update($data)
    {
        // Log::info('Updating chat with data:', $data);

        $chat = $this->find($data['id']); // Find the chat
        if (!$chat) {
            throw new \Exception("Booking not found");
        }
        
        $chat->update(Arr::except($data, 'id'));

        return $chat;

    }


    public function destroy($id)
    {
        $chat = Chat::withTrashed()->find($id);
        if (!$chat) {
            throw new \Exception("Chat not found");
        }
        $chat->delete();
        return $chat;
    }

    public function restore($id)
    {
        $chat = Chat::withTrashed()->find($id);

        if (!$chat) {
            throw new \Exception("Chat not found");
        }

        $chat->restore();

        return $chat;
    }
    
}


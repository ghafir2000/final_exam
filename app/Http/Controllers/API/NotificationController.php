<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth; // Important for getting the user
use Illuminate\Support\Facades\Log;   // <-- ADDED THIS FOR DEBUGGING

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // --- TRACER BULLET DEBUGGING ---
        Log::info('--- API/NotificationController: index() method STARTED. ---');

        try {
            $user_id = Auth::id(); // Switched to Auth::user() for standard practice
            $user = User::find($user_id);


            if (!$user) {
                Log::error('--- API/NotificationController: FAILED - User is not authenticated. ---');
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }

            Log::info('--- API/NotificationController: User is authenticated. User ID: ' . $user->id);

            // Let's break down the query
            Log::info('--- API/NotificationController: About to query for notification COUNT... ---');
            $count = $user->unreadNotifications()->count();
            Log::info('--- API/NotificationController: SUCCESSFULLY got count: ' . $count);

            Log::info('--- API/NotificationController: About to query for notification DATA... ---');
            $notifications = $user->unreadNotifications()->take(5)->get()->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'data' => $notification->data,
                    'created_at' => $notification->created_at,
                ];
            });
            Log::info('--- API/NotificationController: SUCCESSFULLY got notification data.');

            $response_data = [
                'count' => $count,
                'notifications' => $notifications
            ];

            Log::info('--- API/NotificationController: Preparing to send final JSON response. ---');

            return response()->json($response_data);

        } catch (\Throwable $e) {
            // This is a safety net. If any part of the above code throws a catchable error,
            // this will log it instead of crashing silently.
            Log::error('--- API/NotificationController: A CATCHABLE EXCEPTION OCCURRED IN index() ---');
            Log::error('Error Message: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile() . ' on line ' . $e->getLine());
            // You can uncomment the next line for a full stack trace if needed
            // Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Server error in notification controller.'], 500);
        }
    }

    // ... Omitted other empty methods for brevity ...
}

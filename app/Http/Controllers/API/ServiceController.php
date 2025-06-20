<?php

namespace App\Http\Controllers\API;

use App\Models\Service;
use Illuminate\Http\Request;
use App\Services\ServiceService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class ServiceController extends Controller
{
    protected $serviceService;

    public function __construct(ServiceService $serviceService)
    {
        $this->serviceService = $serviceService;
    }

    public function getServicesForAI(Request $request)
    {
        // In a real app, you'd filter based on AI context, pet type etc.
        // For now, let's return the two example services relevant to cats/ears
        Log::info("Internal API: getServicesForAI called.");

        try {
            $services = $this->serviceService->all([], false, ['servicable','breeds']);
            // Example: Fetch services relevant to cats (breed_id=2) and potentially tags/keywords
            // Adjust this query based on your actual Service model structure and needs
                        // Format the response similar to your example
            $formattedServices = $services->map(function ($service) {
                return [
                    'id' => $service->id,
                    'created_at' => $service->created_at->toISOString(),
                    'updated_at' => $service->updated_at->toISOString(),
                    'deleted_at' => $service->deleted_at ? $service->deleted_at->toISOString() : null,
                    'name' => $service->name,
                    'price' => $service->price,
                    'description' => $service->description,
                    'duration' => $service->duration,
                    // Example: Assuming available_times is stored as JSON or needs processing
                    'available_times' => is_array($service->available_times) ? $service->available_times : json_decode($service->available_times, true),
                    'servicable_id' => $service->servicable_id,
                    'servicable_type' => $service->servicable_type,
                    'breeds' => $service->breeds->pluck('name')->toArray(),
                    // 'breed' => $service->breed->name
                ];
            });

            return response()->json(['api_response' => $formattedServices]);

        } catch (\Exception $e) {
            Log::error("Internal API Error in getServicesForAI: " . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve services.'], 500);
        }
    }
    /**
     * Display a listing of the resource.
     */
 }
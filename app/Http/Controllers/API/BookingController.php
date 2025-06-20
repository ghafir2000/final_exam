<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Services\BookingService;
use App\Services\ServiceService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class BookingController extends Controller
{
    protected $bookingService;
    protected $serviceService;
    public function __construct(BookingService $bookingService, ServiceService $serviceService)
    {
        $this->bookingService = $bookingService;
        $this->serviceService = $serviceService;
    }

    public function getTimes(Request $request)
    {
        $serviceId = $request->input('service_id');
        $date = $request->input('date');
        $date = date('Y-m-d', strtotime($date));

        $service = $this->serviceService->find($serviceId);
        $available_times = json_decode($service->available_times, true);

        if (!is_array($available_times)) {
            Log::error('BookingController::getTimes - available_times is not an array', [
                'service_id' => $serviceId,
                'date' => $date,
                'available_times' => $service->available_times,
            ]);
            return [];
        }

        Log::info('BookingController::getTimes', [
            'service_id' => $serviceId,
            'date' => $date,
            'available_times' => $available_times,
        ]);

        $bookings = $this->bookingService->all(['service_id' => $serviceId, 'date' => $date], false, ['service']);
        Log::info($bookings);
        $available = [];

        foreach ($available_times as $time) {
            $time = date('H:i', strtotime($time));
            $available[$time] = $bookings->where('time', $time)->count() === 0;
        }
        Log::info('final available ', $available);
        return $available;
    }
}

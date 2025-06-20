<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Enums\BookingEnums;
use App\Enums\PaymentEnums;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BookingService
{
    public function all($data = [], $paginated = true, $withes = [])
    {
        $query = Booking::query() //with('pet', 'service', 'service.servicable', 'payable')
            ->with($withes)->with('payable')
            ->when(isset($data['booking_status']), function ($query) use ($data) {
                return $query->where('status', $data['booking_status']);
            })
            ->when(isset($data['date']), function ($query) use ($data) {
                return $query->where('date', $data['date']);
            })
            ->when(isset($data['pet_id']), function ($query) use ($data) {
                return $query->where('pet_id', $data['pet_id']);
            })
            ->when(isset($data['service_id']), function ($query) use ($data) {
                return $query->where('service_id', $data['service_id']);
            })
            ->when(isset($data['servicable_id']), function ($query) use ($data) {
                return $query->whereHas('service', function ($query) use ($data) {
                    return $query->whereHas('servicable', function ($query) use ($data) {
                        return $query->where('id', $data['servicable_id']);
                    });
                });
            })
            ->when(isset($data['payment_status']), function ($query) use ($data) {
                return $query->whereHas('payable', function ($query) use ($data) {
                    $query->where('status', $data['payment_status']);
                });
            })
            ->latest();
            // dd($query);
            // dd($query->get());

        if ($paginated) {
            return $query->paginate();
        }
        $result = $query->get();
        $result->each(function ($booking) {
            $booking->time = \Carbon\Carbon::parse($booking->time)->format('H:i');
        });
        return $result;
    }

    
    public function find($id, $withTrashed = false, $withes = [])
    {
        return Booking::with($withes)->withTrashed($withTrashed)->find($id);
    }

    public function checkUsers($id)
    {
        $user = auth()->user();
        $booking = Booking::with('pet', 'service.servicable')->find($id);
        if (($booking->service->servicable_id != $user->userable_id && $user->userable_type == $booking->service->servicable_type)
            &&
            ($booking->pet->customer_id != $user->userable_id && $user->userable_type == 'App/Models/Customer')) {
            throw new \Exception("You are not authorized to perform this action.");
        }
        return true;
    }

 


    public function store($data) 
    {
        // Create Booking
        $data['status'] = BookingEnums::PENDING;
        
        // dd($Booking['status']);
        $data['time'] = \Carbon\Carbon::parse($data['time'])->format('H:i');
        
        
        $Booking = Booking::create($data);
        return $Booking;
    }


    public function update($data)
    {

        $booking = $this->find($data['id']); // Find the booking
        if (!$booking) {
            throw new \Exception("Booking not found");
        }
        
        $booking->update(Arr::except($data, 'id'));

        return $booking;

    }
    
    public function destroy($id)
    {
        // Find Booking with `withTrashed` to handle soft deletes
        $Booking = Booking::withTrashed()->find($id);
        if (!$Booking) {
            throw new \Exception("Booking not found");
        }
        $Booking->delete();
        return $Booking;
    }

    public function restore($id)
    {
        // Find the Booking with soft-deleted records
        $Booking = Booking::withTrashed()->find($id);

        if (!$Booking) {
            throw new \Exception("Booking not found");
        }

        // Restore the Booking
        $Booking->restore();
    }
    
}


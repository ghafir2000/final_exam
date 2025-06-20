<?php

namespace App\Http\Controllers\web;

use App\Models\Booking;
use App\Services\ServiceS;
use App\Services\PetService;
use Illuminate\Http\Request;
use App\Services\AnimalService;
use App\Services\BookingService;
use App\Services\ServiceService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StartBookingRequest;
use App\Http\Requests\CreateBookingRequest;
use App\Http\Requests\UpdateBookingRequest;

class BookingController extends Controller
{
    protected $petService,$serviceService,$bookingService;

    public function __construct(PetService $petService,
                                ServiceService $Service,
                                BookingService $bookingService)
    {
        $this->petService = $petService;
        $this->serviceService = $Service;
        $this->bookingService = $bookingService;
    }

    public function index(Request $request)
    {
        // dd($request->all());
        $fileters = $request->only([ 'pet_id', 'service_id','booking_status','payment_status']);
        $user = auth()->user();
        if ($user->userable_type != "App\Models\Customer") {
            // Add your logic here for when the user is a Veterinarian or Partner
            
            $fileters['servicable_id'] = auth()->user()->userable_id; 
            $bookings = $this->bookingService->all($fileters, true, ['pet', 'service', 'service.servicable', 'payable']);
            // dd($bookings);
            return view('web.auth.bookings.index', compact('bookings'));
        } else {
            // dd($request->pet_id);
            return redirect()->route('pet.show',$request->pet_id);
        }
    }

    public function show($id)
    {
        $booking = $this->bookingService->find($id, true, ['pet', 'service', 'service.servicable', 'payable']);
        return view('web.auth.bookings.show', compact('booking'));
    }

    public function PetIndex(Request $request)
    {
        $data = $request->only(['search', 'animal_id', 'breed_id', 'booking_id']); // Extract filter parameters
        $pets = $this->petService->all($data);
        $select = true;
        return view('web.auth.pets.index', compact('pets', 'select'));
    }
    public function ServiceIndex(Request $request)
    {
        $data = $request->only(['search', 'animal_id', 'breed_id']); // Extract filter parameters
        $services = $this->serviceService->all($data);
        $select = true;
        return view('web.auth.services.index', compact('services', 'select'));
    }


    public function create(Request $request)
    {
        // dd($request);
        switch (true) {
            case isset($request['start']):
                return redirect()->route('booking.PetIndex');
                break;
            case isset($request['pet_id']):
                $request->session()->put('booking.pet_id', $request['pet_id']);
                $pet = $this->petService->find($request['pet_id'], true, ['breed']);
                $breed_id = $pet->breed_id;

                //we can set filters here for the folloewing in the next request ['search'-> by name of service , 'animal_id', 'breed_id']
                return redirect()->route('booking.ServiceIndex', ['breed_id' => $breed_id]);
                break;
            case isset($request['service_id']):
                $request->session()->put('booking.service_id', $request['service_id']);
                $service = $this->serviceService->find(session('booking.service_id'));
                return view('web.auth.bookings.create', compact('service'));
                break;
            default:
            abort(404);
        }
    }
    public function start(StartBookingRequest $request)
    {
        $data = $request->validated();
        $booking = $this->bookingService->update($data);
        $booking = $this->bookingService->find($data['id'],false, ['pet', 'service', 'service.servicable']);
        // dd($booking);
        return view('web.auth.bookings.start', compact('booking'));
    }

    public function reschedule($id)
    {
        $booking = $this->bookingService->find($id);
        $service_id = $booking->service_id;
        $service = $this->serviceService->find($service_id);

        return view('web.auth.bookings.reschedule', compact('service', 'booking'));
    }

    public function store(CreateBookingRequest $request)
    {
        $data = $request->validated();
        $booking_data = session('booking');

        $booking_data = array_merge($booking_data, $data);
        $pet = $this->petService->find(session('booking.pet_id'), true, ['breed']);
        $service = $this->serviceService->find(session('booking.service_id'), true, ['breeds']);
        if (!$service->breeds->pluck('id')->contains($pet->breed_id)) {
            throw new \Exception('The pet and service are not of the same breed.');
        }
        $booking = $this->bookingService->store($booking_data);

        session()->forget('booking');
        
        return redirect()->route('payment.create', [
            'payable_id' => $booking->id,
            'payable_type' => Booking::class
        ]);
    }

    public function edit($id)
    {
        $booking = $this->bookingService->find($id, true, ['payable']);
        return view('web.auth.bookings.edit', compact('booking'));
    }

    public function update(UpdateBookingRequest $request)
    {
        $data = $request->validated();
        $old_time = $this->bookingService->find($data['id'])->time;
        $this->bookingService->checkUsers($data['id']);
        

        $booking = $this->bookingService->update($data);


        // dd($data);
        return redirect()->route('booking.show',$data['id']);
    }

    public function destroy($id)
    {
        $pet_id = $this->bookingService->destroy($id)->pet->id;
        return redirect()->route('booking.index',compact('pet_id'));
    }
}

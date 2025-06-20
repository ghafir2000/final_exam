<?php

namespace App\Http\Controllers\web;

use App\Models\Pet;
use App\Enums\BookingEnums;
use App\Services\PetService;
use Illuminate\Http\Request;
use App\Services\RecordService;
use App\Services\BookingService;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRecordRequest;
use App\Http\Requests\UpdateRecordRequest;

class RecordController extends Controller
{

    protected $petService,$bookingService,$recordService;


    public function __construct(PetService $petService,
                                BookingService $bookingService,
                                RecordService $recordService)
    {
        $this->petService = $petService;
        $this->bookingService = $bookingService;
        $this->recordService = $recordService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index($id) //this is a pet id
    {
        $pet = $this->petService->find($id,false
                                        ,['records','bookings.service.servicable','breed','customer']); 
        return view('web.auth.records.index',compact('pet'));
    }



    public function store(CreateRecordRequest $request)
    {
        $data = $request->validated();
        $booking = $this->recordService->store($data)->booking;
        $pet_id = $booking->pet->id;
        $this->bookingService->update(['id' => $booking->id,'status' => BookingEnums::COMPLETED]);
        return redirect()->route('record.index',['id' => $pet_id]);

    }

    /**
     * Store a newly created resource in storage.
     */

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $record = $this->recordService->find($id);
        return view('web.auth.records.show',compact('record'));
    }

    /**
     * Update the specified resource in storage.
     */

    public function edit(string $id)
    {
        $record = $this->recordService->find($id);
        return view('web.auth.records.edit',compact('record'));
    }
    public function update(UpdateRecordRequest $request, string $id)
    {
        $data = $request->validated();
        $this->recordService->update($id, $data);
        return redirect()->route('record.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->recordService->destroy($id);
        return redirect()->back();
    }
}

<?php

namespace App\Http\Controllers\web;

use App\Services\PetService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePetRequest;
use App\Http\Requests\UpdatePetRequest;
use App\Models\Animal;
use App\Services\AnimalService;

class PetController extends Controller
{

    protected $petService,$animalService;

    public function __construct(PetService $petService, AnimalService $animalService)
    {
        $this->petService = $petService;
        $this->animalService = $animalService;
    }

    public function index(Request $request)
    {
        $data = $request->only(['search', 'animal_id', 'breed_id', 'booking_id']); // Extract filter parameters
        $pets = $this->petService->all($data);
        $select = false;
        return view('web.auth.pets.index', compact('pets', 'select'));
    }

    public function show($id)
    {
        $pet = $this->petService->find($id, true, ['breed', 'bookings']);
        // dd($pet);
        return view('web.auth.pets.show', compact('pet'));
    }

    public function create()
    {
        $animals = $this->animalService->all([],false,$withes = ['breeds']);
        // dd($animals);   
        return view('web.auth.pets.create', compact('animals'));
    }

    public function store(CreatePetRequest $request)
    {
        $data = $request->validated();
        $this->petService->store($data);
        return redirect()->route('pet.index');
    }

    public function edit($id)
    {
        $pet = $this->petService->find($id, true, ['breed']);
        $animal = $this->animalService->find($pet->breed->animal_id,false,['breeds']);
        // dd($animal);
        return view('web.auth.pets.edit', compact('pet','animal'));
    }

    public function update(UpdatePetRequest $request, $id)
    {
        $data = $request->validated();
        $this->petService->update($id, $data);
        return redirect()->route('pet.index');
    }

    public function destroy($id)
    {
        $this->petService->destroy($id);
        return redirect()->route('pet.index');
    }

}

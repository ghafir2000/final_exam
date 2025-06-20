<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;
use App\Services\BreedService;
use App\Services\AnimalService;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateAnimalRequest;
use App\Http\Requests\UpdateAnimalRequest;

class AnimalController extends Controller
{
    protected $animalService;
    protected $breedService;
    public function __construct(AnimalService $animalService, BreedService $breedService)
    {
        $this->animalService = $animalService;
        $this->breedService = $breedService;
    }

    public function index(){
        $animals = $this->animalService->all([],true,['breeds']);
        return view('web.auth.animals.index', compact('animals'));
    }


    public function show($id)
    {
        $animal = $this->animalService->find($id,true,['breeds']);
        return view('web.auth.animals.show', compact('animal'));
    }

    public function edit($id)
    {
        $animal = $this->animalService->find($id);
        return view('web.auth.animals.edit', compact('animal'));
    }

    public function update(UpdateAnimalRequest $request)
    {
        $data = $request->validated();
        $this->animalService->update($data);
        return redirect()->route('animal.index');
    }

    public function create(){
        return view('web.auth.animals.create');
    }

    public function store(CreateAnimalRequest $request){
        $data = $request->validated();
        $breeds = $data['breeds'];
        unset($data['breeds']);
        $animal_id = $this->animalService->store($data)->id;
        $this->breedService->storeMany($breeds,$animal_id);
        return redirect()->route('animal.index');
    }

    
    public function destroy($id){
        $this->animalService->destroy($id);
        return redirect()->route('animal.index');
    }

}


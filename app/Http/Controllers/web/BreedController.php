<?php

namespace App\Http\Controllers\web;

use App\Services\BreedService;
use App\Services\AnimalService;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateBreedRequest;
use App\Http\Requests\UpdateBreedRequest;

class BreedController extends Controller
{
    protected $breedService,$animalService;
    public function __construct(BreedService $breedService, AnimalService $animalService)
    {
        $this->breedService = $breedService;
        $this->animalService = $animalService;
    }

    public function show($id)
    {
        $breed = $this->breedService->find($id, true, ['animal']);
        return view('web.auth.breeds.show', compact('breed'));
    }

    public function create($animal_id)
    {
        $animal = $this->animalService->find($animal_id);
        return view('web.auth.breeds.create', compact('animal'));
    }

    public function store(CreateBreedRequest $request)
    {
        $data = $request->validated();
        $this->breedService->store($data);
        return redirect()->route('breed.edit', ['animal_id' => $data['animal_id']]);
    }

    public function destroy($id)
    {
        $this->breedService->destroy($id);
        return redirect()->back();
    }

    public function edit($animal_id) //THIS ID IS THE ANIMAL ID
    {
        $breeds = $this->animalService->find($animal_id,$withes = ['breeds'])->breeds;
        return view('web.auth.breeds.edit', compact('breeds'));
    }

    public function update(UpdateBreedRequest $request, $id)
    {
        $data = $request->validated();
        $this->breedService->update($data,$id);
        return redirect()->back();

    }
}

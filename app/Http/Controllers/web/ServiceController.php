<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;
use GuzzleHttp\Promise\Create;
use App\Services\AnimalService;
use App\Services\ServiceService;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateServiceRequest;
use App\Http\Requests\UpdateServiceRequest;

class ServiceController extends Controller
{
    protected $serviceService, $animalService;

    public function __construct(ServiceService $serviceService, AnimalService $animalService)
    {
        $this->serviceService = $serviceService;
        $this->animalService = $animalService;
    }


    public function index(Request $request)
    {
        // dd($request->all());
        $data = $request->only(['search', 'breed_id','animal_id','price','price_range', 'servicable_type' , 'servicable_id']); // Extract filter parameters
        $services = $this->serviceService->all($data, true, ['servicable', 'breeds' , 'breeds.animal' ]);
        // dd($services);
        $select = false;
        return view('web.auth.services.index', compact('services', 'select'));
    }

    public function create()
    {
        $animals = $this->animalService->all([],false, ['breeds']);
        return view('web.auth.services.create', compact('animals'));
    }
    
    public function store(CreateServiceRequest $request)
    {
        // dd($request->all());
        $data = $request->all();
        $this->serviceService->store($data);
        return redirect()->route('service.index', ['servicable_id' => auth()->user()->userable_id]);
    }


    public function show($id)
    {
        $service = $this->serviceService->find($id);
        return view('web.auth.services.show', compact('service'));
    }

    public function edit($id)
    {
        $service = $this->serviceService->find($id);
        return view('web.auth.services.edit', compact('service'));
    }

    public function update(UpdateServiceRequest $request, $id)
    {
        $data = $request->validated();
        $this->serviceService->update($id, $data);
        return redirect()->route('service.index');
    }

    public function destroy($id)
    {
        $this->serviceService->destroy($id);
        return redirect()->route('service.index');
    }
}


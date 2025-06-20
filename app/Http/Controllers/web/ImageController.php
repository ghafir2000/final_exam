<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;
use App\Services\ImageService;
use App\Http\Requests\ImageRequest;
use App\Http\Controllers\Controller;

class ImageController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function addImage(ImageRequest $request)
    {
        $data = $request->validated();
        // dd($data);
        
        $modelId = $data['model_id'];
        $this->imageService->store(
            (new $data['model'])->find($modelId),
            $data['image'],
            $data['collection']
        );
        
        return redirect()->back();
    }
    
    public function updateImage(ImageRequest $request)
    {
        $data = $request->validated();
        // dd($data);

        $modelId = $data['model_id'];
        $this->imageService->update(
            (new $data['model'])->find($modelId),
            $data['image'],
            $data['collection']);

        return redirect()->back();

    }
}


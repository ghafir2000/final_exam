<?php

namespace App\Http\Controllers\web;

use App\Models\Wish;
use Illuminate\Http\Request;
use GuzzleHttp\Promise\Create;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Cache\Store;
use App\Http\Requests\CreateWishRequest;

class WishController extends Controller
{


    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateWishRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = auth()->user()->id;


        Wish::create($data);
        return redirect()->back();
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(CreateWishRequest $request)
    {
        $data = $request->validated();
        // dd($data);

        $wish = Wish::where('user_id', auth()->user()->id)
            ->where('wishable_type', $data['wishable_type'])
            ->where('wishable_id', $data['wishable_id'])
            ->firstOrFail();
        if ($wish->deleted_at !== null) {
            $wish->deleted_at = null;
        } else {
            $wish->deleted_at = now();
        }

        $wish->save();
        return redirect()->back();
    }


}

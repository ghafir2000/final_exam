<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class myAssetMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $id = $request['model_id'];
        $user_id = Auth::user()->id;
        $user = User::find($user_id);
        $modelClass = $request['model'];

        if ($modelClass === 'App\Models\User') {
            $model = User::find($id);
        } else {
            $model = (new $modelClass)->find($id);
        }

        // dd($model->customer_id, $user->userable_id);

        switch (true) {
            case $user->userable_type == "App\Models\Admin": //admin always allow 
                return $next($request);

            case $model && $model->customer_id == $user->userable_id: //for customer to add image of pet 
                // dd('hi');
                return $next($request);

            case $model && ($model->id == auth()->id()):  //user add thier own image
                return $next($request);

            case $model && $model->servicable_id == $user->userable_id && $model->servicable_type == get_class($user->userable): //image to service
                return $next($request);
            
            case $model && $model->productable_id == $user->userable_id && $model->productable_type == get_class($user->userable): //image to product
                return $next($request);
            
            case $model && $model->user_id == $user->id: //image to social stuff 
                return $next($request);



            default:
                abort(403);
        }
    }
}

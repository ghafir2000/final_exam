<?php

use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ServiceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


// --- ADD THIS CANARY ROUTE ---
Route::get('/test', function () {
    return response()->json(['message' => 'API test successful!']);
});

Route::post('/login',[UserController::class , 'login']);

Route::middleware('auth')->group( function () {
    Route::get('/notifications/unread-count-and-latest',[NotificationController::class , 'index']);
    Route::post('/logout',[UserController::class , 'logout']);
    
    Route::resource('userAPI',UserController::class);
    /** 
     * GET|HEAD        api/user .............................................................................................. user.index › API\UserController@index  
     * POST            api/user .............................................................................................. user.store › API\UserController@store  
     * GET|HEAD        api/user/create ..................................................................................... user.create › API\UserController@create  
     * GET|HEAD        api/user/{user} ......................................................................................... user.show › API\UserController@show  
     * PUT|PATCH       api/user/{user} ..................................................................................... user.update › API\UserController@update  
     * DELETE          api/user/{user} ................................................................................... user.destroy › API\UserController@destroy  
     * GET|HEAD        api/user/{user}/edit .................................................................................... user.edit › API\UserController@edit  
    **/
});

Route::get('/services/getTimes', [BookingController::class, 'getTimes'])->name('internal.services.getTimes');

Route::prefix('internal/chat-tools')->middleware(['internal.key'])->group(function () {
    Route::get('/services', [ServiceController::class, 'getServicesForAI'])->name('internal.ai.getServices');
    Route::post('/service-owner', [UserController::class, 'getServiceOwnerForAI'])->name('internal.ai.getServiceOwner');
    
});


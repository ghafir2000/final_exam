<?php

namespace App\Http\Controllers\web;

use App\Models\Product;
use App\Services\UserService;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Notifications\ProductAddedToCartNotification;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;   
    }

    public function index()
    {
        if (auth()->check()) {
            return redirect()->route('profile');
        }

        return view('web.auth.login');
    }
    
    public function login(LoginUserRequest $request)
    {

        $data = $request->validated();
        $is_login = $this->userService->login($data);

        if($is_login)
            return redirect()->route('blog.index');

        return redirect()->route('login')->with(['error' => 'Invalid login credentials']);  
    }
    
    public function register()
    {
        return view('web.auth.register');
    }


    public function store(CreateUserRequest $request)
    {
        $data = $request->validated();
        // dd($data);
        $this->userService->store($data);
        return redirect()->route('login')->with(['success' => 'User created successfully, please login']);
    }

    public function show($id)
    {
        $user = $this->userService->find($id, true, ['userable']);
        return view('web.auth.user.show', compact('user'));
    }

    public function profile()
    {
        $id = auth()->id();

        $user = $this->userService->find($id, true, ['userable']);


        return view('web.auth.user.profile', compact('user'));
    }

    public function logout()
    {
        Auth()->logout();
        return redirect()->route('login');
    }   

    public function edit()
    {
        $id = Auth()->id();
        $user = $this->userService->find($id, true, ['userable']);
        return view('web.auth.user.edit_profile', compact('user'));
    }

    public function update(UpdateUserRequest $request){
        $data = $request->validated();
        $this->userService->update($data, Auth()->id());
        return redirect()->route('profile');
    }

    public function destroy($id)
    {
        $this->userService->destroy($id);
        
        return redirect()->route('user.index');
    }

}
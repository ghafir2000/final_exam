<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;
use App\Services\UserService;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\IndexAdminRequest;
use App\Http\Requests\CreateAdminRequest;

class AdminController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;   
    }


    public function index(Request $request)
    {
        // dd($request->all());
        $data = $request->only(['search', 'email', 'role']); // Extract filter parameters
        $users = $this->userService->all($data); // Pass filters to the service
        return view('web.auth.admin.index', compact('users'));
    }

    public function store(CreateAdminRequest $request)
    {
        $data = $request->validated();
        // dd($data);
        $role = Role::where('name', $data['role'])->first();
        $data = array_merge($data, ['userable_type' => 'App\Models\Admin']);
        unset($data['role']);
        $user = $this->userService->store($data);
        $user->assignRole($role);
        
        return redirect()->route('user.show', ['id' => $user->id]);

    }

    public function create()
    {
        return view('web.auth.admin.create');
    }
}

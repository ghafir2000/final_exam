<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Controllers\Controller;

class VeterinarianController extends Controller
{
    protected $userService;
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $users = $this->userService->all(data: ['role' => 'App\Models\Veterinarian']);
        return view('web.auth.user.index', compact('users'));
    }
}

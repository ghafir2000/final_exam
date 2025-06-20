@extends('web.layout') 

<title>@lang('Dr.Pets - Users')</title>


@section('content') {{-- Define the content section --}}
<div class="container mt-4">
    <div class="card mx-auto" style="width: 80%;">
        <div class="card-header text-center bg-warning">
            <h2>@lang('Admin User Management')</h2>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.index') }}" class="mb-4">
                @csrf
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" id= "search" class="form-control" name="search" placeholder="@lang('Search by name')" value="{{ request()->query('search') }}">
                    </div>
                    <div class="col-md-3">
                        <input type="email" id= "email" class="form-control" name="email" placeholder="@lang('Search by email')" value="{{ request()->query('email') }}">
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id= "role" name="role" aria-label="@lang('Filter by type')">
                            <option value="">@lang('All')</option>
                            @foreach (\App\Models\User::distinct()->pluck('userable_type') as $type)
                                <option value="{{ $type }}" {{ request()->query('role') == $type ? 'selected' : '' }}>
                                    {{ __(str_replace('App\\Models\\', '', $type)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-center">
                        <button type="submit" class="btn btn-success w-100">@lang('Filter Users')</button>
                    </div>
                </div>
            </form>
        </div>
            <table class="table table-bordered " style="background-color: #2c2c2c;">
                <thead class="table-dark ">
                    <tr>
                        <th>@lang('Name')</th>
                        <th>@lang('Email')</th>
                        <th>@lang('Type')</th>
                        <th>@lang('Actions')</th>
                    </tr>
                </thead>
                <tbody >
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ __(str_replace('App\\Models\\', '', $user->userable_type)) }}</td>
                            <td>
                                <a href="{{ route('user.show', $user->id) }}" class="btn btn-warning btn-sm">@lang('Show')</a>
                                <form action="{{ route('user.destroy', $user->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">@lang('Delete')</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

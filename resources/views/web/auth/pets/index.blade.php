@extends('web.layout') 

<title>@lang('Dr.Pets - Your Pets')</title>


@section('content') {{-- Define the content section --}}
<style>
    .btn-custom {
        max-height: 39px;
        margin-top: 30px;
        margin-left: 10px;
        
    }
</style>

<div class="container mt-4">
    <div class="card mx-auto">
        <div class="card-header text-center bg-warning">
            <h2>@lang('My Pets')</h2>
        </div>
        <div class="card-body">
            {{-- Check if pets exist --}}
            @if($pets->count() > 0)
                <table class="table table-bordered text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>@lang('Image')</th>
                            <th>@lang('Name')</th>
                            <th>@lang('Animal')</th>
                            <th>@lang('Breed')</th>
                            <th>@lang('Actions')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pets as $pet)
                        <tr>
                            <td>
                                <img class="rounded-circle" width="100px"
                                    src="{{ $pet->getFirstMediaUrl('pet_picture') ?: asset('images/upload_default.jpg') }}"
                                    alt="{{ $pet->name }}'s profile picture">
                            </td>
                            <td>{{ $pet->name }}</td>
                            <td>{{ $pet->breed->animal->name }}</td>
                            <td>{{ $pet->breed->name }}</td>
                            <td>
                                <div class="d-flex gap-2 justify-content-center">
                                    @if(!$select)
                                        <a href="{{ route('pet.show', $pet->id) }}" class="btn btn-info btn-custom">@lang('View')</a>
                                        <a href="{{ route('pet.edit', $pet->id) }}" class="btn btn-warning btn-custom">@lang('Edit')</a>
                                    @else
                                        <form action="{{ route('booking.create') }}" method="GET" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="pet_id" value="{{ $pet->id }}">
                                            <button type="submit" class="btn btn-primary btn-custom">@lang('Select')</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-center">@lang('You don\'t have any pets yet.') <a href="{{ route('pet.create') }}" class="btn btn-success">@lang('Add Pet')</a></p>
            @endif
        </div>
    </div>
</div>


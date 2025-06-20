<!DOCTYPE html>
<html lang="en">
@extends('web.layout') 

<title>@lang('Dr.Pets - create a breed')</title>


@section('content') {{-- Define the content section --}}
<body>
    <div id="navbar"></div>
<body>
    <div class="container">
        <div class="card mx-auto" style="width: 50%;">
            <div class="card-header text-center" style="background-color: #F7DC6F;">
                <h2>@lang('Create a Breed for {{$animal->name}}')</h2>
            </div>
            <div class="card-body">
                <form action="{{ route('breed.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">@lang('Name')</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">@lang($message)</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">@lang('Description')</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">@lang($message)</div>
                        @enderror
                    </div>
                    <input type="hidden" name="animal_id" value="{{ $animal->id }}">

                    
                    <button type="submit" class="btn btn-warning">@lang('Create Breed')</button>
                </form>

            </div>
        </div>
                
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js"
        integrity="sha512-6sSYJqDreZRZGkJ3b+YfdhB3MzmuP9R7X1QZ6g5aIXhRvR1Y/N/P47jmnkENm7YL3oqsmI6AK+V6AD99uWDnIw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</body>

</html>


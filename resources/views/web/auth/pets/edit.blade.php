@extends('web.layout') 

<title>@lang('Dr.Pets - edit') {{ $pet->name }}</title>


@section('content') {{-- Define the content section --}}
<body>
    <div class="container">
        <div class="card mx-auto" style="width: 50%;">
            <div class="card-header text-center bg-warning">
                <h2>@lang('Dr.Pets - edit') {{ $pet->name }}</h2>
            </div>
            <div class="card-body">
                {{-- Display general success or error messages --}}
                @if (session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="row">
                    {{-- Image Upload/Update Form --}}
                    <div class="col-4 text-center">
                        <form action="{{ $pet->hasMedia('pet_picture') ? route('image.update') : route('image.add') }}"
                            method="POST"
                            enctype="multipart/form-data"
                            id="petPictureForm">
                            @csrf

                            @if ($pet->hasMedia('pet_picture'))
                                @method('PUT')
                            @endif

                            <input type="file" name="image" id="imageUpload" class="d-none" onchange="this.form.submit()" />

                            <input type="hidden" name="model" value="{{ $pet::class }}">
                            <input type="hidden" name="model_id" value="{{ $pet->id }}">
                            <input type="hidden" name="collection" value="pet_picture">

                            <label for="imageUpload" style="cursor: pointer;">
                                <img class="rounded-circle mt-5" width="150px" 
                                    src="{{ $pet->getFirstMediaUrl('pet_picture') ?: asset('images/upload_default.jpg') }}"
                                    alt="@lang('pet picture of') {{ $pet->name }}">
                            </label>

                            {{-- Display validation errors for the image field --}}
                            @error('image')
                                <div class="text-danger mt-1">@lang('The image field is required.')</div>
                            @enderror
                             {{-- Display validation errors for hidden fields if any --}}
                             @error('model')
                                <div class="text-danger mt-1">@lang('The model field is required.')</div>
                            @enderror
                            @error('model_id')
                                <div class="text-danger mt-1">@lang('The model_id field is required.')</div>
                            @enderror
                            @error('collection')
                                <div class="text-danger mt-1">@lang('The collection field is required.')</div>
                            @enderror
                        </form>
                    </div>

                    {{-- Pet Details Form --}}
                    <div class="col-8">
                        <h4>@lang('Change Pet Details:')</h4>
                        <form action="{{ route('pet.update', ['id' => $pet->id]) }}" method="POST">
                            @csrf
                            @method('PUT')

                            {{-- Name field --}}
                            <div class="mb-3">
                                <label for="name" class="form-label">@lang('Pet Name')</label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $pet->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">@lang('The name field is required.')</div>
                                @enderror
                            </div>

                            {{-- Breed field (Using breeds from the $animal) --}}
                            {{-- Ensure $animal and $animal->breeds are available from your controller --}}
                            @if (isset($animal) && $animal->relationLoaded('breeds'))
                            <div class="mb-3">
                                <label for="breed_id" class="form-label">@lang('Breed')</label>
                                <select name="breed_id" id="breed_id" class="form-control @error('breed_id') is-invalid @enderror" required>
                                    <option value="">@lang('Select Breed')</option>
                                    @foreach ($animal->breeds as $breed)
                                        <option value="{{ $breed->id }}" {{ old('breed_id', $pet->breed_id) == $breed->id ? 'selected' : '' }}>{{ $breed->name }}</option>
                                    @endforeach
                                </select>
                                @error('breed_id')
                                    <div class="invalid-feedback">@lang('The breed_id field is required.')</div>
                                @enderror
                            </div>
                            @else
                            {{-- Fallback if animal or its breeds are not loaded --}}
                            <div class="mb-3">
                                <label for="breed_id" class="form-label">@lang('Breed (Not available)')</label>
                                <input type="hidden" name="breed_id" value="{{ $pet->breed_id }}">
                                <input type="text" class="form-control" value="{{ $pet->breed->name ?? __('N/A') }}" disabled>
                                @error('breed_id')
                                    <div class="text-danger mt-1">@lang('The breed_id field is required.')</div>
                                @enderror
                            </div>
                            @endif

                            {{-- Removed the customer_id field as it's not in the UpdatePetRequest --}}

                            <div class="row">
                                <div class="col-4">
                                    <label for="age" class="form-label">@lang('Age')</label>
                                    <input type="number" name="age" id="age" class="form-control @error('age') is-invalid @enderror" value="{{ old('age', $pet->age) }}" required>
                                    @error('age')
                                        <div class="invalid-feedback">@lang('The age field is required.')</div>
                                    @enderror
                                </div>
                                <div class="col-4">
                                    <label for="fertility" class="form-label">@lang('Fertility')</label>
                                    <select name="fertility" id="fertility" class="form-control" required>
                                        <option value="1" {{ old('fertility', $pet->fertility) ? 'selected' : '' }}>@lang('Yes')</option>
                                        <option value="0" {{ !old('fertility', $pet->fertility) ? 'selected' : '' }}>@lang('No')</option>
                                    </select>
                                     @error('fertility')
                                        <div class="invalid-feedback">@lang('The fertility field is required.')</div>
                                    @enderror
                                </div>
                                <div class="col-4">
                                    <label for="gender" class="form-label">@lang('Gender')</label>
                                    <select name="gender" id="gender" class="form-control" required>
                                        <option value="1" {{ old('gender', $pet->gender) ? 'selected' : '' }}>@lang('Male')</option>
                                        <option value="0" {{ !old('gender', $pet->gender) ? 'selected' : '' }}>@lang('Female')</option>
                                    </select>
                                     @error('gender')
                                        <div class="invalid-feedback">@lang('The gender field is required.')</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="d-flex align-items-center mt-3">
                                <button type="submit" class="btn btn-primary">@lang('Update Pet')</button>
                            </form>

                            <form action="{{ route('pet.destroy', ['id' => $pet->id]) }}" method="POST" class="mt-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger mt-2 ml-3" onclick="return confirm('@lang('Are you sure you want to delete this pet?')')">@lang('Delete Pet')</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</body>
</html>

@extends('web.layout') 

<title>@lang('Dr.Pets - edit ') {{ $animal->name }}</title>

@section('content') {{-- Define the content section --}}
<body>
    <div class="container mt-4">
        <div class="card mx-auto" style="width: 80%;">
            <div class="card-header text-center" style="background-color: #F7DC6F;">
                <h2>@lang('Edit Animal Profile')</h2>
            </div>
            <div class="card-body">

                {{-- Form for Updating the Image --}}
                <form action="{{ $animal->hasMedia('animal_picture') ? route('image.update') : route('image.add') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if ($animal->hasMedia('animal_picture'))
                        @method('PUT')
                    @endif

                    <div class="mb-3 text-center">
                        <label for="animal_picture" class="form-label">@lang('Current Animal Image (click to change):')</label>
                        <div class="text-center mb-4">
                            {{-- Hidden file input --}}
                            <input type="file" name="image" id="imageUpload" class="d-none" onchange="this.form.submit()" />
                            <input type="hidden" name="model" value="{{ $animal::class }}">
                            <input type="hidden" name="model_id" value="{{ $animal->id }}">
                            <input type="hidden" name="collection" value="animal_picture">
                            <label for="imageUpload" style="cursor: pointer;">
                                <img class="rounded-circle" width="150px" src="{{ $animal->getFirstMediaUrl('animal_picture') ?: asset('images/upload_default.jpg') }}" alt="{{ $animal->name }}'s profile picture">
                            </label>
                        </div>
                    </div>
                </form>

                <hr> {{-- Separator for clarity --}}

                {{-- Form for Updating Animal Information --}}
                <form action="{{ route('animal.update', $animal->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">@lang('Animal Name')</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $animal->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">@lang('Description')</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5">{{ old('description', $animal->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Number of Breeds with Edit Link --}}
                    <input type="hidden" class="-control" id="id" name="id" value="{{ $animal->id }}">
                    <div class="mb-3">
                        <label for="number_of_breeds" class="form-label">@lang('Number of Breeds')</label>
                        <input type="number" class="form-control" id="number_of_breeds" name="number_of_breeds" value="{{ old('number_of_breeds', $animal->number_of_breeds) }}" readonly>
                        <small>
                            <a href="{{ route('breed.edit' , $animal->id) }}" class="text-decoration-underline">@lang('Edit Breeds')</a>
                        </small>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('animal.show', $animal->id) }}" class="btn btn-secondary">@lang('Cancel')</a>
                        <button type="submit" class="btn btn-warning">@lang('Update Animal')</button>
                    </div>

                    {{-- Display validation errors --}}
                    @if ($errors->any())
                        <div class="alert alert-danger mt-3">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js" crossorigin="anonymous"></script>
</body>
</html>


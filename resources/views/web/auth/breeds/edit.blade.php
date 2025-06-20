<!DOCTYPE html>
<html lang="en">
@extends('web.layout') 

<title>@lang('Dr.Pets - edit') {{ $breeds[0]->animal->name }}</title>


@section('content') {{-- Define the content section --}}

<body>
    <div class="container mt-4">
        <div class="card mx-auto" style="width: 80%;">
            <div class="card-header text-center" style="background-color: #F7DC6F;">
                <h2>@lang('Edit Breeds for') {{ $breeds[0]->animal->name }}</h2>
            </div>
            <div class="card-body">

                {{-- Breed Editing Table --}}
                <table class="table table-bordered text-center align-middle">
                    <thead class="table-warning">
                        <tr>
                            <th>@lang('Image')</th>
                            <th>@lang('Breed')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($breeds as $breed)
                        <tr>
                            
                        {{-- Breed Image Upload --}}
                            <td>
                                <form action="{{ $breed->hasMedia('breed_picture') ? route('image.update') : route('image.add') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @if ($breed->hasMedia('breed_picture'))
                                        @method('PUT')
                                    @endif

                                    <input type="hidden" name="model" value="{{ $breed::class }}">
                                    <input type="hidden" name="model_id" value="{{ $breed->id }}">
                                    <input type="hidden" name="collection" value="breed_picture">

                                    <input type="file" name="image" id="imageUpload{{ $breed->id }}" class="d-none"
                                        onchange="this.form.submit()"/>

                                    <label for="imageUpload{{ $breed->id }}" style="cursor: pointer;">
                                        <img class="rounded-circle" width="100px"
                                            src="{{ $breed->getFirstMediaUrl('breed_picture') ?: asset('images/upload_default.jpg') }}"
                                            alt="{{ $breed->name }}'s profile picture">
                                    </label>
                                </form>
                            {{-- Breed Information --}}
                            <td>
                                <div class="d-flex flex-column">
                                    <form action="{{ route('breed.update', $breed->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $breed->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                        <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="3">{{ old('description', $breed->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                        <div class="d-flex justify-content-between mt-2">
                                            <button type="submit" class="btn btn-warning btn-sm">@lang('Update')</button>
                                            <form action="{{ route('breed.destroy', ['id' => $breed->id]) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">@lang('Delete breed')</button>
                                            </form>
                                        </div>
                                    </form>
                                </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Navigation Buttons --}}
                <div class="text-end mt-4">
                    <a href="{{ route('animal.show', $breeds[0]->animal->id) }}" class="btn btn-secondary">@lang('back to animal')</a>
                    <a href="{{ route('breed.create', $breeds[0]->animal->id) }}" class="btn btn-success">@lang('add a breed')</a>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js" crossorigin="anonymous"></script>
</body>
</html>

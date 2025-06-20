@extends('web.layout') 

<title>@lang('Dr.Pets -') {{$breed->name}}</title>


@section('content') {{-- Define the content section --}}
    <div class="container">
        <div class="card mx-auto" style="width: 50%;">
            <div class="card-header text-center" style="background-color: #F7DC6F;">
                <h2> @lang('Breed for') {{$breed->name}}</h2>
            </div>

            <div class="card-body">
                <div class="d-flex">
                    {{-- Form for Updating the Image --}}
                    <form action="{{ $breed->hasMedia('breed_picture') ? route('image.update') : route('image.add') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if ($breed->hasMedia('breed_picture'))
                            @method('PUT')
                        @endif

                        <div class="me-3">
                            <label for="breed_picture" class="form-label">@lang('Current Animal Image (click to change):')</label>
                            {{-- Hidden file input --}}
                            <input
                                type="file"
                                name="image"
                                id="imageUpload" {{-- Keep the ID --}}
                                class="d-none" {{-- Keep hidden --}}
                                onchange="this.form.submit()" {{-- Auto-submit this form --}}
                            />
                            {{-- Add hidden fields for model, model_id, and collection --}}
                            <input type="hidden" name="model" value="{{ $breed::class }}"> {{-- Use the breed class as the model name --}}
                            <input type="hidden" name="model_id" value="{{ $breed->id }}">
                            {{-- We pass breed ID in the route, but if your controller expects model_id in request body, add this: --}}
                            <input type="hidden" name="collection" value="breed_picture"> {{-- Specify the collection name --}}

                            <label for="imageUpload" style="cursor: pointer;">
                                <img
                                    width="150px"
                                    src="{{ $breed->getFirstMediaUrl('breed_picture') ?: asset('images/upload_default.jpg') }}"
                                    alt="{{ $breed->name }}'s @lang('profile picture')"
                                >
                            </label>
                        </div>
                    </form>
                    <div class="col-8">
                        <h4>{{ $breed->name }}</h4>
                        <p>{{ $breed->description }}</p>
                        @can('edit media')
                        <div class="d-flex align-items-center">
                            <a href="{{ route('breed.edit', ['animal_id' => $breed->animal->id]) }}" class="btn btn-warning">@lang('Edit breed')</a>
                            
                            <form action="{{ route('breed.destroy', ['id' => $breed->id]) }}" method="POST" class="ms-2 ml-2">
                                @csrf
                                @method('DELETE')
                                <div style="margin-top: 15px;">
                                    <button type="submit" class="btn btn-danger ">@lang('Delete breed')</button>
                                </div>
                            </form>
                        </div>
                        @endcan
                    </div>
                </div>
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


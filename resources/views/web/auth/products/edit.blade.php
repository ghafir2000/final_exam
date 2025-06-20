@extends('web.layout') 

<title>@lang('Dr.Pets - Edit Product :name', ['name' => $product->name])</title>

@section('content')
<body>
    <div class="container mt-4">
        <div class="card mx-auto" style="width: 80%;">
            <div class="card-header text-center" style="background-color: #F7DC6F;">
                <h2>@lang('Edit Product Profile')</h2>
            </div>
            <div class="card-body">

                {{-- Form for Updating the Image --}}
                <form action="{{ $product->hasMedia('product_image') ? route('image.update') : route('image.add') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if ($product->hasMedia('product_image'))
                        @method('PUT')
                    @endif

                    <div class="mb-3 text-center">
                        <label for="product_image" class="form-label">@lang('Current Product Image (click to change):')</label>
                        <div class="text-center mb-4">
                         {{-- Hidden file input --}}
                        <input
                            type="file"
                            name="image"
                            id="imageUpload"
                            class="d-none"
                            onchange="this.form.submit()"
                        />
                        {{-- Add hidden fields for model, model_id, and collection --}}
                        <input type="hidden" name="model" value="{{ $product::class }}">
                        <input type="hidden" name="model_id" value="{{ $product->id }}">
                        <input type="hidden" name="collection" value="product_image">

                        <label for="imageUpload" style="cursor: pointer;">
                            <img
                                class="rounded-circle"
                                width="150px"
                                src="{{ $product->getFirstMediaUrl('product_image') ?: asset('images/upload_default.jpg') }}"
                                alt="@lang(':name\'s profile picture', ['name' => $product->name])"
                            >
                        </label>
                        </div>
                    </form>

                <hr>

                {{-- Form for Updating Product Information --}}
                <form action="{{ route('product.update', $product->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">@lang('Product Name')</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="CRF" class="form-label">@lang('CRF')</label>
                        <input type="text" class="form-control @error('CRF') is-invalid @enderror" id="CRF" name="CRF" value="{{ old('CRF', $product->CRF) }}" required>
                        @error('CRF')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">@lang('Description')</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">@lang('Price')</label>
                        <input type="number" class="form-control" id="price" name="price" value="{{ old('price', $product->price) }}" required>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('product.show', $product->id) }}" class="btn btn-secondary">@lang('Cancel')</a>
                        <button type="submit" class="btn btn-warning">@lang('Update Product')</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js" crossorigin="anonymous"></script>
</body>
</html>


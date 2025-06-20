@extends('web.layout')

@section('title', __('Edit Post: :title', ['title' => $post->title]))

@section('content')

<div class="container mt-4">
    <div class="card mx-auto">
        <div class="card-header text-center bg-warning">
            <h2>{{ __('Edit Post: :title', ['title' => $post->title]) }}</h2>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

             <form action="{{ $post->hasMedia('post_picture') ? route('image.update') : route('image.add') }}"
                    method="POST" enctype="multipart/form-data" id="postPictureForm"
                    style="display: flex; flex-direction: column; justify-content: center; align-items: center; margin-bottom: 20px;">
                @csrf

                @if ($post->hasMedia('post_picture'))
                    @method('PUT')
                @endif

                <input type="file" name="image" id="imageUpload" class="d-none" onchange="this.form.submit()" />
                <input type="hidden" name="model" value="{{ get_class($post) }}"> {{-- Use get_class for safety --}}
                <input type="hidden" name="model_id" value="{{ $post->id }}">
                <input type="hidden" name="collection" value="post_picture">

                <label for="imageUpload" style="cursor: pointer;">
                    <img style="width: 100%; height: 300px; object-fit: cover;"
                            src="{{ $post->getFirstMediaUrl('post_picture') ?: asset('images/upload_default.jpg') }}"
                            alt="{{ __(':title\'s post picture', ['title' => $post->title]) }}">
                </label>

                @error('image') <div class="text-danger mt-1 text-center">{{ __('The image field is required.') }}</div> @enderror
                @error('model') <div class="text-danger mt-1 text-center">{{ __('The model field is required.') }}</div> @enderror
                @error('model_id') <div class="text-danger mt-1 text-center">{{ __('The model id field is required.') }}</div> @enderror
                @error('collection') <div class="text-danger mt-1 text-center">{{ __('The collection field is required.') }}</div> @enderror
            </form>


            <form action="{{ route('post.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <input type="hidden" name="id" value="{{$post->id}}">

                <div class="mb-3">
                    <label for="title" class="form-label">{{ __('Post Title') }}</label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $post->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ __('The title field is required.') }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">{{ __('Post Content') }}</label>
                    <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="8" required>{{ old('content', $post->content) }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ __('The content field is required.') }}</div>
                    @enderror
                </div>


                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remove_post_image" name="remove_post_image" value="1">
                    <label class="form-check-label" for="remove_post_image">{{ __('Remove current post image') }}</label>
                </div>


                <button type="submit" class="btn btn-primary btn-custom-action">{{ __('Update Post') }}</button>
                <a href="{{ route('post.show', $post->id) }}" class="btn btn-secondary btn-custom-action ms-2">{{ __('Cancel') }}</a>
            </form>
        </div>
    </div>
</div>
@endsection

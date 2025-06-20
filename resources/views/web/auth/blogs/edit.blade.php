@extends('web.layout')


<title> @lang('Dr.Pets - edit') {{ $blog->title }} </title>


@section('content')

<div class="container mt-4">
    <div class="card mx-auto">
        <div class="card-header text-center bg-warning">
            <h2>@lang('Edit Blog') {{ $blog->title }}</h2>
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

            <form action="{{ $blog->hasMedia('blog_picture') ? route('image.update') : route('image.add') }}"
                    method="POST" enctype="multipart/form-data" id="blogPictureForm"
                    style="display: flex; flex-direction: column; justify-content: center; align-items: center; margin-bottom: 20px;">
                @csrf

                @if ($blog->hasMedia('blog_picture'))
                    @method('PUT')
                @endif

                <input type="file" name="image" id="imageUpload" class="d-none" onchange="this.form.submit()" />
                <input type="hidden" name="model" value="{{ get_class($blog) }}"> {{-- Use get_class for safety --}}
                <input type="hidden" name="model_id" value="{{ $blog->id }}">
                <input type="hidden" name="collection" value="blog_picture">

                <label for="imageUpload" style="cursor: pointer;">
                    <img style="width: 100%; height: 300px; object-fit: cover;"
                            src="{{ $blog->getFirstMediaUrl('blog_picture') ?: asset('images/upload_default.jpg') }}"
                            alt="{{ __('blog picture of') }} {{ $blog->title }}">
                </label>

                @error('image') <div class="text-danger mt-1 text-center">{{ $message }}</div> @enderror
                @error('model') <div class="text-danger mt-1 text-center">{{ $message }}</div> @enderror
                @error('model_id') <div class="text-danger mt-1 text-center">{{ $message }}</div> @enderror
                @error('collection') <div class="text-danger mt-1 text-center">{{ $message }}</div> @enderror
            </form>

            <form action="{{ route('blog.update', $blog->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <input type="hidden" name="id" value="{{$blog->id}}">


                <div class="mb-3">
                    <label for="title" class="form-label">@lang('Blog Title')</label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $blog->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">@lang('Blog Content')</label>
                    <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="10" required>{{ old('content', $blog->content) }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="duration" class="form-label">@lang('Reading Duration (e.g., "5 min read")')</label>
                    <input type="text" class="form-control @error('duration') is-invalid @enderror" id="duration" name="duration" value="{{ old('duration', $blog->duration) }}">
                    @error('duration')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>


                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remove_blog_image" name="remove_blog_image" value="1">
                    <label class="form-check-label" for="remove_blog_image">@lang('Remove current blog image')</label>
                </div>


                <button type="submit" class="btn btn-primary btn-custom-action">@lang('Update Blog')</button>
                <a href="{{ route('blog.show', $blog->id) }}" class="btn btn-secondary btn-custom-action ms-2">@lang('Cancel')</a>
            </form>
        </div>
    </div>
</div>
@endsection

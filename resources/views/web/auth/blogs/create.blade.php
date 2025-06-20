@extends('web.layout')


<title> @lang('Dr.Pets - Create Blog') </title>


@section('content')

<div class="container mt-4">
    <div class="card mx-auto">
        <div class="card-header text-center bg-warning">
            <h2> @lang('Create Blog') </h2>
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

            <form action="{{ route('blog.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="title" class="form-label"> @lang('Blog Title') </label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label"> @lang('Blog Content') </label>
                    <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="10" required>{{ old('content') }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="duration" class="form-label"> @lang('Reading Duration (e.g., "5 min read")') </label>
                    <input type="text" class="form-control @error('duration') is-invalid @enderror" id="duration" name="duration" value="{{ old('duration') }}">
                    @error('duration')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="blog_picture" class="form-label"> @lang('Blog Image (Optional)') </label>
                    <input type="file" class="form-control @error('blog_picture') is-invalid @enderror" id="blog_picture" name="blog_picture" accept="image/*">
                    @error('blog_picture')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>


                <button type="submit" class="btn btn-primary btn-custom-action"> @lang('Create Blog') </button>
                <a href="{{ route('blog.index') }}" class="btn btn-secondary btn-custom-action ms-2"> @lang('Cancel') </a>
            </form>
        </div>
    </div>
</div>
@endsection


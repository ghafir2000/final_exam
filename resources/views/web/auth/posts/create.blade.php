@extends('web.layout')


<title> @lang('Dr.Pets - Create Post under') {{ $blog->title }} </title>


@section('content')

<div class="container mt-4">
    <div class="card mx-auto">
        <div class="card-header text-center bg-warning">
            <h2>@lang('Create Post under') {{ $blog->title }}</h2>
        </div>
        <div class="card-body">
            <h4>@lang('Blog Info:')</h4>
            <p>{{ $blog->title }}</p>
            <p>{{ Str::words($blog->content, 25) }}</p>

            <hr>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('post.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="blog_id" value="{{ $blog->id }}">

                <div class="mb-3">
                    <label for="title" class="form-label">@lang('Post Title')</label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">@lang('Post Content')</label>
                    <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="10" required>{{ old('content') }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="post_picture" class="form-label">@lang('Post Image (Optional)')</label>
                    <input type="file" class="form-control @error('post_picture') is-invalid @enderror" id="post_picture" name="post_picture" accept="image/*">
                    @error('post_image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>


                <button type="submit" class="btn btn-primary btn-custom-action">@lang('Create Post')</button>
                <a href="{{ route('blog.index') }}" class="btn btn-secondary btn-custom-action ms-2">@lang('Cancel')</a>
            </form>
        </div>
    </div>
</div>
@endsection


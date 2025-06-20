@extends('web.layout')

{{-- Set the title using a section that your layout file expects, e.g., @section('title') --}}
@section('title', __('Dr.Pets - Blogs'))

@section('styles')
@parent {{-- Good practice to include parent styles if any --}}
<style>
    .blog-card-img {
        height: 200px; /* Or use aspect-ratio in Bootstrap 5.1+ for better responsiveness */
        object-fit: cover;
        width: 100%; /* Ensure image fills the card width */
    }
    .blog-summary {
        /*
        You can set a min-height if you want, but h-100 on the card
        and d-flex flex-column on card-body will handle most height differences.
        If summaries vary wildly in length, min-height can help,
        but often limiting the word count is better.
        */
        /* min-height: 70px; */ /* Re-evaluate if needed after grid changes */
    }
    .card-title {
        min-height: 48px; /* Example: To accommodate two lines of title consistently */
                          /* Adjust based on your typical title lengths and font size */
    }
    .btn-custom-action {
        /* Add any specific styles for your custom action button */
    }
    /* Optional: Style for transparent edit button */
    .transparent-edit-btn {
        background-color: transparent;
        border: none;
        color: #007bff; /* Or your preferred icon color */
        padding: 0.375rem 0.75rem; /* Match button padding if needed */
        margin-bottom: 15px;
    }
    .transparent-edit-btn:hover {
        color: #0056b3; /* Darken on hover */
    }
    .blog-show-img { /* Or whatever you call it */
        max-height: 200px; /* Adjust as needed */
        height: 30%;
        width:  30%;
        object-fit:contain;
        border-radius: .25rem;
        display: block; /* for mx-auto to work if not full width */
        /* mx-auto can be added as a class if needed */
    }
</style>
@endsection

@section('content')
<div class="container mt-4">
    <div class="card mx-auto">
        <div class="card-header text-center bg-warning">
            <h2>{{ __('Latest Blogs') }}</h2>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-12">
                    <form method="GET" action="{{ route('blog.index') }}" class="d-flex">
                        <input type="text" name="search" class="form-control me-2" placeholder="{{ __('Search blog titles...') }}" value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary">{{ __('Search') }}</button>
                    </form>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if($blogs->count() > 0)
            @auth
                @if (auth()->user()->userable_type !== "App\Models\Customer")
                    <div class="card border-dotted-transparent mx-auto mt-3" onclick="location.href='{{ route('blog.create') }}'">
                        <div class="card-body text-center">
                            <p class="mb-0">{{ __('New Blog, what\'s on your mind?') }}</p>
                        </div>
                    </div>
                @endif
            @endauth

            <style>
                    .border-dotted-transparent {
                        background-color: rgba(122, 236, 137, 0.56);
                        border: 2px dotted rgba(122, 255, 140, 0.27);
                        transition: background-color 0.3s ease-in, border-color 0.3s ease-in;
                        font-weight: bold;
                    }
                    .border-dotted-transparent:hover {
                        background-color: rgba(99, 194, 194, 0.75);
                        border: 2px dotted rgba(8, 119, 119, 0.75);
                        font-weight: bold;
                    }
            </style>
                @foreach ($blogs as $blog)
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div>
                            @if($blog->getFirstMediaUrl('blog_picture'))
                                <img src="{{ $blog->getFirstMediaUrl('blog_picture') }}"
                                    alt="{{ $blog->title }}"
                                    class="blog-show-img img-fluid mb-3 mx-auto d-block"> {{-- Use your class, img-fluid for responsiveness, mx-auto for centering --}}
                            @endif
                                <div class="card-body">
                                    <h5 class="card-title">{{ $blog->title }}</h5>
                                    <p class="card-text blog-summary">{{ Str::words($blog->content, 20, '...') }}</p>
                                    <small class="text-muted mb-2">
                                        By: {{ $blog->user->name ?? __('Unknown Author') }} <br>
                                        Reading time: {{ $blog->duration ?? __('N/A') }}
                                    </small>

                                    @if($blog->latestPost)
                                        <div class="card mt-2 mb-2"> {{-- Adjusted margin --}}
                                            <div class="card-body p-2">
                                                <h6 class="card-subtitle mb-1 text-muted" style="font-size: 0.8rem;">{{ __('Latest Post on Topic:') }}</h6>
                                                <a href="{{ route('post.show', $blog->latestPost->id) }}" class="card-link">{{ Str::limit($blog->latestPost->title, 25) }}</a>
                                                <p class="card-text blog-summary mt-1" style="font-size: 0.8rem;">{{ Str::words($blog->latestPost->content, 10, '...') }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="mt-auto d-flex justify-content-between align-items-center pt-2"> {{-- mt-auto pushes this div to the bottom, pt-2 for some top padding --}}
                                        <a href="{{ route('blog.show', $blog->id) }}" class="btn btn-info btn-sm btn-custom-action">{{ __('View Blog') }}</a>
                                        <a href="{{ route('post.create', $blog->id)}}" class="btn btn-warninig btn-sm btn-custom-action">{{ __('Add Post on Topic') }}</a>
                                        @auth {{-- Use @auth directive --}}
                                        <div class="d-flex align-items-center">

                                            @if($blog->user_id === auth()->id())
                                                <a href="{{ route('blog.edit', $blog->id) }}" class="transparent-edit-btn me-2" title="{{ __('Edit Blog') }}">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                            @endif

                                            @php
                                                $isWished = $blog->wishable()->where('user_id', auth()->user()->id)->exists();
                                            @endphp         
                                            <form action="{{ $isWished ? route('wish.update') : route('wish.store') }}" method="POST" class="d-inline">
                                                @csrf
                                                @if($isWished) @method('PUT') @endif
                                                <input type="hidden" name="wishable_id" value="{{ $blog->id }}">
                                                <input type="hidden" name="wishable_type" value="{{ get_class($blog) }}">
                                                <button type="submit" class="btn btn-sm ">
                                                    <i class="fas fa-heart{{ $isWished ?  ' text-danger' : '' }}"></i> {{ $blog->wishable()->count() ?? 0 }}
                                                </button>
                                            </form>
                                        </div>
                                        @endauth
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
            @auth    
            <p class="text-center">{{ __('No blogs found.') }}

                    @if (auth()->user()->userable_type !== \App\Models\Customer::class)
                        <a href="{{ route('blog.create') }}" class="btn btn-success ms-2">{{ __('Create New Blog') }}</a>
                    @endif

                </p>
            @endauth
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')

 <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js"
        integrity="sha512-6sSYJqDreZRZGkJ3b+YfdhB3MzmuP9R7X1QZ6g5aIXhRvR1Y/N/P47jmnkENm7YL3oqsmI6AK+V6AD99uWDnIw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

@parent {{-- Good practice to include parent scripts if any --}}
{{-- Add any page-specific scripts here --}}
@endsection


@extends('web.layout')

{{-- This should ideally be in a @section('title') if your layout supports it --}}
<title> @lang('Dr.Pets - reading') {{ $blog->title }} </title>

@section('styles')
<style>
    .blog-show-img {
        max-height: 400px;
        width: 100%;
        object-fit: cover;
        border-radius: .25rem;
    }
    .post-card-img {
        height: 150px; /* Or adjust as needed, e.g., max-height with width: auto */
        object-fit: cover;
        width: 100%; /* Ensure it takes full width of its container */
    }
    /* Optional: if you want the edit icon to have a bit more space or specific styling */
    .transparent-edit-btn i {
        /* Example: font-size: 1.1em; */
    }

    /* Initially hide the form using CSS for better no-JS fallback */
    #publish-post-form {
        display: none;
    }
    .border-dotted-transparent {
        background-color: rgba(122, 236, 137, 0.56);
        border: 2px dotted rgba(122, 236, 137, 0.56);
        transition: background-color 0.3s ease-in, border-color 0.3s ease-in;
        font-weight: bold;
    }
    .border-dotted-transparent:hover {
        background-color: rgba(99, 194, 194, 0.75);
        border: 2px dotted rgba(8, 119, 119, 0.75);
        font-weight: bold;
    }

    .blog-show-img { /* Or whatever you call it */
        max-height: 400px; /* Adjust as needed */
        height: 35%;
        width: 35%;
        object-fit: cover;
        border-radius: .25rem;
        display: block; /* for mx-auto to work if not full width */
        /* mx-auto can be added as a class if needed */
    }
    .post-show-img { /* Or whatever you call it */
        max-height: 200px; /* Adjust as needed */
        height: 20%;
        width: 20%;
        object-fit: cover;
        border-radius: .25rem;
        display: block; /* for mx-auto to work if not full width */
        /* mx-auto can be added as a class if needed */
    }
</style>
@endsection


@section('content')
<div class="container mt-4">
    {{-- ... (your existing blog content code) ... --}}
    <div class="card mx-auto mb-4">
        <div class="card-header text-center bg-warning">
            <h2>{{ __(' ') . $blog->title }}</h2>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
             @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if($blog->getFirstMediaUrl('blog_picture'))
                <img src="{{ $blog->getFirstMediaUrl('blog_picture') }}"
                    alt="{{ __(' ') . $blog->title }}"
                    class="blog-show-img img-fluid mb-3 mx-auto d-block"> {{-- Use your class, img-fluid for responsiveness, mx-auto for centering --}}
            @endif

            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <p class="text-muted mb-0">{{ __(' By: ') . $blog->user->name ?? __(' Unknown Author') }}</p>
                    <p class="text-muted mb-0">{{ __(' Published: ') . $blog->created_at->format('M d, Y') }}</p>
                    <p class="text-muted mb-0">{{ __(' Reading time: ') . $blog->duration ?? __(' N/A') }}</p>
                </div>
                <div class="d-flex align-items-center">

                    @if(auth()->check() && auth()->user()->id === $blog->user_id) {{-- Added auth()->check() for safety --}}
                    <a href="{{ route('blog.edit', $blog->id) }}" class="btn btn-sm text-primary me-2 mb-3" title="{{ __(' Edit Blog') }}"> {{-- Matched styling to post edit --}}
                        <i class="fas fa-pen"></i>
                    </a>
                    @endif

                    @if(auth()->check()) {{-- Wishlist only if logged in --}}
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
                    @endif
                </div>
            </div>
            <hr>
            <div class="blog-content">
                {!! $blog->content !!} {{-- Use {!! !!} if content contains HTML, otherwise {{ }} is fine --}}
            </div>
        </div>
    </div>

    {{-- Section for Posts --}}
    <div class="card mx-auto mb-4">
        <div class="card-header bg-light">
            <h3> {{ __(' Posts in this Blog') }} (Latest 10)</h3>
        </div>
        <div class="card-body">
            <div class="card border-dotted-transparent mx-auto mt-3 mb-4" onclick="location.href='{{ route('post.create',$blog->id) }}'">
                <div class="card-body text-center">
                    <p class="mb-0">{{ __(' New Post, what\'s on your mind?')}}</p>
                </div>
            </div>
            @if($posts->count() > 0)
                <div class="list-group">
                    @foreach($posts as $post)
                        <div class="list-group-item list-group-item-action flex-column align-items-start mb-2 shadow-sm">
                            @if($post->getFirstMediaUrl('post_picture'))
                                <img src="{{ $post->getFirstMediaUrl('post_picture') }}"
                                    alt="{{ __(' ') . $post->title }}"
                                    class="post-show-img img-fluid mb-3 mx-auto d-block"> {{-- Use your class, img-fluid for responsiveness, mx-auto for centering --}}
                            @endif
                            {{-- Post details --}}
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1">{{ $post->title }}</h5>
                                <small>{{ $post->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-1">{{ Str::limit(strip_tags($post->content), 150) }}</p> {{-- strip_tags for safety if content has HTML --}}

                            @if($post->latestComment)
                                <small class="text-muted">
                                    {{ __(' Latest comment:') }} "{{ Str::limit($post->latestComment->body, 30) }}"
                                    {{ __(' by') }} {{ $post->latestComment->user->name ?? __(' User') }}
                                </small>
                            @else
                                <small class="text-muted">{{ __(' No comments yet.')}}</small>
                            @endif

                            {{-- Actions --}}
                            <div class="mt-2 d-flex justify-content-between align-items-center">
                                <a href="{{ route('post.show', $post->id) }}" class="btn btn-primary btn-sm btn-custom-action">{{ __(' View Post')}}</a>
                                <div class="d-flex align-items-center">
                                    @if(auth()->check() && auth()->user()->id === $post->user_id) {{-- Added auth()->check() --}}
                                    <a href="{{ route('post.edit', $post->id) }}" class="btn btn-sm text-success me-2 mb-3" title="{{ __(' Edit Post')}}"> {{-- Matched styling to post edit --}}
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    @endif

                                    @if(auth()->check()) {{-- Wishlist only if logged in --}}
                                        @php
                                            $isWished = $post->wishable()->where('user_id', auth()->user()->id)->exists();
                                        @endphp
                                        <form action="{{ $isWished ? route('wish.update') : route('wish.store') }}" method="POST" class="d-inline">
                                            @csrf
                                            @if($isWished) @method('PUT') @endif
                                            <input type="hidden" name="wishable_id" value="{{ $post->id }}">
                                            <input type="hidden" name="wishable_type" value="{{ get_class($post) }}">
                                            <button type="submit" class="btn btn-sm">
                                                  <i class="fas fa-heart{{ $isWished ?  ' text-danger' : '' }}"></i> {{ $post->wishable()->count() ?? 0 }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                 {{-- Pagination Links --}}
                <div class="d-flex justify-content-center mt-3">
                    {{ $posts->links() }}
                </div>
            @else
                <p class="text-muted text-center mt-3">{{ __(' No posts yet.')}}</p>
            @endif
        </div>
    </div>
</div>
@endsection


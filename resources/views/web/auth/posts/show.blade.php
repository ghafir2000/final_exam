@extends('web.layout')

{{-- Set the title using a section that your layout file expects --}}
@section('title', __('Dr.Pets - Reading: :title', ['title' => $post->title]))

@section('styles')
<style>
    .post-show-img {
        max-height: 450px;
        width: 100%;
        object-fit: cover;
        border-radius: .25rem; /* Bootstrap's default border-radius */
    }
    .comment-form textarea {
        resize: vertical;
    }
    .comment-edit-form {
        display: none; /* Initially hidden */
    }
    .comment-avatar {
        width: 50px; /* Standard avatar size */
        height: 50px;
        object-fit: cover;
    }
    .transparent-edit-btn {
        background-color: transparent;
        border: none;
        color: #007bff; /* Or your preferred icon color */
        padding: 0.375rem 0.75rem; /* Match button padding if needed */
    }
    .transparent-edit-btn:hover {
        color: #0056b3; /* Darken on hover */
    }
    /* Basic styling for custom action buttons, adjust as needed */

    .post-show-img { /* Or whatever you call it */
        max-height: 400px; /* Adjust as needed */
        height: 35%;
        width: 35%;
        object-fit: cover;
        border-radius: .25rem;
        display: block; /* for mx-auto to work if not full width */
        /* mx-auto can be added as a class if needed */
    }

</style>
@endsection

@section('content')
<div class="container mt-4">
    <div class="card mx-auto mb-4">
        <div class="card-header text-center bg-warning">
            <h2>{{ $post->title }}</h2>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if($post->getFirstMediaUrl('post_picture'))
                <img src="{{ $post->getFirstMediaUrl('post_picture') }}"
                    alt="{{ $post->title }}"
                    class="post-show-img img-fluid mb-3 mx-auto d-block"> {{-- Use your class, img-fluid for responsiveness, mx-auto for centering --}}
            @endif

            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <p class="text-muted mb-0">{{ __('By: :author', ['author' => $post->user->name ?? __('Unknown Author')]) }}
                        @if($post->blog)
                        {{ __('in') }} <a href="{{ route('blog.show', $post->blog->id) }}">{{ $post->blog->title }}</a>
                        @endif
                    </p>
                    <p class="text-muted mb-0">{{ __('Published: :date', ['date' => $post->created_at->format('M d, Y')]) }}</p>
                </div>
                @auth
                <div class="d-flex align-items-center">
                    @can('update', $post)
                    <a href="{{ route('post.edit', $post->id) }}" class="transparent-edit-btn me-2" title="{{ __('Edit Post') }}">
                        <i class="fas fa-pen"></i>
                    </a>
                    @endcan
                   @php
                    $isPostWished = Auth::user()->wishes()->where('wishable_id', $post->id)->where('wishable_type', get_class($post))->exists();
                    @endphp
                    <form action="{{ $isPostWished ? route('wish.update') : route('wish.store') }}" method="POST" class="d-inline">
                        @csrf
                        @if($isPostWished) @method('PUT') @endif
                        <input type="hidden" name="wishable_id" value="{{ $post->id }}">
                        <input type="hidden" name="wishable_type" value="{{ get_class($post) }}">
                        <button type="submit" class="btn btn-link btn-sm p-0 {{ $isPostWished ? 'text-danger' : 'text-muted' }}" title="{{ $isPostWished ? __('Unlike') : __('Like') }}">
                            <i class="fas fa-heart{{ $isPostWished ? ' text-danger' : '' }}"></i> {{ $post->wishable()->count() ?? 0 }}
                        </button>
                    </form>
                </div>
                @endauth
            </div>
            <hr>
            <div class="post-content">
                {!! $post->content !!}
            </div>
        </div>
    </div>

    {{-- Comments Section --}}
    <div class="card mx-auto mb-4">
        <div class="card-header bg-light">
            <h3>{{ __('Comments (:count)', ['count' => $comments->total()]) }}</h3>
        </div>
        <div class="card-body">
            @auth
            <div class="comment-form mb-4">
                <h4>{{ __('Leave a Comment') }}</h4>
                <form action="{{ route('comment.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="post_id" value="{{ $post->id }}">
                    <div class="mb-3">
                        <label for="comment_picture" class="form-label">{{ __('Comment Image (Optional)') }}</label>
                        <input type="file" class="form-control @error('comment_picture') is-invalid @enderror" id="comment_picture" name="comment_picture" accept="image/*">
                        @error('comment_picture')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <textarea class="form-control @error('body', 'storeComment') is-invalid @enderror" name="body" rows="3" placeholder="{{ __('Write your comment here...') }}" required>{{ old('body') }}</textarea>
                        @error('body', 'storeComment') {{-- Use a named error bag if needed --}}
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary btn-custom-action">{{ __('Post Comment') }}</button>
                </form>
            </div>
            <hr class="mb-4">
            @else
            <p class="text-center"><a href="{{ route('login') }}">{{ __('Log in') }}</a> {{ __('or') }} <a href="{{ route('register') }}">{{ __('register') }}</a> {{ __('to leave a comment.') }}</p>
            <hr class="mb-4">
            @endauth


            @if($comments->count() > 0)
                @foreach($comments as $comment)
                <div class="d-flex mb-3" id="comment-{{ $comment->id }}">
                    <img src="{{ $comment->user->getFirstMediaUrl('profile_picture') ?: asset('images/upload_default.jpg') }}" alt="{{ $comment->user->name ?? __('User') }}" class="rounded-circle me-3 comment-avatar">
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>{{ $comment->user->name ?? __('User') }}</strong>
                            <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                        </div>

                        {{-- Comment View Area --}}
                        <div id="comment-view-{{ $comment->id }}">
                            <p class="mb-1 ml-2" id="comment-text-{{ $comment->id }}">{{ $comment->body }}</p>
                            @if($comment->getFirstMediaUrl('comment_picture'))
                                <img src="{{ $comment->getFirstMediaUrl('comment_picture') }}" alt="{{ $comment->user->name ?? __('User') }}" class="rounded float-end comment-avatar" style="width: 150px; height: 150px; object-fit: cover; margin-left: auto;">
                            @endif
                        </div>

                        <div>
                            
                            @auth {{-- Actions available only if authenticated --}}
                                @if($comment->user_id == auth()->id())
                                <button onclick="toggleEditComment({{ $comment->id }})" class="btn btn-link btn-sm text-primary p-0 me-2 transparent-edit-btn" id="edit-btn-{{ $comment->id }}"><i class="fas fa-pen"></i> {{ __('Edit') }}</button>
                                <form action="{{ route('comment.destroy', $comment->id) }}" method="POST" class="d-inline me-2" onsubmit="return confirm('{{ __('Are you sure you want to delete this comment?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link btn-sm text-danger p-0"><i class="fas fa-trash"></i> {{ __('Delete') }}</button>
                                </form>
                                @endif

                                @php
                                $isCommentWished = Auth::user()->wishes()->where('wishable_id', $comment->id)->where('wishable_type', get_class($comment))->exists();
                                @endphp
                                <form action="{{ $isCommentWished ? route('wish.update') : route('wish.store') }}" method="POST" class="d-inline">
                                    @csrf
                                    @if($isCommentWished) @method('PUT') @endif
                                    <input type="hidden" name="wishable_id" value="{{ $comment->id }}">
                                    <input type="hidden" name="wishable_type" value="{{ get_class($comment) }}">
                                    <button type="submit" class="btn btn-link btn-sm p-0 {{ $isCommentWished ? 'text-danger' : 'text-muted' }}" title="{{ $isCommentWished ? __('Unlike') : __('Like') }}">
                                        <i class="fas fa-heart{{ $isCommentWished ? ' text-danger' : '' }}"></i> {{ $comment->wishable()->count() ?? 0 }}
                                    </button>
                                </form>
                            @endauth
                        </div>

                        {{-- Comment Edit Form Area (only for comment owner) --}}
                        @if($comment->user_id == auth()->id())
                        <form id="edit-comment-form-{{ $comment->id }}" action="{{ route('comment.update') }}" method="POST" class="comment-edit-form mt-2">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="id" value="{{ $comment->id }}">
                            <textarea name="body" class="form-control form-control-sm mb-1 @error('body', 'updateComment'.$comment->id) is-invalid @enderror" rows="2" required>{{ old('body', $comment->body) }}</textarea>
                            @error('body', 'updateComment'.$comment->id) {{-- Named error bag for specific comment edit --}}
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <button type="submit" class="btn btn-success btn-sm btn-custom-action">{{ __('Save') }}</button>
                            <button type="button" onclick="toggleEditComment({{ $comment->id }})" class="btn btn-secondary btn-sm">{{ __('Cancel') }}</button>
                        </form>
                        @endif
                    </div>
                </div>
                @if(!$loop->last) <hr> @endif
                @endforeach

                <div class="mt-4">
                    {{ $comments->links() }}
                </div>
            @else
                <p class="text-center">{{ __('No comments yet. Be the first to comment!') }}</p>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent {{-- Good practice to include parent scripts if any --}}

{{-- Font Awesome - Place it where it can be accessed by all parts of the page if needed --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js"
        integrity="sha512-6sSYJqDreZRZGkJ3b+YfdhB3MzmuP9R7X1QZ6g5aIXhRvR1Y/N/P47jmnkENm7YL3oqsmI6AK+V6AD99uWDnIw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

@auth
<script>
function toggleEditComment(commentId) {
    const commentView = document.getElementById('comment-view-' + commentId);
    const editForm = document.getElementById('edit-comment-form-' + commentId);
    // const editButton = document.getElementById('edit-btn-' + commentId); // Original "Edit" button in view mode

    if (editForm.style.display === 'none' || editForm.style.display === '') {
        // Show edit form, hide view area
        commentView.style.display = 'none';
        editForm.style.display = 'block';
        const textarea = editForm.querySelector('textarea[name="body"]');
        if (textarea) {
            textarea.focus(); // Auto-focus the textarea
            textarea.selectionStart = textarea.selectionEnd = textarea.value.length; // Move cursor to end
        }
        // editButton.innerHTML = '<i class="fas fa-times"></i> Cancel Edit'; // Not strictly needed as this button gets hidden
    } else {
        // Show view area, hide edit form
        commentView.style.display = 'block'; // Or 'flex'/'grid' if original display was different
        editForm.style.display = 'none';
        // editButton.innerHTML = '<i class="fas fa-pen"></i> Edit';
    }
}

// This script block runs on page load to re-open an edit form if there was a validation error.
// Assumes your controller, on validation failure for a comment update, redirects back with:
// ->withErrors($validator, 'updateComment'.$comment->id)
// ->withInput()
// ->with('failed_comment_edit_id', $comment->id)
// and the redirect URL includes '#comment-'.$comment->id for better UX.
@if (session('failed_comment_edit_id') && $errors->hasBag('updateComment'.session('failed_comment_edit_id')))
    document.addEventListener('DOMContentLoaded', function() {
        const failedCommentId = {{ session('failed_comment_edit_id') }};
        const editForm = document.getElementById('edit-comment-form-' + failedCommentId);
        const commentView = document.getElementById('comment-view-' + failedCommentId);

        if (editForm && commentView) {
            // Ensure the view is hidden and form is shown
            commentView.style.display = 'none';
            editForm.style.display = 'block';
            const textarea = editForm.querySelector('textarea[name="body"]');
            if (textarea) {
                textarea.focus(); // Auto-focus the textarea
                // Optionally scroll to the comment if not already handled by URL hash
                // editForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
@endif
</script>
@endauth
@endsection

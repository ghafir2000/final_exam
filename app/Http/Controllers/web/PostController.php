<?php

namespace App\Http\Controllers\web;

use App\Models\Post; // For authorization
use App\Models\Blog; // For creating posts under a blog
use Illuminate\Http\Request;
use App\Services\PostService;
use App\Services\BlogService; // If needed to select a blog
use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePostRequest; // Placeholder
use App\Http\Requests\UpdatePostRequest; // Placeholder

class PostController extends Controller
{
    protected $postService;
    protected $blogService; // Optional, if you need to list blogs for selection

    public function __construct(PostService $postService, BlogService $blogService)
    {
        $this->postService = $postService;
        $this->blogService = $blogService; // Or remove if not used directly
        // $this->middleware('auth')->except(['index', 'show']); // Example middleware
    }

    // Example: Posts for a specific blog

    public function show($id)
    {
        $post = $this->postService->find($id, false, ['user', 'blog']);
        if (!$post) {
            return redirect()->route('blog.index')->with('error', 'Post not found.'); // Adjust route
        }
        $comments = $post->comments()->with('user')->latest()->paginate(10);
        
        return view('web.auth.posts.show', compact('post','comments'));
    }

    public function create($blog_id) // Pass blog_id if creating under specific blog
    {
        $blog = $this->blogService->find($blog_id);
        return view('web.auth.posts.create', compact('blog'));
    }

    public function store(CreatePostRequest $request) // Use specific FormRequest
    {
        $data = $request->validated();
        // user_id will be set by service if not present and auth is checked
        // post_image will be handled by service if present
        $blog_id = $this->postService->store($data)->blog->id;

        return redirect()->route('blog.show', $blog_id)->with('success', 'Post created successfully.');
    }

    public function edit($id)
    {
        $post = $this->postService->find($id);
        if (!$post) {
            return redirect()->route('blog.index')->with('error', 'Post not found.'); // Adjust route
        }
        return view('web.auth.posts.edit', compact('post'));
    }

    public function update(UpdatePostRequest $request) // Use specific FormRequest
    {
        $data = $request->validated();
        
        $post_id = $this->postService->update($data)->id;

        return redirect()->route('post.show', $post_id)->with('success', 'Post updated successfully.');
    }

    public function destroy($id)
    {
        $post = $this->postService->find($id);
        $blog_id = $post->blog_id; // For redirecting
        if (!$post) {
            return redirect()->route('blog.show')->with('error', 'Post not found.'); // Adjust route
        }
        $this->postService->destroy($id);

        return redirect()->route('blog.show', $blog_id)->with('success', 'Post deleted successfully.');
    }
}
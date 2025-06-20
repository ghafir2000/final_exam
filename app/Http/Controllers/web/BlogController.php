<?php

namespace App\Http\Controllers\web;

use App\Models\Blog; // For authorization
use Illuminate\Http\Request;
use App\Services\BlogService;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateBlogRequest; // Placeholder
use App\Http\Requests\UpdateBlogRequest; // Placeholder

class BlogController extends Controller
{
    protected $blogService;

    public function __construct(BlogService $blogService)
    {
        $this->blogService = $blogService;
        // $this->middleware('auth')->except(['index', 'show']); // Example middleware
    }

    public function index(Request $request)
    {
        $data = $request->only(['search', 'user_id']);
        // Eager load 'user' and 'latestPost' as per your blade view
        $blogs = $this->blogService->all($data, true, ['user', 'latestPost' , 'latestPost.user']);
        return view('web.auth.blogs.index', compact('blogs'));
    }

    public function show($id)
    {
        // Eager load relations needed for the show page
        $blog = $this->blogService->find($id, false, ['user', 'latestPost']);
        if (!$blog) {
            return redirect()->route('blog.index')->with('error', 'Blog not found.');
        }
        $posts = $blog->posts()->with('user', 'latestComment')->latest()->paginate(6);
        return view('web.auth.blogs.show', compact('blog', 'posts'));
    }

    public function create()
    {
        return view('web.auth.blogs.create');
    }

    public function store(CreateBlogRequest $request) // Use specific FormRequest
    {
        $data = $request->validated();
        
        // The service will handle user_id if not present and auth is checked
        // The service also handles media if 'blog_image' is in $data
        $this->blogService->store($data);

        return redirect()->route('blog.index')->with('success', 'Blog created successfully.');
    }

    public function edit($id)
    {
        $blog = $this->blogService->find($id);
        if (!$blog) {
            return redirect()->route('blog.index')->with('error', 'Blog not found.');
        }
        return view('web.auth.blogs.edit', compact('blog'));
    }

    public function update(UpdateBlogRequest $request) // Use specific FormRequest
    {
        $data = $request->validated();
        
        $blog_id = $this->blogService->update($data)->id;

        return redirect()->route('blog.show', $blog_id)->with('success', 'blog updated successfully.');
    }

    public function destroy($id)
    {
        $blog = $this->blogService->find($id);
        if (!$blog) {
            return redirect()->route('blog.index')->with('error', 'Blog not found.');
        }
        $this->blogService->destroy($id);
        return redirect()->route('blog.index')->with('success', 'Blog deleted successfully.');
    }
}
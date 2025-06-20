<?php

namespace App\Services;

use App\Models\Blog;
use Illuminate\Support\Arr;
use Exception;

use Illuminate\Support\Facades\Auth;

class BlogService
{
    
    protected $imageService;

     public function __construct(ImageService $imageService ) {
        $this->imageService = $imageService;
    }
    public function all($data = [], $paginated = false, $withes = ['user', 'latestPost'])
    {
        $query = Blog::query()
            ->with($withes) // Load specified relationships
            ->when(isset($data['user_id']), function ($query) use ($data) {
                return $query->where('user_id', $data['user_id']);
            })
            ->when(isset($data['search']), function ($query) use ($data) {
                return $query->where('title', 'like', "%{$data['search']}%");
            })
            ->latest(); // Order by creation date descending

        if ($paginated) {
            return $query->paginate(isset($data['per_page']) ? $data['per_page'] : 15);
        }
        return $query->get();
    }

    public function find($id, $withTrashed = false, $withes = ['user'])
    {
        $blog = Blog::with($withes)->withTrashed($withTrashed)->find($id);

        return $blog;
    }

    public function store($data)
    {
        if (!isset($data['user_id']) && Auth::check()) {
            $data['user_id'] = Auth::id();
        }

        if (isset($data['user_id']) && Auth::check() && auth()->user()->userable_type === \App\Models\Customer::class) {
            throw new \Exception("Customer cannot create a blog");
        }

        $blog = Blog::create(Arr::except($data, 'blog_picture'));
        if (isset($data['blog_picture'])) {
            $this->imageService->store($blog, $data['blog_picture'], 'blog_picture');
        }
        

        return $blog;
    }

    public function update($data)
    {
        $blog = $this->find($data['id']);
        if (!$blog) {
            throw new \Exception("Blog not found");
        }

        if ($blog->user_id !== auth()->id()) {
            throw new \Exception("Unauthorized to update this blog");
        }

        $data = Arr::except($data,'id');
        $blog->update($data);

        return $blog->refresh();
    }

    public function destroy($id)
    {
        $blog = $this->find($id, true); // Find with trashed to handle soft deletes
        if (!$blog) {
            throw new \Exception("Blog not found");
        }

        if ($blog->user_id !== auth()->id()) {
            throw new \Exception("Unauthorized to delete this blog");
        }
        $blog->delete();
        return $blog;
    }

    public function restore($id)
    {
        $blog = Blog::withTrashed()->find($id);
        if (!$blog) {
            throw new \Exception("Blog not found");
        }

         if ($blog->user_id !== auth()->id()) {
            throw new \Exception("Unauthorized to restore this blog");
        }
        $blog->restore();
        return $blog;
    }
}
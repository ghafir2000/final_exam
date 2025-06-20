<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class PostService
{

    protected $imageService;

     public function __construct(ImageService $imageService ) {
        $this->imageService = $imageService;
    }
    public function all($data = [], $paginated = false, $withes = ['user', 'blog'])
    {
        $query = Post::query()
            ->with($withes)
            ->when(isset($data['blog_id']), function ($query) use ($data) {
                return $query->where('blog_id', $data['blog_id']);
            })
            ->when(isset($data['user_id']), function ($query) use ($data) {
                return $query->where('user_id', $data['user_id']);
            })
            ->when(isset($data['search']), function ($query) use ($data) {
                return $query->where('title', 'like', "%{$data['search']}%")
                             ->orWhere('content', 'like', "%{$data['search']}%");
            })
            ->latest();

        if ($paginated) {
            return $query->paginate(isset($data['per_page']) ? $data['per_page'] : 15);
        }
        return $query->get();
    }

    public function find($id, $withTrashed = false, $withes = ['user', 'blog', 'comments.user', 'media'])
    {
        $post = Post::with($withes)->withTrashed($withTrashed)->find($id);


        return $post;
    }

    public function store($data)
    {

        if (!isset($data['user_id']) && Auth::check()) {
            $data['user_id'] = Auth::id();
        }
        
        $post = Post::create(Arr::except($data, 'post_picture'));
        if (isset($data['post_picture'])) {
            $this->imageService->store($post, $data['post_picture'], 'post_picture');
        }
        // dd($data);
        return $post;
    }

    public function update($data)
    {
        $post = $this->find($data['id']);
        if (!$post) {
            throw new \Exception("Post not found");
        }
        if ($post->user_id !== auth()->id()) {
            throw new \Exception("Unauthorized");
        }

        $data = Arr::except($data, 'id');
        $post->update($data);

        return $post->refresh();
    }

    public function destroy($id)
    {
        $post = $this->find($id, true);
        if (!$post) {
            throw new \Exception("Post not found");
        }
        if ($post->user_id !== auth()->id()) {
            throw new \Exception("Unauthorized");
        }
        $post->delete();
        return $post;
    }

    public function restore($id)
    {
        $post = Post::withTrashed()->find($id);
        if (!$post) {
            throw new \Exception("Post not found");
        }

        if ($post->user_id !== auth()->id()) {
            throw new \Exception("Unauthorized");
        }
        $post->restore();
        return $post;
    }
}
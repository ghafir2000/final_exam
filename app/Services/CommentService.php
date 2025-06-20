<?php

namespace App\Services;

use App\Models\Comment;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class CommentService
{

    protected $imageService;

     public function __construct(ImageService $imageService ) {
        $this->imageService = $imageService;
    }

    public function all($data = [], $paginated = false, $withes = ['user'])
    {
        $query = Comment::query()
            ->with($withes)
            ->when(isset($data['user_id']), function ($query) use ($data) {
                return $query->where('user_id', $data['user_id']);
            })->latest();

        if ($paginated) {
            return $query->paginate(isset($data['per_page']) ? $data['per_page'] : 15);
        }
        return $query->get();
    }

    public function find($id, $withTrashed = false, $withes = ['user'])
    {
        $comment = Comment::with($withes)->withTrashed($withTrashed)->find($id);

        return $comment;
    }

    public function store($data)
    {
        if (!isset($data['user_id']) && Auth::check()) {
            $data['user_id'] = Auth::id();
        }

        $comment = Comment::create(Arr::except($data, 'comment_picture'));
        if (isset($data['comment_picture'])) {
            $this->imageService->store($comment, $data['comment_picture'], 'comment_picture');
        }

        return $comment;
    }

    public function update($data)
    {
        $comment = $this->find($data['id']);
        if (!$comment) {
            throw new \Exception("Comment not found");
        }

        
        if ($comment->user_id !== auth()->id()) {
            throw new \Exception("Unauthorized ");
        }
        
        $comment->update(Arr::except($data,'id'));
        return $comment;
    }

    public function destroy($id)
    {
        $comment = $this->find($id, true);
        if (!$comment) {
            throw new \Exception("Comment not found");
        }

        
        if ($comment->user_id !== auth()->id()) {
            throw new \Exception("Unauthorized ");
        }
        $comment->delete();
        return $comment;
    }

    public function restore($id)
    {
        $comment = Comment::withTrashed()->find($id);
        if (!$comment) {
            throw new \Exception("Comment not found");
        }
        if ($comment->user_id !== auth()->id()) {
            throw new \Exception("Unauthorized");
        }
        $comment->restore();
        return $comment;
    }
}
<?php

namespace App\Http\Controllers\web;

use App\Models\Comment; // For authorization
use Illuminate\Http\Request;
use App\Services\CommentService;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCommentRequest; // Placeholder
use App\Http\Requests\UpdateCommentRequest; // Placeholder
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;

class CommentController extends Controller
{
    protected $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
        // $this->middleware('auth'); // Comments usually require auth
    }

    // Comments are typically managed within the context of their parent (Post/Blog)
    // So, index/show/create might not be standalone routes.

    public function store(CreateCommentRequest $request) // Use specific FormRequest
    {
        $data = $request->validated();

        $this->commentService->store($data);

        return redirect()->back();
    }


    public function update(UpdateCommentRequest $request) // Use specific FormRequest
    {
        $data = $request->validated();
        $comment = $this->commentService->find($request['id']);
        if (!$comment) {
            return redirect()->back()->with('error', 'Comment not found.');
        }
        // dd($data);
        
        $this->commentService->update($data);
        return redirect()->back();
    }

    public function destroy($id)
    {
        $comment = $this->commentService->find($id);
        if (!$comment) {
            return Redirect::to(URL::previous())->with('error', 'Comment not found.');
        }
        $this->commentService->destroy($id);
        return redirect()->back();
    }
}
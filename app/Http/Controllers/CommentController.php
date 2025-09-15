<?php

namespace App\Http\Controllers;

use App\Strategies\CommentSortingStrategies\SortByLikes;
use App\Strategies\CommentSortingStrategies\SortByLatest;
use App\Strategies\CommentSortingStrategies\SortByOldest;
use App\Services\SortingContext;
use App\Models\Comment;
use App\Models\CommentLike;
use app\Models\QuestionSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CommentController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Comment::class);

        $request->validate([
            'comment_content' => 'required|string|max:500',
            'question_set_id' => 'required|exists:question_sets,id',
        ]);

        Comment::create([
            'comment_content' => $request->comment_content,
            'user_id' => Auth::id(),
            'question_set_id' => $request->question_set_id,
        ]);

        return back()->with('comment_success', 'Comment posted successfully!');
    }

    public function reply(Request $request, Comment $comment)
    {
        $this->authorize('create', Comment::class);

        $request->validate([
            'comment_content' => 'required|string|max:500',
            'question_set_id' => 'required|exists:question_sets,id',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        Comment::create([
            'comment_content' => $request->comment_content,
            'user_id' => Auth::id(),
            'question_set_id' => $comment->question_set_id,
            'parent_id' => $comment->id,
        ]);

        return back()->with('comment_success', 'Reply posted successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $request->validate([
            'comment_content' => 'required|string|max:500',
        ]);

        $comment->update([
            'comment_content' => $request->comment_content,
        ]);

        return back()->with('comment_success', 'Comment updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return back()->with('comment_success', 'Comment deleted successfully!');
    }
    
    public function like(Comment $comment)
    {
        $like = CommentLike::firstOrCreate(
            ['comment_id' => $comment->id, 'user_id' => Auth::id()]
        );

        if (!$like->wasRecentlyCreated) {
            $like->delete();
            $comment->decrement('likes_count');
        } else {
            $comment->increment('likes_count');
        }

        return back();
    }

    public function getComments($questionSetId)
    {
        $questionSet = QuestionSet::findOrFail($questionSetId);

        $sort = request('sort', 'likes');
        $query = $questionSet->comments()->with('user', 'replies.user')->whereNull('parent_id');

        $strategy = match ($sort) {
            'latest' => new SortByLatest(),
            'oldest' => new SortByOldest(),
            default => new SortByLikes(),
        };

        $sortingService = new SortingContext($strategy);
        $comments = $sortingService->sort($query->getQuery())->withCount('replies')->paginate(10);

        return $comments;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Strategies\CommentSortingStrategies\SortByLikes;
use App\Strategies\CommentSortingStrategies\SortByLatest;
use App\Strategies\CommentSortingStrategies\SortByOldest;
use App\Services\SortingContext;
use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\QuestionSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

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

        // if (! $this->isEnrolled($request->question_set_id)) {
        //     abort(403, 'You must be in this class to post a comment.');
        // }

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

        // if (! $this->isEnrolled($request->question_set_id)) {
        //     abort(403, 'You must be in this class to reply.');
        // }

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

        // if (! $this->isEnrolled($comment->question_set_id)) {
        //     abort(403, 'You cannot edit this comment (not in this class).');
        // }

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

        // if (! $this->isEnrolled($comment->question_set_id)) {
        //     abort(403, 'You cannot delete this comment (not in this class).');
        // }

        $comment->delete();

        return back()->with('comment_success', 'Comment deleted successfully!');
    }
    
    public function like(Comment $comment)
    {
        // if (! $this->isEnrolled($comment->question_set_id)) {
        //     abort(403, 'You cannot like this comment (not in this class).');
        // }

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

    private function isEnrolled($questionSetId)
    {
        $questionSet = QuestionSet::findOrFail($questionSetId);
        $classId = $questionSet->class_id;

        $baseUrl = config('services.class_service.url'); 
        $token = Auth::user()->createToken('comment-module')->plainTextToken;

        $response = Http::withToken($token)
            ->get("{$baseUrl}/api/classes/{$classId}/students");

        if ($response->failed()) {
            return false;
        }

        $students = $response->json('students');
        return collect($students)->contains(fn ($student) => $student['id'] === Auth::id());
    }

    // load all comments that belongs to the user with id = $id
    // public function loadUserComments($id)
    // {
    //     $user = User::with([
    //         'comments' => function ($query) {
    //             $query->select('comments.id', 'comments.comment_content'); 
    //         }
    //     ])->findOrFail($id);

    //     $totalComments = Statistic::totalCommentsInClass($id);

    //     if ($quizClass->user_id !== Auth::id()) {
    //         return response()->json(['message' => 'Unauthorized'], 403);
    //     }

    //     return Response::json([
    //         'totalComments' => $totalComments,
    //         'comments' => $user->comments,
    //     ], 200);
    // }
}

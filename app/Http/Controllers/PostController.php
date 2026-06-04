<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostReaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function show(Post $post)
    {
        if (!$post->published_at || $post->published_at->isFuture()) {
            abort(404);
        }

        $reactions = [];
        $userReactions = [];
        if ($post->allow_reactions) {
            $reactions = $post->reactions()
                ->selectRaw('emoji, count(*) as count')
                ->groupBy('emoji')
                ->pluck('count', 'emoji')
                ->toArray();
            if (Auth::check()) {
                $userReactions = $post->reactions()
                    ->where('user_id', Auth::id())
                    ->pluck('emoji')
                    ->toArray();
            }
        }

        $comments = $post->allow_comments
            ? $post->comments()->get()
            : collect();

        return view('posts.show', compact('post', 'reactions', 'userReactions', 'comments'));
    }

    public function react(Request $request, Post $post)
    {
        if (!$post->allow_reactions || !Auth::check()) abort(403);

        $emoji = $request->validate(['emoji' => 'required|string|max:10'])['emoji'];

        $existing = PostReaction::where([
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'emoji'   => $emoji,
        ])->first();

        if ($existing) {
            $existing->delete();
            $active = false;
        } else {
            PostReaction::create(['post_id' => $post->id, 'user_id' => Auth::id(), 'emoji' => $emoji]);
            $active = true;
        }

        $count = PostReaction::where('post_id', $post->id)->where('emoji', $emoji)->count();

        return response()->json(['active' => $active, 'count' => $count]);
    }

    public function comment(Request $request, Post $post)
    {
        if (!$post->allow_comments || !Auth::check()) abort(403);

        $data = $request->validate(['content' => 'required|string|max:1000']);

        $comment = PostComment::create([
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'content' => $data['content'],
        ]);

        $comment->load('user');

        return redirect()->back()->with('success', 'Commentaire ajouté.');
    }
}

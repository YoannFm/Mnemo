<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostCommentReport;
use App\Models\PostReaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            ? $post->comments()->whereNull('parent_id')->with(['user', 'replies.user'])->get()
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

        return redirect()->to(route('posts.show', $post) . '#comments')->with('success', 'Commentaire ajouté.');
    }

    public function reply(Request $request, Post $post)
    {
        if (!$post->allow_comments || !Auth::check()) abort(403);
        $data = $request->validate([
            'content'   => 'required|string|max:1000',
            'parent_id' => 'required|exists:post_comments,id',
        ]);
        PostComment::create([
            'post_id'   => $post->id,
            'user_id'   => Auth::id(),
            'content'   => $data['content'],
            'parent_id' => $data['parent_id'],
        ]);
        return redirect()->to(route('posts.show', $post) . '#comments')->with('success', 'Réponse ajoutée.');
    }

    public function reportComment(PostComment $comment)
    {
        if (!Auth::check()) abort(403);

        $already = PostCommentReport::where([
            'post_comment_id' => $comment->id,
            'user_id'         => Auth::id(),
        ])->exists();

        if (!$already) {
            PostCommentReport::create([
                'post_comment_id' => $comment->id,
                'user_id'         => Auth::id(),
            ]);
        }

        return response()->json(['reported' => true]);
    }

    public function updateComment(Request $request, PostComment $comment)
    {
        if (!Auth::check() || Auth::id() !== $comment->user_id) abort(403);

        $data = $request->validate(['content' => 'required|string|max:1000']);

        // Sauvegarde l'ancienne version dans l'historique
        DB::table('post_comment_history')->insert([
            'post_comment_id' => $comment->id,
            'content'         => $comment->content,
            'created_at'      => now(),
        ]);

        $comment->update(['content' => $data['content'], 'edited_at' => now()]);

        return redirect()->to(route('posts.show', $comment->post) . '#comments')->with('success', 'Commentaire modifié.');
    }

    public function deleteComment(PostComment $comment)
    {
        if (!Auth::check() || Auth::id() !== $comment->user_id) abort(403);

        // Sauvegarde dans l'historique avant suppression logique
        DB::table('post_comment_history')->insert([
            'post_comment_id' => $comment->id,
            'content'         => $comment->content,
            'created_at'      => now(),
        ]);

        $comment->update(['is_deleted' => true, 'content' => '']);

        return redirect()->to(route('posts.show', $comment->post) . '#comments')->with('success', 'Commentaire supprimé.');
    }
}

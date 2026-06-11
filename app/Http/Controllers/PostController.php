<?php
namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Models\Mute;
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
            ? $post->comments()->whereNull('parent_id')->with(['user', 'replies.user'])->orderBy('created_at', 'asc')->get()
            : collect();

        return view('posts.show', compact('post', 'reactions', 'userReactions', 'comments'));
    }

    public function react(Request $request, Post $post)
    {
        if (!$post->allow_reactions || !Auth::check()) abort(403);

        $isMuted = Mute::where('user_id', Auth::id())
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();
        if ($isMuted) {
            return response()->json(['status' => 'muted', 'message' => 'Vous êtes muté et ne pouvez pas interagir.'], 403);
        }

        $emoji = $request->validate(['emoji' => 'required|string|max:100'])['emoji'];

        if (!\App\Models\Emoji::where('slug', $emoji)->exists()) {
            return response()->json(['error' => 'Emoji invalide.'], 422);
        }

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

        $mute = Mute::where('user_id', Auth::id())
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->latest()
            ->first();

        if ($mute) {
            $reason = $mute->reason ? 'Vous êtes muet pour la raison suivante : ' . $mute->reason : 'Vous êtes muet. Aucune raison spécifiée.';
            $expiry = $mute->expires_at ? 'Ce mute expirera dans ' . now()->diffForHumans($mute->expires_at, ['parts' => 2, 'join' => ' et ']) . '.' : 'Ce mute n\'expirera pas.';
            $message = $reason . ' ' . $expiry;
            if ($request->expectsJson()) {
                return response()->json(['status' => 'muted', 'message' => $message], 403);
            }
            abort(403, $message);
        }

        $data = $request->validate(['content' => 'required|string|max:1000']);

        $comment = PostComment::create([
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'content' => $data['content'],
        ]);

        $comment->load('user');

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => 'ok',
                'comment' => [
                    'id'         => $comment->id,
                    'user_name'  => $comment->user->name,
                    'content'    => $comment->content,
                    'created_at' => $comment->created_at->format('d/m/Y H:i'),
                ],
            ]);
        }

        return redirect()->to(route('posts.show', $post) . '#comments')->with('success', 'Commentaire ajouté.');
    }

    public function reply(Request $request, Post $post)
    {
        if (!$post->allow_comments || !Auth::check()) abort(403);

        $mute = Mute::where('user_id', Auth::id())
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->latest()
            ->first();

        if ($mute) {
            $reason = $mute->reason ? 'Vous êtes muet pour la raison suivante : ' . $mute->reason : 'Vous êtes muet. Aucune raison spécifiée.';
            $expiry = $mute->expires_at ? 'Ce mute expirera dans ' . now()->diffForHumans($mute->expires_at, ['parts' => 2, 'join' => ' et ']) . '.' : 'Ce mute n\'expirera pas.';
            $message = $reason . ' ' . $expiry;
            if ($request->expectsJson()) {
                return response()->json(['status' => 'muted', 'message' => $message], 403);
            }
            abort(403, $message);
        }

        $data = $request->validate([
            'content'   => 'required|string|max:1000',
            'parent_id' => ['required', 'exists:post_comments,id', function ($attr, $value, $fail) use ($post) {
                if (!\App\Models\PostComment::where('id', $value)->where('post_id', $post->id)->exists()) {
                    $fail('Ce commentaire n\'appartient pas à cet article.');
                }
            }],
        ]);
        $reply = PostComment::create([
            'post_id'   => $post->id,
            'user_id'   => Auth::id(),
            'content'   => $data['content'],
            'parent_id' => $data['parent_id'],
        ]);
        $reply->load('user');

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => 'ok',
                'comment' => [
                    'id'         => $reply->id,
                    'user_name'  => $reply->user->name,
                    'content'    => $reply->content,
                    'created_at' => $reply->created_at->format('d/m/Y H:i'),
                ],
            ]);
        }

        return redirect()->to(route('posts.show', $post) . '#comments')->with('success', 'Réponse ajoutée.');
    }

    public function reportComment(Request $request, PostComment $comment)
    {
        if (!Auth::check()) abort(403);

        $data = $request->validate([
            'reason' => 'required|in:spam,harassment,inappropriate,misinformation,other',
            'note'   => 'nullable|string|max:500',
        ]);

        $already = PostCommentReport::where([
            'post_comment_id' => $comment->id,
            'user_id'         => Auth::id(),
        ])->exists();

        if ($already) {
            return response()->json(['status' => 'already_reported']);
        }

        PostCommentReport::create([
            'post_comment_id' => $comment->id,
            'user_id'         => Auth::id(),
            'reason'          => $data['reason'],
            'note'            => $data['note'] ?? null,
            'status'          => 'pending',
        ]);

        return response()->json(['status' => 'ok']);
    }

    public function updateComment(Request $request, PostComment $comment)
    {
        if (!Auth::check() || (Auth::id() !== $comment->user_id && !Auth::user()?->is_admin)) abort(403);

        $data = $request->validate(['content' => 'required|string|max:1000']);

        // Sauvegarde l'ancienne version dans l'historique
        DB::table('post_comment_history')->insert([
            'post_comment_id' => $comment->id,
            'content'         => $comment->content,
            'created_at'      => now(),
        ]);

        $comment->update(['content' => $data['content'], 'edited_at' => now()]);

        if (Auth::user()?->is_admin && Auth::id() !== $comment->user_id) {
            LogHelper::log('updated_comment', 'comment', $comment->id, null, 'info');
        }

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok', 'content' => $comment->content]);
        }

        return redirect()->to(route('posts.show', $comment->post) . '#comments')->with('success', 'Commentaire modifié.');
    }

    public function deleteComment(Request $request, PostComment $comment)
    {
        if (!Auth::check() || (Auth::id() !== $comment->user_id && !Auth::user()?->is_admin)) abort(403);

        // Sauvegarde dans l'historique avant suppression logique
        DB::table('post_comment_history')->insert([
            'post_comment_id' => $comment->id,
            'content'         => $comment->content,
            'created_at'      => now(),
        ]);

        $savedContent = $comment->content;
        $isAdminAction = Auth::user()?->is_admin && Auth::id() !== $comment->user_id;

        $comment->update(['is_deleted' => true, 'content' => '']);

        if ($isAdminAction) {
            LogHelper::log('deleted_comment', 'comment', $comment->id, ['content' => substr($savedContent, 0, 100)], 'warning');
        }

        if ($request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return redirect()->to(route('posts.show', $comment->post) . '#comments')->with('success', 'Commentaire supprimé.');
    }
}

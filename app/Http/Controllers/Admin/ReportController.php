<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSanction;
use App\Models\Mute;
use App\Models\PostCommentReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $reports = PostCommentReport::with(['user', 'postComment.user', 'postComment.post'])
            ->orderBy('created_at', 'desc')
            ->paginate(25);
        return view('admin.reports.index', compact('reports'));
    }

    public function markSanctioned(PostCommentReport $report)
    {
        $report->update(['status' => 'sanctioned']);

        if ($report->postComment && $report->postComment->user) {
            AdminSanction::create([
                'user_id'  => $report->postComment->user->id,
                'admin_id' => Auth::id(),
                'reason'   => 'Signalement sanctionné (motif : ' . ($report->reason ?? 'non précisé') . ')',
                'type'     => 'warning',
            ]);
        }

        return back()->with('success', 'Signalement marqué comme sanctionné.');
    }

    public function markUnsanctioned(PostCommentReport $report)
    {
        $report->update(['status' => 'unsanctioned']);
        return back()->with('success', 'Signalement marqué comme non sanctionné.');
    }

    public function deleteComment(PostCommentReport $report)
    {
        $comment = $report->postComment;

        if (!$comment) {
            return redirect()->route('admin.reports.index')->with('error', 'Commentaire introuvable.');
        }

        // Sauvegarde dans l'historique avant suppression logique
        DB::table('post_comment_history')->insert([
            'post_comment_id' => $comment->id,
            'content'         => $comment->content,
            'created_at'      => now(),
        ]);

        $comment->update(['is_deleted' => true, 'content' => '']);

        return redirect()->route('admin.reports.index')->with('success', 'Commentaire supprimé.');
    }

    public function muteUser(Request $request, PostCommentReport $report)
    {
        $data = $request->validate([
            'duration' => 'required|in:1day,7days,30days,permanent',
            'reason'   => 'required|string|max:500',
        ]);

        $comment = $report->postComment;

        if (!$comment || !$comment->user) {
            return redirect()->route('admin.reports.index')->with('error', 'Utilisateur introuvable.');
        }

        $expiresAt = match ($data['duration']) {
            '1day'   => now()->addDay(),
            '7days'  => now()->addDays(7),
            '30days' => now()->addDays(30),
            'permanent' => null,
        };

        Mute::create([
            'user_id'    => $comment->user->id,
            'admin_id'   => Auth::id(),
            'reason'     => $data['reason'],
            'expires_at' => $expiresAt,
        ]);

        AdminSanction::create([
            'user_id'  => $comment->user->id,
            'admin_id' => Auth::id(),
            'reason'   => $data['reason'],
            'type'     => 'mute',
        ]);

        return redirect()->route('admin.reports.index')->with('success', 'Utilisateur muté avec succès.');
    }
}

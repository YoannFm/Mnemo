<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSanction;
use App\Models\Mute;
use App\Models\PostCommentReport;
use App\Models\UserNotification;
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
        $request->validate([
            'reason'            => 'required|string|max:500',
            'duration_value'    => 'nullable|integer|min:1',
            'duration_unit'     => 'nullable|in:minutes,hours,days,weeks,months',
            'duration_permanent'=> 'nullable',
        ]);

        $comment = $report->postComment;

        if (!$comment || !$comment->user) {
            return redirect()->route('admin.reports.index')->with('error', 'Utilisateur introuvable.');
        }

        $permanent = $request->boolean('duration_permanent');

        if ($permanent) {
            $expiresAt = null;
        } else {
            $value = (int) $request->input('duration_value', 1);
            $unit  = $request->input('duration_unit', 'days');
            $expiresAt = now()->add($unit, $value);
        }

        Mute::create([
            'user_id'    => $comment->user->id,
            'admin_id'   => Auth::id(),
            'reason'     => $request->input('reason'),
            'expires_at' => $expiresAt,
        ]);

        AdminSanction::create([
            'user_id'  => $comment->user->id,
            'admin_id' => Auth::id(),
            'reason'   => $request->input('reason'),
            'type'     => 'mute',
        ]);

        $reason = trim($request->input('reason', ''));
        UserNotification::create([
            'user_id' => $comment->user->id,
            'title'   => 'Vous avez été rendu muet',
            'message' => 'Vous avez été rendu muet pour la raison suivante : ' . ($reason !== '' ? $reason : 'Aucune raison spécifiée'),
            'type'    => 'warning',
        ]);

        return redirect()->route('admin.reports.index')->with('success', 'Utilisateur muté avec succès.');
    }
}

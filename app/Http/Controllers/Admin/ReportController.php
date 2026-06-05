<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSanction;
use App\Models\ModuleRatingDeletion;
use App\Models\ModuleRatingReport;
use App\Models\ModuleReport;
use App\Models\Mute;
use App\Models\PostCommentReport;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->input('type', 'comments');

        $commentReports = PostCommentReport::with(['user', 'postComment.user', 'postComment.post'])
            ->orderBy('created_at', 'desc')
            ->paginate(25, ['*'], 'comments_page')
            ->withQueryString();

        $moduleReports = ModuleReport::with(['user', 'module.owner'])
            ->orderBy('created_at', 'desc')
            ->paginate(25, ['*'], 'modules_page')
            ->withQueryString();

        $ratingReports = ModuleRatingReport::with(['user', 'moduleRating.user', 'moduleRating.module'])
            ->orderBy('created_at', 'desc')
            ->paginate(25, ['*'], 'ratings_page')
            ->withQueryString();

        return view('admin.reports.index', compact('commentReports', 'moduleReports', 'ratingReports', 'type'));
    }

    public function markModuleTreated(ModuleReport $report)
    {
        $report->update(['status' => 'treated']);
        return back()->with('success', 'Signalement module marqué comme traité.');
    }

    public function markModuleRejected(ModuleReport $report)
    {
        $report->update(['status' => 'rejected']);
        return back()->with('success', 'Signalement module rejeté.');
    }

    public function markRatingTreated(ModuleRatingReport $report)
    {
        $report->update(['status' => 'treated']);
        return back()->with('success', 'Signalement marqué comme traité.');
    }

    public function markRatingRejected(ModuleRatingReport $report)
    {
        $report->update(['status' => 'rejected']);
        return back()->with('success', 'Signalement rejeté.');
    }

    public function deleteRating(Request $request, ModuleRatingReport $report)
    {
        $rating = $report->moduleRating;

        if (!$rating) {
            return back()->with('error', 'Avis introuvable.');
        }

        $userId = $rating->user_id;

        ModuleRatingDeletion::create([
            'user_id'    => $rating->user_id,
            'module_id'  => $rating->module_id,
            'type'       => 'rating',
            'rating'     => $rating->rating,
            'content'    => $rating->comment,
            'deleted_by' => 'admin',
        ]);

        $rating->delete();
        $report->update(['status' => 'treated']);

        if ($userId) {
            UserNotification::create([
                'user_id' => $userId,
                'title'   => 'Votre avis a été supprimé',
                'message' => 'Votre avis sur un module a été supprimé par un administrateur.',
                'type'    => 'warning',
            ]);
        }

        return back()->with('success', 'Avis supprimé.');
    }

    public function muteRatingUser(Request $request, ModuleRatingReport $report)
    {
        $request->validate([
            'reason'             => 'required|string|max:500',
            'duration_value'     => 'nullable|integer|min:1',
            'duration_unit'      => 'nullable|in:minutes,hours,days,weeks,months',
            'duration_permanent' => 'nullable',
        ]);

        $ratingUser = $report->moduleRating?->user;

        if (!$ratingUser) {
            return back()->with('error', 'Utilisateur introuvable.');
        }

        $permanent = $request->boolean('duration_permanent');
        $expiresAt = $permanent ? null : now()->add(
            $request->input('duration_unit', 'days'),
            (int) $request->input('duration_value', 1)
        );

        Mute::create([
            'user_id'    => $ratingUser->id,
            'admin_id'   => Auth::id(),
            'reason'     => $request->input('reason'),
            'expires_at' => $expiresAt,
        ]);

        AdminSanction::create([
            'user_id'  => $ratingUser->id,
            'admin_id' => Auth::id(),
            'reason'   => $request->input('reason'),
            'type'     => 'mute',
        ]);

        UserNotification::create([
            'user_id' => $ratingUser->id,
            'title'   => 'Vous avez été rendu muet',
            'message' => 'Vous avez été rendu muet pour la raison suivante : ' . $request->input('reason'),
            'type'    => 'warning',
        ]);

        return back()->with('success', 'Utilisateur muté avec succès.');
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

        $commentContent = $comment->content;
        $postTitle = $comment->post?->title ?? 'un article';
        $postUrl = $comment->post ? route('posts.show', $comment->post) : '#';

        $comment->update(['is_deleted' => true, 'content' => '']);

        if ($comment->user_id) {
            UserNotification::create([
                'user_id' => $comment->user_id,
                'title'   => 'Votre commentaire a été supprimé',
                'message' => 'Votre commentaire "' . \Illuminate\Support\Str::limit($commentContent, 100) . '" posté sur l\'article <a href="' . $postUrl . '">' . e($postTitle) . '</a> a été supprimé par un administrateur.',
                'type'    => 'warning',
            ]);
        }

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

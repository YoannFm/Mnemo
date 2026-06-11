<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\AdminSanction;
use App\Models\ModuleRatingDeletion;
use App\Models\ModuleRatingReport;
use App\Models\ModuleRatingReplyReport;
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
        $type         = $request->input('type', 'comments');
        $statusFilter = $request->input('status', 'pending');

        $commentQuery = PostCommentReport::with(['user', 'postComment.user', 'postComment.post'])
            ->orderBy('created_at', 'desc');
        if ($statusFilter !== 'all') {
            $commentQuery->where('status', $statusFilter);
        }
        $commentReports = $commentQuery->paginate(25, ['*'], 'comments_page')->withQueryString();

        $moduleQuery = ModuleReport::with(['user', 'module.owner'])
            ->orderBy('created_at', 'desc');
        if ($statusFilter !== 'all') {
            $moduleQuery->where('status', $statusFilter);
        }
        $moduleReports = $moduleQuery->paginate(25, ['*'], 'modules_page')->withQueryString();

        $ratingQuery = ModuleRatingReport::with(['user', 'moduleRating.user', 'moduleRating.module'])
            ->orderBy('created_at', 'desc');
        if ($statusFilter !== 'all') {
            $ratingQuery->where('status', $statusFilter);
        }
        $ratingReports = $ratingQuery->paginate(25, ['*'], 'ratings_page')->withQueryString();

        $replyQuery = ModuleRatingReplyReport::with(['user', 'reply.user', 'reply.moduleRating.module'])
            ->orderBy('created_at', 'desc');
        if ($statusFilter !== 'all') {
            $replyQuery->where('status', $statusFilter);
        }
        $replyReports = $replyQuery->paginate(25, ['*'], 'replies_page')->withQueryString();

        // Counts per status for filter buttons
        $statusCounts = [
            'pending'  => PostCommentReport::where('status', 'pending')->count()
                        + ModuleReport::where('status', 'pending')->count()
                        + ModuleRatingReport::where('status', 'pending')->count()
                        + ModuleRatingReplyReport::where('status', 'pending')->count(),
            'treated'  => PostCommentReport::whereIn('status', ['treated', 'sanctioned', 'unsanctioned'])->count()
                        + ModuleReport::where('status', 'treated')->count()
                        + ModuleRatingReport::where('status', 'treated')->count()
                        + ModuleRatingReplyReport::where('status', 'treated')->count(),
            'rejected' => PostCommentReport::where('status', 'rejected')->count()
                        + ModuleReport::where('status', 'rejected')->count()
                        + ModuleRatingReport::where('status', 'rejected')->count()
                        + ModuleRatingReplyReport::where('status', 'rejected')->count(),
        ];

        return view('admin.reports.index', compact('commentReports', 'moduleReports', 'ratingReports', 'replyReports', 'type', 'statusFilter', 'statusCounts'));
    }

    public function markModuleTreated(ModuleReport $report)
    {
        $report->update(['status' => 'treated']);
        LogHelper::log('treated_module_report', 'module_report', $report->id, ['module_id' => $report->module_id]);
        return back()->with('success', 'Signalement module marqué comme traité.');
    }

    public function markModuleRejected(ModuleReport $report)
    {
        $report->update(['status' => 'rejected']);
        LogHelper::log('rejected_module_report', 'module_report', $report->id, ['module_id' => $report->module_id]);
        return back()->with('success', 'Signalement module rejeté.');
    }

    public function markReplyTreated(ModuleRatingReplyReport $report)
    {
        $report->update(['status' => 'treated']);
        LogHelper::log('treated_reply_report', 'reply_report', $report->id);
        return back()->with('success', 'Signalement marqué comme traité.');
    }

    public function markReplyRejected(ModuleRatingReplyReport $report)
    {
        $report->update(['status' => 'rejected']);
        LogHelper::log('rejected_reply_report', 'reply_report', $report->id);
        return back()->with('success', 'Signalement rejeté.');
    }

    public function deleteReply(Request $request, ModuleRatingReplyReport $report)
    {
        $reply = $report->reply;

        if (!$reply) {
            return back()->with('error', 'Réponse introuvable.');
        }

        ModuleRatingDeletion::create([
            'user_id'    => $reply->user_id,
            'module_id'  => $reply->moduleRating?->module_id,
            'type'       => 'reply',
            'content'    => $reply->content,
            'deleted_by' => 'admin',
        ]);

        LogHelper::log('deleted_reply', 'reply', $reply->id, ['content' => substr($reply->content, 0, 100), 'user_id' => $reply->user_id], 'warning');

        $userId = $reply->user_id;
        $reply->delete();
        $report->update(['status' => 'treated']);

        if ($userId) {
            UserNotification::create([
                'user_id' => $userId,
                'title'   => 'Votre réponse a été supprimée',
                'message' => 'Votre réponse à un avis a été supprimée par un administrateur.',
                'type'    => 'warning',
            ]);
        }

        return back()->with('success', 'Réponse supprimée.');
    }

    public function muteReplyUser(Request $request, ModuleRatingReplyReport $report)
    {
        $request->validate([
            'reason'             => 'required|string|max:500',
            'duration_value'     => 'nullable|integer|min:1',
            'duration_unit'      => 'nullable|in:minutes,hours,days,weeks,months',
            'duration_permanent' => 'nullable',
        ]);

        $replyUser = $report->reply?->user;

        if (!$replyUser) {
            return back()->with('error', 'Utilisateur introuvable.');
        }

        $permanent = $request->boolean('duration_permanent');
        $expiresAt = $permanent ? null : now()->add(
            $request->input('duration_unit', 'days'),
            (int) $request->input('duration_value', 1)
        );

        Mute::create([
            'user_id'    => $replyUser->id,
            'admin_id'   => Auth::id(),
            'reason'     => $request->input('reason'),
            'expires_at' => $expiresAt,
        ]);

        AdminSanction::create([
            'user_id'  => $replyUser->id,
            'admin_id' => Auth::id(),
            'reason'   => $request->input('reason'),
            'type'     => 'mute',
        ]);

        UserNotification::create([
            'user_id' => $replyUser->id,
            'title'   => 'Vous avez été rendu muet',
            'message' => 'Vous avez été rendu muet pour la raison suivante : ' . $request->input('reason'),
            'type'    => 'warning',
        ]);

        LogHelper::log('muted_user', 'user', $replyUser->id, ['reason' => $request->input('reason'), 'expires_at' => $expiresAt], 'warning');

        return back()->with('success', 'Utilisateur muté avec succès.');
    }

    public function markRatingTreated(ModuleRatingReport $report)
    {
        $report->update(['status' => 'treated']);
        LogHelper::log('treated_rating_report', 'rating_report', $report->id);
        return back()->with('success', 'Signalement marqué comme traité.');
    }

    public function markRatingRejected(ModuleRatingReport $report)
    {
        $report->update(['status' => 'rejected']);
        LogHelper::log('rejected_rating_report', 'rating_report', $report->id);
        return back()->with('success', 'Signalement rejeté.');
    }

    public function deleteRating(Request $request, ModuleRatingReport $report)
    {
        $rating = $report->moduleRating;

        if (!$rating) {
            return back()->with('error', 'Avis introuvable.');
        }

        LogHelper::log('deleted_rating', 'rating', $rating->id, ['module_id' => $rating->module_id, 'user_id' => $rating->user_id, 'rating' => $rating->rating, 'comment' => substr($rating->comment ?? '', 0, 100)], 'warning');

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

        LogHelper::log('muted_user', 'user', $ratingUser->id, ['reason' => $request->input('reason'), 'expires_at' => $expiresAt], 'warning');

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

        LogHelper::log('sanctioned_comment', 'comment', $report->id, null, 'warning');

        return back()->with('success', 'Signalement marqué comme sanctionné.');
    }

    public function markUnsanctioned(PostCommentReport $report)
    {
        $report->update(['status' => 'unsanctioned']);
        LogHelper::log('unsanctioned_comment', 'comment', $report->id);
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

        LogHelper::log('deleted_comment', 'comment', $comment->id, ['content' => substr($commentContent, 0, 100), 'post_title' => $postTitle], 'warning');

        if ($comment->user_id) {
            UserNotification::create([
                'user_id' => $comment->user_id,
                'title'   => 'Votre commentaire a été supprimé',
                'message' => 'Votre commentaire "' . e(\Illuminate\Support\Str::limit($commentContent, 100)) . '" posté sur l\'article <a href="' . e($postUrl) . '">' . e($postTitle) . '</a> a été supprimé par un administrateur.',
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

        LogHelper::log('muted_user', 'user', $comment->user->id, ['reason' => $request->input('reason'), 'expires_at' => $expiresAt], 'warning');

        return redirect()->route('admin.reports.index')->with('success', 'Utilisateur muté avec succès.');
    }
}

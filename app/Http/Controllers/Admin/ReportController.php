<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSanction;
use App\Models\PostCommentReport;
use Illuminate\Support\Facades\Auth;

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
}

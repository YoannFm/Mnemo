<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostCommentReport;

class ReportController extends Controller
{
    public function index()
    {
        $reports = PostCommentReport::with(['user', 'postComment.user', 'postComment.post'])
            ->orderBy('created_at', 'desc')
            ->paginate(25);
        return view('admin.reports.index', compact('reports'));
    }

    public function dismiss(PostCommentReport $report)
    {
        $report->update(['status' => 'dismissed']);
        return back()->with('success', 'Signalement ignoré.');
    }

    public function review(PostCommentReport $report)
    {
        $report->update(['status' => 'reviewed']);
        return back()->with('success', 'Signalement marqué comme traité.');
    }
}

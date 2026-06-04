<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostCommentHistory;

class CommentHistoryController extends Controller
{
    public function index()
    {
        $entries = PostCommentHistory::with(['postComment.user', 'postComment.post'])
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('admin.comment-history.index', compact('entries'));
    }
}

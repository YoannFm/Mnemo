<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Item;
use App\Models\Module;
use App\Models\ModuleRating;
use App\Models\ModuleRatingReport;
use App\Models\ModuleRatingReplyReport;
use App\Models\ModuleReport;
use App\Models\Mute;
use App\Models\PostCommentReport;
use App\Models\Progress;
use App\Models\Score;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'users'           => User::count(),
            'modules'         => Module::count(),
            'items'           => Item::count(),
            'tests'           => Score::count(),
            'progress'        => Progress::count(),
            'public_modules'  => Module::where('is_public', true)->count(),
            'private_modules' => Module::where('is_public', false)->count(),
            'ratings'         => ModuleRating::count(),
            'pending_reports' => ModuleReport::where('status', 'pending')->count()
                               + PostCommentReport::where('status', 'pending')->count()
                               + ModuleRatingReport::where('status', 'pending')->count()
                               + ModuleRatingReplyReport::where('status', 'pending')->count(),
            'active_mutes'    => Mute::where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })->count(),
        ];

        $recentLogs  = ActivityLog::with('user')->latest()->take(5)->get();
        $latestUsers = User::latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'latestUsers', 'recentLogs'));
    }
}

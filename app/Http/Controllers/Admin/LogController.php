<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;

class LogController extends Controller
{
    public function index()
    {
        $logs = ActivityLog::with('user')->latest('created_at')->paginate(50);
        return view('admin.logs.index', compact('logs'));
    }

    public function show(ActivityLog $log)
    {
        $log->load('user');
        return view('admin.logs.show', compact('log'));
    }

    public function clear()
    {
        ActivityLog::truncate();
        return redirect()->route('admin.logs.index')->with('success', 'Logs effacés.');
    }
}

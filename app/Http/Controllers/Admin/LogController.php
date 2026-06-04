<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest('created_at');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%");
            });
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($level = $request->input('level')) {
            $query->where('level', $level);
        }

        $logs = $query->paginate(25)->withQueryString();
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
        return redirect()->route('admin.logs.index')->with('success', 'Logs effaces.');
    }

    public function purge()
    {
        $count = ActivityLog::where('created_at', '<', now()->subDays(30))->count();
        ActivityLog::where('created_at', '<', now()->subDays(30))->delete();
        return redirect()->route('admin.logs.index')->with('success', "{$count} log(s) de plus de 30 jours supprimés.");
    }
}

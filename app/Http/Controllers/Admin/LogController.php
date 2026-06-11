<?php
namespace App\Http\Controllers\Admin;

use App\Helpers\LogHelper;
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

        if ($targetType = $request->input('target_type')) {
            $query->where('target_type', $targetType);
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        $logs = $query->paginate(25)->withQueryString();

        $targetTypes = ActivityLog::whereNotNull('target_type')
            ->distinct()
            ->pluck('target_type')
            ->sort()
            ->values();

        $admins = \App\Models\User::whereIn('id',
            ActivityLog::whereNotNull('user_id')->distinct()->pluck('user_id')
        )->orderBy('name')->get(['id', 'name']);

        return view('admin.logs.index', compact('logs', 'targetTypes', 'admins'));
    }

    public function show(ActivityLog $log)
    {
        $log->load('user');
        return view('admin.logs.show', compact('log'));
    }

    public function clear()
    {
        $count = ActivityLog::count();
        ActivityLog::truncate();
        LogHelper::log('cleared_all_logs', 'log', null, ['deleted_count' => $count], 'warning');
        return redirect()->route('admin.logs.index')->with('success', 'Logs effaces.');
    }

    public function purge()
    {
        $count = ActivityLog::where('created_at', '<', now()->subDays(30))->count();
        ActivityLog::where('created_at', '<', now()->subDays(30))->delete();
        LogHelper::log('purged_logs', 'log', null, ['deleted_count' => $count], 'warning');
        return redirect()->route('admin.logs.index')->with('success', "{$count} log(s) de plus de 30 jours supprimés.");
    }
}

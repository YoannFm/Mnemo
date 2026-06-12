<?php
namespace App\Http\Controllers\Admin;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

    public function export(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
            'format'   => 'required|in:csv,json',
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return back()->withErrors(['password' => 'Mot de passe incorrect.'])->withInput();
        }

        $logs = ActivityLog::with('user')->orderBy('created_at', 'desc')->get();
        $format = $request->format;
        $filename = 'logs_' . now()->format('Y-m-d_H-i-s');

        if ($format === 'csv') {
            $content = $this->generateCsv($logs);
        } else {
            $content = json_encode($logs->map(fn($l) => [
                'id'          => $l->id,
                'user'        => $l->user?->name,
                'action'      => $l->action,
                'target_type' => $l->target_type,
                'target_id'   => $l->target_id,
                'data'        => $l->data,
                'level'       => $l->level,
                'old_value'   => $l->old_value,
                'new_value'   => $l->new_value,
                'created_at'  => $l->created_at?->format('Y-m-d H:i:s'),
            ])->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        $zipPath = storage_path('app/temp/' . $filename . '.zip');
        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE);
        $zip->addFromString('logs/latest.' . $format, $content);

        // Include related files (update archives, backups)
        foreach (['app/updates', 'app/backups'] as $dir) {
            $fullDir = storage_path($dir);
            if (is_dir($fullDir)) {
                foreach (new \FilesystemIterator($fullDir, \FilesystemIterator::SKIP_DOTS) as $file) {
                    if ($file->isFile()) {
                        $zip->addFile($file->getPathname(), 'logs/files/' . $file->getFilename());
                    }
                }
            }
        }

        $zip->close();

        LogHelper::log('exported_logs', 'log', null, ['format' => $format, 'count' => $logs->count()]);

        return response()->download($zipPath, $filename . '.zip')->deleteFileAfterSend(true);
    }

    private function generateCsv($logs): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['ID', 'Utilisateur', 'Action', 'Cible', 'ID Cible', 'Données', 'Niveau', 'Valeur avant', 'Valeur après', 'Date']);
        foreach ($logs as $log) {
            fputcsv($handle, [
                $log->id,
                $log->user?->name ?? 'Système',
                $log->action,
                $log->target_type,
                $log->target_id,
                json_encode($log->data),
                $log->level,
                $log->old_value,
                $log->new_value,
                $log->created_at?->format('Y-m-d H:i:s'),
            ]);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        return $csv;
    }
}

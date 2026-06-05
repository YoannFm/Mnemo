<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\ModuleReport;

class ModuleReportController extends Controller
{
    public function index()
    {
        $reports = ModuleReport::with(['user', 'module.owner'])
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('admin.module-reports.index', compact('reports'));
    }

    public function markTreated(ModuleReport $report)
    {
        $report->update(['status' => 'treated']);
        LogHelper::log('treated_report', 'module', $report->module_id, ['report_id' => $report->id]);
        return back()->with('success', 'Signalement marqué comme traité.');
    }

    public function markRejected(ModuleReport $report)
    {
        $report->update(['status' => 'rejected']);
        LogHelper::log('rejected_report', 'module', $report->module_id, ['report_id' => $report->id]);
        return back()->with('success', 'Signalement marqué comme rejeté.');
    }
}

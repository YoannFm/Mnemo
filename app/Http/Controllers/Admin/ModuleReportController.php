<?php

namespace App\Http\Controllers\Admin;

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
        return back()->with('success', 'Signalement marqué comme traité.');
    }

    public function markRejected(ModuleReport $report)
    {
        $report->update(['status' => 'rejected']);
        return back()->with('success', 'Signalement marqué comme rejeté.');
    }
}

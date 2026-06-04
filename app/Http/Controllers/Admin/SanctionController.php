<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSanction;

class SanctionController extends Controller
{
    public function index()
    {
        $sanctions = AdminSanction::with(['user', 'admin'])
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('admin.sanctions.index', compact('sanctions'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Module;
use App\Models\Progress;
use App\Models\Score;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'users'    => User::count(),
            'modules'  => Module::count(),
            'items'    => Item::count(),
            'tests'    => Score::count(),
            'progress' => Progress::count(),
        ];

        $latestUsers = User::latest()->take(10)->get();

        return view('admin.dashboard', compact('stats', 'latestUsers'));
    }
}

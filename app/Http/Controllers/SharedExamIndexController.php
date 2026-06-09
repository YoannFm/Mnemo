<?php

namespace App\Http\Controllers;

use App\Models\SharedExam;
use Illuminate\Support\Facades\Auth;

class SharedExamIndexController extends Controller
{
    public function index()
    {
        $sharedExams = SharedExam::where('user_id', Auth::id())
            ->with('module')
            ->withCount('attempts')
            ->latest()
            ->get();

        return view('shared-exam.index', compact('sharedExams'));
    }
}

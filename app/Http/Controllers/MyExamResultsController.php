<?php

namespace App\Http\Controllers;

use App\Models\SharedExamAttempt;
use Illuminate\Support\Facades\Auth;

class MyExamResultsController extends Controller
{
    public function index()
    {
        $attempts = SharedExamAttempt::where('user_id', Auth::id())
            ->with('sharedExam.module', 'sharedExam.user')
            ->whereNotNull('finished_at')
            ->latest('finished_at')
            ->get();

        return view('shared-exam.my-results', compact('attempts'));
    }
}

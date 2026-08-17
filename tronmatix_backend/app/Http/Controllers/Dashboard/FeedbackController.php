<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(): View
    {
        $feedbacks = Feedback::latest()->get();
        return view('dashboard.feedback.list', compact('feedbacks'));
    }
}

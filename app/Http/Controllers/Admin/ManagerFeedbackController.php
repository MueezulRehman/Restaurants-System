<?php

namespace App\Http\Controllers\Admin;

use App\Models\ManagerFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class ManagerFeedbackController extends Controller
{
    /**
     * Show manager's own feedback submissions
     */
    public function index()
    {
        $user = Auth::user();

        $feedbacks = ManagerFeedback::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.feedback.manager-index', compact('feedbacks'));
    }

    /**
     * Submit new feedback/suggestion to super admin
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $restaurant = $user->effectiveRestaurant();

        $validated = $request->validate([
            'type' => 'required|in:bug_report,feature_request,complaint,general',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        $feedback = ManagerFeedback::create([
            'restaurant_id' => $restaurant->id,
            'user_id' => $user->id,
            'type' => $validated['type'],
            'title' => $validated['title'],
            'message' => $validated['message'],
            'status' => 'new',
        ]);

        return redirect()->route('manager.feedback.index')
            ->with('success', 'Your feedback has been submitted to the platform administrator.');
    }

    /**
     * View feedback detail and any admin reply
     */
    public function show(ManagerFeedback $feedback)
    {
        $user = Auth::user();

        abort_unless($feedback->user_id === $user->id, 403);

        if ($feedback->status === 'new') {
            $feedback->update(['status' => 'reviewing']);
        }

        return view('admin.feedback.manager-show', compact('feedback'));
    }
}

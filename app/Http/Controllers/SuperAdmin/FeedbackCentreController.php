<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\ManagerFeedback;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FeedbackCentreController extends Controller
{
    /**
     * Show all feedback from all managers (super admin only)
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'new');

        $feedbacks = ManagerFeedback::when($status !== 'all', function ($query) use ($status) {
            $query->where('status', $status);
        })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('super-admin.feedback-centre.index', compact('feedbacks', 'status'));
    }

    /**
     * View feedback detail and reply
     */
    public function show(ManagerFeedback $feedback)
    {
        return view('super-admin.feedback-centre.show', compact('feedback'));
    }

    /**
     * Send admin reply to manager feedback
     */
    public function reply(Request $request, ManagerFeedback $feedback)
    {
        $validated = $request->validate([
            'admin_reply' => 'required|string|max:2000',
        ]);

        $feedback->update([
            'admin_reply' => $validated['admin_reply'],
            'replied_at' => now(),
            'status' => 'resolved',
        ]);

        return redirect()->back()->with('success', 'Reply sent to manager.');
    }

    /**
     * Mark feedback as resolved/closed
     */
    public function updateStatus(Request $request, ManagerFeedback $feedback)
    {
        $status = $request->validate(['status' => 'required|in:reviewing,resolved,closed'])['status'];

        $feedback->update(['status' => $status]);

        return back()->with('success', 'Feedback status updated.');
    }
}

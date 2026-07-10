<?php

namespace App\Http\Controllers\Admin;

use App\Models\Feedback;
use App\Models\FeedbackReply;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    use AuthorizesRequests;

    /**
     * Show all feedback for restaurant (manager/admin view).
     */
    public function index()
    {
        $restaurantId = auth()->user()->restaurant_id;
        $filter = request('filter', 'open');

        $query = Feedback::where('restaurant_id', $restaurantId);

        if ($filter !== 'all') {
            $query->where('status', $filter);
        }

        $feedback = $query->orderBy('created_at', 'desc')
            ->with('customer', 'replies')
            ->paginate(20);

        $stats = [
            'open' => Feedback::where('restaurant_id', $restaurantId)->where('status', 'open')->count(),
            'in_progress' => Feedback::where('restaurant_id', $restaurantId)->where('status', 'in_progress')->count(),
            'resolved' => Feedback::where('restaurant_id', $restaurantId)->where('status', 'resolved')->count(),
            'total' => Feedback::where('restaurant_id', $restaurantId)->count(),
        ];

        return view('admin.feedback.index', compact('feedback', 'filter', 'stats'));
    }

    /**
     * Show feedback details (manager/admin view).
     */
    public function show(Feedback $feedback)
    {
        $this->authorize('view', $feedback);

        return view('admin.feedback.show', compact('feedback'));
    }

    /**
     * Add a reply to feedback.
     */
    public function reply(Request $request, Feedback $feedback)
    {
        $this->authorize('update', $feedback);

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'is_internal' => 'boolean',
        ]);

        FeedbackReply::create([
            'feedback_id' => $feedback->id,
            'user_id' => auth()->id(),
            'message' => $validated['message'],
            'is_internal' => $validated['is_internal'] ?? false,
        ]);

        // Update feedback status to in_progress if it was open
        if ($feedback->status === 'open') {
            $feedback->update(['status' => 'in_progress']);
        }

        return redirect()->route('admin.feedback.show', $feedback)
            ->with('success', 'Reply added successfully.');
    }

    /**
     * Update feedback status.
     */
    public function updateStatus(Request $request, Feedback $feedback)
    {
        $this->authorize('update', $feedback);

        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $feedback->update($validated);

        if ($validated['status'] === 'resolved') {
            $feedback->resolve();
        }

        return redirect()->route('admin.feedback.show', $feedback)
            ->with('success', 'Feedback status updated.');
    }

    /**
     * Delete feedback (admin only).
     */
    public function destroy(Feedback $feedback)
    {
        $this->authorize('delete', $feedback);

        $feedback->delete();

        return redirect()->route('admin.feedback.index')
            ->with('success', 'Feedback deleted successfully.');
    }
}

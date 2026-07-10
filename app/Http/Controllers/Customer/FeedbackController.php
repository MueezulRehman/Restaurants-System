<?php

namespace App\Http\Controllers\Customer;

use App\Models\Feedback;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    use AuthorizesRequests;

    /**
     * Show customer feedback form.
     */
    public function create()
    {
        return view('customer.feedback.create');
    }

    /**
     * Store customer feedback.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:suggestion,complaint,praise,bug_report',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        $restaurantId = session('current_restaurant_id')
            ?? session('restaurant_id')
            ?? null;

        if (! $restaurantId) {
            $restaurantId = app('restaurant')?->id ?? 1;
        }

        $feedback = Feedback::create([
            'restaurant_id' => $restaurantId,
            'customer_id' => auth('customer')->id(),
            'type' => $validated['type'],
            'title' => $validated['title'],
            'message' => $validated['message'],
            'rating' => $validated['rating'] ?? null,
            'status' => 'open',
        ]);

        return redirect()->route('customer.feedback.show', $feedback)
            ->with('success', 'Your feedback has been submitted. Thank you!');
    }

    /**
     * Show feedback details (customer view).
     */
    public function show(Feedback $feedback)
    {
        $this->authorize('view', $feedback);

        return view('customer.feedback.show', compact('feedback'));
    }

    /**
     * List customer's own feedback.
     */
    public function index()
    {
        $feedback = Feedback::where('customer_id', auth('customer')->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('customer.feedback.index', compact('feedback'));
    }
}

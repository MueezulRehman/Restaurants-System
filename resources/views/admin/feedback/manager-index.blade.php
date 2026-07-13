@extends('layouts.app')

@section('content')
<div class="container">
    <h1>My Feedback</h1>

    <form action="{{ route('manager.feedback.store') }}" method="POST">
        @csrf
        <label>Type</label>
        <select name="type">
            <option value="bug_report">Bug Report</option>
            <option value="feature_request">Feature Request</option>
            <option value="complaint">Complaint</option>
            <option value="general">General</option>
        </select>
        <label>Title</label>
        <input type="text" name="title" required>
        <label>Message</label>
        <textarea name="message" required></textarea>
        <button type="submit">Send Feedback</button>
    </form>

    <h2>Previous Feedback</h2>
    <ul>
        @foreach($feedbacks as $feedback)
            <li><a href="{{ route('manager.feedback.show', $feedback) }}">{{ $feedback->title }}</a> — {{ $feedback->status }}</li>
        @endforeach
    </ul>
</div>
@endsection

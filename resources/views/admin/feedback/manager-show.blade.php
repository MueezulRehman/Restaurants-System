@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ $feedback->title }}</h1>
    <p>{{ $feedback->message }}</p>
    <p>Status: {{ $feedback->status }}</p>
    @if($feedback->admin_reply)
        <div>
            <h2>Reply from admin</h2>
            <p>{{ $feedback->admin_reply }}</p>
        </div>
    @endif
</div>
@endsection

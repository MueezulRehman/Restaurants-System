@props(['validation' => true])

@if(session('success'))
    <div class="page-alert page-alert--success" role="status">
        <i class="fas fa-circle-check" aria-hidden="true"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="page-alert page-alert--error" role="alert">
        <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

@if($validation && $errors->any())
    <div class="page-alert page-alert--error" role="alert">
        <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
        <div>
            <p class="font-semibold">Please fix the following errors:</p>
            <ul class="mt-1 list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

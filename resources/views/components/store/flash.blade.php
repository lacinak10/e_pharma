@php
    $success = session('success');
    $error = session('error');
@endphp

<div class="max-w-7xl mx-auto px-4 mt-4 space-y-3">
    @if($success)
        <div class="rounded-2xl border border-green-200 bg-green-50 p-4 text-green-800 flex items-start gap-3">
            <i class="fa-regular fa-circle-check mt-0.5"></i>
            <div class="text-sm font-medium">{{ $success }}</div>
        </div>
    @endif

    @if($error)
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800 flex items-start gap-3">
            <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
            <div class="text-sm font-medium">{{ $error }}</div>
        </div>
    @endif
</div>

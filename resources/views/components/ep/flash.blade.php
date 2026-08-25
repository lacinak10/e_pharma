@if(session('success') || session('error') || session('info') || $errors->any())
    <div {{ $attributes->merge(['class' => 'ep-stack']) }} style="gap:.75rem" role="status" aria-live="polite">
        @if(session('success'))
            <p class="ep-flash ep-flash--success">{{ session('success') }}</p>
        @endif

        @if(session('error'))
            <p class="ep-flash ep-flash--error">{{ session('error') }}</p>
        @endif

        @if(session('info'))
            <p class="ep-flash ep-flash--info">{{ session('info') }}</p>
        @endif

        @if($errors->any() && ! $errors->hasBag('default'))
            <div class="ep-flash ep-flash--error">
                <ul style="margin:0;padding-left:1.125rem">
                    @foreach($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endif

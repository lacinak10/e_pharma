@props(['order'])

@php
    /** @var \App\Models\Order $order */
    $steps = $order->deliveryTimeline();
@endphp

<ol {{ $attributes->merge(['class' => 'ep-timeline']) }} style="list-style:none;margin:0;padding:0">
    @foreach($steps as $step)
        <li class="ep-timeline__step ep-timeline__step--{{ $step['state'] }}">
            <span class="ep-timeline__rail" aria-hidden="true">
                <span class="ep-timeline__dot"></span>
                <span class="ep-timeline__line"></span>
            </span>
            <div class="ep-timeline__content">
                <p class="ep-timeline__label">{{ $step['label'] }}</p>
                <p class="ep-timeline__sub">{{ $step['sub'] }}</p>
            </div>
            <span class="ep-timeline__time">{{ $step['time'] }}</span>
        </li>
    @endforeach
</ol>

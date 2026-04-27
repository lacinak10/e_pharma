@props(['header' => null])

@extends('layouts.store')

@section('title', strip_tags((string) ($header ?? 'Mon profil')) . ' — E-PHARMA')

@section('content')
<section class="max-w-4xl mx-auto px-4 py-10">
    @if($header)
        <div class="mb-6">
            {{ $header }}
        </div>
    @endif
    {{ $slot }}
</section>
@endsection

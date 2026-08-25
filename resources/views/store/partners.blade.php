@extends('layouts.store')

@section('title', 'Nos pharmacies partenaires — ePharma')

@section('content')
    <section class="ep-shell ep-section">
        <p class="ep-eyebrow ep-eyebrow--green">Nos pharmacies partenaires</p>
        <h1 class="ep-h2" style="margin-top:.75rem">
            {{ $pharmacies->count() }} pharmacies, {{ $areas->count() }} quartiers couverts
        </h1>
        <p class="ep-lead" style="max-width:56ch;margin-top:1rem">
            Ce sont elles que nous appelons, une par une, pour confirmer la disponibilité réelle
            de votre médicament avant qu'un livreur ne se mette en route.
        </p>
    </section>

    <section class="ep-shell" style="padding-bottom:clamp(2.5rem,1.5rem + 4vw,5rem)">
        <div class="ep-grid ep-grid--wide">
            @forelse($pharmacies as $pharmacy)
                <article class="ep-card">
                    <img src="{{ asset('assets/images/photos/pharmacie.jpg') }}" alt=""
                         style="width:100%;height:96px;object-fit:cover;border-bottom:1px solid var(--ep-rule-soft)" loading="lazy">
                    <div class="ep-card__body">
                        <h2 style="font-size:.9375rem;font-weight:650;margin:0">{{ $pharmacy->name }}</h2>
                        <p class="ep-small" style="margin:.1875rem 0 0">{{ $pharmacy->area_label }}</p>
                        <p class="ep-mono ep-small" style="display:flex;align-items:center;gap:.5rem;margin:.75rem 0 0;color:var(--ep-green-dark)">
                            <span style="width:6px;height:6px;border-radius:50%;background:var(--ep-green)"></span>
                            {{ $pharmacy->response_label }}
                        </p>
                        <a href="tel:{{ preg_replace('/\s+/', '', $pharmacy->phone) }}" class="ep-mono ep-small">{{ $pharmacy->phone }}</a>
                    </div>
                </article>
            @empty
                <x-ep.empty title="Aucune pharmacie partenaire pour l'instant."
                            text="Le réseau est en cours de constitution." />
            @endforelse
        </div>
    </section>
@endsection

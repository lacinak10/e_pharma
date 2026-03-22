@extends('layouts.store')

@section('title', 'Envoyer ordonnance — E-PHARMA')

@section('content')
<section class="max-w-4xl mx-auto px-4 py-10">
    <h1 class="text-3xl font-extrabold">Télécharger votre ordonnance</h1>
    <p class="text-gray-600 mt-1">PNG, JPG ou PDF — max 4MB.</p>

    <div class="mt-8 bg-white rounded-3xl border border-gray-100 shadow-sm p-8">
        <form method="POST" action="{{ route('store.prescriptions.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="border-2 border-dashed border-gray-200 rounded-3xl p-10 text-center hover:border-blue-300 hover:bg-blue-50/30 transition">
                <i class="fa-regular fa-file-lines text-4xl text-gray-400"></i>
                <div class="mt-4 font-bold">Glisser-déposer ou choisir un fichier</div>
                <p class="text-sm text-gray-600 mt-1">Nous contacterons si besoin de précision.</p>

                <div class="mt-6">
                    <input type="file" name="prescription" disabled
                        class="block w-full text-sm text-gray-400 cursor-not-allowed
                        file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0
                        file:bg-gray-300 file:text-white file:font-semibold">

                    <p class="text-sm text-red-600 mt-3">
                        ⚠️ L’envoi d’ordonnance est temporairement indisponible.
                    </p>
                </div>

                @error('prescription')
                    <div class="text-sm text-red-600 mt-3">{{ $message }}</div>
                @enderror
            </div>

            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <x-store.button type="submit" variant="primary" class="w-full sm:w-auto" disabled>
    <i class="fa-solid fa-cloud-arrow-up"></i> Envoyer
</x-store.button>
                <a href="{{ route('store.medicines.index') }}" class="w-full sm:w-auto">
                    <x-store.button type="button" variant="outline" class="w-full">
                        Aller au catalogue
                    </x-store.button>
                </a>
            </div>

            @if(session('prescription_path'))
                <div class="mt-6 text-sm text-gray-600">
                    Fichier stocké: <b>{{ session('prescription_path') }}</b>
                    <div class="text-xs text-gray-400 mt-1">Astuce: exécute <code>php artisan storage:link</code> si tu veux servir les fichiers.</div>
                </div>
            @endif
        </form>
    </div>
</section>
@endsection

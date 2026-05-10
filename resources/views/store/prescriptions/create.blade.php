@extends('layouts.store')

@section('title', 'Commander par ordonnance — E-PHARMA')

@section('content')
<section class="max-w-7xl mx-auto px-4 py-10">
    <h1 class="text-3xl font-extrabold">Commander par ordonnance</h1>
    <p class="text-gray-600 mt-1">Envoyez votre ordonnance et nous préparons votre commande.</p>

    {{-- Info box --}}
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-2xl p-5 text-sm text-blue-900">
        <i class="fa-solid fa-circle-info mr-2"></i>
        <strong>Information :</strong> Le montant des médicaments sera déterminé après examen de votre ordonnance.
        Seuls les frais de livraison (<strong>1 500 FCFA</strong>) sont facturés à l'avance.
    </div>

    <form class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6"
          method="POST"
          action="{{ route('store.prescriptions.store') }}"
          enctype="multipart/form-data">
        @csrf

        <div class="lg:col-span-2 space-y-6">

            {{-- Ordonnance --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-lg font-extrabold">Ordonnance</h3>

                <div class="mt-4 border-2 border-dashed border-gray-200 rounded-2xl p-8 text-center hover:border-blue-300 hover:bg-blue-50/30 transition">
                    <i class="fa-regular fa-file-medical text-4xl text-gray-400"></i>
                    <div class="mt-4 font-bold text-gray-700">Glisser-déposer ou choisir un fichier</div>
                    <p class="text-sm text-gray-500 mt-1">PNG, JPG ou PDF — max 4 Mo</p>

                    <div class="mt-5">
                        <input type="file" name="prescription" id="prescription" accept=".jpg,.jpeg,.png,.pdf"
                            class="block w-full text-sm text-gray-600
                            file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0
                            file:bg-blue-600 file:text-white file:font-semibold
                            hover:file:bg-blue-700 cursor-pointer">
                    </div>

                    @error('prescription')
                        <div class="text-sm text-red-600 mt-3">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Livraison --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-lg font-extrabold">Livraison</h3>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-store.input name="delivery_phone" label="Téléphone" placeholder="Ex : 0505050505" value="{{ old('delivery_phone') }}" />
                    <x-store.input name="delivery_address" label="Adresse" placeholder="Ex: Cocody Angré, Rue..." value="{{ old('delivery_address') }}" />
                </div>
                <div class="mt-4">
                    <label class="block">
                        <span class="block text-sm font-semibold text-gray-700 mb-1">Note (optionnel)</span>
                        <textarea name="notes" rows="3"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-blue-600 focus:border-transparent">{{ old('notes') }}</textarea>
                        @error('notes') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </label>
                </div>
            </div>

            {{-- Paiement --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-lg font-extrabold">Paiement</h3>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                    <label class="p-4 rounded-2xl border border-gray-200 hover:border-blue-300 cursor-pointer">
                        <input type="radio" name="payment_method" value="cash" class="mr-2" checked>
                        Espèces
                    </label>
                    <label class="p-4 rounded-2xl border border-gray-200 cursor-not-allowed opacity-50">
                        <input type="radio" name="payment_method" value="momo" class="mr-2" disabled>
                        Mobile Money
                    </label>
                    <label class="p-4 rounded-2xl border border-gray-200 cursor-not-allowed opacity-50">
                        <input type="radio" name="payment_method" value="card" class="mr-2" disabled>
                        Carte
                    </label>
                </div>
                @error('payment_method') <span class="text-sm text-red-600 mt-2 block">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Colonne droite --}}
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-lg font-extrabold">Récapitulatif</h3>

                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Médicaments (après examen)</span>
                        <span class="font-semibold text-gray-400">À définir</span>
                    </div>
                    <div class="border-t border-gray-100 pt-3 space-y-2">
                        <div class="flex justify-between text-gray-600">
                            <span>Livraison</span>
                            <span class="font-semibold text-gray-900">1 500 FCFA</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-bold text-gray-900">Payable maintenant</span>
                            <span class="font-extrabold text-blue-700">1 500 FCFA</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <x-store.button type="submit" variant="primary" class="w-full">
                        <i class="fa-solid fa-cloud-arrow-up"></i> Envoyer et commander
                    </x-store.button>
                    <p class="text-xs text-gray-500 mt-2 text-center">
                        Nous vous contacterons si nous avons besoin de précisions.
                    </p>
                </div>
            </div>

            <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5 text-sm text-blue-900">
                <b>Conseil</b> : assurez-vous que l'ordonnance est lisible et que tous les médicaments y figurent clairement.
            </div>

            <div class="text-center">
                <a href="{{ route('store.medicines.index') }}" class="text-sm text-blue-700 font-semibold hover:underline">
                    ← Aller au catalogue
                </a>
            </div>
        </div>
    </form>
</section>
@endsection

<footer class="bg-gray-900 text-white mt-12">
    <div class="max-w-7xl mx-auto px-4 py-12 grid grid-cols-1 md:grid-cols-4 gap-8">
        <div>
            <div class="flex items-center gap-2 mb-4">
                <i class="fa-solid fa-pills text-blue-400 text-2xl"></i>
                <h3 class="text-xl font-bold">E-PHARMA</h3>
            </div>
            <p class="text-gray-400">Vos médicaments livrés rapidement, partout en Côte d’Ivoire.</p>
        </div>

        <div>
            <h4 class="font-semibold mb-3">Liens</h4>
            <ul class="space-y-2 text-gray-400 text-sm">
                <li><a class="hover:text-white" href="{{ route('store.home') }}">Accueil</a></li>
                <li><a class="hover:text-white" href="{{ route('store.medicines.index') }}">Catalogue</a></li>
                <li><a class="hover:text-white" href="{{ route('store.prescriptions.create') }}">Ordonnance</a></li>
            </ul>
        </div>

        <div>
            <h4 class="font-semibold mb-3">Support</h4>
            <ul class="space-y-2 text-gray-400 text-sm">
                <li>WhatsApp: +225 XX XX XX XX</li>
                <li>Email: contact@epharma.ci</li>
                <li>Abidjan, Côte d’Ivoire</li>
            </ul>
        </div>

        <div>
            <h4 class="font-semibold mb-3">Paiements</h4>
            <p class="text-gray-400 text-sm">Espèces, Mobile Money, Carte</p>
        </div>
    </div>

    <div class="border-t border-white/10 py-6 text-center text-gray-400 text-sm">
        © {{ date('Y') }} E-PHARMA — Tous droits réservés.
    </div>
</footer>

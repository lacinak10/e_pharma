@extends('layouts.base')

@section('title', 'Accueil')

@section('head')
    <link rel="stylesheet" href="{{ asset('assets/css/epharma.css') }}">
@endsection

@section('content')

<!-- Hero Section -->
<section id="home" class="pharmacy-gradient text-white py-16">
    <div class="container mx-auto px-4 flex flex-col md:flex-row items-center">
        <div class="md:w-1/2 mb-8 md:mb-0">
            <h2 class="text-4xl md:text-5xl font-bold mb-4">Vos médicaments livrés en moins de 2 heures</h2>
            <p class="text-xl mb-6">Commandez vos médicaments en ligne et recevez-les rapidement à votre domicile en Côte d'Ivoire.</p>
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                <a href="/medicaments" class="bg-white text-blue-600 hover:bg-gray-100 px-6 py-3 rounded-md font-medium text-center transition">Rechercher un médicament</a>
                <a href="#how-it-works" class="border-2 border-white hover:bg-white hover:text-blue-600 px-6 py-3 rounded-md font-medium text-center transition">Comment ça marche</a>
            </div>
        </div>
        <div class="md:w-1/2 flex justify-center">
            <img src="https://images.unsplash.com/photo-1587854692152-cbe660dbde88?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1469&q=80"
                 alt="Pharmacie"
                 class="rounded-lg shadow-xl max-w-md w-full">
        </div>
    </div>
</section>

<!-- Medicines Search Section -->
<section id="search" class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Trouvez vos médicaments</h2>
            <div class="relative w-full md:w-96">
                <input type="text" placeholder="Rechercher un médicament..."
                       class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <i class="fas fa-search absolute left-3 top-3.5 text-gray-400"></i>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach(range(1, 4) as $i)
                @include('components.cardMedicament', [
                    'title' => 'Doliprane 1000mg',
                    'description' => 'Boite de 16 comprimés',
                    'price' => '2 550',
                    'status' => 'En Stock',
                ])
            @endforeach
        </div>

        <div class="mt-10 text-center">
            <a href="/medicaments">
                <button class="border-2 border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white px-6 py-3 rounded-lg font-medium transition">
                    Voir plus de médicaments
                </button>
            </a>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section id="how-it-works" class="py-16 bg-white">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Comment commander vos médicaments</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center p-6 rounded-lg bg-gray-50 hover:shadow-md transition">
                <div class="bg-blue-100 text-blue-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-search text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">1. Trouvez votre médicament</h3>
                <p class="text-gray-600">Recherchez par nom ou téléchargez votre ordonnance pour trouver les médicaments disponibles.</p>
            </div>

            <div class="text-center p-6 rounded-lg bg-gray-50 hover:shadow-md transition">
                <div class="bg-blue-100 text-blue-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shopping-cart text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">2. Passez commande</h3>
                <p class="text-gray-600">Ajoutez vos médicaments au panier et choisissez votre mode de paiement préféré.</p>
            </div>

            <div class="text-center p-6 rounded-lg bg-gray-50 hover:shadow-md transition">
                <div class="bg-blue-100 text-blue-600 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-truck text-2xl"></i>
                </div>
                <h3 class="text-xl font-semibold mb-2">3. Recevez votre livraison</h3>
                <p class="text-gray-600">Votre commande est préparée et livrée à votre domicile en moins de 2 heures.</p>
            </div>
        </div>
    </div>
</section>

<!-- Partner Pharmacies Section -->
<section id="pharmacies" class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-bold mb-8 text-center">Nos pharmacies partenaires</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Pharmacy 1 -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="h-48 bg-gray-100 flex items-center justify-center">
                    <img src="{{ asset('assets/epharmaclient/images/african-american-pharmacist-working-drugstore-hospital-pharmacy-african-healthcare-stethoscope-black-woman-doctor.jpg') }}"
                         alt="Pharmacie Principale" class="h-48 object-cover">
                </div>
                <div class="p-6">
                    <h3 class="font-bold text-xl mb-2">Pharmacie Principale</h3>
                    <p class="text-gray-600 mb-4">
                        <i class="fas fa-map-marker-alt text-blue-500 mr-2"></i>
                        Plateau, Abidjan
                    </p>
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400 mr-2">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <span class="text-gray-600 text-sm">4.5 (128 avis)</span>
                    </div>
                    <button class="text-blue-500 hover:text-blue-600 font-medium">
                        Voir les médicaments disponibles
                    </button>
                </div>
            </div>

            <!-- Pharmacy 2 -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="h-48 bg-gray-100 flex items-center justify-center">
                    <img src="{{ asset('assets/epharmaclient/images/portrait-female-pharmacist-working-drugstore.jpg') }}"
                         alt="Pharmalys" class="h-48 object-cover">
                </div>
                <div class="p-6">
                    <h3 class="font-bold text-xl mb-2">Pharmalys</h3>
                    <p class="text-gray-600 mb-4">
                        <i class="fas fa-map-marker-alt text-blue-500 mr-2"></i>
                        Cocody, Abidjan
                    </p>
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400 mr-2">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="far fa-star"></i>
                        </div>
                        <span class="text-gray-600 text-sm">4.0 (92 avis)</span>
                    </div>
                    <button class="text-blue-500 hover:text-blue-600 font-medium">
                        Voir les médicaments disponibles
                    </button>
                </div>
            </div>

            <!-- Pharmacy 3 -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="h-48 bg-gray-100 flex items-center justify-center">
                    <img src="{{ asset('assets/epharmaclient/images/portrait-woman-working-pharmaceutical-industry.jpg') }}"
                         alt="Pharmaprix" class="h-48 object-cover">
                </div>
                <div class="p-6">
                    <h3 class="font-bold text-xl mb-2">Pharmaprix</h3>
                    <p class="text-gray-600 mb-4">
                        <i class="fas fa-map-marker-alt text-blue-500 mr-2"></i>
                        Yopougon, Abidjan
                    </p>
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400 mr-2">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <span class="text-gray-600 text-sm">5.0 (156 avis)</span>
                    </div>
                    <button class="text-blue-500 hover:text-blue-600 font-medium">
                        Voir les médicaments disponibles
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-10 text-center">
            <button class="border-2 border-blue-500 text-blue-500 hover:bg-blue-500 hover:text-white px-6 py-3 rounded-lg font-medium transition">
                Voir plus
            </button>
        </div>
    </div>

    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-6">
            <h3 class="text-3xl font-bold text-center text-gray-800 mb-12">Ils nous font confiance</h3>
            <div class="relative">
                <div class="testimonial-carousel flex overflow-x-auto space-x-6 pb-6 scrollbar-hide">
                    <div class="testimonial-slide flex-shrink-0 w-full md:w-1/2 lg:w-1/3 bg-white p-6 rounded-lg shadow-sm">
                        <div class="flex items-center mb-4">
                            <img src="https://picsum.photos/100?random=1" alt="Client satisfait" class="w-12 h-12 rounded-full mr-4" loading="lazy">
                            <div>
                                <h4 class="font-semibold">Marie D.</h4>
                                <div class="flex text-yellow-400">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600">"Livraison ultra rapide, le livreur était très professionnel. Je recommande !"</p>
                    </div>

                    <div class="testimonial-slide flex-shrink-0 w-full md:w-1/2 lg:w-1/3 bg-white p-6 rounded-lg shadow-sm">
                        <div class="flex items-center mb-4">
                            <img src="https://picsum.photos/100?random=2" alt="Client satisfait" class="w-12 h-12 rounded-full mr-4" loading="lazy">
                            <div>
                                <h4 class="font-semibold">Jean P.</h4>
                                <div class="flex text-yellow-400">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600">"Service impeccable, j'ai reçu mes médicaments en 25 minutes exactement."</p>
                    </div>

                    <div class="testimonial-slide flex-shrink-0 w-full md:w-1/2 lg:w-1/3 bg-white p-6 rounded-lg shadow-sm">
                        <div class="flex items-center mb-4">
                            <img src="https://picsum.photos/100?random=3" alt="Client satisfait" class="w-12 h-12 rounded-full mr-4" loading="lazy">
                            <div>
                                <h4 class="font-semibold">Sophie L.</h4>
                                <div class="flex text-yellow-400">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600">"Très pratique quand on est malade et qu'on ne peut pas sortir. Merci !"</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>

@endsection




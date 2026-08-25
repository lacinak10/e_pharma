<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Medicine;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Antibiotiques', 'slug' => 'antibiotiques'],
            ['name' => 'Analgésiques', 'slug' => 'analgesiques'],
            ['name' => 'Antiparasitaires', 'slug' => 'antiparasitaires'],
            ['name' => 'Vitamines & Compléments', 'slug' => 'vitamines-complements'],
            ['name' => 'Antidiabétiques', 'slug' => 'antidiabetiques'],
            ['name' => 'Antipaludéens', 'slug' => 'antipaludeens'],
            ['name' => 'Hypertension', 'slug' => 'hypertension'],
            ['name' => 'Pédiatrie', 'slug' => 'pediatrie'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['slug' => $cat['slug']], ['name' => $cat['name'], 'is_active' => true]);
        }

        $medicines = [
            ['name' => 'Amoxicilline 500mg', 'category' => 'antibiotiques', 'price' => 3500, 'description' => 'Antibiotique à large spectre, utilisé pour les infections bactériennes.', 'indication' => "Antibiotique — infections bactériennes", 'pack' => '14 comprimés', 'dosage' => '500 mg', 'rx' => true],
            ['name' => 'Paracétamol 500mg', 'category' => 'analgesiques', 'price' => 800, 'description' => 'Antalgique et antipyrétique pour les douleurs légères à modérées.', 'indication' => "Douleur et fièvre — dès 15 kg", 'pack' => '16 comprimés', 'dosage' => '500 mg', 'rx' => false],
            ['name' => 'Ibuprofène 400mg', 'category' => 'analgesiques', 'price' => 1200, 'description' => 'Anti-inflammatoire non stéroïdien pour les douleurs et la fièvre.', 'indication' => "Douleur et inflammation — dès 30 kg", 'pack' => '20 comprimés', 'dosage' => '400 mg', 'rx' => false],
            ['name' => 'Mebendazole 100mg', 'category' => 'antiparasitaires', 'price' => 2500, 'description' => 'Antiparasitaire pour le traitement des infections à vers intestinaux.', 'indication' => "Vers intestinaux — dès 2 ans", 'pack' => '6 comprimés', 'dosage' => '100 mg', 'rx' => false],
            ['name' => 'Vitamine C 1000mg', 'category' => 'vitamines-complements', 'price' => 1500, 'description' => 'Complément alimentaire pour renforcer le système immunitaire.', 'indication' => "Fatigue passagère — dès 12 ans", 'pack' => '20 comprimés effervescents', 'dosage' => '1000 mg', 'rx' => false],
            ['name' => 'Metformine 500mg', 'category' => 'antidiabetiques', 'price' => 4500, 'description' => 'Antidiabétique oral pour le traitement du diabète de type 2.', 'indication' => "Diabète de type 2", 'pack' => '30 comprimés', 'dosage' => '500 mg', 'rx' => true],
            ['name' => 'Coartem 80/480mg', 'category' => 'antipaludeens', 'price' => 8500, 'description' => 'Antipaludéen combiné pour le traitement du paludisme non compliqué.', 'indication' => "Paludisme non compliqué", 'pack' => '24 comprimés', 'dosage' => '80/480 mg', 'rx' => true],
            ['name' => 'Amlodipine 5mg', 'category' => 'hypertension', 'price' => 5000, 'description' => 'Inhibiteur calcique pour le traitement de l\'hypertension artérielle.', 'indication' => "Hypertension artérielle", 'pack' => '30 comprimés', 'dosage' => '5 mg', 'rx' => true],
            ['name' => 'Sirop Pédiatrique Paracétamol', 'category' => 'pediatrie', 'price' => 2000, 'description' => 'Sirop antalgique et antipyrétique pour enfants de 2 mois à 12 ans.', 'indication' => "Douleur et fièvre — de 2 mois à 12 ans", 'pack' => 'flacon 90 ml', 'dosage' => '2,4 %', 'rx' => false],
            ['name' => 'Azithromycine 250mg', 'category' => 'antibiotiques', 'price' => 6500, 'description' => 'Antibiotique macrolide pour les infections respiratoires et ORL.', 'indication' => "Antibiotique — infections ORL", 'pack' => '6 comprimés', 'dosage' => '250 mg', 'rx' => true],
            ['name' => 'Oméprazole 20mg', 'category' => 'analgesiques', 'price' => 3000, 'description' => 'Inhibiteur de la pompe à protons pour les ulcères et reflux gastrique.', 'indication' => "Reflux et brûlures d'estomac", 'pack' => '14 gélules', 'dosage' => '20 mg', 'rx' => false],
            ['name' => 'Zinc 20mg', 'category' => 'vitamines-complements', 'price' => 1800, 'description' => 'Supplément en zinc pour l\'immunité et la croissance.', 'indication' => "Immunité et croissance", 'pack' => '60 comprimés', 'dosage' => '20 mg', 'rx' => false],
        ];

        foreach ($medicines as $med) {
            $category = Category::where('slug', $med['category'])->first();
            if (!$category) continue;



            Medicine::updateOrCreate(
                ['reference' => 'MED-' . strtoupper(substr(md5($med['name']), 0, 6))],
                [
                    'category_id'           => $category->id,
                    'name'                  => $med['name'],
                    'description'           => $med['description'],
                    'indication'            => $med['indication'],
                    'pack'                  => $med['pack'],
                    'dosage'                => $med['dosage'],
                    'requires_prescription' => $med['rx'],
                    'price'                 => $med['price'],
                    'is_active'             => true,
                ]
            );
        }
    }
}

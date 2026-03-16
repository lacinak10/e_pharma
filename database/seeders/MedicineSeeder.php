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
            ['name' => 'Amoxicilline 500mg', 'category' => 'antibiotiques', 'price' => 3500, 'stock' => 150, 'threshold' => 20, 'description' => 'Antibiotique à large spectre, utilisé pour les infections bactériennes.'],
            ['name' => 'Paracétamol 500mg', 'category' => 'analgesiques', 'price' => 800, 'stock' => 500, 'threshold' => 50, 'description' => 'Antalgique et antipyrétique pour les douleurs légères à modérées.'],
            ['name' => 'Ibuprofène 400mg', 'category' => 'analgesiques', 'price' => 1200, 'stock' => 300, 'threshold' => 30, 'description' => 'Anti-inflammatoire non stéroïdien pour les douleurs et la fièvre.'],
            ['name' => 'Mebendazole 100mg', 'category' => 'antiparasitaires', 'price' => 2500, 'stock' => 80, 'threshold' => 15, 'description' => 'Antiparasitaire pour le traitement des infections à vers intestinaux.'],
            ['name' => 'Vitamine C 1000mg', 'category' => 'vitamines-complements', 'price' => 1500, 'stock' => 200, 'threshold' => 25, 'description' => 'Complément alimentaire pour renforcer le système immunitaire.'],
            ['name' => 'Metformine 500mg', 'category' => 'antidiabetiques', 'price' => 4500, 'stock' => 60, 'threshold' => 10, 'description' => 'Antidiabétique oral pour le traitement du diabète de type 2.'],
            ['name' => 'Coartem 80/480mg', 'category' => 'antipaludeens', 'price' => 8500, 'stock' => 100, 'threshold' => 20, 'description' => 'Antipaludéen combiné pour le traitement du paludisme non compliqué.'],
            ['name' => 'Amlodipine 5mg', 'category' => 'hypertension', 'price' => 5000, 'stock' => 75, 'threshold' => 15, 'description' => 'Inhibiteur calcique pour le traitement de l\'hypertension artérielle.'],
            ['name' => 'Sirop Pédiatrique Paracétamol', 'category' => 'pediatrie', 'price' => 2000, 'stock' => 120, 'threshold' => 20, 'description' => 'Sirop antalgique et antipyrétique pour enfants de 2 mois à 12 ans.'],
            ['name' => 'Azithromycine 250mg', 'category' => 'antibiotiques', 'price' => 6500, 'stock' => 40, 'threshold' => 10, 'description' => 'Antibiotique macrolide pour les infections respiratoires et ORL.'],
            ['name' => 'Oméprazole 20mg', 'category' => 'analgesiques', 'price' => 3000, 'stock' => 90, 'threshold' => 15, 'description' => 'Inhibiteur de la pompe à protons pour les ulcères et reflux gastrique.'],
            ['name' => 'Zinc 20mg', 'category' => 'vitamines-complements', 'price' => 1800, 'stock' => 180, 'threshold' => 30, 'description' => 'Supplément en zinc pour l\'immunité et la croissance.'],
        ];

        foreach ($medicines as $med) {
            $category = Category::where('slug', $med['category'])->first();
            if (!$category) continue;

            $stock     = $med['stock'];
            $threshold = $med['threshold'];

            if ($stock <= 0) {
                $status = 'Épuisé';
            } elseif ($stock <= $threshold) {
                $status = 'Stock faible';
            } else {
                $status = 'En stock';
            }

            Medicine::firstOrCreate(
                ['reference' => 'MED-' . strtoupper(substr(md5($med['name']), 0, 6))],
                [
                    'category_id'     => $category->id,
                    'name'            => $med['name'],
                    'description'     => $med['description'],
                    'price'           => $med['price'],
                    'stock'           => $stock,
                    'alert_threshold' => $threshold,
                    'status'          => $status,
                    'is_active'       => true,
                ]
            );
        }
    }
}

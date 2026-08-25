<?php

namespace Database\Seeders;

use App\Models\Pharmacy;
use Illuminate\Database\Seeder;

class PharmacySeeder extends Seeder
{
    public function run(): void
    {
        $pharmacies = [
            ['Pharmacie du Plateau',      'Plateau',       '+225 27 22 00 00 01', 5.3244, -4.0189, true,  96, 2],
            ['Pharmacie Sainte-Anne',     'Cocody Angré',  '+225 27 22 00 00 07', 5.3960, -3.9860, false, 88, 3],
            ['Pharmacie de la Riviera',   'Riviera 3',     '+225 27 22 00 00 12', 5.3610, -3.9490, false, 82, 4],
            ['Pharmacie Marcory Zone 4',  'Marcory',       '+225 27 22 00 00 18', 5.2990, -3.9930, false, 74, 4],
            ['Pharmacie Yopougon Siporex', 'Yopougon',     '+225 27 22 00 00 25', 5.3380, -4.0890, false, 61, 6],
            ['Pharmacie Treichville',     'Treichville',   '+225 27 22 00 00 31', 5.2930, -4.0100, true,  90, 3],
            ['Pharmacie Adjamé Liberté',  'Adjamé',        '+225 27 22 00 00 44', 5.3560, -4.0230, false, 79, 5],
            ['Pharmacie Deux-Plateaux',   'Cocody',        '+225 27 22 00 00 52', 5.3830, -3.9990, false, 93, 2],
        ];

        foreach ($pharmacies as [$name, $area, $phone, $lat, $lng, $is24h, $reliability, $response]) {
            Pharmacy::updateOrCreate(
                ['name' => $name],
                [
                    'area'                 => $area,
                    'phone'                => $phone,
                    'address'              => "{$area}, Abidjan",
                    'latitude'             => $lat,
                    'longitude'            => $lng,
                    'is_24h'               => $is24h,
                    'reliability'          => $reliability,
                    'avg_response_minutes' => $response,
                    'is_active'            => true,
                ]
            );
        }
    }
}

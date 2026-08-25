<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // ── Managers ──────────────────────────────────────────────
            [
                'name'  => 'Koffi Assamoi',
                'email' => 'manager@epharma.test',
                'phone' => '+225 27 22 00 00 00',
                'role'  => 'manager',
                'zone'  => 'Plateau',
                'lat'   => 5.3244,
                'lng'   => -4.0189,
            ],
            [
                'name'  => 'Nadège Kouassi',
                'email' => 'manager2@epharma.test',
                'phone' => '+225 27 22 00 00 21',
                'role'  => 'manager',
                'zone'  => 'Cocody',
                'lat'   => 5.3480,
                'lng'   => -3.9970,
            ],
            [
                'name'  => 'Serge Kouadio',
                'email' => 'manager3@epharma.test',
                'phone' => '+225 27 22 00 00 33',
                'role'  => 'manager',
                'zone'  => 'Treichville',
                'lat'   => 5.2930,
                'lng'   => -4.0110,
            ],

            // ── Livreurs (positions Abidjan pour le calcul de distance) ─
            [
                'name'  => 'Mamadou Koné',
                'email' => 'livreur@epharma.test',
                'phone' => '+225 07 00 00 00 00',
                'role'  => 'courier',
                'zone'  => 'Plateau',
                'lat'   => 5.3280,
                'lng'   => -4.0210,
            ],
            [
                'name'  => 'Fatou Sylla',
                'email' => 'livreur2@epharma.test',
                'phone' => '+225 07 00 00 00 12',
                'role'  => 'courier',
                'zone'  => 'Adjamé',
                'lat'   => 5.3540,
                'lng'   => -4.0250,
            ],
            [
                'name'  => 'Yao Brou',
                'email' => 'livreur3@epharma.test',
                'phone' => '+225 07 00 00 00 31',
                'role'  => 'courier',
                'zone'  => 'Yopougon',
                'lat'   => 5.3400,
                'lng'   => -4.0700,
            ],
            [
                'name'  => 'Adjoua Tanoh',
                'email' => 'livreur4@epharma.test',
                'phone' => '+225 07 00 00 00 44',
                'role'  => 'courier',
                'zone'  => 'Marcory',
                'lat'   => 5.3010,
                'lng'   => -3.9950,
            ],

            // ── Clients ───────────────────────────────────────────────
            [
                'name'  => 'Aïcha Diallo',
                'email' => 'client@epharma.test',
                'phone' => '+225 07 00 00 00 02',
                'role'  => 'client',
                'zone'  => 'Cocody Angré',
                'lat'   => 5.3960,
                'lng'   => -3.9860,
            ],
            [
                'name'  => 'Ismaël Touré',
                'email' => 'client2@epharma.test',
                'phone' => '+225 05 00 00 00 09',
                'role'  => 'client',
                'zone'  => 'Marcory',
                'lat'   => 5.2990,
                'lng'   => -3.9930,
            ],
            [
                'name'  => 'Awa Bamba',
                'email' => 'client3@epharma.test',
                'phone' => '+225 01 00 00 00 17',
                'role'  => 'client',
                'zone'  => 'Yopougon Niangon',
                'lat'   => 5.3350,
                'lng'   => -4.0830,
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'         => $data['name'],
                    'phone'        => $data['phone'],
                    'password'     => Hash::make('password'),
                    'role'         => $data['role'],
                    'zone'         => $data['zone'],
                    'latitude'     => $data['lat'],
                    'longitude'    => $data['lng'],
                    'is_available' => true,
                    'is_active'    => true,
                ]
            );
        }
    }
}

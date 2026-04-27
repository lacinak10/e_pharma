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
                'name'      => 'Admin Manager',
                'email'     => 'manager@epharma.test',
                'phone'     => null,
                'role'      => 'manager',
            ],

            // ── Livreurs ──────────────────────────────────────────────
            [
                'name'      => 'Jean Livreur',
                'email'     => 'livreur@epharma.test',
                'phone'     => '+225 07 00 00 00 01',
                'role'      => 'courier',
            ],
            [
                'name'      => 'Oumar Koné',
                'email'     => 'livreur2@epharma.test',
                'phone'     => '+225 05 33 44 55 66',
                'role'      => 'courier',
            ],
            [
                'name'      => 'Aya Traoré',
                'email'     => 'livreur3@epharma.test',
                'phone'     => '+225 07 44 55 66 77',
                'role'      => 'courier',
            ],
            [
                'name'      => 'Moussa Bamba',
                'email'     => 'livreur4@epharma.test',
                'phone'     => '+225 01 55 66 77 88',
                'role'      => 'courier',
            ],

            // ── Clients ───────────────────────────────────────────────
            [
                'name'      => 'Marie Cliente',
                'email'     => 'client@epharma.test',
                'phone'     => '+225 07 00 00 00 02',
                'role'      => 'client',
            ],
        ];

        foreach ($users as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'      => $data['name'],
                    'phone'     => $data['phone'],
                    'password'  => Hash::make('password'),
                    'role'      => $data['role'],
                    'is_active' => true,
                ]
            );
        }
    }
}

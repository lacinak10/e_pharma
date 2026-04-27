<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Manager
        User::firstOrCreate(
            ['email' => 'manager@epharma.test'],
            [
                'name'      => 'Admin Manager',
                'password'  => Hash::make('password'),
                'role'      => 'manager',
                'is_active' => true,
            ]
        );

        // Livreur
        User::firstOrCreate(
            ['email' => 'livreur@epharma.test'],
            [
                'name'      => 'Jean Livreur',
                'phone'     => '+225 07 00 00 00 01',
                'password'  => Hash::make('password'),
                'role'      => 'courier',
                'is_active' => true,
            ]
        );

        // Client
        User::firstOrCreate(
            ['email' => 'client@epharma.test'],
            [
                'name'      => 'Marie Cliente',
                'phone'     => '+225 07 00 00 00 02',
                'password'  => Hash::make('password'),
                'role'      => 'client',
                'is_active' => true,
            ]
        );
    }
}

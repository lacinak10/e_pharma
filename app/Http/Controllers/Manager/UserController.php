<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'prenom'    => ['nullable', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'     => ['nullable', 'string', 'max:30'],
            'role'      => ['required', 'in:manager,courier,client'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable'],
        ]);

        $user = User::create([
            'name'      => $data['name'],
            'prenom'    => $data['prenom'] ?? null,
            'email'     => $data['email'],
            'phone'     => $data['phone'] ?? null,
            'role'      => $data['role'],
            'password'  => Hash::make($data['password']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        Log::info('User created by manager', [
            'manager_id' => auth()->id(),
            'user_id'    => $user->id,
            'role'       => $user->role,
            'email'      => $user->email,
        ]);

        $successMsg = match ($data['role']) {
            'manager' => 'Manager créé avec succès.',
            'courier' => 'Livreur créé avec succès.',
            default   => 'Client créé avec succès.',
        };

        return redirect()->route('manager.users.create')
            ->with('success', $successMsg);
    }
}

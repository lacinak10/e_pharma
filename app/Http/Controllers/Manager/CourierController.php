<?php

namespace App\Http\Controllers\Manager;

use App\Enums\AssignmentStatus;
use App\Http\Controllers\Controller;
use App\Models\DeliveryAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CourierController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));
        $status = $request->get('status');

        $query = User::query()
            ->where('role', 'courier')
            ->withAvg('reviews as reviews_avg_rating', 'rating')
            ->withCount('reviews');

        if ($q !== '') {
            $query->where(function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                   ->orWhere('email', 'like', "%{$q}%")
                   ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $couriers = $query->latest()->paginate(10)->withQueryString();

        // Stats livraisons (évite N+1)
        $ids = $couriers->getCollection()->pluck('id')->all();
        $statsByCourier = collect();

        if (!empty($ids)) {
            $a  = AssignmentStatus::ASSIGNED->value;
            $ac = AssignmentStatus::ACCEPTED->value;
            $de = AssignmentStatus::DELIVERING->value;
            $dv = AssignmentStatus::DELIVERED->value;
            $rf = AssignmentStatus::REFUSED->value;

            $statsByCourier = DeliveryAssignment::query()
                ->select(
                    'courier_id',
                    DB::raw("SUM(CASE WHEN status='{$a}' THEN 1 ELSE 0 END) as assigned"),
                    DB::raw("SUM(CASE WHEN status IN ('{$ac}','{$de}') THEN 1 ELSE 0 END) as in_progress"),
                    DB::raw("SUM(CASE WHEN status='{$dv}' THEN 1 ELSE 0 END) as delivered"),
                    DB::raw("SUM(CASE WHEN status='{$rf}' THEN 1 ELSE 0 END) as refused")
                )
                ->whereIn('courier_id', $ids)
                ->groupBy('courier_id')
                ->get()
                ->keyBy('courier_id');
        }

        $filters = ['q' => $q, 'status' => $status];

        return view('admin.couriers.index', compact('couriers', 'statsByCourier', 'filters'));
    }

    public function create()
    {
        return view('admin.couriers.form', [
            'courier' => new User(['role' => User::ROLE_COURIER, 'is_active' => true]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'phone' => ['nullable','string','max:30'],
            'zone' => ['nullable','string','max:80'],
            'latitude' => ['nullable','numeric','between:-90,90'],
            'longitude' => ['nullable','numeric','between:-180,180'],
            'password' => ['required','string','min:8','confirmed'],
            'is_active' => ['nullable'],
        ]);

        $courier = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'zone' => $data['zone'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => 'courier',
            'is_active' => $request->boolean('is_active', true),
        ]);

        Log::info('Courier created', ['manager_id' => auth()->id(), 'courier_id' => $courier->id, 'email' => $courier->email]);

        return redirect()->route('manager.couriers.show', $courier)
            ->with('success', 'Livreur créé avec succès.');
    }

    public function show(User $courier)
    {
        abort_unless($courier->role === 'courier', 404);

        $total = DeliveryAssignment::where('courier_id', $courier->id)
            ->whereIn('status', [
                AssignmentStatus::ASSIGNED,
                AssignmentStatus::ACCEPTED,
                AssignmentStatus::REFUSED,
                AssignmentStatus::DELIVERING,
                AssignmentStatus::DELIVERED,
            ])
            ->count();

        $assigned   = DeliveryAssignment::where('courier_id', $courier->id)->where('status', AssignmentStatus::ASSIGNED)->count();
        $accepted   = DeliveryAssignment::where('courier_id', $courier->id)->where('status', AssignmentStatus::ACCEPTED)->count();
        $delivering = DeliveryAssignment::where('courier_id', $courier->id)->where('status', AssignmentStatus::DELIVERING)->count();
        $delivered  = DeliveryAssignment::where('courier_id', $courier->id)->where('status', AssignmentStatus::DELIVERED)->count();
        $refused    = DeliveryAssignment::where('courier_id', $courier->id)->where('status', AssignmentStatus::REFUSED)->count();

        $acceptedLike = DeliveryAssignment::where('courier_id', $courier->id)
            ->whereIn('status', [AssignmentStatus::ACCEPTED, AssignmentStatus::DELIVERING, AssignmentStatus::DELIVERED])
            ->count();

        $acceptRate = $total > 0 ? (int) round(($acceptedLike / $total) * 100) : 0;

        $stats = compact('total','assigned','accepted','delivering','delivered','refused','acceptRate');

        $courier->loadAvg('reviews as reviews_avg_rating', 'rating')->loadCount('reviews');

        $recentAssignments = DeliveryAssignment::with('order.user:id,name')
            ->where('courier_id', $courier->id)
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.couriers.show', compact('courier','stats','recentAssignments'));
    }

    public function edit(User $courier)
    {
        abort_unless($courier->role === User::ROLE_COURIER, 404);

        return view('admin.couriers.form', compact('courier'));
    }

    public function update(Request $request, User $courier)
    {
        abort_unless($courier->role === User::ROLE_COURIER, 404);

        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255', Rule::unique('users','email')->ignore($courier->id)],
            'phone' => ['nullable','string','max:30'],
            'zone' => ['nullable','string','max:80'],
            'latitude' => ['nullable','numeric','between:-90,90'],
            'longitude' => ['nullable','numeric','between:-180,180'],
            'password' => ['nullable','string','min:8','confirmed'],
            'is_active' => ['nullable'],
        ]);

        $courier->name = $data['name'];
        $courier->email = $data['email'];
        $courier->phone = $data['phone'] ?? null;
        $courier->zone = $data['zone'] ?? null;
        $courier->latitude = $data['latitude'] ?? null;
        $courier->longitude = $data['longitude'] ?? null;
        $courier->is_active = $request->boolean('is_active', $courier->is_active);

        if (!empty($data['password'])) {
            $courier->password = Hash::make($data['password']);
        }

        $courier->save();

        return redirect()->route('manager.couriers.show', $courier)
            ->with('success', 'Livreur mis à jour.');
    }

    public function toggle(User $courier)
    {
        abort_unless($courier->role === User::ROLE_COURIER, 404);

        $courier->is_active = ! $courier->is_active;
        $courier->save();

        return back()->with('success', $courier->is_active ? 'Livreur activé.' : 'Livreur désactivé.');
    }

    public function destroy(User $courier)
    {
        abort_unless($courier->role === User::ROLE_COURIER, 404);

        Log::info('Courier deleted', ['manager_id' => auth()->id(), 'courier_id' => $courier->id, 'email' => $courier->email]);

        // Suppression douce : le livreur disparaît des listes et ne peut plus se
        // connecter, mais les courses déjà effectuées gardent son nom.
        $courier->delete();

        return redirect()->route('manager.couriers.index')
            ->with('success', 'Livreur supprimé.');
    }
}

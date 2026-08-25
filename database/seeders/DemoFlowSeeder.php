<?php

namespace Database\Seeders;

use App\Enums\AssignmentStatus;
use App\Enums\ItemAvailability;
use App\Enums\OrderStatus;
use App\Models\CourierReview;
use App\Models\DeliveryAssignment;
use App\Models\Medicine;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Peuple le parcours avec des commandes réparties sur le cycle de vie, afin
 * que le back-office, le suivi client et l'espace livreur aient tous du contenu.
 */
class DemoFlowSeeder extends Seeder
{
    public function run(): void
    {
        $clients   = User::clients()->get();
        $couriers  = User::couriers()->get();
        $pharmacies = Pharmacy::active()->get();
        $pharmacy   = $pharmacies->first();
        $medicines = Medicine::active()->get();

        if ($clients->isEmpty() || $couriers->isEmpty() || $medicines->isEmpty() || $pharmacies->isEmpty()) {
            $this->command?->warn('DemoFlowSeeder ignoré : lancez d\'abord UserSeeder, MedicineSeeder et PharmacySeeder.');

            return;
        }

        $scenarios = [
            // [statut, minutes écoulées, ordonnance, livreur ?, note ?]
            [OrderStatus::PENDING_VALIDATION,  3,   false, false, null],
            [OrderStatus::PENDING_VALIDATION,  9,   true,  false, null],
            [OrderStatus::CHECKING,            2,   false, false, null],
            [OrderStatus::CHECKING,            4,   true,  false, null],
            [OrderStatus::AVAILABLE,           14,  false, false, null],
            [OrderStatus::PARTIALLY_AVAILABLE, 22,  false, false, null],
            [OrderStatus::UNAVAILABLE,         40,  false, false, null],
            [OrderStatus::COURIER_ASSIGNED,    18,  false, true,  null],
            [OrderStatus::TO_PHARMACY,         26,  false, true,  null],
            [OrderStatus::AT_PHARMACY,         31,  true,  true,  null],
            [OrderStatus::PICKED_UP,           38,  false, true,  null],
            [OrderStatus::TO_CLIENT,           44,  false, true,  null],
            [OrderStatus::DELIVERED,           95,  false, true,  null],
            [OrderStatus::RATED,               180, false, true,  5],
            [OrderStatus::RATED,               320, true,  true,  4],
            [OrderStatus::CANCELED,            260, false, false, null],
        ];

        foreach ($scenarios as $index => [$status, $minutesAgo, $withPrescription, $withCourier, $rating]) {
            $client    = $clients[$index % $clients->count()];
            $createdAt = now()->subMinutes($minutesAgo);

            $order = Order::create([
                'user_id'              => $client->id,
                'pharmacy_id'          => $pharmacy->id,
                'status'               => $status,
                'delivery_address'     => $client->zone . ', Abidjan',
                'delivery_phone'       => $client->phone,
                'payment_method'       => $index % 2 === 0 ? 'momo' : 'cash',
                'delivery_fee'         => 1500,
                'has_prescription'     => $withPrescription,
                // Une ordonnance sans fichier laisserait le manager et le livreur
                // devant un lecteur vide : la démo en dépose une vraie.
                'prescription_path'    => $withPrescription ? $this->demoPrescription($index) : null,
                'prescription_scope'   => $withPrescription ? ($index % 2 ? 'partial' : 'all') : null,
                'prescription_comment' => $withPrescription && $index % 2
                    ? "Uniquement l'Amoxicilline et le sirop, j'ai déjà le Paracétamol."
                    : null,
                'created_at'           => $createdAt,
                'updated_at'           => $createdAt,
            ]);

            $subtotal = $this->addItems($order, $medicines, $pharmacies, $status, $index);

            $order->forceFill($this->milestones($status, $createdAt, $subtotal))->save();

            if ($withCourier) {
                $courier = $couriers[$index % $couriers->count()];

                DeliveryAssignment::create([
                    'order_id'    => $order->id,
                    'courier_id'  => $courier->id,
                    'assigned_by' => User::where('role', User::ROLE_MANAGER)->value('id'),
                    'status'      => $this->assignmentStatus($status),
                    'assigned_at' => $createdAt->copy()->addMinutes(2),
                ]);

                if ($rating !== null) {
                    CourierReview::create([
                        'order_id'   => $order->id,
                        'courier_id' => $courier->id,
                        'client_id'  => $client->id,
                        'rating'     => $rating,
                        'comment'    => $rating === 5
                            ? "Disponibilité confirmée en 3 minutes, livrée en 25. {$courier->short_name} a appelé avant d'arriver."
                            : "Un médicament était indisponible, le générique m'a été proposé tout de suite.",
                        'tags'       => ['Ponctuel', 'Bien emballé'],
                        'created_at' => $createdAt->copy()->addHours(2),
                    ]);
                }
            }

            $this->addEvents($order, $status, $createdAt);
        }
    }

    /**
     * Dépose une image d'ordonnance sur le disque privé et renvoie son chemin.
     * Les commandes de démonstration deviennent ainsi réellement consultables.
     */
    private function demoPrescription(int $index): ?string
    {
        $source = public_path('assets/images/photos/ordonnance.jpg');

        if (! is_file($source)) {
            return null;
        }

        $path = "prescriptions/demo-{$index}.jpg";

        Storage::disk('local')->put($path, (string) file_get_contents($source));

        return $path;
    }

    private function addItems(Order $order, $medicines, $pharmacies, OrderStatus $status, int $index): int
    {
        $picked   = $medicines->random(min(3, $medicines->count()));
        $subtotal = 0;

        foreach ($picked as $position => $medicine) {
            $quantity = 1 + ($position % 2);
            $lineTotal = $medicine->price * $quantity;
            $subtotal += $lineTotal;

            OrderItem::create([
                'order_id'              => $order->id,
                'medicine_id'           => $medicine->id,
                'medicine_name'         => $medicine->name,
                'unit_price'            => $medicine->price,
                'quantity'              => $quantity,
                'line_total'            => $lineTotal,
                'requires_prescription' => $medicine->requires_prescription,
                'availability'          => $this->itemAvailability($status, $position, $index),
                // Réparties sur le réseau : le signal de disponibilité du catalogue
                // n'a de sens que si plusieurs partenaires ont été contactés.
                'pharmacy_id'           => $status === OrderStatus::PENDING_VALIDATION
                    ? null
                    : $pharmacies[($index + $position) % $pharmacies->count()]->id,
                'checked_at'            => $status === OrderStatus::PENDING_VALIDATION ? null : now(),
            ]);
        }

        return $subtotal;
    }

    private function itemAvailability(OrderStatus $status, int $position, int $index): ItemAvailability
    {
        return match (true) {
            $status === OrderStatus::PENDING_VALIDATION => ItemAvailability::PENDING,
            $status === OrderStatus::CHECKING           => $position === 0 ? ItemAvailability::AVAILABLE : ItemAvailability::PENDING,
            $status === OrderStatus::UNAVAILABLE        => ItemAvailability::UNAVAILABLE,
            $status === OrderStatus::PARTIALLY_AVAILABLE => $position === 2 ? ItemAvailability::UNAVAILABLE : ItemAvailability::AVAILABLE,
            default                                     => ItemAvailability::AVAILABLE,
        };
    }

    /** @return array<string, mixed> */
    private function milestones(OrderStatus $status, \Illuminate\Support\Carbon $createdAt, int $subtotal): array
    {
        $data = [
            'subtotal'     => $subtotal,
            'total_amount' => $subtotal + 1500,
        ];

        if ($status === OrderStatus::PENDING_VALIDATION) {
            return $data;
        }

        $data['validated_at']        = $createdAt->copy()->addMinute();
        $data['checking_started_at'] = $createdAt->copy()->addMinute();
        // Le chrono des commandes en vérification reste vivant pour la démonstration.
        $data['check_deadline_at']   = $status === OrderStatus::CHECKING
            ? now()->addSeconds(random_int(45, 280))
            : $createdAt->copy()->addMinutes(6);

        if ($status === OrderStatus::CHECKING) {
            return $data;
        }

        $data['verdict_at'] = $createdAt->copy()->addMinutes(4);

        if ($status->isCourierPhase() || in_array($status, [OrderStatus::DELIVERED, OrderStatus::RATED], true)) {
            $data['assigned_at']   = $createdAt->copy()->addMinutes(6);
            $data['eta_minutes']   = random_int(14, 32);
            $data['delivery_code'] = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        }

        if (in_array($status, [OrderStatus::DELIVERED, OrderStatus::RATED], true)) {
            $data['delivered_at'] = $createdAt->copy()->addMinutes(34);
        }

        if ($status === OrderStatus::CANCELED) {
            $data['canceled_at']   = $createdAt->copy()->addMinutes(8);
            $data['cancel_reason'] = 'Le client a trouvé le médicament sur place.';
        }

        return $data;
    }

    private function assignmentStatus(OrderStatus $status): AssignmentStatus
    {
        return match ($status) {
            OrderStatus::COURIER_ASSIGNED => AssignmentStatus::ASSIGNED,
            OrderStatus::TO_PHARMACY      => AssignmentStatus::ACCEPTED,
            OrderStatus::DELIVERED,
            OrderStatus::RATED            => AssignmentStatus::DELIVERED,
            default                       => AssignmentStatus::DELIVERING,
        };
    }

    private function addEvents(Order $order, OrderStatus $status, \Illuminate\Support\Carbon $createdAt): void
    {
        $sequence = collect(OrderStatus::lifecycle())
            ->filter(fn (OrderStatus $s) => $s->number() <= $status->number() && ! in_array($s, [
                OrderStatus::REFUSED, OrderStatus::UNAVAILABLE, OrderStatus::CANCELED,
                OrderStatus::PARTIALLY_AVAILABLE,
            ], true) || $s === $status);

        foreach ($sequence->values() as $position => $step) {
            $order->events()->create([
                'user_id'    => $order->user_id,
                'status'     => $step,
                'message'    => $step->label(),
                'created_at' => $createdAt->copy()->addMinutes($position * 3),
                'updated_at' => $createdAt->copy()->addMinutes($position * 3),
            ]);
        }
    }
}

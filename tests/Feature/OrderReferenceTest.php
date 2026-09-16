<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * « CMD-20260904-0001 » — la date, puis une séquence remise à zéro chaque jour.
 */
class OrderReferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_reference_suit_le_format_attendu(): void
    {
        $this->assertMatchesRegularExpression(
            '/^CMD-\d{8}-\d{4}$/',
            Order::factory()->create()->reference,
        );
    }

    public function test_la_sequence_s_incremente_dans_la_journee_et_repart_le_lendemain(): void
    {
        $this->travelTo(Carbon::parse('2026-09-04 08:00:00'));
        $premiere = Order::factory()->create();
        $seconde  = Order::factory()->create();

        $this->travelTo(Carbon::parse('2026-09-05 07:00:00'));
        $lendemain = Order::factory()->create();

        $this->assertSame('CMD-20260904-0001', $premiere->reference);
        $this->assertSame('CMD-20260904-0002', $seconde->reference);
        $this->assertSame('CMD-20260905-0001', $lendemain->reference);
    }

    /** Le tri lexicographique doit suivre le tri numérique au-delà de la dixième. */
    public function test_la_sequence_reste_ordonnee_apres_dix_commandes(): void
    {
        $this->travelTo(Carbon::parse('2026-09-04 08:00:00'));

        $references = collect(range(1, 11))->map(fn () => Order::factory()->create()->reference);

        $this->assertSame('CMD-20260904-0010', $references[9]);
        $this->assertSame('CMD-20260904-0011', $references[10]);
    }

    public function test_une_reference_fournie_explicitement_est_respectee(): void
    {
        $order = Order::factory()->create(['reference' => 'CMD-20260101-0042']);

        $this->assertSame('CMD-20260101-0042', $order->reference);
    }
}

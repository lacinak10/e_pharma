<?php

namespace Tests\Unit\GeniusPay;

use App\Services\GeniusPay\WebhookSignature;
use PHPUnit\Framework\TestCase;

class WebhookSignatureTest extends TestCase
{
    private const SECRET = 'whsec_test';

    private WebhookSignature $signature;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signature = new WebhookSignature();
    }

    public function test_elle_valide_une_signature_calculee_sur_le_corps_brut(): void
    {
        $raw       = '{"id":"abc","data":{"amount":10000.00}}';
        $timestamp = '1735587600';

        $this->assertTrue($this->signature->verify(
            $raw,
            $timestamp,
            hash_hmac('sha256', $timestamp . '.' . $raw, self::SECRET),
            self::SECRET,
        ));
    }

    /**
     * Verrouille la décision prise le 13 septembre 2026 : GeniusPay signe les
     * octets reçus. L'exemple de leur page publique, qui signe un ré-encodage,
     * doit être rejeté — sans quoi on accepterait deux formes là où le
     * fournisseur n'en émet qu'une.
     */
    public function test_elle_rejette_la_forme_reencodee_de_la_page_publique(): void
    {
        $raw     = '{"id":"abc","data":{"amount":10000.00}}';
        $decoded = json_decode($raw, true);

        // « 10000.00 » redevient « 10000.0 » : les octets diffèrent.
        $this->assertNotSame($raw, json_encode($decoded));

        $timestamp = '1735587600';

        $this->assertFalse($this->signature->verify(
            $raw,
            $timestamp,
            hash_hmac('sha256', $timestamp . '.' . json_encode($decoded), self::SECRET),
            self::SECRET,
        ));
    }

    public function test_elle_rejette_une_signature_etrangere(): void
    {
        $raw = '{"id":"abc"}';

        $this->assertFalse($this->signature->verify(
            $raw,
            '1735587600',
            hash_hmac('sha256', '1735587600.' . $raw, 'mauvais_secret'),
            self::SECRET,
        ));
    }

    public function test_elle_rejette_une_signature_vide_ou_un_secret_absent(): void
    {
        $this->assertFalse($this->signature->verify('{}', '1735587600', '', self::SECRET));
        $this->assertFalse($this->signature->verify('{}', '1735587600', 'abc', ''));
    }

    public function test_la_fenetre_anti_rejeu_couvre_cinq_minutes(): void
    {
        $now = 1735587600;

        $this->assertTrue($this->signature->timestampIsFresh((string) ($now - 299), $now));
        $this->assertTrue($this->signature->timestampIsFresh((string) ($now + 299), $now));
        $this->assertFalse($this->signature->timestampIsFresh((string) ($now - 301), $now));
        $this->assertFalse($this->signature->timestampIsFresh('pas-un-nombre', $now));
    }
}

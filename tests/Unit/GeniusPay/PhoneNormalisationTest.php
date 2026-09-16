<?php

namespace Tests\Unit\GeniusPay;

use App\Services\GeniusPay\GeniusPayClient;
use PHPUnit\Framework\TestCase;

/**
 * Le checkout accepte « 0700000000 » comme « +2250700000000 » ; GeniusPay
 * veut l'international. Le plan ivoirien à dix chiffres garde son zéro.
 */
class PhoneNormalisationTest extends TestCase
{
    public function test_elle_prefixe_un_numero_local(): void
    {
        $this->assertSame('+2250700000000', GeniusPayClient::toInternational('0700000000'));
    }

    public function test_elle_laisse_un_numero_deja_international(): void
    {
        $this->assertSame('+2250700000000', GeniusPayClient::toInternational('+2250700000000'));
        $this->assertSame('+2250700000000', GeniusPayClient::toInternational('225 07 00 00 00 00'));
    }

    public function test_elle_ignore_les_separateurs(): void
    {
        $this->assertSame('+2250700000000', GeniusPayClient::toInternational('07 00 00 00 00'));
        $this->assertSame('+2250700000000', GeniusPayClient::toInternational('07-00-00-00-00'));
    }

    public function test_elle_rend_null_sur_une_saisie_vide(): void
    {
        $this->assertNull(GeniusPayClient::toInternational(null));
        $this->assertNull(GeniusPayClient::toInternational('   '));
        $this->assertNull(GeniusPayClient::toInternational('sans chiffre'));
    }
}

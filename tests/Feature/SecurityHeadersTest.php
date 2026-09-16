<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_les_en_tetes_de_securite_sont_presents(): void
    {
        $this->get('/up')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    /**
     * HSTS reçu en clair doit être ignoré par le navigateur (RFC 6797 §8.1).
     * L'émettre quand même épinglait en HTTPS, pour un an et avec
     * includeSubDomains, tout domaine de développement partagé.
     */
    public function test_hsts_n_est_pas_emis_sur_une_reponse_en_clair(): void
    {
        $this->get('http://localhost/up')->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_hsts_est_emis_sur_une_reponse_chiffree(): void
    {
        $this->get('https://localhost/up')
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Support\CompanyProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Les coordonnées de l'entreprise se règlent depuis le back-office et
 * s'appliquent aussitôt à toute la boutique.
 */
class CompanySettingsTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): User
    {
        return User::factory()->create(['role' => User::ROLE_MANAGER]);
    }

    private function courier(): User
    {
        return User::factory()->create(['role' => User::ROLE_COURIER]);
    }

    /** @return array<string, string> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name'          => 'ePharma',
            'phone'         => '+225 27 22 00 00 00',
            'email'         => 'contact@epharma.ci',
            'address'       => 'Cocody, Abidjan',
            'city'          => 'Abidjan · Côte d\'Ivoire',
            'language'      => 'fr',
            'notifications' => '1',
        ], $overrides);
    }

    public function test_tant_que_rien_n_est_saisi_la_boutique_affiche_les_valeurs_par_defaut(): void
    {
        $profile = app(CompanyProfile::class)->current();

        $this->assertSame(CompanyProfile::DEFAULTS['phone'], $profile['phone']);
        $this->assertSame(CompanyProfile::DEFAULTS['email'], $profile['email']);
    }

    public function test_le_manager_enregistre_les_coordonnees(): void
    {
        $this->actingAs($this->manager())
            ->put(route('admin.settings.update'), $this->payload([
                'phone' => '+225 05 04 03 02 01',
                'email' => 'bonjour@epharma.ci',
            ]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $profile = app(CompanyProfile::class)->current();

        $this->assertSame('+225 05 04 03 02 01', $profile['phone']);
        $this->assertSame('bonjour@epharma.ci', $profile['email']);
        $this->assertDatabaseHas('settings', ['key' => 'company.phone', 'value' => '+225 05 04 03 02 01']);
    }

    public function test_le_nouveau_numero_apparait_sur_la_boutique(): void
    {
        $this->actingAs($this->manager())
            ->put(route('admin.settings.update'), $this->payload(['phone' => '+225 01 02 03 04 05']));

        $this->get(route('store.home'))
            ->assertOk()
            ->assertSee('+225 01 02 03 04 05')
            ->assertSee('tel:+2250102030405');
    }

    public function test_un_champ_facultatif_vide_revient_a_la_valeur_par_defaut(): void
    {
        $manager = $this->manager();

        $this->actingAs($manager)->put(route('admin.settings.update'), $this->payload(['city' => 'Bouaké']));
        $this->assertSame('Bouaké', app(CompanyProfile::class)->current()['city']);

        $this->actingAs($manager)->put(route('admin.settings.update'), $this->payload(['city' => '']));

        $this->assertSame(CompanyProfile::DEFAULTS['city'], app(CompanyProfile::class)->current()['city']);
        $this->assertDatabaseMissing('settings', ['key' => 'company.city']);
    }

    public function test_un_email_invalide_est_refuse(): void
    {
        $this->actingAs($this->manager())
            ->put(route('admin.settings.update'), $this->payload(['email' => 'pas-un-email']))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('settings', 0);
    }

    public function test_le_livreur_regle_ses_preferences_sans_toucher_au_contact_public(): void
    {
        $this->actingAs($this->courier())
            ->put(route('admin.settings.update'), $this->payload([
                'phone' => '+225 00 00 00 00 00',
                'email' => 'pirate@ailleurs.test',
            ]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $profile = app(CompanyProfile::class)->current();

        $this->assertSame(CompanyProfile::DEFAULTS['phone'], $profile['phone']);
        $this->assertSame(CompanyProfile::DEFAULTS['email'], $profile['email']);
        $this->assertDatabaseCount('settings', 0);
    }

    public function test_le_formulaire_des_coordonnees_n_est_montre_qu_au_manager(): void
    {
        $this->actingAs($this->manager())
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee("Informations de l'entreprise")
            ->assertSee('Préférences');

        $this->actingAs($this->courier())
            ->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertDontSee("Informations de l'entreprise")
            ->assertSee('Préférences');
    }

    public function test_le_lien_telephonique_ne_garde_que_les_chiffres(): void
    {
        $this->assertSame('+2252722000000', CompanyProfile::telHref('+225 27 22 00 00 00'));
        $this->assertSame('+2250102030405', CompanyProfile::telHref('+225 01-02-03-04-05'));
    }

    public function test_une_cle_etrangere_au_profil_est_ignoree(): void
    {
        app(CompanyProfile::class)->save(['phone' => '+225 07', 'role' => 'manager']);

        $this->assertDatabaseHas('settings', ['key' => 'company.phone']);
        $this->assertDatabaseMissing('settings', ['key' => 'company.role']);
        $this->assertSame(1, Setting::count());
    }
}

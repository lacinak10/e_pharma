<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Les coordonnées de l'entreprise, éditables depuis le back-office.
 *
 * Elles sont rendues sur chaque page de la boutique (barre utilitaire, pied de
 * page, fiche médicament) : la lecture passe donc par un cache long, vidé à
 * l'enregistrement. Tant qu'un champ n'a jamais été saisi, c'est la valeur par
 * défaut qui s'affiche — la boutique ne montre jamais un contact vide.
 */
class CompanyProfile
{
    private const CACHE_KEY     = 'company.profile';
    private const CACHE_SECONDS = 3600;
    private const PREFIX        = 'company.';

    /**
     * Les champs éditables et leur valeur de repli.
     *
     * @var array<string, string>
     */
    public const DEFAULTS = [
        'name'    => 'ePharma',
        'phone'   => '+225 27 22 00 00 00',
        'email'   => 'contact@epharma.ci',
        'address' => 'Cocody, Abidjan',
        'city'    => 'Abidjan · Côte d\'Ivoire',
    ];

    /**
     * Le profil complet : les valeurs enregistrées par-dessus les valeurs par
     * défaut, plus `phone_href`, prêt à poser dans un lien `tel:`.
     *
     * @return array<string, string>
     */
    public function current(): array
    {
        $stored = Cache::remember(
            self::CACHE_KEY,
            self::CACHE_SECONDS,
            fn () => $this->fromDatabase()
        );

        $profile = array_merge(self::DEFAULTS, $stored);
        $profile['phone_href'] = static::telHref($profile['phone']);

        return $profile;
    }

    /**
     * Enregistre les champs fournis. Une valeur vide efface la personnalisation
     * et fait réapparaître la valeur par défaut, plutôt que d'afficher un vide.
     *
     * @param array<string, string|null> $values
     */
    public function save(array $values): void
    {
        foreach ($values as $field => $value) {
            if (! array_key_exists($field, self::DEFAULTS)) {
                continue;
            }

            $value = is_string($value) ? trim($value) : '';

            $value === ''
                ? Setting::where('key', self::PREFIX . $field)->delete()
                : Setting::updateOrCreate(['key' => self::PREFIX . $field], ['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Les règles de validation du formulaire. Aucune contrainte de format sur le
     * téléphone : l'entreprise peut afficher un numéro court, un numéro vert ou
     * une écriture locale — on n'impose pas le +225.
     *
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:80'],
            'phone'   => ['required', 'string', 'max:40'],
            'email'   => ['required', 'email', 'max:120'],
            'address' => ['nullable', 'string', 'max:160'],
            'city'    => ['nullable', 'string', 'max:80'],
        ];
    }

    /** Un lien `tel:` ne garde que les chiffres et l'indicatif. */
    public static function telHref(string $phone): string
    {
        return (string) preg_replace('/[^\d+]/', '', $phone);
    }

    /** @return array<string, string> */
    private function fromDatabase(): array
    {
        $pairs = [];

        foreach (Setting::pairs() as $key => $value) {
            if (! str_starts_with($key, self::PREFIX) || $value === null || $value === '') {
                continue;
            }

            $pairs[substr($key, strlen(self::PREFIX))] = $value;
        }

        return $pairs;
    }
}

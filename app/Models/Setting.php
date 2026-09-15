<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Un réglage de la plateforme, sous forme de couple clé/valeur.
 *
 * Volontairement anémique : c'est {@see \App\Support\CompanyProfile} qui sait
 * quelles clés existent, ce qu'elles valent par défaut et quand vider le cache.
 * Rien d'autre ne doit lire cette table directement.
 */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /** @return array<string, string|null> */
    public static function pairs(): array
    {
        return static::pluck('value', 'key')->all();
    }
}

<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Filet de sécurité de l'encaissement : un webhook perdu laisserait une
 * commande payée bloquée avant attribution. La relecture est idempotente,
 * la repasser n'a aucun effet de bord.
 */
Schedule::command('epharma:reconcile-payments')
    ->everyFiveMinutes()
    ->withoutOverlapping();

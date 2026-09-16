<?php

use App\Http\Controllers\Webhooks\GeniusPayController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Webhooks entrants
|--------------------------------------------------------------------------
|
| Ces routes sont hors du groupe « web » : ni session, ni CSRF, ni
| EnsureAccountIsActive. L'appelant n'est pas un utilisateur d'ePharma mais
| un serveur tiers, authentifié par la signature HMAC de son payload.
|
*/

Route::post('webhooks/geniuspay', GeniusPayController::class)
    ->middleware('throttle:60,1')
    ->name('webhooks.geniuspay');

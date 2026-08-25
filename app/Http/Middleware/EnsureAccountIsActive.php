<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ferme la session d'un compte désactivé en cours de route.
 *
 * Le contrôle à la connexion ne suffit pas : un livreur désactivé par le manager
 * pendant sa journée resterait sinon connecté et continuerait de traiter des
 * courses jusqu'à sa prochaine déconnexion.
 */
class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isDeactivated()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Ce compte a été désactivé. Contactez la pharmacie pour le réactiver.']);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Usage: ->middleware(EnsureRole::class . ':manager')
     * Or multiple: ->middleware(EnsureRole::class . ':manager,courier')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        $roles = array_map('strval', $roles);

        if (!in_array((string) $user->role, $roles, true)) {
            Log::warning('Unauthorized role access attempt', [
                'user_id'        => $user->id,
                'user_role'      => $user->role,
                'required_roles' => $roles,
                'url'            => $request->url(),
                'method'         => $request->method(),
                'ip'             => $request->ip(),
            ]);

            // Rediriger vers la page d'accueil correspondant au rôle réel
            if ($user->isClient()) {
                return redirect()->route('store.home')
                    ->with('error', 'Vous n\'avez pas accès à cette section.');
            }

            if ($user->isCourier()) {
                return redirect()->route('courier.my_orders.index')
                    ->with('error', 'Vous n\'avez pas les droits nécessaires pour accéder à cette page.');
            }

            // Manager essayant d'accéder à une section réservée au livreur
            return redirect()->route('admin.dashboard')
                ->with('error', 'Vous n\'avez pas les droits nécessaires pour accéder à cette page.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        /*
         * HSTS n'a de sens que sur une réponse déjà chiffrée : la RFC 6797 §8.1
         * impose au navigateur d'ignorer l'en-tête reçu en clair. L'émettre sur
         * du HTTP local était donc inutile — et nuisible dès qu'on passe par un
         * tunnel de développement, où « includeSubDomains » épingle en HTTPS,
         * pour un an, un domaine partagé avec d'autres tunnels.
         */
        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        return $response;
    }
}

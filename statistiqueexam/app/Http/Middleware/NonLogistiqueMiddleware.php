<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NonLogistiqueMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isLogistique() && ! $user->isAdmin()) {
            abort(403, 'Accès non autorisé pour le compte logistique.');
        }

        return $next($request);
    }
}

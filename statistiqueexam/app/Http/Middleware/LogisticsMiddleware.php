<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogisticsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->canAccessLogistics()) {
            abort(403, 'Accès réservé à la logistique et à l\'administrateur.');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        $allowedRoles = collect($roles)
            ->flatMap(fn (string $role): array => array_map('trim', explode(',', $role)))
            ->filter()
            ->map(fn (string $role): string => Str::lower(Str::ascii($role)))
            ->all();

        if ($allowedRoles !== [] && ! $user->hasRole($allowedRoles)) {
            abort(Response::HTTP_FORBIDDEN, 'Accès non autorisé pour ce rôle.');
        }

        return $next($request);
    }
}

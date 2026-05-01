<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\AdminScope;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_if(! $user instanceof User || ! AdminScope::hasRole($user, $roles), 403);

        return $next($request);
    }
}

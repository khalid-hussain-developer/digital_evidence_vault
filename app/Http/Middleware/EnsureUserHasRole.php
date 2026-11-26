<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts a route to the given roles. Registered as the 'role' middleware
 * alias in bootstrap/app.php — use it as middleware('role:admin') or
 * middleware('role:admin,investigator') on any route or group.
 *
 * This is the enforcement layer the brief calls "deny by default": a user
 * who is not authenticated, or authenticated with the wrong role, never
 * reaches the controller, regardless of what the UI happens to show.
 */
class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $allowedRoles = array_map(
            fn (string $role) => UserRole::from($role),
            $roles,
        );

        if (! $user->hasRole(...$allowedRoles)) {
            abort(403, 'You are not authorized to access this page.');
        }

        return $next($request);
    }
}

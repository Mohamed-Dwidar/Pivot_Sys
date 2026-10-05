<?php

namespace Modules\UserModule\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage: 'user.active' or 'user.active:' . Account::class to also limit the route to one user type.
 * Logs the user out when his owner model no longer allows the login (pending, deactivated, ...).
 */
class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next, ?string $type = null): Response
    {
        $userable = $request->user()?->userable;

        $error = $userable ? $userable->loginError() : 'Your account is no longer available, please contact the administrator.';
        if ($error) {
            Auth::guard('web')->logout();
            $request->session()->regenerate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors(['email' => $error]);
        }

        if ($type && !$userable instanceof $type) {
            abort(403);
        }

        return $next($request);
    }
}

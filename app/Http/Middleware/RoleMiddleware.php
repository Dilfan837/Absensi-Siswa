<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect('/login');
        }

        // Get user's role name
        $userRole = trim(auth()->user()->role->nama_role);

        // Check if user's role is in the allowed roles
        if (!in_array($userRole, $roles)) {
            // Redirect to appropriate dashboard based on user's actual role
            switch ($userRole) {
                case 'admin':
                    if ($request->is('dashboard') || $request->is('dashboard/*')) {
                        return $next($request);
                    }
                    return redirect('/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
                case 'guru':
                    if ($request->is('guru/dashboard') || $request->is('guru/*')) {
                        return $next($request);
                    }
                    return redirect('/guru/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
                case 'siswa':
                    if ($request->is('siswa/dashboard') || $request->is('siswa/*')) {
                        return $next($request);
                    }
                    return redirect('/siswa/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
                default:
                    abort(403, 'Unauthorized action.');
            }
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $userRole = Auth::user()->role;

        // Admin HRD atau Superadmin diizinkan
        if (!in_array($userRole, ['admin', 'superadmin'])) {
            abort(403, 'Akses ditolak. Halaman ini memerlukan hak akses Administrator.');
        }

        return $next($request);
    }
}

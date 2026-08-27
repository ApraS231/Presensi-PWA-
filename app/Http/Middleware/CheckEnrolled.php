<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckEnrolled
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->role === 'karyawan' && $user->enrollment_status !== 'enrolled') {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan lakukan pendaftaran biometrik wajah terlebih dahulu sebelum dapat melakukan presensi.',
                    'redirect' => route('karyawan.enrollment'),
                ], 403);
            }

            return redirect()->route('karyawan.enrollment')->with('warning', 'Silakan lakukan pendaftaran biometrik wajah terlebih dahulu sebelum dapat melakukan presensi.');
        }

        return $next($request);
    }
}

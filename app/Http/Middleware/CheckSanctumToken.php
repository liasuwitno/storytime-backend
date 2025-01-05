<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSanctumToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Abaikan middleware untuk rute publik (misalnya login dan register)
        if ($request->is('login') || $request->is('register')) {
            return $next($request);
        }

        // Cek apakah user memiliki token valid
        if (auth('sanctum')->check()) {
            return $next($request);
        }

        // Jika token tidak valid atau expired, kembalikan respons 401
        return response()->json([
            'code' => 401,
            'status' => 'error',
            'message' => 'Unauthorized. Token expired or invalid.',
            'data' => null
        ], Response::HTTP_UNAUTHORIZED);
    }
}

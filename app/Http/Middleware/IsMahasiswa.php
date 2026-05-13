<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsMahasiswa
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->hasRole('mahasiswa')) {
            return response()->json([
                'message' => 'Akses ditolak. Anda bukan Mahasiswa.'
            ], 403);
        }

        return $next($request);
    }
}
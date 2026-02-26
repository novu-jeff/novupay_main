<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $providedKey = $request->bearerToken(); // from Authorization: Bearer <key>
        $expectedKey = env('PELCO_API_KEY');

        if (!$providedKey || $providedKey !== $expectedKey) {
            return response()->json([
                'error' => 'Unauthorized. Invalid or missing API key.'
            ], 401);
        }

        return $next($request);
    }
}

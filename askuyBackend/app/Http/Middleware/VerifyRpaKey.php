<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyRpaKey
{
    /**
     * Handle an incoming request from UiPath RPA bot.
     * Checks X-RPA-Key header against config('services.rpa.key').
     */
    public function handle(Request $request, Closure $next): Response
    {
        $providedKey = $request->header('X-RPA-Key');
        $validKey = config('services.rpa.key');

        if (empty($validKey) || empty($providedKey) || !hash_equals((string) $validKey, (string) $providedKey)) {
            return response()->json([
                'message' => 'Unauthorized: Header X-RPA-Key tidak valid atau tidak disediakan.',
            ], 401);
        }

        return $next($request);
    }
}

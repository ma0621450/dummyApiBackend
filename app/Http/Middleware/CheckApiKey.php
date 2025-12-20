<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiKey;
use Carbon\Carbon;

class CheckApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check for Authorization header
        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json([
                'message' => 'Unauthenticated. Please provide a valid API Key in the Authorization header (Bearer <key>).'
            ], 401);
        }

        $token = substr($header, 7); // Remove 'Bearer '

        // 2. Find Key
        $apiKey = ApiKey::where('key', $token)->first();

        // 3. Validate
        if (!$apiKey) {
            return response()->json(['message' => 'Invalid API Key.'], 401);
        }

        if ($apiKey->expires_at->isPast()) {
            return response()->json(['message' => 'API Key has expired. Please generate a new one.'], 401);
        }

        // 4. Update Usage (Optional: can be async or sampled to reduce DB writes)
        // For this project, updating every time is fine (low traffic)
        $apiKey->update(['last_used_at' => Carbon::now()]);

        return $next($request);
    }
}

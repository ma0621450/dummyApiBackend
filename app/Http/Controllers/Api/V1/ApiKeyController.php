<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ApiKeyController extends Controller
{
    public function store(Request $request)
    {
        // Optional: Simple rate limit check by IP
        // Allowed: 1 key per hour per IP (example)
        $existing = ApiKey::where('ip_address', $request->ip())
            ->where('created_at', '>', Carbon::now()->subHour())
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'You have already generated a key recently. Please use your existing key or wait.',
                'key' => $existing->key,
                'expires_at' => $existing->expires_at->toIso8601String(),
            ], 200);
        }

        // Generate Key
        $key = 'sk_' . Str::random(32);

        $apiKey = ApiKey::create([
            'key' => $key,
            'ip_address' => $request->ip(),
            'expires_at' => Carbon::now()->addDays(3),
        ]);

        return response()->json([
            'message' => 'API Key generated successfully. Save this key, it expires in 3 days.',
            'key' => $apiKey->key,
            'expires_at' => $apiKey->expires_at->toIso8601String(),
        ], 201);
    }
}

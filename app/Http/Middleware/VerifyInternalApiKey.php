<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyInternalApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $providedKey = $request->header('X-Internal-Api-Key');
        $expectedKey = config('app.internal_api_key'); // Get key from config

        if (!$expectedKey) {
             Log::critical('Internal API Key is not configured in config/app.php or .env');
             return response()->json(['error' => 'Internal API misconfiguration.'], 500);
        }

        if (!$providedKey || !hash_equals($expectedKey, $providedKey)) {
             Log::warning('Invalid or missing internal API key attempted.', ['ip' => $request->ip()]);
             return response()->json(['error' => 'Unauthorized internal access.'], 401);
        }

        return $next($request);
    }
}
<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class AuthTokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'No token provided. Please log in.'], 401);
        }

        $user = User::where('token', $token)->first();

        if (! $user) {
            return response()->json(['message' => 'Invalid or expired token. Please log in again.'], 401);
        }

        // Make the user available to controllers via $request->user()
        $request->setUserResolver(fn() => $user);

        return $next($request);
    }
}
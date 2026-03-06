<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * POST /api/login
     * Validates credentials and returns a plain API token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password.'
            ], 401);
        }

        // Generate a simple random token and save it
        $token = bin2hex(random_bytes(32));
        $user->update(['token' => $token]);

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * POST /api/logout
     * Clears the user's token.
     */
    public function logout(Request $request)
    {
        $token = $request->bearerToken();

        if ($token) {
            $user = User::where('token', $token)->first();
            if ($user) {
                $user->update(['token' => null]);
            }
        }

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * GET /api/me
     * Returns the currently logged-in user (optional, used by React to verify token).
     */
    public function me(Request $request)
    {
        $token = $request->bearerToken();
        $user  = User::where('token', $token)->first();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
        ]);
    }
}
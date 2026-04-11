<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

/**
 * API Authentication using Laravel Sanctum
 *
 * DevOps practice: test with curl or Postman
 *
 * Register:
 *   curl -X POST http://localhost/api/v1/auth/register \
 *     -H "Content-Type: application/json" \
 *     -d '{"name":"Test","email":"test@test.com","password":"password","password_confirmation":"password"}'
 *
 * Login:
 *   curl -X POST http://localhost/api/v1/auth/login \
 *     -H "Content-Type: application/json" \
 *     -d '{"email":"admin@devflow.local","password":"password"}'
 *
 * Authenticated request:
 *   curl http://localhost/api/v1/auth/me \
 *     -H "Authorization: Bearer YOUR_TOKEN"
 */
class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user  = User::create([...$data, 'password' => Hash::make($data['password'])]);
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        // Revoke old tokens (optional — keep only latest)
        $user->tokens()->delete();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->load(['ownedProjects', 'assignedTasks']),
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $user  = $request->user();
        $user->tokens()->delete();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['token' => $token]);
    }
}

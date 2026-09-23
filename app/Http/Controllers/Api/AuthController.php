<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request): JsonResponse
    {
        // Password is hashed by the "hashed" cast on the User model.
        $user = User::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'token' => $this->issueToken($user),
            'user' => (new UserResource($user))->resolve(),
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            return $this->error('Invalid credentials', [
                'email' => ['These credentials do not match our records.'],
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $this->issueToken($user),
            'user' => (new UserResource($user))->resolve(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

        return $this->success(null, 'Logged out successfully');
    }

    public function user(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()), 'User retrieved successfully');
    }

    private function issueToken(User $user): string
    {
        // Tokens expire after 30 days.
        return $user->createToken('api-token', ['*'], now()->addDays(30))->plainTextToken;
    }
}
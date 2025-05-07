<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register()
    {
        $validator = Validator::make(request()->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => 'Validation failed',
                'data' => $validator->errors(),
            ], 400);
        }

        $user = User::create([
            'name' => request()->name,
            'email' => request()->email,
            'password' => bcrypt(request()->password),
        ]);

        // Gửi mail xác thực
        event(new Registered($user));

        return response()->json([
            'code' => 201,
            'message' => 'User registered successfully. Please verify your email.',
            'data' => $user,
        ], 201);
    }

    /**
     * Xác thực email (không dùng session / JWT)
     */
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'code' => 404,
                'message' => 'User not found',
            ], 404);
        }

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'code' => 403,
                'message' => 'Invalid verification link',
            ], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'code' => 200,
                'message' => 'Email already verified',
            ]);
        }

        $user->markEmailAsVerified();

        return response()->json([
            'code' => 200,
            'message' => 'Email verified successfully',
        ]);
    }

    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json([
                'code' => 401,
                'message' => 'Unauthorized',
                'data' => null,
            ], 401);
        }

        $user = auth()->user();

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'code' => 403,
                'message' => 'Email not verified',
                'data' => null,
            ], 403);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Login successfully',
            'data' => $this->getTokenResponse($token),
        ], 200);
    }

    public function me()
    {
        return response()->json([
            'code' => 200,
            'message' => 'Authenticated user fetched successfully',
            'data' => auth()->user(),
        ]);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json([
            'code' => 200,
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'code' => 200,
            'message' => 'Token refreshed successfully',
            'data' => $this->getTokenResponse(auth()->refresh()),
        ]);
    }

    protected function getTokenResponse($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ];
    }
}
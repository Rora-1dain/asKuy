<?php

namespace App\Http\Controllers;

use App\Http\Requests\GoogleLoginRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class AuthController extends Controller
{
    /**
     * Register akun baru.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $educationLevel = match ($validated['education_level']) {
            'sma', 'smk' => 'sma_smk',
            'sarjana' => 'sarjana',
            'magister' => 'pascasarjana',
            'prefer_not_to_say' => null,
        };

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'student',
            'education_level' => $educationLevel,
            'major' => $validated['major'] ?? null,
            'study_goal' => $validated['study_goal'] ?? null,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 201);
    }

    /**
     * Login via email & password.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! $user->password || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Kredensial tidak valid.',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Login via Google id_token dari React.
     */
    public function google(GoogleLoginRequest $request): JsonResponse
    {
        $idToken = $request->validated('id_token');

        try {
            $googleUser = Socialite::driver('google')->stateless()->userFromToken($idToken);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Verifikasi token Google gagal: ' . $e->getMessage(),
            ], 401);
        }

        $googleId = (string) $googleUser->getId();
        $email = $googleUser->getEmail();
        $name = $googleUser->getName() ?: 'Google User';
        $avatar = $googleUser->getAvatar();

        $user = User::where('google_id', $googleId)->first();

        if (! $user && $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->google_id = $googleId;
                if (! $user->avatar_url && $avatar) {
                    $user->avatar_url = $avatar;
                }
                $user->save();
            }
        }

        if (! $user) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => null,
                'role' => 'student',
                'google_id' => $googleId,
                'avatar_url' => $avatar,
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login Google berhasil.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Ambil data user yang sedang login.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }

    /**
     * Logout dan hapus token saat ini.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil logout.',
        ]);
    }
}
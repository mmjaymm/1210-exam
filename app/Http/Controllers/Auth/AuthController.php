<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;

class AuthController extends Controller
{
    public function login(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function signup(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function authenticate(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->remember_token)) {
            $request->session()->regenerate();
            $auth_user = Auth::user();
            $token = $request->user()->createToken($auth_user->name);

            return response()->json([
                'status' => true,
                'message' => 'Authenticated.',
                'data' => [
                    "auth_token" => $token->plainTextToken,
                    "auth_user" => $auth_user
                ]
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Unauthenticated.',
            'data' => null
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'name' => ['required'],
            'password' => ['required', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($user) {
            return response()->json([
                'status' => true,
                'message' => 'Registered.',
                'data' => $user
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Unable to register.',
            'data' => $user
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

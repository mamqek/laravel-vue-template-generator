<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Log in a user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $user = User::where('username', $request->identifier)
            ->orWhere('email', $request->identifier)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => __('response.error'),
                'message' => __('auth.failed'),
            ], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => __('response.error'),
                'message' => __('auth.password'),
            ], 401);
        }

        Auth::login($user);

        return response()->json([
            'status' => __('response.success'),
            'message' => __('response.*_successful', ['action' => __('login')]),
            'user' => $user,
        ], 200);
    }

    /**
     * Register a new user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'user',
        ]);

        return response()->json([
            'status' => __('response.success'),
            'message' => __('response.account_created'),
            'user' => $user,
        ], 200);
    }

    /**
     * Check if the user is authenticated.
     *
     * @return false|User
     */
    public function authenticate()
    {
        if (!Auth::check()) {
            return false;
        }

        return Auth::user();
    }

    /**
     * Log out the current user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            Auth::logout();
            $locale = session('locale');
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            session(['locale' => $locale]);

            return response()->json([
                'status' => __('response.success'),
                'message' => __('response.*_successful', ['action' => __('logout')]),
                'csrf_token' => csrf_token(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => __('response.error'),
                'message' => __('response.error_while_*', ['action' => __('logout'), 'attribute' => '']),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
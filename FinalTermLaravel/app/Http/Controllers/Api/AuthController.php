<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AuthController
{
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => 'Please enter both username and password.'], 422);
        }

        $user = User::where('username', trim($request->input('username')))->first();
        $passwordHash = $user?->password_hash ?: $user?->password;

        if (!$user || !$passwordHash || !Hash::check($request->input('password'), $passwordHash)) {
            return response()->json(['success' => false, 'error' => 'Invalid username or password.'], 422);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'error' => 'This account has been deactivated. Please contact an administrator.',
            ], 403);
        }

        Auth::login($user);
        $request->session()->regenerate();
        DB::table('users')->where('id', $user->id)->update(['last_login' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'csrfToken' => $request->session()->token(),
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'regex:/^[A-Za-z0-9_.]{3,50}$/', Rule::unique('users', 'username')],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')],
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8', 'same:confirm_password'],
            'confirm_password' => ['required', 'string'],
        ], [
            'username.regex' => 'Username must be 3-50 characters and contain only letters, numbers, underscores, or periods.',
            'password.same' => 'Passwords do not match.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()->all()], 422);
        }

        if (!DB::table('roles')->where('id', 2)->exists()) {
            return response()->json(['success' => false, 'errors' => ['The staff role is not configured.']], 422);
        }

        $passwordHash = Hash::make($request->input('password'));
        User::create([
            'username' => trim($request->input('username')),
            'email' => trim($request->input('email')),
            'password_hash' => $passwordHash,
            'password' => $passwordHash,
            'first_name' => trim($request->input('first_name')),
            'last_name' => trim($request->input('last_name')),
            'name' => trim($request->input('first_name').' '.$request->input('last_name')),
            'role_id' => 2,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully. You can now log in.',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'csrfToken' => $request->session()->token(),
        ]);
    }
}
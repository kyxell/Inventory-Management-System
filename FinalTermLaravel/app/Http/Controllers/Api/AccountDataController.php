<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Role;
use App\Models\User;

class AccountDataController
{
    public function index(Request $request): JsonResponse
    {
        if ((int) Auth::user()->role_id !== 1) {
            return response()->json(['error' => 'Forbidden - Admin access required'], 403);
        }

        return match ($request->query('type')) {
            'roles' => response()->json([
                'roles' => DB::table('roles')->orderBy('id')->get(['id', 'name']),
            ]),
            'users' => response()->json([
                'users' => DB::table('users as u')
                    ->leftJoin('roles as r', 'u.role_id', '=', 'r.id')
                    ->orderBy('u.id')
                    ->get(['u.id', 'u.username', 'u.email', 'u.role_id', 'r.name as role_name', 'u.last_login']),
            ]),
            'user_for_edit' => $this->userForEdit($request),
            'user_for_delete' => $this->userForDelete($request),
            default => response()->json(['error' => 'Invalid type parameter'], 400),
        };
    }

    private function userForEdit(Request $request): JsonResponse
    {
        $id = (int) $request->query('id', 0);
        $user = DB::table('users')
            ->where('id', $id)
            ->first(['id', 'username', 'email', 'role_id', 'last_login']);

        return $user
            ? response()->json($user)
            : response()->json(['error' => $id ? 'User not found.' : 'User ID is required.'], 404);
    }

    private function userForDelete(Request $request): JsonResponse
    {
        $id = (int) $request->query('id', 0);
        $user = DB::table('users')->where('id', $id)->first(['id', 'username']);

        return $user
            ? response()->json($user)
            : response()->json(['error' => $id ? 'User not found.' : 'User ID is required.'], 404);
    }

    public function update(Request $request): JsonResponse
    {
        if ((int) Auth::user()->role_id !== 1) {
            return response()->json(['success' => false, 'error' => 'Forbidden - Admin access required.'], 403);
        }

        $values = $request->validate([
            'id' => ['required', 'integer', 'exists:users,id'],
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($request->input('id'))],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($request->input('id'))],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'last_login' => ['nullable', 'date'],
        ]);

        $user = User::findOrFail($values['id']);
        $role = Role::findOrFail($values['role_id']);

        $user->forceFill([
            'username' => trim($values['username']),
            'email' => trim($values['email']),
            'role_id' => $values['role_id'],
            'last_login' => $values['last_login'] ?? null,
        ])->save();
        $user->syncRoles($role);

        return response()->json(['success' => true, 'message' => 'Account updated successfully.']);
    }

    public function destroy(Request $request): JsonResponse
    {
        if ((int) Auth::user()->role_id !== 1) {
            return response()->json(['success' => false, 'error' => 'Forbidden - Admin access required.'], 403);
        }

        $values = $request->validate([
            'id' => ['required', 'integer', 'exists:users,id'],
        ]);

        if ((int) $values['id'] === (int) Auth::id()) {
            return response()->json(['success' => false, 'error' => 'You cannot delete your own active account.'], 422);
        }

        DB::table('users')->where('id', $values['id'])->delete();

        return response()->json(['success' => true, 'message' => 'Account deleted successfully.']);
    }
}
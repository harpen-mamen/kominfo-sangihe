<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AdminScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('email', $credentials['email'])
            ->with(['kecamatan', 'opd'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->kata_sandi) || ! $user->aktif) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password tidak valid.'],
            ]);
        }

        $user->forceFill(['login_terakhir_pada' => now()])->save();

        return response()->json([
            'token' => $user->createToken('admin-portal')->plainTextToken,
            'user' => $this->serializeUser($user->refresh()->load(['kecamatan', 'opd'])),
            'workspace' => $this->workspacePayload($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()?->load(['kecamatan', 'opd']);

        return response()->json([
            'user' => $user instanceof User ? $this->serializeUser($user) : null,
            'workspace' => $user instanceof User ? $this->workspacePayload($user) : null,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Sesi berhasil diakhiri.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function workspacePayload(User $user): array
    {
        return [
            'role' => AdminScope::primaryRole($user),
            'key' => AdminScope::workspaceKey($user),
            'scope_label' => AdminScope::scopeLabel($user),
            'department_id' => $user->opd_id,
            'administrative_area_id' => $user->kecamatan_id,
            'organization_unit_id' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeUser(User $user): array
    {
        return [
            ...$user->toArray(),
            'name' => $user->nama,
            'status' => $user->aktif ? 'active' : 'inactive',
            'department' => $user->opd,
            'administrative_area' => $user->kecamatan,
        ];
    }
}

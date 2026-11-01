<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // POST /api/auth/register
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'admin_username' => 'required|string|max:50|unique:admins,admin_username',
            'admin_password' => 'required|string|min:6|confirmed',
        ], [
            'admin_username.required' => 'Username wajib diisi.',
            'admin_username.max' => 'Username maksimal 50 karakter.',
            'admin_username.unique' => 'Username sudah digunakan.',
            'admin_password.required' => 'Password wajib diisi.',
            'admin_password.min' => 'Password minimal 6 karakter.',
            'admin_password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendaftarkan akun admin!',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $admin = Admin::create([
            'admin_username' => $validated['admin_username'],
            'admin_password' => Hash::make(
                $validated['admin_password']
            ),
        ]);

        $token = auth('api')->login($admin);

        return response()->json([
            'success' => true,
            'message' => 'Akun admin berhasil didaftarkan.',
            'data' => [
                'admin' => $admin,
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
            ],
        ], 201);
    }

    // POST /api/auth/login
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'admin_username' => 'required|string',
            'admin_password' => 'required|string',
        ], [
            'admin_username.required' => 'Username wajib diisi.',
            'admin_password.required' => 'Password wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan login!',
                'errors' => $validator->errors(),
            ], 422);
        }

        // ✅ FIX: key harus sama persis dengan nama kolom di tabel admins
       $credentials = [
    'admin_username' => $request->admin_username,
    'password' => $request->admin_password,
];

if (!$token = auth('api')->attempt($credentials)) {
    return response()->json([
        'success' => false,
        'message' => 'Username atau password salah.',
        'data' => null,
    ], 401);
}

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'admin' => auth('api')->user(),
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
            ],
        ], 200);
    }

    // GET /api/auth/me
    public function me(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Data admin berhasil diambil.',
            'data' => auth('api')->user(),
        ], 200);
    }

    // POST /api/auth/logout
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
            'data' => null,
        ], 200);
    }
}
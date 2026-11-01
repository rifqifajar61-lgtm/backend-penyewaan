<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    // GET /api/admin
    public function index(): JsonResponse
    {
        $admin = Admin::latest()->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar admin berhasil diambil.',
            'data' => $admin,
        ], 200);
    }

    // POST /api/admin
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'admin_username' => 'required|string|max:50|unique:admins,admin_username',
            'admin_password' => 'required|string|min:6',
        ], [
            'admin_username.required' => 'Username wajib diisi.',
            'admin_username.max' => 'Username maksimal 50 karakter.',
            'admin_username.unique' => 'Username sudah digunakan, silakan pilih yang lain.',
            'admin_password.required' => 'Password wajib diisi.',
            'admin_password.min' => 'Password minimal 6 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan akun admin!',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $admin = Admin::create([
            'admin_username' => $validated['admin_username'],
            'admin_password' => Hash::make($validated['admin_password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Akun admin berhasil ditambahkan.',
            'data' => $admin,
        ], 201);
    }

    // GET /api/admin/{admin}
    public function show(Admin $admin): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Detail admin berhasil diambil.',
            'data' => $admin,
        ], 200);
    }

    // PUT/PATCH /api/admin/{admin}
    public function update(Request $request, Admin $admin): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'admin_username' =>
                'sometimes|required|string|max:50|unique:admins,admin_username,' .
                $admin->admin_id . ',admin_id',

            'admin_password' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui akun admin!',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        if (isset($validated['admin_username'])) {
            $admin->admin_username = $validated['admin_username'];
        }

        if (!empty($validated['admin_password'])) {
            $admin->admin_password = Hash::make(
                $validated['admin_password']
            );
        }

        $admin->save();

        return response()->json([
            'success' => true,
            'message' => 'Akun admin berhasil diperbarui.',
            'data' => $admin,
        ], 200);
    }

    // DELETE /api/admin/{admin}
    public function destroy(Admin $admin): JsonResponse
    {
        $admin->delete();

        return response()->json([
            'success' => true,
            'message' => 'Akun admin berhasil dihapus.',
            'data' => null,
        ], 200);
    }
}
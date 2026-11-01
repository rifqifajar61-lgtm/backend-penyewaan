<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PelangganController extends Controller
{
    // ============================================================
    // DAFTAR PELANGGAN
    // GET /api/pelanggan
    // ============================================================
    public function index(): JsonResponse
    {
        $pelanggan = Pelanggan::with('data')
            ->latest('pelanggan_id')
            ->get();

        $pelanggan->makeHidden([
            'pelanggan_password',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Daftar pelanggan berhasil diambil.',
            'data'    => $pelanggan,
        ], 200);
    }


    // ============================================================
    // REGISTER PELANGGAN - EXPO
    // POST /api/pelanggan/register
    // ============================================================
   public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap'   => 'required|string|max:150',
            'alamat'         => 'required|string|max:200',
            'no_telp'        => 'required|string|max:13',
            'email'          => 'required|email|max:100|unique:pelanggans,pelanggan_email',
            'password'       => 'required|string|min:6',
            'jenis_dokumen'  => 'required|in:KTP,SIM',
            'ktp_sim'        => 'required|file|image|max:5120',
        ], [
            'nama_lengkap.required' => 'Nama wajib diisi.',
            'nama_lengkap.max'      => 'Nama maksimal 150 karakter.',

            'alamat.required' => 'Alamat wajib diisi.',
            'alamat.max'      => 'Alamat maksimal 200 karakter.',

            'no_telp.required' => 'Nomor telepon wajib diisi.',
            'no_telp.max'      => 'Nomor telepon maksimal 13 karakter.',

            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.max'      => 'Email maksimal 100 karakter.',
            'email.unique'   => 'Email sudah terdaftar.',

            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 6 karakter.',

            'jenis_dokumen.required' => 'Pilih jenis dokumen (KTP atau SIM).',
            'jenis_dokumen.in'       => 'Jenis dokumen harus KTP atau SIM.',

            'ktp_sim.required' => 'Dokumen KTP/SIM wajib diupload.',
            'ktp_sim.image'    => 'File harus berupa gambar.',
            'ktp_sim.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Registrasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // ========================================================
        // SIMPAN DATA PELANGGAN
        // ========================================================
        $pelanggan = Pelanggan::create([
            'pelanggan_nama'     => $request->nama_lengkap,
            'pelanggan_alamat'   => $request->alamat,
            'pelanggan_notelp'   => $request->no_telp,
            'pelanggan_email'    => $request->email,
            'pelanggan_password' => Hash::make($request->password),
        ]);

        // ========================================================
        // UPLOAD KTP / SIM (sesuai pilihan user)
        // ========================================================
        if ($request->hasFile('ktp_sim')) {
            $file = $request->file('ktp_sim');

            // Simpan file ke:
            // storage/app/public/ktp_sim
            $path = $file->store('ktp_sim', 'public');

            $pelanggan->data()->create([
                'pelanggan_data_jenis' => $request->jenis_dokumen, // KTP atau SIM
                'pelanggan_data_file'  => $path,
            ]);
        }

        // Ambil relasi data
        $pelanggan->load('data');

        // Jangan kirim password ke client
        $pelanggan->makeHidden([
            'pelanggan_password',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registrasi pelanggan berhasil.',
            'data'    => $pelanggan,
        ], 201);
    }


    // ============================================================
    // LOGIN PELANGGAN
    // POST /api/pelanggan/login
    // ============================================================
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Login gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Cari pelanggan berdasarkan email
        $pelanggan = Pelanggan::where(
            'pelanggan_email',
            $request->email
        )->first();

        if (!$pelanggan) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
            ], 401);
        }

        // Cek password
        if (!Hash::check(
            $request->password,
            $pelanggan->pelanggan_password
        )) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
            ], 401);
        }

        // Ambil relasi data
        $pelanggan->load('data');

        // Jangan kirim password
        $pelanggan->makeHidden([
            'pelanggan_password',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login pelanggan berhasil.',
            'data'    => $pelanggan,
        ], 200);
    }


    // ============================================================
    // TAMBAH PELANGGAN
    // POST /api/pelanggan
    // ============================================================
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pelanggan_nama'     => 'required|string|max:150',
            'pelanggan_alamat'   => 'required|string|max:200',
            'pelanggan_notelp'   => 'required|string|max:13',
            'pelanggan_email'    => 'required|email|max:100|unique:pelanggans,pelanggan_email',
            'pelanggan_password' => 'required|string|min:6',
        ], [
            'pelanggan_nama.required' => 'Nama pelanggan wajib diisi.',
            'pelanggan_nama.max'      => 'Nama pelanggan maksimal 150 karakter.',

            'pelanggan_alamat.required' => 'Alamat pelanggan wajib diisi.',
            'pelanggan_alamat.max'      => 'Alamat pelanggan maksimal 200 karakter.',

            'pelanggan_notelp.required' => 'Nomor telepon wajib diisi.',
            'pelanggan_notelp.max'      => 'Nomor telepon maksimal 13 karakter.',

            'pelanggan_email.required' => 'Email pelanggan wajib diisi.',
            'pelanggan_email.email'    => 'Format email tidak valid.',
            'pelanggan_email.max'      => 'Email maksimal 100 karakter.',
            'pelanggan_email.unique'   => 'Email sudah digunakan.',

            'pelanggan_password.required' => 'Password wajib diisi.',
            'pelanggan_password.min'      => 'Password minimal 6 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data pelanggan.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $pelanggan = Pelanggan::create([
            'pelanggan_nama'     => $request->pelanggan_nama,
            'pelanggan_alamat'   => $request->pelanggan_alamat,
            'pelanggan_notelp'   => $request->pelanggan_notelp,
            'pelanggan_email'    => $request->pelanggan_email,
            'pelanggan_password' => Hash::make($request->pelanggan_password),
        ]);

        $pelanggan->makeHidden([
            'pelanggan_password',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data pelanggan berhasil ditambahkan.',
            'data'    => $pelanggan,
        ], 201);
    }


    // ============================================================
    // DETAIL PELANGGAN
    // GET /api/pelanggan/{pelanggan}
    // ============================================================
    public function show(Pelanggan $pelanggan): JsonResponse
    {
        $pelanggan->load(
            'data',
            'penyewaan'
        );

        $pelanggan->makeHidden([
            'pelanggan_password',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail pelanggan berhasil diambil.',
            'data'    => $pelanggan,
        ], 200);
    }


    // ============================================================
    // UPDATE PELANGGAN
    // PUT/PATCH /api/pelanggan/{pelanggan}
    // ============================================================
    public function update(
        Request $request,
        Pelanggan $pelanggan
    ): JsonResponse {
        $validator = Validator::make($request->all(), [
            'pelanggan_nama'     => 'sometimes|required|string|max:150',
            'pelanggan_alamat'   => 'sometimes|required|string|max:200',
            'pelanggan_notelp'   => 'sometimes|required|string|max:13',
            'pelanggan_email' => 'sometimes|required|email|max:100|unique:pelanggans,pelanggan_email,' . $pelanggan->pelanggan_id . ',pelanggan_id',
            'pelanggan_password' => 'sometimes|required|string|min:6',
        ], [
            'pelanggan_nama.required' => 'Nama pelanggan wajib diisi.',
            'pelanggan_nama.max'      => 'Nama pelanggan maksimal 150 karakter.',

            'pelanggan_alamat.required' => 'Alamat pelanggan wajib diisi.',
            'pelanggan_alamat.max'      => 'Alamat pelanggan maksimal 200 karakter.',

            'pelanggan_notelp.required' => 'Nomor telepon wajib diisi.',
            'pelanggan_notelp.max'      => 'Nomor telepon maksimal 13 karakter.',

            'pelanggan_email.required' => 'Email pelanggan wajib diisi.',
            'pelanggan_email.email'    => 'Format email tidak valid.',
            'pelanggan_email.max'      => 'Email maksimal 100 karakter.',
            'pelanggan_email.unique'   => 'Email sudah digunakan.',

            'pelanggan_password.required' => 'Password wajib diisi.',
            'pelanggan_password.min'      => 'Password minimal 6 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data pelanggan.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Hash password jika password diubah
        if (isset($data['pelanggan_password'])) {
            $data['pelanggan_password'] = Hash::make(
                $data['pelanggan_password']
            );
        }

        $pelanggan->update($data);

        $pelanggan->refresh();

        $pelanggan->makeHidden([
            'pelanggan_password',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data pelanggan berhasil diperbarui.',
            'data'    => $pelanggan,
        ], 200);
    }


    // ============================================================
    // HAPUS PELANGGAN
    // DELETE /api/pelanggan/{pelanggan}
    // ============================================================
    public function destroy(Pelanggan $pelanggan): JsonResponse
    {
        $pelanggan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data pelanggan berhasil dihapus.',
            'data'    => null,
        ], 200);
    }
}
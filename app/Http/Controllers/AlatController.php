<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AlatController extends Controller
{
    // GET /api/alat
    public function index(): JsonResponse
    {
        $alat = Alat::with('kategori')->latest()->get();

        if ($alat->isEmpty()) {
            return $this->successResponse('Successfully get alat data', null);
        }

        return $this->successResponse('Successfully get alat data', $alat);
    }

    // POST /api/alat
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'alat_kategori_id'  => 'required|exists:kategoris,kategori_id',
            'alat_nama'         => 'required|string|max:150',
            'alat_deskripsi'    => 'required|string|max:255',
            'alat_gambar'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'alat_hargaperhari' => 'required|integer|min:0',
            'alat_stok'         => 'required|integer|min:0',
        ], [
            'alat_kategori_id.required'  => 'Kategori alat wajib dipilih.',
            'alat_kategori_id.exists'    => 'Kategori yang dipilih tidak ditemukan.',
            'alat_nama.required'         => 'Nama alat wajib diisi.',
            'alat_nama.max'              => 'Nama alat maksimal 150 karakter.',
            'alat_deskripsi.required'    => 'Deskripsi alat wajib diisi.',
            'alat_deskripsi.max'         => 'Deskripsi alat maksimal 255 karakter.',
            'alat_gambar.image'          => 'File harus berupa gambar.',
            'alat_gambar.mimes'          => 'Format gambar harus JPG, PNG, atau WebP.',
            'alat_gambar.max'            => 'Ukuran gambar maksimal 2MB.',
            'alat_hargaperhari.required' => 'Harga per hari wajib diisi.',
            'alat_hargaperhari.integer'  => 'Harga per hari harus berupa angka.',
            'alat_hargaperhari.min'      => 'Harga per hari tidak boleh negatif.',
            'alat_stok.required'         => 'Stok alat wajib diisi.',
            'alat_stok.integer'          => 'Stok alat harus berupa angka.',
            'alat_stok.min'              => 'Stok alat tidak boleh negatif.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator, 'Gagal menambahkan data alat!');
        }

        $data = $validator->validated();

        if ($request->hasFile('alat_gambar')) {
            $data['alat_gambar'] = $request->file('alat_gambar')->store('alat', 'public');
        }

        $alat = Alat::create($data);

        return $this->successResponse('Successfully created alat data', $alat, 201);
    }

    // GET /api/alat/{alat}
    public function show(Alat $alat): JsonResponse
    {
        $alat->load('kategori');

        return $this->successResponse('Successfully get alat data', $alat);
    }

    // PATCH /api/alat/{alat}
    public function update(Request $request, Alat $alat): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'alat_kategori_id'  => 'sometimes|required|exists:kategoris,kategori_id',
            'alat_nama'         => 'sometimes|required|string|max:150',
            'alat_deskripsi'    => 'sometimes|required|string|max:255',
            'alat_gambar'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'alat_hargaperhari' => 'sometimes|required|integer|min:0',
            'alat_stok'         => 'sometimes|required|integer|min:0',
        ], [
            'alat_kategori_id.exists'    => 'Kategori yang dipilih tidak ditemukan.',
            'alat_nama.max'              => 'Nama alat maksimal 150 karakter.',
            'alat_deskripsi.max'         => 'Deskripsi alat maksimal 255 karakter.',
            'alat_gambar.image'          => 'File harus berupa gambar.',
            'alat_gambar.mimes'          => 'Format gambar harus JPG, PNG, atau WebP.',
            'alat_gambar.max'            => 'Ukuran gambar maksimal 2MB.',
            'alat_hargaperhari.integer'  => 'Harga per hari harus berupa angka.',
            'alat_hargaperhari.min'      => 'Harga per hari tidak boleh negatif.',
            'alat_stok.integer'          => 'Stok alat harus berupa angka.',
            'alat_stok.min'              => 'Stok alat tidak boleh negatif.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator, 'Gagal memperbarui data alat!');
        }

        $data = $validator->validated();

        if ($request->hasFile('alat_gambar')) {
            // Hapus foto lama supaya tidak numpuk file yang tidak terpakai.
            if ($alat->alat_gambar) {
                Storage::disk('public')->delete($alat->alat_gambar);
            }
            $data['alat_gambar'] = $request->file('alat_gambar')->store('alat', 'public');
        }

        $alat->update($data);

        return $this->successResponse('Successfully updated alat data', $alat);
    }

    // DELETE /api/alat/{alat}
    public function destroy(Alat $alat): JsonResponse
    {
        if ($alat->alat_gambar) {
            Storage::disk('public')->delete($alat->alat_gambar);
        }

        $alat->delete();

        return $this->successResponse('Successfully deleted alat data', null);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class KategoriController extends Controller
{
    // GET /api/kategori
    public function index(): JsonResponse
    {
        $kategori = Kategori::withCount('alat')
            ->latest()
            ->get();

        if ($kategori->isEmpty()) {
            return $this->successResponse(
                'Successfully get kategori data',
                []
            );
        }

        return $this->successResponse(
            'Successfully get kategori data',
            $kategori
        );
    }

    // GET /api/kategori/{kategori}
    public function show(Kategori $kategori): JsonResponse
    {
        $kategori->load('alat');

        return $this->successResponse(
            'Successfully get kategori data',
            $kategori
        );
    }

    // POST /api/kategori
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make(
            $request->all(),
            [
                'kategori_nama' => 'required|string|max:100|unique:kategoris,kategori_nama',
            ],
            [
                'kategori_nama.required' => 'Nama kategori wajib diisi.',
                'kategori_nama.max'      => 'Nama kategori maksimal 100 karakter.',
                'kategori_nama.unique'   => 'Nama kategori sudah ada.',
            ]
        );

        if ($validator->fails()) {
            return $this->validationErrorResponse(
                $validator,
                'Gagal menambahkan data kategori!'
            );
        }

        $kategori = Kategori::create(
            $validator->validated()
        );

        // Ambil jumlah alat setelah dibuat
        $kategori->loadCount('alat');

        return $this->successResponse(
            'Successfully created kategori data',
            $kategori,
            201
        );
    }

    // PATCH /api/kategori/{kategori}
    public function update(
        Request $request,
        Kategori $kategori
    ): JsonResponse {
        $validator = Validator::make(
            $request->all(),
            [
                'kategori_nama' =>
                    'sometimes|required|string|max:100|unique:kategoris,kategori_nama,' .
                    $kategori->kategori_id .
                    ',kategori_id',
            ],
            [
                'kategori_nama.required' => 'Nama kategori wajib diisi.',
                'kategori_nama.max'      => 'Nama kategori maksimal 100 karakter.',
                'kategori_nama.unique'   => 'Nama kategori sudah ada.',
            ]
        );

        if ($validator->fails()) {
            return $this->validationErrorResponse(
                $validator,
                'Gagal memperbarui data kategori!'
            );
        }

        $kategori->update(
            $validator->validated()
        );

        // Sertakan jumlah alat
        $kategori->loadCount('alat');

        return $this->successResponse(
            'Successfully updated kategori data',
            $kategori
        );
    }

    // DELETE /api/kategori/{kategori}
    public function destroy(
        Kategori $kategori
    ): JsonResponse {
        // Cek jumlah alat terlebih dahulu
        $jumlahAlat = $kategori->alat()->count();

        if ($jumlahAlat > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak dapat dihapus karena masih digunakan oleh ' .
                    $jumlahAlat .
                    ' barang.',
                'alat_count' => $jumlahAlat,
            ], 422);
        }

        $kategori->delete();

        return $this->successResponse(
            'Successfully deleted kategori data',
            null
        );
    }
}
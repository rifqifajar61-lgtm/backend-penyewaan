<?php

namespace App\Http\Controllers;

use App\Models\PelangganData;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PelangganDataController extends Controller
{
    // GET /api/pelanggan-data
    public function index(): JsonResponse
    {
        $data = PelangganData::with('pelanggan')->latest()->get();

        if ($data->isEmpty()) {
            return $this->successResponse('Successfully get pelanggan data', null);
        }

        return $this->successResponse('Successfully get pelanggan data', $data);
    }

    // POST /api/pelanggan-data
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pelanggan_data_pelanggan_id' => 'required|exists:pelanggans,pelanggan_id',
            'pelanggan_data_jenis'        => 'required|in:KTP,SIM',
            'pelanggan_data_file'         => 'required|file|mimes:jpg,png,jpeg|max:2048',
        ], [
            'pelanggan_data_pelanggan_id.required' => 'Pelanggan wajib dipilih.',
            'pelanggan_data_pelanggan_id.exists'   => 'Pelanggan yang dipilih tidak ditemukan.',
            'pelanggan_data_jenis.required'        => 'Jenis dokumen wajib dipilih.',
            'pelanggan_data_jenis.in'              => 'Jenis dokumen harus KTP atau SIM.',
            'pelanggan_data_file.required'         => 'File dokumen wajib diunggah.',
            'pelanggan_data_file.file'             => 'File yang diunggah tidak valid.',
            'pelanggan_data_file.mimes'            => 'File harus memiliki format .jpg, .png, atau .jpeg',
            'pelanggan_data_file.max'              => 'Ukuran file maksimal 2MB.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator, 'Gagal menambahkan data pelanggan!');
        }

        $validated = $validator->validated();
        $path = $request->file('pelanggan_data_file')->store('pelanggan_data', 'public');

        $data = PelangganData::create([
            'pelanggan_data_pelanggan_id' => $validated['pelanggan_data_pelanggan_id'],
            'pelanggan_data_jenis'        => $validated['pelanggan_data_jenis'],
            'pelanggan_data_file'         => $path,
        ]);

        return $this->successResponse('Successfully created pelanggan data', $data, 201);
    }

    // GET /api/pelanggan-data/{pelanggan_data}
    public function show(PelangganData $pelanggan_data): JsonResponse
    {
        $pelanggan_data->load('pelanggan');

        return $this->successResponse('Successfully get pelanggan data', $pelanggan_data);
    }

    // PATCH /api/pelanggan-data/{pelanggan_data}
    // CATATAN: kalau upload file baru via PATCH, kirim sebagai POST + field _method=PATCH
    // (form-data tidak terbaca PHP untuk method PATCH asli)
    public function update(Request $request, PelangganData $pelanggan_data): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pelanggan_data_pelanggan_id' => 'sometimes|required|exists:pelanggans,pelanggan_id',
            'pelanggan_data_jenis'        => 'sometimes|required|in:KTP,SIM',
            'pelanggan_data_file'         => 'nullable|file|mimes:jpg,png,jpeg|max:2048',
        ], [
            'pelanggan_data_pelanggan_id.exists' => 'Pelanggan yang dipilih tidak ditemukan.',
            'pelanggan_data_jenis.in'            => 'Jenis dokumen harus KTP atau SIM.',
            'pelanggan_data_file.file'           => 'File yang diunggah tidak valid.',
            'pelanggan_data_file.mimes'          => 'File harus memiliki format .jpg, .png, atau .jpeg',
            'pelanggan_data_file.max'            => 'Ukuran file maksimal 2MB.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator, 'Gagal memperbarui data pelanggan!');
        }

        $validated = $validator->validated();

        if ($request->hasFile('pelanggan_data_file')) {
            // hapus file lama supaya storage tidak numpuk sampah
            if ($pelanggan_data->pelanggan_data_file) {
                Storage::disk('public')->delete($pelanggan_data->pelanggan_data_file);
            }

            $validated['pelanggan_data_file'] = $request->file('pelanggan_data_file')
                ->store('pelanggan_data', 'public');
        }

        $pelanggan_data->update($validated);

        return $this->successResponse('Successfully updated pelanggan data', $pelanggan_data);
    }

    // DELETE /api/pelanggan-data/{pelanggan_data}
    public function destroy(PelangganData $pelanggan_data): JsonResponse
    {
        if ($pelanggan_data->pelanggan_data_file) {
            Storage::disk('public')->delete($pelanggan_data->pelanggan_data_file);
        }

        $pelanggan_data->delete();

        return $this->successResponse('Successfully deleted pelanggan data', null);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Penyewaan;
use App\Models\PenyewaanDetail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PenyewaanDetailController extends Controller
{
    // GET /api/penyewaan-detail
    public function index(): JsonResponse
    {
        $detail = PenyewaanDetail::with(['alat', 'penyewaan'])->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar detail penyewaan berhasil diambil.',
            'data'    => $detail,
        ], 200);
    }

    // POST /api/penyewaan-detail
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'penyewaan_detail_penyewaan_id' => 'required|exists:penyewaans,penyewaan_id',
            'penyewaan_detail_alat_id'      => 'required|exists:alats,alat_id',
            'penyewaan_detail_jumlah'       => 'required|integer|min:1',
        ], [
            'penyewaan_detail_penyewaan_id.required' => 'Penyewaan wajib diisi.',
            'penyewaan_detail_penyewaan_id.exists'   => 'Penyewaan tidak ditemukan.',
            'penyewaan_detail_alat_id.required'      => 'Alat wajib dipilih.',
            'penyewaan_detail_alat_id.exists'        => 'Alat yang dipilih tidak ditemukan.',
            'penyewaan_detail_jumlah.required'       => 'Jumlah alat wajib diisi.',
            'penyewaan_detail_jumlah.integer'        => 'Jumlah alat harus berupa angka.',
            'penyewaan_detail_jumlah.min'             => 'Jumlah alat minimal 1.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator, 'Gagal menambahkan alat ke penyewaan!');
        }

        $validated = $validator->validated();

        DB::beginTransaction();
        try {
            $penyewaan = Penyewaan::findOrFail($validated['penyewaan_detail_penyewaan_id']);
            $alat = Alat::findOrFail($validated['penyewaan_detail_alat_id']);

            if ($alat->alat_stok < $validated['penyewaan_detail_jumlah']) {
                throw new \Exception("Stok alat '{$alat->alat_nama}' tidak mencukupi.");
            }

            $lamaSewa = \Carbon\Carbon::parse($penyewaan->penyewaan_tglsewa)
                ->diffInDays(\Carbon\Carbon::parse($penyewaan->penyewaan_tglkembali)) ?: 1;

            $subharga = $alat->alat_hargaperhari * $validated['penyewaan_detail_jumlah'] * $lamaSewa;

            $detail = PenyewaanDetail::create([
                'penyewaan_detail_penyewaan_id' => $penyewaan->penyewaan_id,
                'penyewaan_detail_alat_id'      => $alat->alat_id,
                'penyewaan_detail_jumlah'       => $validated['penyewaan_detail_jumlah'],
                'penyewaan_detail_subharga'     => $subharga,
            ]);

            $alat->decrement('alat_stok', $validated['penyewaan_detail_jumlah']);
            $penyewaan->increment('penyewaan_totalharga', $subharga);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Alat berhasil ditambahkan ke penyewaan.',
                'data'    => $detail->load('alat'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan alat ke penyewaan!',
                'data'    => null,
                'errors'  => [['stok' => $e->getMessage()]],
            ], 422);
        }
    }

    // GET /api/penyewaan-detail/{penyewaan_detail}
    public function show(PenyewaanDetail $penyewaan_detail): JsonResponse
    {
        $penyewaan_detail->load('alat', 'penyewaan');

        return response()->json([
            'success' => true,
            'message' => 'Detail item penyewaan berhasil diambil.',
            'data'    => $penyewaan_detail,
        ], 200);
    }

    // PATCH/PUT /api/penyewaan-detail/{penyewaan_detail}
    public function update(Request $request, PenyewaanDetail $penyewaan_detail): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'penyewaan_detail_jumlah' => 'required|integer|min:1',
        ], [
            'penyewaan_detail_jumlah.required' => 'Jumlah alat wajib diisi.',
            'penyewaan_detail_jumlah.integer'  => 'Jumlah alat harus berupa angka.',
            'penyewaan_detail_jumlah.min'      => 'Jumlah alat minimal 1.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator, 'Gagal memperbarui detail penyewaan!');
        }

        $validated = $validator->validated();

        DB::beginTransaction();
        try {
            $penyewaan = $penyewaan_detail->penyewaan;
            $alat = $penyewaan_detail->alat;
            $jumlahLama = $penyewaan_detail->penyewaan_detail_jumlah;
            $jumlahBaru = $validated['penyewaan_detail_jumlah'];
            $selisih = $jumlahBaru - $jumlahLama;

            if ($selisih > 0 && $alat->alat_stok < $selisih) {
                throw new \Exception("Stok alat '{$alat->alat_nama}' tidak mencukupi.");
            }

            $lamaSewa = \Carbon\Carbon::parse($penyewaan->penyewaan_tglsewa)
                ->diffInDays(\Carbon\Carbon::parse($penyewaan->penyewaan_tglkembali)) ?: 1;

            $subhargaBaru = $alat->alat_hargaperhari * $jumlahBaru * $lamaSewa;
            $selisihHarga = $subhargaBaru - $penyewaan_detail->penyewaan_detail_subharga;

            $penyewaan_detail->update([
                'penyewaan_detail_jumlah'   => $jumlahBaru,
                'penyewaan_detail_subharga' => $subhargaBaru,
            ]);

            $alat->decrement('alat_stok', $selisih);
            $penyewaan->increment('penyewaan_totalharga', $selisihHarga);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Detail penyewaan berhasil diperbarui.',
                'data'    => $penyewaan_detail,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui detail penyewaan!',
                'data'    => null,
                'errors'  => [['stok' => $e->getMessage()]],
            ], 422);
        }
    }

    // DELETE /api/penyewaan-detail/{penyewaan_detail}
    public function destroy(PenyewaanDetail $penyewaan_detail): JsonResponse
    {
        DB::beginTransaction();
        try {
            $penyewaan_detail->alat->increment('alat_stok', $penyewaan_detail->penyewaan_detail_jumlah);
            $penyewaan_detail->penyewaan->decrement('penyewaan_totalharga', $penyewaan_detail->penyewaan_detail_subharga);
            $penyewaan_detail->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Item alat berhasil dihapus dari penyewaan.',
                'data'    => null,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus item dari penyewaan!',
                'data'    => null,
                'errors'  => [['penyewaan_detail' => $e->getMessage()]],
            ], 422);
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Penyewaan;
use App\Models\PenyewaanDetail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PenyewaanController extends Controller
{
    // GET /api/penyewaan
    public function index(): JsonResponse
    {
        $penyewaan = Penyewaan::with('pelanggan', 'detail.alat')->latest()->get();

        if ($penyewaan->isEmpty()) {
            return $this->successResponse('Successfully get penyewaan data', null);
        }

        return $this->successResponse('Successfully get penyewaan data', $penyewaan);
    }

    // POST /api/penyewaan
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'penyewaan_pelanggan_id'   => 'required|exists:pelanggans,pelanggan_id',
            'penyewaan_tglsewa'        => 'required|date',
            'penyewaan_tglkembali'     => 'required|date|after_or_equal:penyewaan_tglsewa',
            'penyewaan_sttspembayaran' => 'required|in:Lunas,Belum Dibayar,DP',
            'penyewaan_sttskembali'    => 'required|in:Sudah Kembali,Belum Kembali',
            'alat_id'                  => 'required|array|min:1',
            'alat_id.*'                => 'required|exists:alats,alat_id',
            'jumlah'                   => 'required|array|min:1',
            'jumlah.*'                 => 'required|integer|min:1',
        ], [
            'penyewaan_pelanggan_id.required'     => 'Pelanggan wajib dipilih.',
            'penyewaan_pelanggan_id.exists'       => 'Pelanggan yang dipilih tidak ditemukan.',
            'penyewaan_tglsewa.required'          => 'Tanggal sewa wajib diisi.',
            'penyewaan_tglsewa.date'              => 'Tanggal sewa harus berupa tanggal yang valid.',
            'penyewaan_tglkembali.required'       => 'Tanggal kembali wajib diisi.',
            'penyewaan_tglkembali.date'           => 'Tanggal kembali harus berupa tanggal yang valid.',
            'penyewaan_tglkembali.after_or_equal' => 'Tanggal kembali tidak boleh sebelum tanggal sewa.',
            'penyewaan_sttspembayaran.required'   => 'Status pembayaran wajib diisi.',
            'penyewaan_sttspembayaran.in'         => 'Status pembayaran harus Lunas, Belum Dibayar, atau DP.',
            'penyewaan_sttskembali.required'      => 'Status kembali wajib diisi.',
            'penyewaan_sttskembali.in'            => 'Status kembali harus Sudah Kembali atau Belum Kembali.',
            'alat_id.required'                    => 'Minimal satu alat harus dipilih.',
            'alat_id.array'                       => 'Data alat tidak valid.',
            'alat_id.*.required'                  => 'Alat wajib dipilih.',
            'alat_id.*.exists'                    => 'Salah satu alat yang dipilih tidak ditemukan.',
            'jumlah.required'                     => 'Jumlah alat wajib diisi.',
            'jumlah.array'                        => 'Data jumlah tidak valid.',
            'jumlah.*.required'                   => 'Jumlah alat wajib diisi.',
            'jumlah.*.integer'                    => 'Jumlah alat harus berupa angka.',
            'jumlah.*.min'                        => 'Jumlah alat minimal 1.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator, 'Gagal menambahkan data penyewaan!');
        }

        $validated = $validator->validated();

        DB::beginTransaction();
        try {
            $totalHarga = 0;
            $lamaSewa = Carbon::parse($validated['penyewaan_tglsewa'])
                ->diffInDays(Carbon::parse($validated['penyewaan_tglkembali'])) ?: 1;

            $penyewaan = Penyewaan::create([
                'penyewaan_pelanggan_id'   => $validated['penyewaan_pelanggan_id'],
                'penyewaan_tglsewa'        => $validated['penyewaan_tglsewa'],
                'penyewaan_tglkembali'     => $validated['penyewaan_tglkembali'],
                'penyewaan_sttspembayaran' => $validated['penyewaan_sttspembayaran'],
                'penyewaan_sttskembali'    => $validated['penyewaan_sttskembali'],
                'penyewaan_totalharga'     => 0,
            ]);

            foreach ($validated['alat_id'] as $index => $alatId) {
                $alat = Alat::findOrFail($alatId);
                $jumlah = $validated['jumlah'][$index];

                if ($alat->alat_stok < $jumlah) {
                    throw new \Exception("Stok alat '{$alat->alat_nama}' tidak mencukupi.");
                }

                $subharga = $alat->alat_hargaperhari * $jumlah * $lamaSewa;
                $totalHarga += $subharga;

                PenyewaanDetail::create([
                    'penyewaan_detail_penyewaan_id' => $penyewaan->penyewaan_id,
                    'penyewaan_detail_alat_id'      => $alatId,
                    'penyewaan_detail_jumlah'       => $jumlah,
                    'penyewaan_detail_subharga'     => $subharga,
                ]);

                $alat->decrement('alat_stok', $jumlah);
            }

            $penyewaan->update(['penyewaan_totalharga' => $totalHarga]);

            DB::commit();

            return $this->successResponse(
                'Successfully created penyewaan data',
                $penyewaan->load('detail.alat'),
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan data penyewaan!',
                'data'    => null,
                'errors'  => [['stok' => $e->getMessage()]],
            ], 422);
        }
    }

    // GET /api/penyewaan/{penyewaan}
    public function show(Penyewaan $penyewaan): JsonResponse
    {
        $penyewaan->load('pelanggan', 'detail.alat');

        return $this->successResponse('Successfully get penyewaan data', $penyewaan);
    }

    // PATCH /api/penyewaan/{penyewaan}
    public function update(Request $request, Penyewaan $penyewaan): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'penyewaan_pelanggan_id'   => 'sometimes|required|exists:pelanggans,pelanggan_id',
            'penyewaan_tglsewa'        => 'sometimes|required|date',
            'penyewaan_tglkembali'     => 'sometimes|required|date|after_or_equal:penyewaan_tglsewa',
            'penyewaan_sttspembayaran' => 'sometimes|required|in:Lunas,Belum Dibayar,DP',
            'penyewaan_sttskembali'    => 'sometimes|required|in:Sudah Kembali,Belum Kembali',
        ], [
            'penyewaan_pelanggan_id.exists'       => 'Pelanggan yang dipilih tidak ditemukan.',
            'penyewaan_tglsewa.date'              => 'Tanggal sewa harus berupa tanggal yang valid.',
            'penyewaan_tglkembali.date'           => 'Tanggal kembali harus berupa tanggal yang valid.',
            'penyewaan_tglkembali.after_or_equal' => 'Tanggal kembali tidak boleh sebelum tanggal sewa.',
            'penyewaan_sttspembayaran.in'         => 'Status pembayaran harus Lunas, Belum Dibayar, atau DP.',
            'penyewaan_sttskembali.in'            => 'Status kembali harus Sudah Kembali atau Belum Kembali.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator, 'Gagal memperbarui data penyewaan!');
        }

        $validated = $validator->validated();

        // Dua arah transisi status kembali, supaya stok selalu simetris
        // meskipun statusnya di-toggle bolak-balik:
        // - Belum Kembali -> Sudah Kembali : alat fisik balik, stok DITAMBAH
        // - Sudah Kembali -> Belum Kembali : dikoreksi/dibatalkan, alat
        //   dianggap dipinjam lagi, stok DIKURANGI
        $jadiSudahKembali = isset($validated['penyewaan_sttskembali'])
            && $validated['penyewaan_sttskembali'] === 'Sudah Kembali'
            && $penyewaan->penyewaan_sttskembali !== 'Sudah Kembali';

        $jadiBelumKembali = isset($validated['penyewaan_sttskembali'])
            && $validated['penyewaan_sttskembali'] === 'Belum Kembali'
            && $penyewaan->penyewaan_sttskembali === 'Sudah Kembali';

        DB::beginTransaction();
        try {
            $penyewaan->update($validated);

            if ($jadiSudahKembali) {
                foreach ($penyewaan->detail as $detail) {
                    $detail->alat->increment('alat_stok', $detail->penyewaan_detail_jumlah);
                }
            } elseif ($jadiBelumKembali) {
                foreach ($penyewaan->detail as $detail) {
                    $detail->alat->decrement('alat_stok', $detail->penyewaan_detail_jumlah);
                }
            }

            DB::commit();

            return $this->successResponse(
                'Successfully updated penyewaan data',
                $penyewaan->load('detail.alat')
            );
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // DELETE /api/penyewaan/{penyewaan}
    public function destroy(Penyewaan $penyewaan): JsonResponse
    {
        DB::beginTransaction();
        try {
            // Restore stok cuma kalau belum dikembalikan (hindari double increment
            // untuk kasus yang statusnya sudah "Sudah Kembali" via update sebelumnya)
            if ($penyewaan->penyewaan_sttskembali !== 'Sudah Kembali') {
                foreach ($penyewaan->detail as $detail) {
                    $detail->alat->increment('alat_stok', $detail->penyewaan_detail_jumlah);
                }
            }

            $penyewaan->delete();

            DB::commit();

            return $this->successResponse('Successfully deleted penyewaan data', null);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
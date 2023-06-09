<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pengajuan;
use App\Models\User;
use App\Models\Investasi;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;;

class InvestasiController extends Controller
{

    public function getInvestasi($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $investasi = Investasi::where('user_id', $user->id)->get();

        if ($investasi->isEmpty()) {
            return response()->json(['error' => 'Investasi not found'], 404);
        }

        $pengajuanIds = $investasi->pluck('pengajuan_id');
        $pengajuan = Pengajuan::whereIn('id', $pengajuanIds)->get();

        return response()->json([
            'user_id' => $user->id,
            'investasi' => $investasi,
            'pengajuan' => $pengajuan,
        ], 200);
    }


    public function investasi(Request $request, $id)
    {
        $userUser = auth()->guard('user-api')->user();

        if (!$userUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }


        // Mendapatkan id pengajuan dari proyek pengajuan yang sedang aktif
        $pengajuan = Pengajuan::findOrFail($id);
        $pengajuanId = $pengajuan->id;
        $hargaPerUnit = $pengajuan->harga_unit; 

        // Mengambil nilai amount dan unit dari request
        $amount = $request->input('amount');
        $unit = $request->input('unit');

        if ($amount) {
            // Memeriksa apakah amount merupakan kelipatan dari harga per unit
            if ($amount % $hargaPerUnit !== 0) {
                return response()->json(['message' => 'Amount harus merupakan kelipatan dari harga per unit'], 400);
            }

            // Menghitung jumlah unit berdasarkan nominal investasi
            $unit = $amount / $hargaPerUnit;
            // Pembulatan jumlah unit ke angka terdekat
            $unit = round($unit);
        } elseif ($unit) {
            // Menghitung nominal investasi berdasarkan jumlah unit
            $amount = $unit * $hargaPerUnit;
        }

        // Mendapatkan saldo user yang sedang login
        $saldo = $userUser->saldo;

        $iduser = $userUser->id;

        // Memastikan saldo mencukupi untuk investasi
        if ($saldo < $amount) {
            return response()->json(['message' => 'Saldo tidak mencukupi'], 400);
        }

        // Lakukan validasi atau operasi lain yang diperlukan sebelum menyimpan ke database

        // Mulai transaksi database
        DB::beginTransaction();

        try {
            // Simpan data investasi ke database
            $investasi = new Investasi([
                'amount' => $amount,
                'unit' => $unit,
                'user_id' => $iduser,
                'pengajuan_id' => $pengajuanId,
                'status' => 'Proyek Berjalan'
            ]);

            // Simpan investasi terkait dengan pengajuan
            $investasi->save();

            // Kurangi saldo user sesuai dengan jumlah investasi
            $userUser->saldo -= $amount;
            $userUser->save();

            // Tambahkan dana terkumpul pada pengajuan
            $pengajuan->dana_terkumpul += $amount;
            $pengajuan->save();

            // Kurangi jumlah unit yang tersedia pada pengajuan
            $pengajuan->unit_tersedia -= $unit;
            $pengajuan->save();

            // Commit transaksi database
            DB::commit();

            return response()->json([
                'message' => 'Investasi berhasil',
                'pengajuan' => $pengajuan,
                'investasi' => $investasi,
            ], 200);
        } catch (\Exception $e) {
            // Rollback transaksi database jika terjadi error
            DB::rollback();

            return response()->json(['message' => 'Investasi gagal', 'error' => $e->getMessage()], 500);
        }
    }

    public function simulasiHitung(Request $request, $id)
    {

        $pengajuan = Pengajuan::findOrFail($id);
        $pengajuanId = $pengajuan->id;
        $hargaPerUnit = $pengajuan->harga_unit; 

        $validator = Validator::make($request->all(), [
            'nominal' => 'numeric',
            'unit' => 'numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $nominal = $request->input('nominal');
        $unit = $request->input('unit');

        // Validasi apakah nominal merupakan kelipatan harga per unit
        if ($nominal % $hargaPerUnit !== 0) {
            return response()->json(['error' => 'Nominal harus kelipatan harga per unit'], 422);
        }

        $keuntungan = $nominal * $imbalHasil;
        $total = $nominal + $keuntungan;

        $result = [
            'nominal' => $nominal,
            'unit' => $unit,
            'harga_per_unit' => $hargaPerUnit,
            'imbal_hasil' => $imbalHasil,
            'keuntungan' => $keuntungan,
            'total' => $total,
        ];

        return response()->json($result, 200);
    }

    public function totalInvesAktif(){

        $userUser = auth()->guard('user-api')->user();
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $total = Investasi::where('status', 'proyek berjalan')
                      ->where('user_id', $user->id)
                      ->sum('nominal');

        return response()->json(['total' => $total]);
    }

}

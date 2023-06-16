<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Pengajuan;
use App\Models\InfoPemasukan;

class InfoPemasukanController extends Controller
{
    public function getInfoPemasukan($pengajuanId){
        $adminUser = auth()->guard('admin-api')->user();
        $userUser = auth()->guard('user-api')->user();

        if (!$adminUser && !$userUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $pengajuan = Pengajuan::find($pengajuanId);

        if (!$pengajuan) {
            return response()->json(['error' => 'Pengajuan not found'], 404);
        }

        $infopemasukan = $pengajuan->infoPemasukan()->get();

        return response()->json($infopemasukan, 200);
    }

    public function addInfoPemasukan(Request $request, $id){
        $user = auth()->guard('user-api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date|before_or_equal:today',
            'nama_produk' => 'required|string',
            'jumlah' => 'required|numeric',
            'harga' => 'required|numeric',
        ]);
        
        if ($validator->fails()) {
           return response()->json($validator->errors(), 422);
        }

        $pengajuan = Pengajuan::findOrFail($id);

        try {
            $infoPemasukan = InfoPemasukan::create([
                'pengajuan_id' => $pengajuan->id,
                'tanggal' => $request->input('tanggal'),
                'nama_produk' => $request->input('nama_produk'),
                'jumlah' => $request->input('jumlah'),
                'harga' => $request->input('harga'), 
            ]);

            return response()->json([
                'message' => 'Info pemasukan berhasil ditambahkan',
                'info_pemasukan' => $infoPemasukan
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Gagal menyimpan data'], 500);
        }

    }
}

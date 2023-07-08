<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Pengajuan;
use App\Models\InfoPengembalian;

class InfoPengembalianController extends Controller
{

    public function getInfoPengembalian($pengajuanId){
        $adminUser = auth()->guard('admin-api')->user();
        $userUser = auth()->guard('user-api')->user();

        if (!$adminUser && !$userUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $pengajuan = Pengajuan::find($pengajuanId);

        if (!$pengajuan) {
            return response()->json(['error' => 'Pengajuan not found'], 404);
        }

        $infoPengembalian = $pengajuan->infoPengembalian()->get();

        if ($infoPengembalian->isEmpty()) {
            return response()->json(['error' => 'Belum Memiliki Info Pengembalian'], 404);
        }

        return response()->json($infoPengembalian, 200);
    }

    public function addInfoPengembalianPetani(Request $request, $id){

        $user = auth()->guard('user-api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'pilih_pembayaran' => 'required|string',
            'photo' => 'required|image|mimes:jpeg,png,jpg',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pengajuan = Pengajuan::findOrFail($id);


        try {
            $infoPengembalian = InfoPengembalian::create([
            'pengajuan_id' => $pengajuan->id,
            'jumlah_pembayaran' => $pengajuan->total_pengembalian, 
            'pilih_pembayaran' => $request->input('pilih_pembayaran'),
            'nama_bank' => $request->input('nama_bank'),
            'nama_rekening' => $request->input('nama_rekening'),
            'nomor_rekening' => $request->input('nomor_rekening'),
            'deskripsi' => 'Pengembalian Dana oleh Petani', 
            'status' => 'Success'
                
            ]);

            if ($request->hasFile('photo')) {
                $image = $request->file('photo');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move('image', $imageName);

                // Menambahkan field photo ke data yang akan diubah
                $infoPengembalian->photo = $imageName;
            }

            $infoPengembalian->save();

            return response()->json([
                'message' => 'Info pengembalian berhasil ditambahkan',
                'info pengembalian' => $infoPengembalian
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Gagal menyimpan data'], 500);
        }
    }

    public function addInfoPengembalianAdmin(Request $request, $id){

        $user = auth()->guard('admin-api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'deskripsi' => 'required|string',
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pengajuan = Pengajuan::findOrFail($id);


        try {
            $infoPengembalian = InfoPengembalian::create([
            'pengajuan_id' => $pengajuan->id,
            'deskripsi' => $request->input('deskripsi'),
            'status' => $request->input('status'),
                
            ]);

            $infoPengembalian->save();

            return response()->json([
                'message' => 'Info pengembalian berhasil ditambahkan',
                'info pengembalian' => $infoPengembalian
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Gagal menyimpan data'], 500);
        }
    }
}
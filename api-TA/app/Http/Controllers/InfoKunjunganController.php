<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Pengajuan;
use App\Models\InfoKunjungan;


class InfoKunjunganController extends Controller
{
    public function getInfoKunjungan($pengajuanId){
        $adminUser = auth()->guard('admin-api')->user();
        $userUser = auth()->guard('user-api')->user();

        if (!$adminUser && !$userUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $pengajuan = Pengajuan::find($pengajuanId);

        if (!$pengajuan) {
            return response()->json(['error' => 'Pengajuan not found'], 404);
        }

        $infokunjungan = $pengajuan->infoKunjungan()->get();

        return response()->json($infokunjungan, 200);
            
    }

    public function addInfoKunjungan(Request $request, $id){

        $user = auth()->guard('user-api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date|before_or_equal:today',
            'nama_petugas' => 'required|string',
            'tujuan' => 'required|string',
            'photo' => 'required|image|mimes:jpeg,png,jpg',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pengajuan = Pengajuan::findOrFail($id);

        $infoKunjungan = InfoKunjungan::create([
            'pengajuan_id' => $pengajuan->id,
            'tanggal' => $request->input('tanggal'),
            'nama_petugas' => $request->input('nama_petugas'),
            'tujuan' => $request->input('tujuan'),
                
        ]);

        if ($request->hasFile('photo')) {
            $image = $request->file('photo');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move('image', $imageName);

            // Menambahkan field photo ke data yang akan diubah
            $infoKunjungan->photo = $imageName;
        }

        $infoKunjungan->save();

        return response()->json([
            'message' => 'Info kunjungan berhasil ditambahkan',
            'info kunjungan' => $infoKunjungan
        ], 200);
    }
    
}

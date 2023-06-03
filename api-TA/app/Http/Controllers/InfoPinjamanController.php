<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Pengajuan;
use App\Models\FilePinjaman;
use App\Models\InfoPinjaman;

class InfoPinjamanController extends Controller
{

    public function getInfoPinjam($pengajuanId){
        $adminUser = auth()->guard('admin-api')->user();
        $userUser = auth()->guard('user-api')->user();

        if (!$adminUser && !$userUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }


        $pengajuan = Pengajuan::find($pengajuanId);

        if (!$pengajuan) {
            return response()->json(['error' => 'Pengajuan not found'], 404);
        }

        $infoPinjaman = $pengajuan->infoPinjaman()->with('filepinjam')->get();

        return response()->json([$infoPinjaman],200);
    
    }


    public function addInfoPinjam(Request $request, $id){

        $user = auth()->guard('admin-api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'infoPinjam' => 'required|array',
            'infoPinjam.*.tanggal' => 'required|date|before_or_equal:today',
            'infoPinjam.*.barang' => 'required|string',
            'infoPinjam*.jumlah' => 'required|numeric',
            'infoPinjam.*.harga' => 'required|numeric',
            'gambar' => 'required|image|mimes:jpeg,png,jpg',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

    
        $pengajuan = Pengajuan::findorfail($id);

        $idpengajuan = $pengajuan->id;

        $infoPinjamanData = $request->input('infoPinjam');
        $infoPinjamanModels = [];

        foreach ($infoPinjamanData as $infoPinjamanItem) {
            $infoPinjaman = new InfoPinjaman();
            $infoPinjaman->pengajuan_id = $idpengajuan;
            $infoPinjaman->tanggal = $infoPinjamanItem['tanggal'];
            $infoPinjaman->barang = $infoPinjamanItem['barang'];
            $infoPinjaman->jumlah = $infoPinjamanItem['jumlah'];
            $infoPinjaman->harga = $infoPinjamanItem['harga'];
            $infoPinjaman->total = $infoPinjamanItem['jumlah'] * $infoPinjamanItem['harga'];

            $infoPinjamanModels[] = $infoPinjaman;
        }

        $pengajuan->infoPinjaman()->saveMany($infoPinjamanModels);

        $infoPinjam = $infoPinjaman->id;
        
        if ($request->hasFile('gambar')) {
            $image = $request->file('gambar');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move('image', $imageName);

            $file = new FilePinjaman();
            $file->alamat_gambar = $imageName;
            $file->infopinjam_id = $infoPinjam;
            $file->save();
        }

        return response()->json([
            'message' => 'Info Pinjaman created successfully',
            'Informasi Pinjaman' => $infoPinjaman
        ],200);
    }

    public function updateInfoPinjam(Request $request, $id, $infoPinjamId)
    {
        $user = auth()->guard('admin-api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date|before_or_equal:today',
            'barang' => 'required|string',
            'jumlah' => 'required|numeric',
            'harga' => 'required|numeric',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pengajuan = Pengajuan::findorfail($id);

        $infoPinjaman = InfoPinjaman::where('id', $infoPinjamId)
            ->where('pengajuan_id', $pengajuan->id)
            ->first();

        if (!$infoPinjaman) {
            return response()->json(['error' => 'Info Pinjaman not found'], 404);
        }

        $infoPinjaman->tanggal = $request->input('tanggal');
        $infoPinjaman->barang = $request->input('barang');
        $infoPinjaman->jumlah = $request->input('jumlah');
        $infoPinjaman->harga = $request->input('harga');
        $infoPinjaman->total = $request->input('jumlah') * $request->input('harga');
        $infoPinjaman->save();

        if ($request->hasFile('gambar')) {
            $image = $request->file('gambar');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move('image', $imageName);

            $file = FilePinjaman::where('infopinjam_id', $infoPinjamId)->first();
            if ($file) {
                $file->alamat_gambar = $imageName;
                $file->save();
            } else {
                $newFile = new FilePinjaman();
                $newFile->alamat_gambar = $imageName;
                $newFile->infopinjam_id = $infoPinjamId;
                $newFile->save();
            }
        }

        return response()->json([
            'message' => 'Info Pinjaman updated successfully',
            'Informasi Pinjaman' => $infoPinjaman
        ], 200);
    }



}

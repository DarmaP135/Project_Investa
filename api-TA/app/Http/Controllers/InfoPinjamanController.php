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

    public function getInfoPinjam($id){
        $adminUser = auth()->guard('admin-api')->user();
        $userUser = auth()->guard('user-api')->user();

        if (!$adminUser && !$userUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }


        $pengajuan = Pengajuan::findOrFail($id);
    
        $infoPinjaman = $pengajuan->infoPinjaman()->get();
        $fileInfoPinjaman = $pengajuan->filepinjam()->get();

        $totalKeseluruhan = 0;
        foreach ($infoPinjaman as $info) {
            $totalKeseluruhan += $info->total;
        }

        $imbal = $pengajuan->imbal_hasil;
        $imbal_hasil = $pengajuan->imbal_hasil / 100;
        $TotalSetelahImbal = $totalKeseluruhan + ($totalKeseluruhan * $imbal_hasil);

        return response()->json([
            'imbal_hasil' => $imbal,
            'Info Pinjaman' => $infoPinjaman,
            'File Info Pinjaman' => $fileInfoPinjaman,
            'Total Keseluruhan' => $totalKeseluruhan,
            'Total Setelah Imbal' => $TotalSetelahImbal
        ], 200);
    
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
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

    
        $pengajuan = Pengajuan::findorfail($id);

        $idpengajuan = $pengajuan->id;
        $imbal_hasil = $pengajuan->imbal_hasil / 100;
        $totalKeseluruhan = 0;

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

            $totalKeseluruhan += $infoPinjaman->total;
        }

        $pengajuan->infoPinjaman()->saveMany($infoPinjamanModels);

        
        
        if ($request->hasFile('gambar')) {
            $image = $request->file('gambar');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move('image', $imageName);

            $file = new FilePinjaman();
            $file->alamat_gambar = $imageName;
            $file->pengajuan_id = $idpengajuan;
            $file->save();
        }

        $totalPinjam = InfoPinjaman::where('pengajuan_id', $pengajuan->id)->sum('total');
        $TotalSetelahImbal = $totalPinjam + ($totalPinjam * $imbal_hasil); 

        $pengajuan->total_pengembalian = $TotalSetelahImbal;
        $pengajuan->save();

        return response()->json([
            'message' => 'Info Pinjaman created successfully',
            'Informasi Pinjaman' => $infoPinjaman,
            'Total Keseluruhan' => $totalKeseluruhan,
            "Total Setelah Imbal Hasil" => $TotalSetelahImbal
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

        return response()->json([
            'message' => 'Info Pinjaman updated successfully',
            'Informasi Pinjaman' => $infoPinjaman
        ], 200);
    }

    public function updateInfoFilePinjaman(Request $request, $id, $filePinjamanId){
        $user = auth()->guard('admin-api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'gambar' => 'required|image|mimes:jpeg,png,jpg',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $filePinjaman = FilePinjaman::findOrFail($filePinjamanId);

        if ($request->hasFile('gambar')) {
            $image = $request->file('gambar');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move('image', $imageName);

            $filePinjaman->alamat_gambar = $imageName;
            $filePinjaman->save();
        }

        return response()->json([
            'message' => 'File Info Pinjaman updated successfully',
            'File Info Pinjaman' => $filePinjaman
        ], 200);
    }

    public function deleteInfoFilePinjaman($id, $filePinjamanId)
    {
        $user = auth()->guard('admin-api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $filePinjaman = FilePinjaman::findOrFail($filePinjamanId);
        $filePinjaman->delete();

        return response()->json(['message' => 'File Info Pinjaman deleted successfully'], 200);
    }

    public function deleteInfoPinjam($id, $infoPinjamId)
    {
        $user = auth()->guard('admin-api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $infoPinjaman = InfoPinjaman::findOrFail($infoPinjamId);

        $infoPinjaman->delete();

        return response()->json([
            'message' => 'Info Pinjaman deleted successfully'
        ], 200);
    }



}

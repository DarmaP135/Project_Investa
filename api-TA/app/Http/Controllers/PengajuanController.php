<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Pengajuan;
use App\Models\Kebutuhan;
use App\Models\InfoTani;
use App\Models\FilePengajuan;
use Symfony\Component\HttpFoundation\Response;

class PengajuanController extends Controller
{

    public function getPengajuan()
    {
        $user = auth()->guard('user-api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $pengajuan = Pengajuan::with('files', 'kebutuhan')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        if ($pengajuan->isEmpty()) {
            return response()->json(['error' => 'Belum Memiliki Pengajuan'], 404);
        }

        return response()->json($pengajuan);
    }

    public function addPengajuan(Request $request)
    {
         // Ambil user yang sedang login
        $user = auth()->guard('user-api')->user();
        if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'pengajuan_name'        => 'required',
            'komoditas'             => 'required|string',
            'start_date'            => 'required|date|after_or_equal:today',
            'end_date'              => 'required|date|after_or_equal:start_date',
            'estimasi_pengembalian' => 'required|date|after:today',
            'tenor'                 => 'required',
            'gambar'                => 'required|array',
            'gambar.*'              => 'image',
            'kebutuhan.*.nama'      => 'required',
            'kebutuhan.*.jenis'     => 'required',
            'kebutuhan.*.jumlah'    => 'required|numeric',
            'kebutuhan.*.harga'     => 'required|numeric',
            'pengalaman_tani'       => 'required|string',
            'kelompok_tani'         => 'nullable|string',
            'nama_kelompok'         => 'nullable|string',
            'jumlah_anggota'        => 'nullable|numeric',
            'status_lahan'          => 'required|string',
            'luas_lahan'            => 'required|numeric',
            'kecamatan'             => 'required|string',
            'kode_pos'              => 'required|string',
            'alamat'                => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pengajuan = Pengajuan::create([
            'user_id'               => $user->id,
            'pengajuan_name'        => $request->input('pengajuan_name'),
            'start_date'            => $request->input('start_date'),
            'end_date'              => $request->input('end_date'),
            'komoditas'             => $request->input('komoditas'),
            'estimasi_pengembalian' => $request->input('estimasi_pengembalian'),
            'tenor'                 => $request->input('tenor'),
            'status'                => 'Menunggu Konfirmasi',
            
        ]);

        $idinvest = $pengajuan->id;

        if ($request->hasFile('gambar')) {
            $files = [];

            foreach ($request->file('gambar') as $image) {
                $name = time() . '_' . $image->getClientOriginalName();
                $path = $image->move('image', $name);

                $file = new FilePengajuan();
                $file->alamat_gambar = $path;
                $file->pengajuan_id = $idinvest;
                $file->save();

                $files[] = $file;
            }
            $pengajuan->files()->saveMany($files);
        }

        $totalPengajuan = 0;

        foreach ($request->input('kebutuhan') as $kebutuhan) {
            $jumlah = $kebutuhan['jumlah'];
            $harga = $kebutuhan['harga'];
            $total = $jumlah * $harga;
            $totalPengajuan += $total;

            Kebutuhan::create([
                'pengajuan_id' => $idinvest,
                'nama' => $kebutuhan['nama'],
                'jenis' => $kebutuhan['jenis'],
                'jumlah' => $jumlah,
                'harga' => $harga,
                'total' => $total
            ]);
        }

        $pengajuan->total_pengajuan = $totalPengajuan;
        $pengajuan->save();

        $infotani = InfoTani::where('user_id', $user->id)->first();
        if (!$infotani) {
            $infotani = new InfoTani();
            $infotani->user_id = $user->id;
        }

        $infotani->pengalaman_tani = $request->input('pengalaman_tani');
        $infotani->kelompok_tani = $request->input('kelompok_tani');
        $infotani->nama_kelompok = $request->input('nama_kelompok');
        $infotani->jumlah_anggota = $request->input('jumlah_anggota');
        $infotani->status_lahan = $request->input('status_lahan');
        $infotani->luas_lahan = $request->input('luas_lahan');
        $infotani->provinsi = 'Jawa TImur';  //Contoh 
        $infotani->kota = 'Nganjuk';        //Contoh
        $infotani->kecamatan = $request->input('kecamatan');
        $infotani->kode_pos = $request->input('kode_pos');
        $infotani->alamat = $request->input('alamat');
        $infotani->save();



        return response()->json([
            'success' => true,
            'pengajuan' => $pengajuan,
        ], 201);

        //return JSON process insert failed 
        return response()->json([
            'success' => false,
        ], 409);
    }

    public function detailPengajuan($id){

        $user = auth()->guard('user-api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $pengajuan = Pengajuan::with('files', 'kebutuhan')
            ->where('user_id', $user->id)
            ->find($id);

        if (!$pengajuan) {
            return response()->json(['error' => 'Belum Memiliki Pengajuan'], 404);
        }

        $informasiTani = $user->infoTani;

        return response()->json([
            'Pengajuan' => $pengajuan,
            'Informasi Petani' => $informasiTani
        ]);

    }
    
    /**
     * Update berikut untuk admin jadi admin menerima atau menolak pengajuan 
     */
    public function updatePengajuan(Request $request, $id){
 
        $user = auth()->guard('user-api','admin-api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'imbal_hasil' => 'required|numeric|min:0|max:100', 
            'status' => 'required|string',
            'resiko' => 'required|string',
            'jumlah_unit' => 'numeric',
            'deskripsi' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pengajuan = Pengajuan::where('user_id', $user->id)
            ->findOrFail($id);

        // Memperbarui kolom tertentu
        $pengajuan->imbal_hasil = $request->input('imbal_hasil');
        $pengajuan->status = $request->input('status');
        $pengajuan->resiko = $request->input('resiko');
        $pengajuan->jumlah_unit = $request->input('jumlah_unit');
        $pengajuan->deskripsi = $request->input('deskripsi');


        $pengajuan->save();

        // Mengambil data pengajuan dengan relasi yang terkait
        $pengajuan = Pengajuan::with('files', 'kebutuhan')
            ->where('user_id', $user->id)
            ->findOrFail($id);

        return response()->json($pengajuan);
    }

    public function deletePengajuan($id){
        $user = auth()->guard('admin-api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $pengajuan = Pengajuan::where('user_id', $user->id)
            ->findOrFail($id);

        // Hapus relasi file gambar dari storage
        foreach ($pengajuan->files as $file) {
            $imagePath = public_path($file->alamat_gambar);
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        // Hapus relasi file gambar
        $pengajuan->files()->delete();

        // Hapus relasi kebutuhan
        $pengajuan->kebutuhan()->delete();

        // Hapus pengajuan
        $pengajuan->delete();

        return response()->json(['message' => 'Pengajuan deleted successfully']);
    }
}

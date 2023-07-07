<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Pengajuan;
use App\Models\Kebutuhan;
use App\Models\InfoTani;
use App\Models\User;
use App\Models\FilePengajuan;
use Symfony\Component\HttpFoundation\Response;

class PengajuanController extends Controller
{

    public function getPengajuan(){ 
       $user = auth()->guard('user-api')->user();
        if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
        }

        $pengajuan = Pengajuan::with('files', 'kebutuhan', 'infoTani')
            ->where('user_id', $user->id)
            ->get();

        if ($pengajuan->isEmpty()) {
            return response()->json(['error' => 'Belum Memiliki Pengajuan'], 404);
        }

        return response()->json($pengajuan);
    }

    public function getProyek(){
        $user = auth()->guard('admin-api')->user();
        if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
        }

        $pengajuan = Pengajuan::where(function ($query) {
            $query->where('status', 'Proyek Berjalan')
                ->orWhere('status', 'Pendanaan Terpenuhi')
                ->orWhere('status', 'Proyek Selesai');
        })
        
        ->with('kebutuhan', 'files', 'user', 'infoTani')
        ->get();

        if ($pengajuan->isEmpty()) {
            return response()->json(['error' => 'Belum Memiliki Pengajuan'], 404);
        }

        

        return response()->json($pengajuan);
    }

    public function getPengajuanSeluruhnya(){
        $user = auth()->guard('admin-api')->user();
        if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
        }

        $pengajuan = Pengajuan::with('files', 'kebutuhan', 'infoTani')
            ->get();

        if ($pengajuan->isEmpty()) {
            return response()->json(['error' => 'Belum Memiliki Pengajuan'], 404);
        }

        foreach ($pengajuan as $p) {
            $p->updateStatus();
        }

        return response()->json($pengajuan);
    }

    public function pengajuanAccept(){ 
        $adminUser = auth()->guard('admin-api')->user();
        $userUser = auth()->guard('user-api')->user();

        if (!$adminUser && !$userUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = $adminUser ? $adminUser : $userUser;


        $pengajuan = Pengajuan::where(function ($query) {
            $query->where('status', 'Proyek Berjalan')
                ->orWhere('status', 'Pendanaan Terpenuhi');
        })
        
        ->with('kebutuhan', 'files', 'user', 'infoTani')
        ->get();

        foreach ($pengajuan as $p) {
            $p->updateStatus();
        }

        if ($pengajuan->isEmpty()) {
            return response()->json(['error' => 'Belum Memiliki Pengajuan'], 404);
        }
        return response()->json($pengajuan);
    }



    //Petani 
    public function addPengajuan(Request $request){
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
            'metode_pelunasan'      => 'required',
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
            'metode_pelunasan'      => $request->input('metode_pelunasan'),
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

        
        $infotani = InfoTani::create([
            'pengajuan_id' => $idinvest,
            'pengalaman_tani' => $request->input('pengalaman_tani'),
            'kelompok_tani' => $request->input('kelompok_tani'),
            'nama_kelompok' => $request->input('nama_kelompok'),
            'jumlah_anggota' => $request->input('jumlah_anggota'),
            'status_lahan' => $request->input('status_lahan'),
            'luas_lahan' => $request->input('luas_lahan'),
            'provinsi' => $request->input('provinsi'),
            'kota' => $request->input('kota'),
            'kecamatan' => $request->input('kecamatan'),
            'kode_pos' => $request->input('kode_pos'),
            'alamat' => $request->input('alamat'),
        ]);



        return response()->json([
            'success' => true,
            'pengajuan' => $pengajuan,
        ], 200);

        //return JSON process insert failed 
        return response()->json([
            'success' => false,
        ], 409);
    }

    
    public function detailPengajuan($id){

        $adminUser = auth()->guard('admin-api')->user();
        $userUser = auth()->guard('user-api')->user();

        if (!$adminUser && !$userUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = $adminUser ? $adminUser : $userUser;

        $pengajuan = Pengajuan::with('files', 'kebutuhan', 'user', 'infoTani')
            ->findOrFail($id);

        if (!$pengajuan) {
            return response()->json(['error' => 'Belum Memiliki Pengajuan'], 404);
        }

        return response()->json([
            'Pengajuan' => $pengajuan,
        ],200);

    }
    
    /**
     * Update berikut untuk admin jadi admin menerima atau menolak pengajuan 
     */
    public function acceptPengajuan(Request $request, $id){
 
        $user = auth()->guard('admin-api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }


        $pengajuan = Pengajuan::findOrFail($id);
        $jumlahUnit = $request->input('jumlah_unit');
        $hargaPerUnit = 10000; // Harga per unit, misalnya 10000

        $pengajuan->update([
            'imbal_hasil' => $request->input('imbal_hasil'),
            'status' => $request->input('status'),
            'resiko' => $request->input('resiko'),
            'jumlah_unit' => $jumlahUnit,
            'unit_tersedia' => $jumlahUnit,
            'harga_unit' => $hargaPerUnit,
            'deskripsi' => $request->input('deskripsi')
        ]);

        // Mengambil data pengajuan dengan relasi yang terkait
        $pengajuan = Pengajuan::with('files', 'kebutuhan', 'user')
            ->findOrFail($id);

        return response()->json([
            'message' => 'Pengajuan berhasil disetujui',
            'pengajuan' => $pengajuan
        ], 200);
    }

    public function rejectPengajuan(Request $request, $id){
 
        $user = auth()->guard('admin-api')->user();
        if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pengajuan = Pengajuan::findOrFail($id);

        // Memperbarui kolom tertentu
        $pengajuan->status = $request->input('status');


        $pengajuan->save();

        // Mengambil data pengajuan dengan relasi yang terkait
        $pengajuan = Pengajuan::with('files', 'kebutuhan', 'user')
            ->findOrFail($id);

        return response()->json([
            'messagae' => 'Pengajuan ditolak karena tidak memenuhi beberapa syarat',
            'pengajuan' => $pengajuan],200);
    }

    public function deletePengajuan($id){
        $user = auth()->guard('user-api')->user();
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

    public function formTransaksi($id){
        $adminUser = auth()->guard('admin-api')->user();
        $userUser = auth()->guard('user-api')->user();

        if (!$adminUser && !$userUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = $adminUser ? $adminUser : $userUser;

        $pengajuan = Pengajuan::find($id);

        if (!$pengajuan) {
            return response()->json(['error' => 'Pengajuan not found'], 404);
        }

        return response()->json([
            'user' => $user,
            'pengajuan_id' => $pengajuan->id,
            'pengajuan' => $pengajuan
        ], 200);

    }

}

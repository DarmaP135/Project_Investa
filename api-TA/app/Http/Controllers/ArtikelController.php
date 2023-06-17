<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Artikel;

class ArtikelController extends Controller
{
    public function getArtikel(){
        $adminUser = auth()->guard('admin-api')->user();
        $userUser = auth()->guard('user-api')->user();

        if (!$adminUser && !$userUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $artikel = Artikel::all();

        if (!$artikel) {
            return response()->json(['error' => 'Artikel not found'], 404);
        }

        return response()->json($artikel, 200);
    }

    public function addArtikel(Request $request){
        $adminUser = auth()->guard('admin-api')->user();
        if (!$adminUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'judul' => 'required|string',
            'sub_judul' => 'required|string',
            'gambar' => 'image|mimes:jpeg,png,jpg',
            'deskripsi' => 'required|string',
            'tanggal_upload' => 'required|date|before_or_equal:today',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $dataArtikel = Artikel::create([
                'judul' => $request->input('judul'),
                'sub_judul' => $request->input('sub_judul'),
                'deskripsi' => $request->input('deskripsi'),
                'tanggal_upload' => $request->input('tanggal_upload'),
            ]);

            if ($request->hasFile('gambar')) {
                $image = $request->file('gambar');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move('image', $imageName);

                // Menambahkan field gambar ke data yang akan diubah
                $dataArtikel->gambar = $imageName;

                $dataArtikel->save();

                return response()->json([
                    'message' => 'Artikel berhasil ditambahkan',
                    'Artikel' => $dataArtikel
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'Gagal menyimpan data'], 500);
        }
    }

    public function updateArtikel(Request $request, $id){
        $adminUser = auth()->guard('admin-api')->user();
        if (!$adminUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'judul' => 'required|string',
            'sub_judul' => 'required|string',
            'gambar' => 'image|mimes:jpeg,png,jpg',
            'deskripsi' => 'required|string',
            'tanggal_upload' => 'required|date|before_or_equal:today',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $dataArtikel = Artikel::findOrFail($id);

        if (!$dataArtikel) {
            return response()->json(['error' => 'Artikel not found'], 404);
        }

        try {
            $dataArtikel->judul = $request->input('judul');
            $dataArtikel->sub_judul = $request->input('sub_judul');
            $dataArtikel->deskripsi = $request->input('deskripsi');
            $dataArtikel->tanggal_upload = $request->input('tanggal_upload');
            $dataArtikel->save();
        } catch (Exception $e) {
            return response()->json(['error' => 'Gagal menyimpan data'], 500);
        }

        return response()->json([
            'message' => 'Artikel updated successfully',
            'Artikel' => $dataArtikel
        ], 200);
    }

    public function updateGambarArtikel(Request $request, $id){
        $adminUser = auth()->guard('admin-api')->user();
        if (!$adminUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'gambar' => 'image|mimes:jpeg,png,jpg',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $dataArtikel = Artikel::findOrFail($id);

        if (!$dataArtikel) {
            return response()->json(['error' => 'Artikel not found'], 404);
        }

        try {
            if ($request->hasFile('gambar')) {
                $image = $request->file('gambar');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move('image', $imageName);
    
                $dataArtikel->gambar = $imageName;
                $dataArtikel->save();
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'Gagal menyimpan data'], 500);
        }

        return response()->json([
            'message' => 'Gambar Artikel updated successfully',
            'Gambar Artikel' => $dataArtikel
        ], 200);
    }

    public function deleteArtikel($id){
        $adminUser = auth()->guard('admin-api')->user();
        if (!$adminUser) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $dataArtikel = Artikel::findOrFail($id);
        $dataArtikel->delete();

        return response()->json([
            'message' => 'Artikel deleted successfully'
        ], 200);
    }
}

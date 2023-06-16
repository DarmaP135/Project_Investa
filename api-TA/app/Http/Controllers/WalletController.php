<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Wallet;
use App\Models\User;

class WalletController extends Controller
{
    
    public function totalAset(){
        $user = auth()->guard('user-api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'id_user' => $user->id,
            'nama_user' => $user->name,
            'total_asset' => $user->saldo]
        );
        
    }

    public function deposit(Request $request)
    {
        $user = auth()->guard('user-api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'jumlah_deposit' => 'required|numeric',
            'pilih_pembayaran' => 'required|string',
            'pilih_bank' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $nama_bank = '';
        $nama_rekening = '';
        $nomor_rekening = '';

        if ($request->input('pilih_bank') === 'BCA') {
            $nama_bank = 'Bank BCA';
            $nama_rekening = $user->name; // Menggunakan nama pengguna sebagai nama rekening
            $nomor_rekening = 'BCA' . mt_rand(1000000000, 9999999999); // Menggunakan angka acak sebagai nomor rekening BCA
        } elseif ($request->input('pilih_bank') === 'BNI') {
            $nama_bank = 'Bank BNI';
            $nama_rekening = $user->name; // Menggunakan nama pengguna sebagai nama rekening
            $nomor_rekening = 'BNI' . mt_rand(1000000000, 9999999999); // Menggunakan angka acak sebagai nomor rekening BNI
        } elseif ($request->input('pilih_bank') === 'BRI') {
            $nama_bank = 'Bank BRI';
            $nama_rekening = $user->name; // Menggunakan nama pengguna sebagai nama rekening
            $nomor_rekening = 'BRI' . mt_rand(1000000000, 9999999999); // Menggunakan angka acak sebagai nomor rekening BRI
        }
        

        try {
            $deposit = Wallet::create([
                'user_id' => $user->id,
                'tipe' => 'Deposit',
                'note' => $request->input('note'),
                'jumlah_deposit' => $request->input('jumlah_deposit'),
                'pilih_pembayaran' => $request->input('pilih_pembayaran'),
                'pilih_bank' => $request->input('pilih_bank'),
                'nama_bank' => $nama_bank,
                'nama_rekening' => $nama_rekening,
                'nomor_rekening' => $nomor_rekening,
            ]);

            $user->saldo += $request->jumlah_deposit;
            $user->save();

            return response()->json([
                'message' => 'Deposit berhasil ditambahkan',
                'deposit' => $deposit
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Gagal menyimpan data'], 500);
        }
    }

    public function withdraw(Request $request)
    {
        $user = auth()->guard('user-api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'jumlah_withdraw' => 'required|numeric',
            'pilih_pembayaran' => 'required|string',
            'nama_bank' => 'required|string',
            'nama_rekening' => 'required|string',
            'nomor_rekening' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Periksa kecocokan password
        if (!password_verify($request->input('password'), $user->password)) {
            return response()->json(['error' => 'Password tidak valid'], 401);
        }

        $withdrawAmount = $request->input('jumlah_withdraw');

        if ($withdrawAmount > $user->saldo) {
            return response()->json(['error' => 'Saldo tidak cukup'], 422);
        }
 
        try {
            $withdraw = Wallet::create([
                'user_id' => $user->id,
                'tipe' => 'Withdraw',
                'jumlah_withdraw' => $withdrawAmount,
                'pilih_pembayaran' => $request->input('pilih_pembayaran'),
                'nama_bank' => $request->input('nama_bank'),
                'nama_rekening' => $request->input('nama_rekening'),
                'nomor_rekening' => $request->input('nomor_rekening'),
            ]);

            $user->saldo -= $withdrawAmount;
            $user->save();

            return response()->json([
                'message' => 'Withdraw berhasil ditambahkan',
                'withdraw' => $withdraw
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Gagal menyimpan data'], 500);
        }
    }


}

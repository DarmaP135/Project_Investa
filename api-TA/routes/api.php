<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

//Admin Auth
Route::group([
    'middleware' => ['api', 'cors'],
    'prefix' => 'admin'
], function ($router) {
    Route::post('adminregister', [App\Http\Controllers\AdminController::class,'adminregister'])->name('adminregister');
    Route::post('adminlogin', [App\Http\Controllers\AdminController::class,'adminlogin'])->name('adminlogin');
    Route::post('adminlogout', [App\Http\Controllers\AdminController::class, 'adminlogout'])->name('adminlogout');
    Route::post('adminme', [App\Http\Controllers\AdminController::class, 'adminme'])->name('adminme');
    Route::post('adminrefresh', [App\Http\Controllers\AdminController::class, 'adminrefresh'])->name('adminrefresh');
});


//User Auth
Route::group([
    'middleware' => ['api', 'cors'],
    'prefix' => 'auth'
], function ($router) {
    Route::post('petaniregister', [App\Http\Controllers\AuthContoller::class,'petaniregister'])->name('petaniregister');
    Route::post('investorregister', [App\Http\Controllers\AuthContoller::class,'investorregister'])->name('investorregister');
    Route::post('login', [App\Http\Controllers\AuthContoller::class,'login'])->name('login');
    Route::post('sendResetToken', [App\Http\Controllers\AuthContoller::class,'sendResetToken'])->name('sendResetToken');
    Route::post('resetPassword', [App\Http\Controllers\AuthContoller::class,'resetPassword'])->name('resetPassword');
    Route::post('logout', [App\Http\Controllers\AuthContoller::class, 'logout'])->name('logout');
    Route::post('me', [App\Http\Controllers\AuthContoller::class, 'me'])->name('me');
    Route::post('refresh', [App\Http\Controllers\AuthContoller::class, 'refresh'])->name('refresh');
    Route::post('updateProfile', [App\Http\Controllers\AuthContoller::class, 'updateProfile'])->name('updateProfile');
    Route::post('removePhoto', [App\Http\Controllers\AuthContoller::class, 'removePhoto'])->name('removePhoto');

});


Route::group([
    'middleware' => ['api', 'cors'],
    'prefix' => 'pengajuan' 
], function ($route){

    //getPengajuan Untuk Petani
    Route::get('getPengajuan', [App\Http\Controllers\PengajuanController::class, 'getPengajuan'])->name('getPengajuan');
   
    //getPengajuan untuk admin
    Route::get('getPengajuanSeluruhnya', [App\Http\Controllers\PengajuanController::class, 'getPengajuanSeluruhnya'])->name('getPengajuan');

    //getPengajuan yang sudah diacc dengan status "Proyek Berjalan"
    Route::get('pengajuanAccept', [App\Http\Controllers\PengajuanController::class, 'pengajuanAccept'])->name('pengajuanAccept');
    
    Route::post('addPengajuan', [App\Http\Controllers\PengajuanController::class, 'addPengajuan'])->name('addPengajuan');
    Route::post('detailPengajuan/{id}', [App\Http\Controllers\PengajuanController::class, 'detailPengajuan'])->name('detailPengajuan');
    Route::post('acceptPengajuan/{id}', [App\Http\Controllers\PengajuanController::class, 'acceptPengajuan'])->name('acceptPengajuan');
    Route::post('rejectPengajuan/{id}', [App\Http\Controllers\PengajuanController::class, 'rejectPengajuan'])->name('rejectPengajuan');
    Route::post('deletePengajuan/{id}', [App\Http\Controllers\PengajuanController::class, 'deletePengajuan'])->name('deletePengajuan');

    //Form Transaksi Info Pinjaman
    Route::get('{id}/getInfoPinjam', [App\Http\Controllers\InfoPinjamanController::class, 'getInfoPinjam'])->name('getInfoPinjam');
    Route::post('{id}/addInfoPinjam', [App\Http\Controllers\InfoPinjamanController::class, 'addInfoPinjam'])->name('addInfoPinjam');
    Route::post('{id}/updateInfoPinjam/{infoPinjamId}', [App\Http\Controllers\InfoPinjamanController::class, 'updateInfoPinjam'])->name('updateInfoPinjam');
    Route::post('{id}/updateInfoFilePinjaman/{filePinjamanId}', [App\Http\Controllers\InfoPinjamanController::class, 'updateInfoFilePinjaman'])->name('updateInfoFilePinjaman');
    Route::post('{id}/deleteInfoFilePinjaman/{filePinjamanId}', [App\Http\Controllers\InfoPinjamanController::class, 'deleteInfoFilePinjaman'])->name('deleteInfoFilePinjaman');
    Route::post('{id}/deleteInfoPinjam/{infoPinjamId}', [App\Http\Controllers\InfoPinjamanController::class, 'deleteInfoPinjam'])->name('deleteInfoPinjam');

    //Form Transaksi Info Kunjungan
    Route::get('{id}/getInfoKunjungan', [App\Http\Controllers\InfoKunjunganController::class, 'getInfoKunjungan'])->name('getInfoKunjungan');
    Route::post('{id}/addInfoKunjungan', [App\Http\Controllers\InfoKunjunganController::class, 'addInfoKunjungan'])->name('addInfoKunjungan');

    //Form Transaksi Info Pemasukan
    Route::get('{id}/getInfoPemasukan', [App\Http\Controllers\InfoPemasukanController::class, 'getInfoPemasukan'])->name('getInfoPemasukan');
    Route::post('{id}/addInfoPemasukan', [App\Http\Controllers\InfoPemasukanController::class, 'addInfoPemasukan'])->name('addInfoPemasukan');

    Route::get('{id}/getInfoPengembalian', [App\Http\Controllers\InfoPengembalianController::class, 'getInfoPengembalian'])->name('getInfoPengembalian');
    Route::post('{id}/addInfoPengembalianPetani', [App\Http\Controllers\InfoPengembalianController::class, 'addInfoPengembalianPetani'])->name('addInfoPengembalianPetani');
    
    
});

//Artikel
Route::group([
    'middleware' => ['api', 'cors'],
    'prefix' => 'artikel'
], function ($router) {
    //Ambil semua artikel 
    Route::get('getArtikel', [App\Http\Controllers\ArtikelController::class, 'getArtikel'])->name('getArtikel');
    //Tambah artikel
    Route::post('addArtikel', [App\Http\Controllers\ArtikelController::class, 'addArtikel'])->name('addArtikel');
    //Update artikel
    Route::post('updateArtikel/{id}', [App\Http\Controllers\ArtikelController::class, 'updateArtikel'])->name('updateArtikel');
    //Update gambar artikel
    Route::post('updateGambarArtikel/{id}', [App\Http\Controllers\ArtikelController::class, 'updateGambarArtikel'])->name('updateGambarArtikel');
    //Hapus artikel
    Route::delete('deleteArtikel/{id}', [App\Http\Controllers\ArtikelController::class, 'deleteArtikel'])->name('deleteArtikel');
});

Route::group([
    'middleware' => ['api', 'cors'],
    'prefix' => 'wallet' 
], function ($route){
    Route::post('deposit', [App\Http\Controllers\WalletController::class, 'deposit'])->name('deposit');
    Route::post('withdraw', [App\Http\Controllers\WalletController::class, 'withdraw'])->name('withdraw');
    Route::post('totalAset', [App\Http\Controllers\WalletController::class, 'totalAset'])->name('totalAset');
    
});

Route::group([
    'middleware' => ['api', 'cors'], 
], function ($route){
    Route::post('/pengajuan/{id}/investasi', [App\Http\Controllers\InvestasiController::class, 'investasi'])->name('investasi');
    Route::post('/pengajuan/{id}/simulasi-hitung', [App\Http\Controllers\PengajuanController::class, 'simulasiHitung'])->name('simulasiHitung');
    Route::get('/investasi/{userId}/getInvestasi', [App\Http\Controllers\InvestasiController::class, 'getInvestasi'])->name('getInvestasi');
    Route::get('/investasi/total', [App\Http\Controllers\InvestasiController::class, 'totalInvesAktif'])->name('totalInvesAktif');
});

Route::group([
    'middleware' => ['api', 'cors'],
    'prefix' => 'tracking' 
], function ($route){
    Route::get('getInvestor', [App\Http\Controllers\InvestasiController::class, 'getInvestor'])->name('getInvestor');
    Route::get('getProyek', [App\Http\Controllers\PengajuanController::class, 'getProyek'])->name('getProyek');
});
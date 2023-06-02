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
    'middleware' => 'api',
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
    'middleware' => 'api',
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
    'middleware' => 'api',
    'prefix' => 'pengajuan' 
], function ($route){
    Route::get('getPengajuan', [App\Http\Controllers\PengajuanController::class, 'getPengajuan'])->name('getPengajuan');
    Route::post('addPengajuan', [App\Http\Controllers\PengajuanController::class, 'addPengajuan'])->name('addPengajuan');
    Route::post('detailPengajuan/{id}', [App\Http\Controllers\PengajuanController::class, 'detailPengajuan'])->name('detailPengajuan');
    Route::post('updatePengajuan/{id}', [App\Http\Controllers\PengajuanController::class, 'updatePengajuan'])->name('updatePengajuan');
    Route::post('deletePengajuan/{id}', [App\Http\Controllers\PengajuanController::class, 'deletePengajuan'])->name('deletePengajuan');

    Route::get('{id}/getInfoPinjam', [App\Http\Controllers\InfoPinjamanController::class, 'getInfoPinjam'])->name('getInfoPinjam');
    Route::post('{id}/addInfoPinjam', [App\Http\Controllers\InfoPinjamanController::class, 'addInfoPinjam'])->name('addInfoPinjam');


});
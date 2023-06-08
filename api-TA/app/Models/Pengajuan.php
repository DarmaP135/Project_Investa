<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\FilePengajuan;
use App\Models\User;
use App\Models\InfoPinjaman;
use App\Models\InfoKunjungan;
use App\Models\InfoPemasukan;
use App\Models\infoTani;
use App\Models\Kebutuhan;

class Pengajuan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'pengajuan_name',
        'komoditas',
        'total_pengajuan',
        'start_date',
        'end_date',
        'estimasi_pengembalian',
        'tenor',
        'resiko',
        'deskripsi',
        'imbal_hasil',
        'harga_unit',
        'jumlah_unit',
        'unit_tersedia',
        'dana_terkumpul',
        'total_pengembalian',
        'metode_pelunasan',
        'status',
    ];

    protected $appends = ['day_left', 'message'];

    public function getDayLeftAttribute()
    {
        if ($this->status === 'Finished') {
            return 0;
        }

        $currentDate = Carbon::now()->startOfDay();
        $endDate = Carbon::parse($this->end_date)->startOfDay();
        $dayLeft = $endDate->diffInDays($currentDate, true);

        return $dayLeft . ' hari lagi.';
    }

    public function getMessageAttribute()
    {
        if ($this->status === 'Finished') {
            return 'Investasi ini telah berakhir.';
        }

        return 'Investasi ini masih berlangsung. Tersisa ' . $this->day_left;
    }

    public function updateStatus()
    {
        $totalPengajuan = $this->total_pengajuan;
        $totalDanaTerkumpul = $this->dana_terkumpul;

        if ($totalPengajuan == $totalDanaTerkumpul) {
            $this->status = 'Pendanaan Terpenuhi';
            $this->save();
        }
    }

    public function User()
    {
         return $this->belongsTo(User::class);
    }

    public function Files()
    {
         return $this->hasMany(FilePengajuan::class, 'pengajuan_id');
    }

    public function Kebutuhan()
    {
        return $this->hasMany(Kebutuhan::class,'pengajuan_id');
    }

    public function infoTani()
    {
        return $this->hasOne(infoTani::class);
    }

    public function infoPinjaman()
    {
        return $this->hasOne(InfoPinjaman::class);
    }

    public function filepinjam()
    {
        return $this->hasOne(FilePinjaman::class);
    }

    public function infoKunjungan(){
        return $this->hasOne(InfoKunjungan::class);
    }

    public function infoPemasukan(){
        return $this->hasOne(InfoPemasukan::class);
    }

    public function Investasi()
    {
        return $this->hasMany(Investasi::class);
    }
    
}

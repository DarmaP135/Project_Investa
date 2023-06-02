<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\FilePengajuan;
use App\Models\InfoPinjaman;
use App\Models\kebutuhan;

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
        'imbal_hasil',
        'total_pengembalian',
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

    public function Files()
    {
         return $this->hasMany(FilePengajuan::class, 'pengajuan_id');
    }

    public function Kebutuhan()
    {
        return $this->hasMany(Kebutuhan::class,'pengajuan_id');
    }

    public function infoPinjaman()
    {
        return $this->hasOne(InfoPinjaman::class);
    }
    
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfoKunjungan extends Model
{
    use HasFactory;
    protected $fillable = [
        'pengajuan_id',
        'tanggal',
        'nama_petugas',
        'tujuan',
        'photo',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }


}

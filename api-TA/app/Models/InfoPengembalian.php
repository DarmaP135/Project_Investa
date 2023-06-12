<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfoPengembalian extends Model
{
    use HasFactory;
    protected $fillable = [
        'pengajuan_id',
        'jumlah_pembayaran',
        'pilih_pembayaran',
        'nama_bank',
        'nama_rekening',
        'nomor_rekening',
        'deskripsi',
        'status',
        'photo'
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }
}

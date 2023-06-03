<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfoPemasukan extends Model
{
    use HasFactory;
    protected $fillable = [
        'pengajuan_id',
        'tanggal',
        'nama_produk',
        'jumlah',
        'harga',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }
}

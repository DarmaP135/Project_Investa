<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Pengajuan;
use App\Models\FilePinjaman;

class InfoPinjaman extends Model
{
    use HasFactory;
    protected $fillable = [
        'pengajuan_id',
        'tangal',
        'barang',
        'jumlah',
        'harga',
        'total',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }

    

} 

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfoTani extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'pengalaman_tani',
        'kelompok_tani',
        'nama_kelompok',
        'jumlah_anggota',
        'status_lahan',
        'luas_lahan',
        'provinsi',
        'kota',
        'kecamatan',
        'kode_pos',
        'alamat',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

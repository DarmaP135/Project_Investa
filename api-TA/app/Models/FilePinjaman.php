<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilePinjaman extends Model
{
    use HasFactory;
    protected $fillable = [
        'pengajuan_id ',
        'alamat_gambar',
    ];

}

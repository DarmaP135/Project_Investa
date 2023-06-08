<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Pengajuan;
use App\Models\User;


class Investasi extends Model
{
    use HasFactory;
    protected $fillable = [
        'pengajuan_id',
        'user_id',
        'amount',
        'unit',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

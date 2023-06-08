<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Wallet extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'tipe',
        'jumlah_deposit',
        'jumlah_withdraw',
        'pilih_pembayaran',
        'pilih_bank',
        'note',
        'nama_bank',
        'nama_rekening',
        'nomor_rekening'
    ];

    public function users(){
        return $this->belongsTo(User::class);
    }

    
}

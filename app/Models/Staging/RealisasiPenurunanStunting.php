<?php

namespace App\Models\Staging;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RealisasiPenurunanStunting extends Model
{
    use HasFactory;

    protected $table = 'stagging.t_realisasi_penurunan_stunting';

    protected $casts = [
        'alokasi_anggaran' => 'decimal:3',
        'realisasi_anggaran' => 'decimal:3',
    ];

    public function kementerian()
    {
        return $this->belongsTo(App\Models\Pub\Kementerian::class, 'kddept', 'kode');
    }

    public function satker(){
        return $this->belongsTo(App\Models\Pub\Kementerian::class, 'kdsatker', 'kode');
    }
}

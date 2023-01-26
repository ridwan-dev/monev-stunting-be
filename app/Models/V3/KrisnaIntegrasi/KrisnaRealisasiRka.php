<?php

namespace App\Models\V3\KrisnaIntegrasi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KrisnaRealisasiRka extends Model
{
    use HasFactory;
    protected $table = 'versi_tiga.krisna_realisasi_rka';
    protected $casts = [
        'attrs' => 'array'
    ];
}
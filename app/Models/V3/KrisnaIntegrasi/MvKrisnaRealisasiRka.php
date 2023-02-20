<?php

namespace App\Models\V3\KrisnaIntegrasi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MvKrisnaRealisasiRka extends Model
{
    use HasFactory;
    protected $table = 'versi_tiga.mv_krisna_realisasi_rka';
    protected $casts = [
        'lokasi_ro' => 'array',
        'satuan_output' => 'array',
        'unit_kerja_eselon1' => 'array',
        'attrs' => 'array'
    ];
}
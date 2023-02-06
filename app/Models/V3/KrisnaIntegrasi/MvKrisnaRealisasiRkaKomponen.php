<?php

namespace App\Models\V3\KrisnaIntegrasi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MvKrisnaRealisasiRkaKomponen extends Model
{
    use HasFactory;
    protected $table = 'versi_tiga.mv_krisna_realisasi_rka_komponen';
    protected $casts = [
        'lokasi_ro' => 'array',
        'satuan_output' => 'array',
        'unit_kerja_eselon1' => 'array'
    ];
}
<?php

namespace App\Models\V3\KrisnaIntegrasi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MvKrisnaRealisasiRkaRoKompLokasi extends Model
{
    use HasFactory;
    protected $table = 'versi_tiga.mv_krisna_renjarka_rokomp_lokasi';
    protected $casts = [
        'satuan_output' => 'array',
        'lokasi_ro' => 'array',
        'lokasi_alokasi' => 'array',
        'komponen' => 'array',        
        'realisasi_rka_komp' => 'array'
    ];
}
<?php

namespace App\Models\V3\KrisnaIntegrasi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KrisnaDakData extends Model
{
    use HasFactory;
    protected $table = 'versi_tiga.krisna_dak_data';
    protected $casts = [
        'pelaksana' => 'array',
        'pengadaan_ids' => 'array',
        'komponens' => 'array',
        'criterias' => 'array',
        'coordinate' => 'array'
    ];
}

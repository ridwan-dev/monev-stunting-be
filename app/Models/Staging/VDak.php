<?php

namespace App\Models\Staging;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Staging\MappingLokasiDak;

class VDak extends Model
{
    use HasFactory;

    protected $table = 'stagging.v_rekap_dak';

    protected $casts = [
        'tahun' => 'string',
        'nilai_rk' => 'float',
        'grand_total' => 'float'
    ];

}

<?php

namespace App\Models\Staging;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MappingLokasiDak extends Model
{
    use HasFactory;

    protected $table = 'stagging.mapping_lokasi_dak';

    public function lokasi()
    {
        return $this->belongsTo(\App\Models\Pub\Lokasi::class, 'lokasi_id', 'id');
    }
}

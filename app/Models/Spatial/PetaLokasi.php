<?php

namespace App\Models\Spatial;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MStaack\LaravelPostgis\Eloquent\PostgisTrait;
use MStaack\LaravelPostgis\Geometries\MultiPolygon;

class PetaLokasi extends Model
{
    use HasFactory, PostgisTrait;

    protected $table = 'spatial_data.peta_lokasi';

    protected $postgisFields = [
        'shape'
    ];

    protected $postgisTypes = [
        'shape' => [
            'geomtype' => 'multipolygon'
        ]
    ];

    public function properties()
    {
        return $this->hasOne(\App\Models\Pub\Lokasi::class, 'id', 'id');
    }

    public function kegiatan(){
        return $this->hasMany(\App\Models\Pub\TRenjakl::class, 'wilayah_id', 'id');
    }
}

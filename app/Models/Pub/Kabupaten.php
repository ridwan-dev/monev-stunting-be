<?php

namespace App\Models\Pub;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kabupaten extends Model
{
    use HasFactory;

    protected $table = 'api.kabupaten';
    protected $casts = [
        'tahun_prioritas' => 'array'
    ];

    public function provinsi()
    {
        return $this->belongsTo(Lokasi::class, 'provinsi_id');
    }
}

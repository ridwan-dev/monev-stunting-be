<?php

namespace App\Models\Pub;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lokasi extends Model
{
    use HasFactory;

    protected $table = 'public.r_lokasi';


    public function lokasiPrioritas()
    {
        return $this->hasMany('App\Models\Pub\LokasiPrioritas', 'lokasi_id');
    }
}

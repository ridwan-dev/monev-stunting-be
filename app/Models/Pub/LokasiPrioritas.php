<?php

namespace App\Models\Pub;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LokasiPrioritas extends Model
{
    use HasFactory;

    protected $table = 'public.t_lokasi_prioritas';

    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_id');
    }
}

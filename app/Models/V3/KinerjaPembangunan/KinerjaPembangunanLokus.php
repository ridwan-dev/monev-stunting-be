<?php

namespace App\Models\V3\KinerjaPembangunan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KinerjaPembangunanLokus extends Model
{
    use HasFactory;
    public $keyType = 'string';
    protected $table = 'versi_tiga.kinerja_pembangunan';

    protected $casts = [];

}

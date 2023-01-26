<?php

namespace App\Models\Staging;

use Illuminate\Database\Eloquent\Model;

class DakWilayahPemda extends Model
{
    protected $table = 'stagging.dak_wilayah_pemda';

    protected $casts = [
        'last_synced'     => 'date',
    ];
}

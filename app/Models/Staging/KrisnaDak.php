<?php

namespace App\Models\Staging;

use Illuminate\Database\Eloquent\Model;

class KrisnaDak extends Model
{
    protected $table = 'stagging.dak_data';

    protected $casts = [
        "criterias" => "json",
        "komponens" => "json",
        "pengadaan_ids" => "json",
        "coordinate" => "json",
    ];
}

<?php

namespace App\Models\Pub;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provinsi extends Model
{
    use HasFactory;

    protected $table = 'api.provinsi';


    public function kabupaten()
    {
        return $this->hasMany('App\Models\Pub\Kabupaten', 'provinsi_id');
    }
}

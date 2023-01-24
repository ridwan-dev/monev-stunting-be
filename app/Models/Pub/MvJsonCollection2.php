<?php

namespace App\Models\Pub;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MvJsonCollection2 extends Model
{
    use HasFactory;

    protected $table = 'public.mv_json_collection2';

    protected $hidden = ['id'];

    protected $appends = ['hashid'];

    public function getHashidAttribute()
    {
        return \UrlHash::encodeId('cirgobanggocir', $this->attributes['id'], 50);
    }

}

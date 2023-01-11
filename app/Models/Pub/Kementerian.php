<?php

namespace App\Models\Pub;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kementerian extends Model
{
    use HasFactory;

    protected $table = 'public.r_kementerian';

    protected $casts = [
        'attrs' => 'array'
    ];

    public function kegiatan()
    {
        return $this->hasMany('\App\TRenjakl', 'kementerian_id');
    } 

}

<?php

namespace App\Models\Pub;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RPeriode extends Model
{
    use HasFactory;

    protected $table = 'public.r_periode';

    protected $casts = [
        'periode_awal' => 'date',
        'periode_akhir' => 'date',
    ];
}

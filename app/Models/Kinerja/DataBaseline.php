<?php

namespace App\Models\Kinerja;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataBaseline extends Model
{
    use HasFactory;

    protected $table = 'dashboard_kinerja.data_baseline';

    // protected $casts = [
    //     'alokasi_renja' => 'float',
    //     'alokasi_rkakl' => 'float',
    //     'alokasi_anal' => 'float',
    // ];
}

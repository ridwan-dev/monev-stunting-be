<?php

namespace App\Models\Kinerja;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Kinerja\perkembanganPenandaan;

class IndikasiKonvergensiImplementasi extends Model
{
    use HasFactory;

    protected $table = 'dashboard_kinerja.sys_form2';

    protected $casts = [];
}

<?php

namespace App\Models\Kinerja;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefKementerian extends Model
{
    use HasFactory;

    protected $table = 'dashboard_kinerja.ref_kementerian';

    protected $casts = [];
}

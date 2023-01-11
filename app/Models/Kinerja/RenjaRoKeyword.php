<?php

namespace App\Models\Kinerja;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RenjaRoKeyword extends Model
{
    use HasFactory;

    protected $table = 'renja.krisnarenja_ro_keyword';
    protected $fillable = [
        'keyword',
        'tahun',
    ];

}

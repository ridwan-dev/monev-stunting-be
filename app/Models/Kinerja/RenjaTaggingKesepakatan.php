<?php

namespace App\Models\Kinerja;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RenjaTaggingKesepakatan extends Model
{
    use HasFactory;

    protected $table = 'renja.krisnarenja_tagging_kesepakatan';
    protected $fillable = [
        'id_ro',
        'kesepakatan',
        'tahun',
        'semester',
        'tgl_kesepakatan',
        'tingkat_ro',
        'analisis_lanjutan',
        'publish'
    ];

}

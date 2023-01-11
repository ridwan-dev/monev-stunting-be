<?php

namespace App\Models\Kinerja;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RenjaTaggingRo extends Model
{
    use HasFactory;

    protected $table = 'renja.krisnarenja_tagging_ro';
    protected $fillable = [
        'id_ro',
        'kode_intervensi',
        'tahun',
    ];

}

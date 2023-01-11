<?php

namespace App\Models\Kinerja;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RenjaTagging extends Model
{
    use HasFactory;

    protected $table = 'renja.krisnarenja_tagging';

    protected $casts = [];

    protected $primary = 'id';
    protected $timestamp = FALSE;
    protected $fillable = [
        'id_ro',
        'tahun',
        'kode_intervensi',
        'tahun',
        'allow',
        'disepakati',
        'ditandai'
    ];
}

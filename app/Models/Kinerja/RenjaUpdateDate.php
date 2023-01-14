<?php

namespace App\Models\Kinerja;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RenjaUpdateDate extends Model
{
    use HasFactory;

    protected $table = 'renja.krisnarenja_update_date';
    protected $fillable = [
        'updated_at',
        'name',
    ];

}

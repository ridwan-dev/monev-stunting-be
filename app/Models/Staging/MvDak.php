<?php

namespace App\Models\Staging;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Staging\MappingLokasiDak;

class MvDak extends Model
{
    use HasFactory;

    protected $table = 'stagging.mv_dak';

    protected $casts = [
        'tahun' => 'string',
        'nilai_rk' => 'float',
        'grand_total' => 'float'
    ];

    protected function castAttribute($key, $value)
    {
        if (!is_null($value)) {
            return parent::castAttribute($key, $value);
        }

        switch ($this->getCastType($key)) {
            case 'int':
            case 'integer':
                return (int) 0;
            case 'real':
            case 'float':
            case 'double':
                return (float) 0;
            case 'string':
                return '';
            case 'bool':
            case 'boolean':
                return false;
            case 'object':
            case 'array':
            case 'json':
                return [];
            case 'collection':
                return new BaseCollection();
            case 'date':
                return $this->asDate('0000-00-00');
            case 'datetime':
                return $this->asDateTime('0000-00-00');
            case 'timestamp':
                return $this->asTimestamp('0000-00-00');
            default:
                return $value;
        }
    }

    public function mappingLokasi()
    {
        return $this->belongsTo(\App\Models\Staging\MappingLokasiDak::class, 'pemda_kode', 'pemda_kode');
    }
}

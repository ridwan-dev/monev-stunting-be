<?php

namespace App\Models\Kinerja;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KinerjaAnggaran extends Model
{
    use HasFactory;

    protected $table = 'dashboard_kinerja.mv_kinerja_anggaran';

    protected $casts = [
        'tahun' => 'string',
        'kode' => 'string',
        'intervensi_kode' => 'string',
        'intervensi_nama' => 'string',
        'kementerian_kode' => 'string',
        'kementerian_nama' => 'string',
        'kementerian_nama_short' => 'string',
        'program_kode' => 'string',
        'program_nama' => 'string',
        'kegiatan_kode' => 'string',
        'kegiatan_nama' => 'string',
        'output_kode' => 'string',
        'output_nama' => 'string',
        'suboutput_kode' => 'string',
        'suboutput_nama' => 'string',
        'alokasi_0' => 'float',
        'alokasi_1' => 'float',
        'alokasi_2' => 'float',
        'alokasi_realisasi' => 'float',
        'volume_0' => 'float',
        'volume_1' => 'float',
        'volume_2' => 'float',
        'volume_realisasi' => 'float',
        'satuan' => 'string',
        'anl_alokasi_0' => 'float',
        'anl_alokasi_1' => 'float',
        'anl_alokasi_2' => 'float',
        'anl_alokasi_rpd' => 'float',
        'anl_alokasi_realisasi' => 'float',
        'anl_volume_0' => 'float',
        'anl_volume_1' => 'float',
        'anl_volume_2' => 'float',
        'anl_volume_realisasi' => 'float',
        'satuan2' => 'string',
        'prsn_realisasi' => 'float',
        'prsen_output' => 'float',
        'prsn_anl_realisasi' => 'float',
        'prsn_anl_realisasi_rpd' => 'float',
        'prsn_anl_output' => 'float',
        'kinerja_umum' => 'float',
        'semester' => 'string',
        'keterangan' => 'float'
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
}

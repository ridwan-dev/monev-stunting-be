<?php

namespace App\Models\Pub;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Intervensi extends Model
{
    use HasFactory;

    protected $table = 'api.ref_intervensi';

    public function kegiatan(){
        return $this->hasManyThrough('App\DumpRenjaKl', 'App\DumpRenjaKlIntervensi', 'intervensi_kode', 'kode', 'kode', 'kode_dump_renjakl');
    }

    public $timestamps = false;
 
    protected $fillable = [
        'id',
        'intervensi_kode',
        'intervensi_nama',
        'tipe_id',
        'tipe_nama',
        'intervensi_nama_alias',
        'link',
        'deskripsi'
    ]; 
    
}

<?php

namespace App\Models\Pub;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TRenjakl extends Model
{
    use HasFactory;

    protected $table = 'public.t_renjakl';

    public function kementerian()
    {
        return $this->belongsTo('\App\Models\Pub\Kementerian', 'kementerian_id');
    }

    public function intervensi(){
        return $this->hasManyThrough(
            TRenjaklMappingIntervensi::class,
            App\Models\Pub\Intervensi::class,
            'id', // Foreign key on the environments table...
            'sub_output_id', // Foreign key on the deployments table...
            'intervensi_id', // Local key on the projects table...
            'id' // Local key on the environments table...
        );
    }
}

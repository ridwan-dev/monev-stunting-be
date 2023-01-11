<?php

namespace App\Models\Pub;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MvDmRenjakl extends Model
{
    use HasFactory;

    protected $table = 'public.mv_dm_renjakl';

    public function intervensi(){
        return $this->belongsTo(App\Models\Pub\Intervensi::class, 'intervensi_id');
    }

}

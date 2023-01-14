<?php

namespace App\Models\Staging;

use Illuminate\Database\Eloquent\Model;

class KrisnaRenjaBackup extends Model
{
    public function __construct( array $attributes = [] )
    {
        if (array_key_exists('table', $attributes)) {
            $this->setTable("renja_backup.".$attributes['table']) ;
        }else {
            // do staff when table is not specified 
        }
    }
    public  $timestamps = false;
}

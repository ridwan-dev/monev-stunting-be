<?php

namespace App\Models\Staging;

use Illuminate\Database\Eloquent\Model;

class KrisnaRenja extends Model
{
    public function __construct( array $attributes = [] )
    {
        if (array_key_exists('table', $attributes)) {
            $this->setTable("renja.".$attributes['table']) ;
        }else {
            // do staff when table is not specified 
        }
    }
    public  $timestamps = false;
}

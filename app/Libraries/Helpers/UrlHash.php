<?php

namespace App\Libraries\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Hashids\Hashids;

class UrlHash
{
    public static function encodeId($salty, $id, $sizeof)
    {
        $hash = new Hashids($salty, $sizeof);

        return $hash->encode($id);
    }

    public static function decodeId($salty, $id, $sizeof)
    {

        if(strpos($id, '-')){
            $string = explode('-', $id);
            $string = $string[1];
        } else {
            $string = $id;
        }

        $hash = new Hashids($salty, $sizeof);
        // Array return
        $key = $hash->decode($string);

        // Return as integer
        if(!empty($key)){
            return $key[0];
        } else {
            return null;
        }
    }
}

<?php

namespace App\Http\Controllers\Api\V3\KinerjaPembangunan;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\V3\KinerjaPembangunan\KinerjaPembangunanLokus ;
use App\Models\V3\KinerjaPembangunan\KinerjaPembangunanRoIstilah ;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class KinerjaPembangunanController extends BaseController
{
   public function lokusRo(Request $request){     

      $where = $request->tahun ? " AND tahun = " . $request->tahun : "" ;
      $request->semester ?  $where = $where . " AND semester = '" . $request->semester . "'" : $where ;
      if($request->ro){         
         foreach($request->ro as $ro){
            $where = $where . " AND " . $ro . " = 'Y'";
         }
      }else{
         $where = $where;
      }

      $results = DB::select(
         "select 
            json_build_object(
               'type', 'FeatureCollection',
               'features', json_agg(public.ST_AsGeoJSON(t.*)::json)
            ) AS data
         from (
            SELECT
               a.*,
               kabupaten_kode,geom
            FROM
               versi_tiga.kinerja_pembangunan a
            LEFT JOIN api.kabupaten b
            ON a.kab_kode = b.kabupaten_kode
            WHERE 1 = 1" . $where . "
         ) as t(a)"
      );
      
      $result = [
         "detail" => json_decode($results[0]->data),
         "field" => KinerjaPembangunanRoIstilah::where("publish","Y")->get(),
      ];
      return $this->returnJsonSuccess("Data fetched successfully", $result);
   }

}

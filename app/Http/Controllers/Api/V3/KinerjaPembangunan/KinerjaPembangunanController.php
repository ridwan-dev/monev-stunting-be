<?php

namespace App\Http\Controllers\Api\V3\KinerjaPembangunan;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\V3\KinerjaPembangunan\KinerjaPembangunanLokus ;
use App\Models\V3\KinerjaPembangunan\KinerjaPembangunanRoIstilah ;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class KinerjaPembangunanController extends BaseController
{
   public function lokusRo(Request $request){
      $tahun = now()->year;
      $bulan = now()->month;
      $semester = ($bulan/6) <= 6 ? 1 : 2;
      $ro = [];
      if($request->has('tahun') && !empty($request->tahun)){
         $tahun = $request->tahun;
      }

      if($request->has('semester') && !empty($request->semester)){
         $semester = $request->semester;
      } 

      if($request->has('ro') && !empty($request->ro)){
            $ro = $request->ro;
      }
      if($request->has('level') && !empty($request->level)){
         $level = $request->level;
      }else{
         $level = 'district'; //province
      }

      $dataKinerjaPembangunan = KinerjaPembangunanLokus::where(function($q) use($tahun, $bulan, $semester, $ro,$level){
            if($tahun != "all"){
               $q->where('tahun', $tahun);
            }

            if($semester != "all"){
               $q->where('semester', $semester);
            }

            if(count($ro)<58){
               foreach($ro as $rw){
                  $q->where($rw, "Y");
               }               
            }
         })->join('spatial_data.peta_lokasi', function ($join) {
            $join->on('spatial_data.peta_lokasi.kode_bps', '=', 'versi_tiga.kinerja_pembangunan.kab_id')
               ->where('level', '=', $level);
      })
      ->select(['versi_tiga.kinerja_pembangunan.*','spatial_data.peta_lokasi.shape'])
      ->get();
      
      $result = [
         "data" => $dataKinerjaPembangunan,
         "field" => KinerjaPembangunanRoIstilah::all(),
      ];
      return $this->returnJsonSuccess("Data fetched successfully", $result);
   }

}

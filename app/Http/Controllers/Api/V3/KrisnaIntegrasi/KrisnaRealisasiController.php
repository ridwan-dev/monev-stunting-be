<?php

namespace App\Http\Controllers\Api\V3\KrisnaIntegrasi;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\V3\KrisnaIntegrasi\KrisnaRealisasiRka ;
use App\Models\V3\KrisnaIntegrasi\KrisnaRealisasiRkaKomponen ;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class KrisnaRealisasiController extends BaseController
{
   
   public function realisasiKomponen($tahun){
      $dataK = KrisnaRealisasiRka::where('tahun',$tahun)->get();

      print_r("Delete tahun ".$tahun."\n");
      KrisnaRealisasiRkaKomponen::where('tahun',$tahun)->delete();

      $return = [];
      foreach($dataK as $dk){
         
         $subreturn = [];
         if(!empty($dk->attrs['alokasis'])){
            
            foreach($dk->attrs['alokasis'] as $kmp){
               $returnX = [
                  "rka_komp_kode" => $tahun.$dk['kode_kl'].$dk['kode_program'].$dk['kode_kegiatan'].$dk['kode_kro'].$dk['kode_ro'].$kmp['komponen_kode'],
                  "tahun" => $tahun,
                  "kode_kl" => $dk['kode_kl'],
                  "nama_kl" => $dk['nama_kl'],
                  "kode_program" => $dk['kode_program'],
                  "kode_kegiatan" => $dk['kode_kegiatan'],
                  "kode_kro" => $dk['kode_kro'],
                  "kode_ro" => $dk['kode_ro'],
                  "kode_lro" => $dk['kode_lro'],
                  "alokasi_lro" => $dk['alokasi_lro'],
                  "alokasi" => $kmp['alokasi'],
                  "alokasi" => $kmp['alokasi'],
                  "komponen_kode" => $kmp['komponen_kode'],
                  "komponen_nama" => $kmp['komponen_nama'],
                  "sumber_dana_id" => $kmp['sumber_dana_id']         
               ];
               print_r("Created ".$dk['nama_kl']."-".$kmp['komponen_nama']." tahun ".$tahun."\n");
               KrisnaRealisasiRkaKomponen::create($returnX);
            }
         }
         else{
            $returnY = [
               "rka_komp_kode" => $tahun.$dk['kode_kl'].$dk['kode_program'].$dk['kode_kegiatan'].$dk['kode_kro'].$dk['kode_ro'],
               "tahun" => $tahun,
               "kode_kl" => $dk['kode_kl'],
               "nama_kl" => $dk['nama_kl'],
               "kode_program" => $dk['kode_program'],
               "kode_kegiatan" => $dk['kode_kegiatan'],
               "kode_kro" => $dk['kode_kro'],
               "kode_ro" => $dk['kode_ro'],
               "kode_lro" => $dk['kode_lro'],
               "alokasi_lro" => $dk['alokasi_lro'],
               "alokasi" => null,
               "alokasi" => null,
               "komponen_kode" => null,
               "komponen_nama" => null,
               "sumber_dana_id" => null         
            ];
            print_r("Created ".$dk['nama_kl']."-kosong tahun ".$tahun."\n");
            KrisnaRealisasiRkaKomponen::create($returnY);
         }
      }
      

      //print_r("Created tahun ".$tahun."\n");
     /*  KrisnaRealisasiRkaKomponen::insert($return); */
      return $this->returnJsonSuccess("Data fetched successfully", []); 
      

   }
   
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

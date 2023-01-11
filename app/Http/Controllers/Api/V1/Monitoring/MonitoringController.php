<?php

namespace App\Http\Controllers\Api\V1\Monitoring;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\Pub\Lokasi;
use App\Models\Pub\LokasiPrioritas;
use App\Models\Pub\Provinsi;
use App\Models\Pub\Kabupaten;
use Illuminate\Support\Facades\DB;

class MonitoringController extends BaseController
{
    public function __construct()
    {
        // $this->middleware(
        //     [
        //         'auth:api'
        //     ]);
    }

    public function capaian(Request $request)
    {
        // $where = $request->tahun ? ' AND tahun IN (' . implode(',', $request->tahun) . ')' : '' ;
        $where = $request->tahun ? " AND tahun = " . $request->tahun : "" ;
        $request->intervensi ?  $where = $where . " AND intervensi_id = '" . $request->intervensi . "'" : $where ;
        $request->kementerian ?  $where = $where . " AND kementerian_id = '" . $request->kementerian . "'" : $where ;
        $request->provinsi ?  $where = $where . " AND a.provinsi_id = '" . $request->provinsi . "'" : $where ;
        $request->kabupaten ?  $where = $where . " AND a.kabupaten_id = '" . $request->kabupaten . "'" : $where ;
        
        $results = DB::SELECT("SELECT
                            tahun,
                            kementerian_id,
                            intervensi_id,
                            kementerian_nama_alias as kementerian_nama, 
                            intervensi_nama,
                            sum(alokasi_1) as alokasi,
                            sum(realisasi) as realisasi,
                            round((sum(realisasi)/sum(alokasi_1))*100,2) as capaian_keu,
                            round(avg(case when capaian_fisik > 100 then 100 else capaian_fisik end),2) as capaian_fisik
                        FROM 
                            api.mv_renja_monev_agg
                        GROUP BY
                            tahun,
                            kementerian_id,
                            intervensi_id,
                            kementerian_nama_alias, 
                            intervensi_nama
                    ");
            
        return $this->returnJsonSuccessCheck("Data fetched successfully", $results);
    }

    public function capaianDetail(Request $request)
    {
        $where = $request->tahun ? ' AND tahun IN (' . implode(',', $request->tahun) . ')' : "" ;
        $where = $request->intervensi ? $where = $where . ' AND intervensi_id IN (' . implode(',', $request->intervensi) . ')' : '' ;
        $where = $request->kementerian ? $where = $where . ' AND kementerian_id IN (' . implode(',', $request->kementerian) . ')' : '' ;
        $request->lokasi_id ?  $where = $where . " AND kabupaten_id = '" . $request->lokasi_id . "'" : $where ;
        
        $result = DB::SELECT("SELECT
                            kabupaten_id AS lokasi_id,
                            tahun,
                            kementerian_id,
                            intervensi_id,
                            program_id,
                            kegiatan_id,
                            output_id,
                            sub_output_id,
                            komponen_id,
                            alokasi_1 as alokasi,
                            realisasi as realisasi,
                            capaian_keu AS capaian,
                            volume AS target,
                            capaian_fisik
                        FROM 
                            api.mv_renja_monev 
                        WHERE 1=1" . $where
                    );
        
        
        if($request->has('tahun')){
            $result= collect($result)->whereIn('tahun',$request->tahun);
        }
        if($request->has('kementerian')){
            $result= collect($result)->whereIn('kementerian_id',$request->kementerian);
        }
        if($request->has('intervensi')){
            $result= collect($result)->whereIn('intervensi_id',$request->intervensi);
        }
        $results=[];
        foreach ($result as $rs) {
            array_push($results,$rs);
        }

        return $this->returnJsonSuccessCheck("Data fetched successfully", $results);
    }

    public function intervensiPage(Request $request)
    {
        $tahun = $request->tahun ? array_map(function($value) {
                return intval($value);
            }, $request->tahun) : NULL;
        $intervensi = $request->intervensi ? array_map(function($value) {
                return intval($value);
            }, $request->intervensi) : NULL;
        $kementerian = $request->kementerian ? array_map(function($value) {
                return intval($value);
            }, $request->kementerian) : NULL;
        $lokasi_id = intval($request->lokasi_id);

        $where = $tahun ? ' AND tahun IN (' . implode(',', $request->tahun) . ')' : "" ;
        $where = $intervensi ? $where = $where . ' AND intervensi_id IN (' . implode(',', $intervensi) . ')' : '' ;
        $where = $kementerian ? $where = $where . ' AND kementerian_id IN (' . implode(',', $kementerian) . ')' : '' ;
        $lokasi_id ?  $where = $where . " AND kabupaten_id = " . $lokasi_id  : $where ;
        
        $detail = DB::SELECT("SELECT
                            kabupaten_id AS lokasi_id,
                            tahun,
                            kementerian_id,
                            intervensi_id,
                            program_id,
                            kegiatan_id,
                            output_id,
                            sub_output_id,
                            komponen_id,
                            alokasi_1 as alokasi,
                            realisasi as realisasi,
                            capaian_keu AS capaian,
                            volume AS target,
                            capaian_fisik
                        FROM 
                            api.mv_renja_monev 
                        WHERE 1=1" . $where
                    );
            $tahun ? $detail= collect($detail)->whereIn('tahun',$tahun) : false;
            $kementerian ? $detail= collect($detail)->whereIn('kementerian_id',$kementerian)  : false;
            $intervensi ? $detail= collect($detail)->whereIn('intervensi_id',$intervensi) : false;

            $details=[];
            foreach ($detail as $rs) {
                array_push($details,$rs);
            }

        $indikator = DB::SELECT("SELECT
                            kabupaten_id AS lokasi_id,
                            komponen_id AS id,
                            komponen_id AS parent_id,
                            tahun,
                            kementerian_id, 
                            intervensi_id,
                            coalesce(satuan,'-') AS satuan,
                            volume as target,
                            realisasi
                        FROM 
                            api.mv_renja_monev 
                        WHERE 1=1" . $where
                    );

            $tahun ? $indikator = collect($indikator)->whereIn('tahun',$tahun) : false;
            $kementerian ? $indikator = collect($indikator)->whereIn('kementerian_id',$kementerian)  : false;
            $intervensi ? $indikator = collect($indikator)->whereIn('intervensi_id',$intervensi) : false;

            $indikators=[];
            foreach ($indikator as $rs) {
                array_push($indikators,$rs);
            }

        $chart = DB::SELECT("SELECT
                            tahun,
                            kementerian_id, 
                            kementerian_nama_alias as kementerian, 
                            intervensi_id,
                            intervensi_nama intervensi,
                            count(tahun) as jumlah,
                            sum(alokasi_1) as alokasi,
                            sum(realisasi) as realisasi,
                             round((
                                CASE
                                    WHEN (sum(alokasi_1) = (0)::numeric) THEN (0)::numeric
                                    ELSE (sum(realisasi) / sum(alokasi_1))
                                END * (100)::numeric), 2) AS capaian_keu,
                            round(avg(case when capaian_fisik > 100 then 100 else capaian_fisik end),2) as capaian_fisik
                        FROM 
                            api.mv_renja_monev_agg
                        WHERE 1=1" .$where .
                        " GROUP BY
                            tahun,kementerian_id,
                            kementerian_nama_alias, 
                            intervensi_id,intervensi_nama
                    ");
            $tahun ? $chart= collect($chart)->whereIn('tahun',$tahun) : false;
            $kementerian ? $chart= collect($chart)->whereIn('kementerian_id',$kementerian)  : false;
            $intervensi ? $chart= collect($chart)->whereIn('intervensi_id',$intervensi) : false;
            $charts=[];
            foreach ($chart as $rs) {
                array_push($charts,$rs);
            }
        
        $results = new \stdClass;
        $results->detail = $details;
        $results->indikator = $indikators;
        $results->chart = $charts;
        
        
        return $this->returnJsonSuccessCheck("Data fetched successfully", $results);
    }

    public function capaianDetailByLokasi(Request $request)
    {

        $where = $request->lokasi_id ?  $where = $where . " AND kabupaten_id = '" . $request->lokasi_id . "'" : '' ;
        // $where = $request->lokasi_id ? " WHERE id = " . $request->lokasi_id : "" ;
        $where = $request->tahun ? ' AND tahun IN (' . implode(',', $request->tahun) . ')' : "" ;
        $where = $request->intervensi ? $where = $where . ' AND intervensi_id IN (' . implode(',', $request->intervensi) . ')' : '' ;
        $where = $request->kementerian ? $where = $where . ' AND kementerian_id IN (' . implode(',', $request->kementerian) . ')' : '' ;
        

        $lokasi = DB::SELECT("SELECT id, kabupaten_kode,kabupaten_nama,provinsi_id,provinsi_kode,provinsi_nama,provinsi_nama_alias FROM api.kabupaten" . $where);

        set_time_limit(300);

        foreach ($lokasi as $lok){
            $data = DB::SELECT("SELECT
                            tahun,
                            kementerian_id,
                            intervensi_id,
                            program_id,
                            kegiatan_id,
                            output_id,
                            sub_output_id,
                            komponen_id,
                            alokasi_1 AS alokasi,
                            realisasi,
                            capaian_keu AS capaian,
                            volume AS target,
                            capaian_fisik FROM api.mv_renja_monev WHERE 1=1" .$where);
            $lok->data = $data;
        }
        return $this->returnJsonSuccessCheck("Data fetched successfully", $lokasi);
    }

    public function capaianIndikator(Request $request)
    {
        // $where = $request->tahun ? ' AND tahun IN (' . implode(',', $request->tahun) . ')' : '' ;
        $where = $request->tahun ? " AND tahun = " . $request->tahun : "" ;
        $request->intervensi ?  $where = $where . " AND intervensi_id = '" . $request->intervensi . "'" : $where ;
        $request->kementerian ?  $where = $where . " AND kementerian_id = '" . $request->kementerian . "'" : $where ;
        // $request->provinsi ?  $where = $where . " AND a.provinsi_id = '" . $request->provinsi . "'" : $where ;
        $request->lokasi_id ?  $where = $where . " AND kabupaten_id = '" . $request->lokasi_id . "'" : $where ;
        
        $results = DB::SELECT("SELECT
                            kabupaten_id AS lokasi_id,
                            komponen_id AS id,
                            komponen_id AS parent_id,
                            coalesce(satuan,'-') AS satuan,
                            volume as target,
                            realisasi
                        FROM 
                            api.mv_renja_monev 
                        WHERE id>0" . $where
                    );
            
        return $this->returnJsonSuccessCheck("Data fetched successfully", $results);
    }


    public function intervensiTotal(Request $request)
    {
        $tahun = $request->tahun ? array_map(function($value) {
                return intval($value);
            }, $request->tahun) : NULL;
        $intervensi = $request->intervensi ? array_map(function($value) {
                return intval($value);
            }, $request->intervensi) : NULL;
        $kementerian = $request->kementerian ? array_map(function($value) {
                return intval($value);
            }, $request->kementerian) : NULL;
            $where = '';

        

            if($request->has('tahun') AND $request->tahun != NULL){

                if(count($request->tahun) == 1){
                   $tahun = $request->tahun;
                   $tahun[1] = $tahun[0] - 1;

               
                }else{
                    $tahun = $request->tahun;
                }

                $where .= ' AND tahun IN ('.implode(',',$tahun). ')';
            }

            
            if($request->has('intervensi') AND $request->intervensi != NULL){
                $where .= ' AND intervensi_id IN ('.implode(',',$intervensi). ')';
            }

            if($request->has('kementerian') AND $request->kementerian != NULL){
                $where .= ' AND kementerian_id IN ('.implode(',',$kementerian). ')';
            }
      

           // echo $where;

       // $where = $tahun ? ' AND tahun IN (' . implode(',', $request->tahun) . ')' : "" ;
        //$where = $intervensi ?  $where . ' AND intervensi_id IN (' . implode(',', $intervensi) . ')' : '' ;
       // $where = $kementerian ?  $where . ' AND kementerian_id IN (' . implode(',', $kementerian) . ')' : '' ;
        // $lokasi_id ?  $where = $where . " AND kabupaten_id = " . $lokasi_id  : $where ;
    
        $query = "SELECT tahun,
        sum(alokasi_1) as alokasi,
        sum(realisasi) as realisasi,
        round((sum(realisasi)/sum(alokasi_1))*100,2) as capaian_keu,
        round(avg(case when capaian_fisik >= 100 then 100 else capaian_fisik end),2) as capaian_fisik
        FROM api.mv_renja_monev_agg  WHERE 1=1" . $where. " GROUP BY tahun  ORDER BY tahun DESC";

        // echo $query;
        // exit;
     


        $res = DB::SELECT($query);
            $result = [];
      //  echo count($res);
      $realisasi = 100;
      $alokasi = 100;
      $capaian_keu = 100;
      $suboutput = 100;

      $result = $res[0];
       
        if(count($res) > 1){

            $alokasi = $res[0]->alokasi - $res[1]->alokasi;
            $alokasi = $alokasi / $res[1]->alokasi;
            $alokasi = $alokasi * 100;
            $alokasi = number_format($alokasi,2);

            $result->alokasi_sebelumnya = $res[1]->alokasi;
            $result->perbandinganalokasi = (float) $alokasi;



            $realisasi = $res[0]->realisasi - $res[1]->realisasi;
            $realisasi = $realisasi / $res[1]->realisasi;
            $realisasi = $realisasi * 100;
            $realisasi = number_format($realisasi,2);

            $result->realisasi_sebelumnya = $res[1]->realisasi;
            $result->perbandingan_realisasi =  (float) $realisasi;



            $capaian_keu = $res[0]->capaian_keu - $res[1]->capaian_keu;
            $capaian_keu = $realisasi / $res[1]->capaian_keu;
            $capaian_keu = $realisasi * 100;
            $capaian_keu = number_format($capaian_keu,2);

            $result->capaian_keu_sebelumnya = $res[1]->capaian_keu;
            $result->perbandingan_capaiankeu = (float) $capaian_keu;



            $suboutput = $res[0]->capaian_fisik - $res[1]->capaian_fisik;
            $suboutput = $suboutput / $res[1]->capaian_fisik;
            $suboutput = $suboutput * 100;
            $suboutput = number_format($suboutput,2);

            $result->capaian_fisik_sebelumnya = $res[1]->capaian_fisik;
            $result->perbandingan_capaianfisik = (float)  $suboutput;



          //  echo number_format($kinerja,2);
            ///exit;

        }else{
            $result->capaian_fisik_sebelumnya = 0;
            $result->capaian_keu_sebelumnya = 0;
            $result->realisasi_sebelumnya = 0;
            $result->alokasi_sebelumnya = 0;
            $result->perbandingan_capaianfisik = $suboutput;
            $result->perbandingan_capaiankeu = $suboutput;
            $result->perbandingan_alokasi = $suboutput;
            $result->perbandingan_realisasi = $suboutput;


        }

    //dd($result);


        
        
        
        
        return $this->returnJsonSuccessCheck("Data fetched successfully", $result);
    }

}

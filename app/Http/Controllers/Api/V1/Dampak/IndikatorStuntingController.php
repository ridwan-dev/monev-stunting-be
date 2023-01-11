<?php
namespace App\Http\Controllers\Api\V1\Dampak;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\Pub\Provinsi;
use App\Models\Pub\Kabupaten;
use Illuminate\Support\Facades\DB;

class IndikatorStuntingController extends BaseController
{


    public function renjakrisna(Request $request){
        $where = $request->tahun ? " AND thang::int = " . $request->tahun : "" ;


        $data = DB::select("SELECT * FROM testingstagging2.mv_krisna_renja WHERE kdtema  ILIKE '%008%'  ".$where);
        return $this->returnJsonSuccess("Data fetched successfully",$data);

    }
    public function dataSwuNasional(Request $request)
    {
        $where = $request->tahun ? " AND (data->>'tahun')::integer = " . $request->tahun : "" ;
        $request->sumber ?  $where = $where . " AND data->>'sumber' = '" . $request->sumber . "'" : $where ;
        $request->age ?  $where = $where . " AND data->>'age' = '" . $request->age . "'" : $where ;

        $stunting = DB::select("SELECT
                -- jenis,
                data->>'tahun' as tahun, 
                data->>'sumber' as sumber,
                data->>'age' as age,
                data->>'pb_u_sangat_pendek' as pb_u_sangat_pendek,
                data->>'pb_u_pendek' as pb_u_pendek,
                data->>'pb_u_stunting' as pb_u_stunting,
                data->>'pb_u_normal' as pb_u_normal
                FROM api.dt_swu
                WHERE lokasi_id=0 AND jenis='stunting'" . $where 
             ); 

        $wasting = DB::select("SELECT
                -- jenis,
                data->>'tahun' as tahun, 
                data->>'sumber' as sumber,
                data->>'age' as age,
                data->>'jml_a1' as jml_a1,
                data->>'jml_a2' as jml_a2,
                data->>'jml_a3' as jml_a3
                FROM api.dt_swu
                WHERE lokasi_id=0 AND jenis='wasting'" . $where 
             ); 
       
        $underweight = DB::select("SELECT
                -- jenis,
                data->>'tahun' as tahun, 
                data->>'sumber' as sumber,
                data->>'age' as age,
                data->>'jml_a1' as jml_a1,
                data->>'jml_a2' as jml_a2,
                data->>'jml_a3' as jml_a3
                FROM api.dt_swu
                WHERE lokasi_id=0 AND jenis='underweight'" . $where 
             ); 
        
        $results = new \stdClass;
        $results->stunting = $stunting;
        $results->wasting = $wasting;
        $results->underweight = $underweight;

        return $this->returnJsonSuccess("Data fetched successfully", $results);
    }

    public function dataStunting(Request $request)
    {
        $where = $request->tahun ? " AND tahun = " . $request->tahun : "" ;
        $request->sumber ?  $where = $where . " AND sumber = '" . $request->sumber . "'" : $where ;
        $request->age ?  $where = $where . " AND age = '" . $request->age . "'" : $where ;

        $results = DB::select("select json_agg(t) AS data
            from  
            (SELECT DISTINCT
                sp.id, 
                sp.provinsi_kode,
                sp.provinsi_nama,
                sp.provinsi_nama_alias,
                (select 
                jsonb_agg(jsonb_build_object(
                    'tahun', b.tahun,
                    'age', b.age,
                    'sumber', b.sumber,
                    'pb_u_sangat_pendek', b.pb_u_sangat_pendek,
                    'pb_u_pendek', b.pb_u_pendek,
                    'pb_u_stunting', b.pb_u_stunting,
                    'pb_u_normal', b.pb_u_normal
                    ))
                from api.data_stunting b
                where b.lokasi_id= a.lokasi_id" . $where .
                ") as data
            FROM
                api.data_stunting a
                INNER JOIN
                api.provinsi sp
            ON 
                sp.id = a.lokasi_id 
            WHERE a.tingkat = 1" . $where .
            ") as t(id,provinsi_kode,provinsi_nama,provinsi_nama_alias,data)"); 
       

        return $this->returnJsonSuccess("Data fetched successfully", json_decode($results[0]->data));
    }

    public function dataWasting(Request $request)
    {
        $where = $request->tahun ? " AND tahun = " . $request->tahun : "" ;
        $request->sumber ?  $where = $where . " AND sumber = '" . $request->sumber . "'" : $where ;
        $request->age ?  $where = $where . " AND age = '" . $request->age . "'" : $where ;

        $results = DB::select("select json_agg(t) AS data
            from  
            (SELECT DISTINCT
                sp.id, 
                sp.provinsi_kode,
                sp.provinsi_nama,
                sp.provinsi_nama_alias,
                (select 
                jsonb_agg(jsonb_build_object(
                    'tahun', b.tahun,
                    'age', b.age,
                    'sumber', b.sumber,
                    'jml_a1', b.jml_a1,
                    'jml_a2', b.jml_a2,
                    'jml_a3', b.jml_a3
                    ))
                from api.data_wasting b
                where b.lokasi_id= a.lokasi_id" . $where .
                ") as data
            FROM
                api.data_wasting a
                INNER JOIN
                api.provinsi sp
            ON 
                sp.id = a.lokasi_id 
            WHERE a.tingkat = 1" . $where .
            ") as t(id,provinsi_kode,provinsi_nama,provinsi_nama_alias,data)");
            
                

        return $this->returnJsonSuccess("Data fetched successfully", json_decode($results[0]->data));
    }

    public function dataUnderweight(Request $request)
    {
        $where = $request->tahun ? " AND tahun = " . $request->tahun : "" ;
        $request->sumber ?  $where = $where . " AND sumber = '" . $request->sumber . "'" : $where ;
        $request->age ?  $where = $where . " AND age = '" . $request->age . "'" : $where ;

        $results = DB::select("select json_agg(t) AS data
            from  
            (SELECT DISTINCT
                sp.id, 
                sp.provinsi_kode,
                sp.provinsi_nama,
                sp.provinsi_nama_alias,
                (select 
                jsonb_agg(jsonb_build_object(
                    'tahun', b.tahun,
                    'age', b.age,
                    'sumber', b.sumber,
                    'jml_a1', b.jml_a1,
                    'jml_a2', b.jml_a2,
                    'jml_a3', b.jml_a3
                    ))
                from api.data_underweight b
                where b.lokasi_id= a.lokasi_id" . $where .
                ") as data
            FROM
                api.data_underweight a
                INNER JOIN
                api.provinsi sp
            ON 
                sp.id = a.lokasi_id 
            WHERE a.tingkat = 1" . $where .
            ") as t(id,provinsi_kode,provinsi_nama,provinsi_nama_alias,data)"); 
       

        return $this->returnJsonSuccess("Data fetched successfully", json_decode($results[0]->data));
    }


    //v2 iqbal handle it

    public function dataStuntingSurvey(Request $request)
    {
        $where = '';
        $area = $request->area ? $request->area : "";
        if(strtoupper($area) == 'PROVINSI'){
            $where = "  tingkat = 1 ";
        }else{
            $where = "  tingkat = 2 ";
        }

        $request->age ?  $where = $where . " AND age = '" . $request->age . "'" : $where  ;
        $qq = "select sumber,tahun FROM api.data_stunting WHERE ".$where. " GROUP BY sumber, tahun ORDER BY tahun ASC";
       
        $results = DB::select($qq); 
        $dt  = [];
        foreach($results as $k => $v){
            if(@$dt[$v->sumber] == ''){
                $dt[$v->sumber][] = $v->tahun;
               // $dt[$v->sumber]['tahun'][] = $v->tahun;

            }else{
                $dt[$v->sumber][] = $v->tahun;

            }

        }
      

        return $this->returnJsonSuccess("Data fetched successfully",$dt);
    }


    public function dataWastingSurvey(Request $request)
    {
        $where = '';
        $area = $request->area ? $request->area : "";
        if(strtoupper($area) == 'PROVINSI'){
            $where = "  tingkat = 1 ";
        }else{
            $where = "  tingkat = 2 ";
        }

        $request->age ?  $where = $where . " AND age = '" . $request->age . "'" : $where  ;
        $qq = "select sumber,tahun FROM api.data_wasting WHERE ".$where. " GROUP BY sumber, tahun ORDER BY tahun ASC";
      
        $results = DB::select($qq); 
        $dt  = [];
        foreach($results as $k => $v){
            if(@$dt[$v->sumber] == ''){
                $dt[$v->sumber][] = $v->tahun;
               // $dt[$v->sumber]['tahun'][] = $v->tahun;

            }else{
                $dt[$v->sumber][] = $v->tahun;

            }

        }
      

        return $this->returnJsonSuccess("Data fetched successfully",$dt);
    }


    public function dataUnderweightSurvey(Request $request)
    {
        $where = '';
        $area = $request->area ? $request->area : "";
        if(strtoupper($area) == 'PROVINSI'){
            $where = "  tingkat = 1 ";
        }else{
            $where = "  tingkat = 2 ";
        }

        $request->age ?  $where = $where . " AND age = '" . $request->age . "'" : $where  ;
        $qq = "select sumber,tahun FROM api.data_underweight WHERE ".$where. " GROUP BY sumber, tahun ORDER BY tahun ASC";
      
        $results = DB::select($qq); 
        $dt  = [];
        foreach($results as $k => $v){
            if(@$dt[$v->sumber] == ''){
                $dt[$v->sumber][] = $v->tahun;
               // $dt[$v->sumber]['tahun'][] = $v->tahun;

            }else{
                $dt[$v->sumber][] = $v->tahun;

            }

        }
      

        return $this->returnJsonSuccess("Data fetched successfully",$dt);
    }




    public function dataStuntingPartial(Request $request)
    {    
        // $request->area ?  $where = $where . " AND age = '" . $request->age . "'" : $where ;
        $area=$request->area;
        $area=strtoupper($area);
        if($area == 'PROVINSI'){
            $tingkat = 1;
        }else if($area == 'KABUPATEN'){
            $tingkat = 2;
        }else{
            $tingkat = 3;
        }
        $where = " AND tingkat = $tingkat ";
        $request->age ?  $where = $where . " AND age = '" . $request->age . "'" : $where ;
        $request->sumber ?  $where = $where . " AND sumber = '" . $request->sumber . "'" : $where ;
        $request->provinsi ?  $where = $where . " AND a.provinsi_kode  = '" . $request->provinsi . "'" : $where ;
        $request->kabupaten ?  $where = $where . " AND a.kabupaten_kode  = '" . $request->kabupaten . "'" : $where ;
        $request->kecamatan ?  $where = $where . " AND a.kecamatan_kode  = '" . $request->kecamatan . "'" : $where ;
        // set_time_limit(300);
        $tahun = NULL;
        if($request->has('tahun') && !empty($request->tahun)){
            $tahun = $request->tahun;
        }       
        
        $qtahun = "
            SELECT tahun FROM api.data_stunting a WHERE tingkat = $tingkat $where  GROUP BY tahun ORDER BY tahun ASC
            ";
        $tahun_arr = DB::select($qtahun);
        $string_tahun = '';
        foreach($tahun_arr as $k => $v){
            $string_tahun .= "'$v->tahun',";
        }
        $string_tahun = rtrim($string_tahun, ", ");
        $where = $request->tahun ? " AND tahun = " . $request->tahun : "" ;
        $var = json_encode(array('BALITA','BADUTA'),JSON_UNESCAPED_SLASHES);
        //$sumber = json_encode(array("riskesdas","ssgi","psg"),JSON_UNESCAPED_SLASHES);
        $sumber = json_encode(array("riskesdas","ssgi","psg","ssgbi"),JSON_UNESCAPED_SLASHES);
        $qsumber = "
            SELECT sumber FROM api.data_stunting WHERE tingkat = $tingkat  
            ";
        if($request->has('age') && !empty($request->age)){
            $qsumber .=  " AND age = '".$request->age."'";
        }
        if($request->has('provinsi') && !empty($request->provinsi)){
            $qsumber .=  " AND provinsi_kode = '".$request->provinsi."'";
        }
        if($request->has('kabupaten') && !empty($request->kabupaten)){
            $qsumber .=  " AND kabupaten_kode = '".$request->kabupaten."'";
        }
        $qsumber .= " GROUP BY sumber  ORDER BY sumber ASC ";
        $sumber_arr = DB::select($qsumber);
        $string_sumber = '';
        foreach($sumber_arr as $k => $v){
            $string_sumber .= "'$v->sumber',";
        }
        $string_sumber = rtrim($string_sumber, ", ");

        if($area == 'PROVINSI'){
            if($request->has('provinsi') && !empty($request->provinsi)){
                $where = '';
                $where = ' AND tingkat =  2 '; 
                $request->age ?  $where = $where . " AND age = '" . $request->age . "'" : $where ;
                $request->sumber ?  $where = $where . " AND sumber = '" . $request->sumber . "'" : $where ;
                $request->provinsi ?  $where = $where . " AND a.provinsi_kode  = '" . $request->provinsi . "'" : $where ;

                $query = "select  
                            json_build_object(
                                'type', 'FeatureCollection','features',json_agg(public.ST_AsGeoJSON(t.*)::json),
                                'data', json_build_object(
                                    'age',json_build_object('active','".$request->age."','data',json_build_array('balita','baduta')),
                                    'tahun',json_build_object('active','".$tahun."','data',json_build_array(".$string_tahun.")),
                                    'sumber',json_build_object('active','".$request->sumber."','data',json_build_array(".$string_sumber."))
                                )
                            )  AS data
                from (
                    SELECT DISTINCT
                    sp.id, sp.kabupaten_kode, sp.kabupaten_nama,sp.geom,TRIM(sp.latitude),
                    TRIM(sp.longitude),
                    (select 
                        jsonb_agg(
                            jsonb_build_object(
                                'tahun', b.tahun,'area', 'kabupaten','age', b.age,
                                'sumber', b.sumber,'pb_u_sangat_pendek', b.pb_u_sangat_pendek,
                                'pb_u_pendek', b.pb_u_pendek,'pb_u_stunting', b.pb_u_stunting,
                                'pb_u_normal', b.pb_u_normal,'jml',bl.jumlah_balita,
                                'balita_ditimbang',b.balita_ditimbang,'ps', 
                                CAST((b.pb_u_stunting / 100) * bl.jumlah_balita AS INT),
                                'rse', b.rse
                                )
                            )
                    from api.data_stunting b 
                    left join api.data_balita bl 
                        ON bl.provinsi_id = b.lokasi_id AND b.tahun = bl.thn
                    where b.lokasi_id= a.lokasi_id" . $where .") as data,
                    (select 
                        jsonb_agg(jsonb_build_object('nama',puskesmas))
                    from api.data_puskesmas pusk 
                    LEFT JOIN api.kecamatan kec 
                        ON pusk.kode_kecamatan = kec.kecamatan_kode
                    where kec.parent_kode_kemendagri = sp.kode_kemendagri AND sumber  = '$request->sumber' 
                    ) as puskesmas
                FROM
                    api.data_stunting a
                INNER JOIN api.kabupaten sp
                    ON sp.id = a.lokasi_id 
                WHERE a.tingkat = 2" . $where .
                ") as t(id,kabupaten_kode,kabupaten_nama,geom,latitude,longitude,data)";

                //$puskesmas =  DB::select("SELECT puskesmas FROM api.data_puskesmas LEFT JOIN api.kecamatan ON api.data_puskesmas.kode_kecamatan = kecamatan.kecamatan_kode WHERE sumber = '$request->sumber' AND parent_kode_kemendagri = '$request->kabupaten'");
            }else{
                ///  $where = ' AND tingkat =  1 '; 
                $request->age ?  $where = $where . " AND age = '" . $request->age . "'" : $where ;
               // $request->age ?  $where = $where . " AND tingkat = 2 " : $where ;
                $request->sumber ?  $where = $where . " AND sumber = '" . $request->sumber . "'" : $where ;
                $query = "select  
                            json_build_object(
                                'type', 'FeatureCollection','features', json_agg(public.ST_AsGeoJSON(t.*)::json),'data',
                                json_build_object('age',
                                    json_build_object(
                                        'active','".$request->age."','data',json_build_array('balita','baduta')
                                    ),'tahun',
                                    json_build_object(
                                        'active','".$tahun."','data',json_build_array(".$string_tahun.")
                                    ),'sumber',
                                    json_build_object(
                                        'active','".$request->sumber."','data',json_build_array(".$string_sumber.")
                                    )
                                )
                            )  AS data
                        from (
                            SELECT 
                                DISTINCT sp.id, sp.provinsi_kode, sp.provinsi_nama,
                                sp.geom,TRIM(sp.latitude),TRIM(sp.longitude),
                                (
                                    select 
                                        jsonb_agg(
                                            jsonb_build_object(
                                                'tahun', b.tahun,'area', '$request->area','age', b.age,
                                                'sumber', b.sumber,'pb_u_sangat_pendek', b.pb_u_sangat_pendek,
                                                'pb_u_pendek', b.pb_u_pendek,'pb_u_stunting', b.pb_u_stunting,
                                                'pb_u_normal', b.pb_u_normal,'jml',bl.jumlah_balita,
                                                'balita_ditimbang',b.balita_ditimbang,'ps', 
                                                CAST((b.pb_u_stunting / 100) * bl.jumlah_balita AS INT),
                                                'rse', b.rse
                                            )
                                        )
                                    from api.data_stunting b 
                                    left join api.data_balita bl 
                                        ON bl.provinsi_id = b.lokasi_id AND b.tahun = bl.thn
                                    where b.lokasi_id= a.lokasi_id" . $where .
                                ") as data,
                                (
                                    select 
                                        jsonb_agg(jsonb_build_object('nama',puskesmas))
                                    from api.data_puskesmas pusk
                                    where pusk.kode_provinsi = a.provinsi_kode AND sumber  = '$request->sumber' 
                                ) as puskesmas
                            FROM
                                api.data_stunting a
                            INNER JOIN api.provinsi sp
                                ON sp.id = a.lokasi_id 
                            WHERE a.tingkat = 1" . $where .
                        ") as t(id,provinsi_kode,provinsi_nama,geom,latitude,longitude,data)";
                }
                //$puskesmas =  DB::select("SELECT puskesmas FROM api.data_puskesmas WHERE sumber = '$request->sumber' AND kode_provinsi = '$request->provinsi'");
                //echo $query;
                //exit;    
        }else if($area == 'KABUPATEN') { 
            $query = "select  
                        json_build_object(
                            'type', 'FeatureCollection','features', 
                            json_agg(public.ST_AsGeoJSON(t.*)::json),'data',
                            json_build_object('age',
                                json_build_object(
                                    'active','".$request->age."','data',json_build_array('balita','baduta')
                                ),'tahun',
                                json_build_object(
                                    'active','".$tahun."','data',json_build_array(".$string_tahun.")
                                ),'sumber',
                                json_build_object(
                                    'active','".$request->sumber."','data',json_build_array(".$string_sumber.")
                                )
                            )
                        )  AS data
                    from (
                        SELECT 
                            DISTINCT sp.id, sp.kabupaten_kode,sp.kabupaten_nama,sp.geom,
                            TRIM(sp.latitude),TRIM(sp.longitude),
                            (
                                select 
                                    jsonb_agg(
                                        jsonb_build_object(
                                            'tahun', b.tahun,'area', '$request->area',
                                            'age', b.age,'sumber', b.sumber,
                                            'pb_u_sangat_pendek', b.pb_u_sangat_pendek,
                                            'pb_u_pendek', b.pb_u_pendek,
                                            'pb_u_stunting', b.pb_u_stunting,
                                            'pb_u_normal', b.pb_u_normal,'jml',bl.jumlah_balita,
                                            'balita_ditimbang',b.balita_ditimbang,'ps', 
                                            CAST((b.pb_u_stunting / 100) * bl.jumlah_balita AS INT),
                                            'rse', b.rse
                                        )
                                    )
                                from api.data_stunting b 
                                left join api.data_balita bl 
                                    ON bl.provinsi_id = b.lokasi_id AND b.tahun = bl.thn
                                where b.lokasi_id= a.lokasi_id" . $where .
                            ") as data,
                            (
                                select 
                                    jsonb_agg(jsonb_build_object('nama',puskesmas))
                                from api.data_puskesmas pusk 
                                LEFT JOIN api.kecamatan 
                                    ON pusk.kode_kecamatan = kecamatan.kecamatan_kode 
                                where parent_kode_kemendagri = a.kabupaten_kode AND sumber  = '$request->sumber' 
                            ) as puskesmas
                        FROM
                            api.data_stunting a
                        INNER JOIN api.kabupaten sp
                            ON  sp.id = a.lokasi_id 
                        WHERE a.tingkat = 2" . $where .
                    ") as t(id,kabupaten_kode,kabupaten_nama,geom,latitude,longitude,data)";
        ///$puskesmas =  DB::select("SELECT puskesmas FROM api.data_puskesmas LEFT JOIN api.kecamatan ON api.data_puskesmas.kode_kecamatan = kecamatan.kecamatan_kode WHERE sumber = '$request->sumber' AND parent_kode_kemendagri = '$request->kabupaten'");
        
        }else if($area == 'KECAMATAN'){
            if($request->has('kabupaten') && !empty($request->kabupaten)){
                $where = '';
                $where = ' AND tingkat =  3 '; 
                $request->age ?  $where = $where . " AND age = '" . $request->age . "'" : $where ;
               // $request->age ?  $where = $where . " AND tingkat = 2 " : $where ;
                $request->sumber ?  $where = $where . " AND sumber = '" . $request->sumber . "'" : $where ;
                $request->kabupaten ?  $where = $where . " AND a.kabupaten_kode  = '" . $request->kabupaten . "'" : $where ;
               // echo 'here';
               // exit;
                $query = "select
                            json_build_object(
                                'type', 'FeatureCollection','features', json_agg(public.ST_AsGeoJSON(t.*)::json),'data', 
                                json_build_object('age',
                                    json_build_object(
                                        'active','".$request->age."','data',json_build_array('balita','baduta')
                                    ),'tahun',
                                    json_build_object(
                                        'active','".$tahun."','data',json_build_array(".$string_tahun.")
                                    ),'sumber',
                                    json_build_object(
                                        'active','".$request->sumber."','data',json_build_array(".$string_sumber.")
                                    )
                                )
                            )  AS data
                        from (
                            SELECT 
                                DISTINCT sp.id, sp.kecamatan_kode,sp.kecamatan_nama,sp.geom,
                                (
                                    select 
                                        jsonb_agg(
                                            jsonb_build_object(
                                                'tahun', b.tahun,'area', 'kecamatan_nama',
                                                'age', b.age,'sumber', b.sumber,
                                                'pb_u_sangat_pendek', b.pb_u_sangat_pendek,
                                                'pb_u_pendek', b.pb_u_pendek,'pb_u_stunting',
                                                b.pb_u_stunting,'pb_u_normal', b.pb_u_normal,
                                                'jml',bl.jumlah_balita,'balita_ditimbang',
                                                b.balita_ditimbang,'ps', 
                                                CAST((b.pb_u_stunting / 100) * bl.jumlah_balita AS INT),
                                                'rse', b.rse
                                            )
                                        )
                                    from api.data_stunting b 
                                    left join api.data_balita bl 
                                        ON bl.provinsi_id = b.lokasi_id AND b.tahun = bl.thn
                                    where b.kecamatan_kode = a.kecamatan_kode  " . $where .
                                ") as data,  
                                (
                                    select 
                                        jsonb_agg(jsonb_build_object('nama',puskesmas))
                                    from api.data_puskesmas pusk 
                                    WHERE kode_kecamatan = a.kecamatan_kode AND sumber  = '$request->sumber' 
                                ) as puskesmas
                            FROM
                                api.data_stunting a
                            INNER JOIN api.kecamatan sp
                                ON sp.kecamatan_kode = a.kecamatan_kode
                            WHERE a.tingkat = 3" . $where .
                        ") as t(id,kecamatan_kode,kecamatan_nama,geom,data)";
             //  echo $query;
             //  exit;
             //$puskesmas =  DB::select("SELECT puskesmas FROM api.data_puskesmas LEFT JOIN api.kecamatan ON api.data_puskesmas.kode_kecamatan = kecamatan.kecamatan_kode WHERE sumber = '$request->sumber' AND parent_kode_kemendagri = '$request->kabupaten'");
            }else{
                $query = "select 
                            json_build_object(
                                'type', 'FeatureCollection','features', json_agg(public.ST_AsGeoJSON(t.*)::json),'data', 
                                json_build_object('age',
                                    json_build_object(
                                        'active','".$request->age."','data',json_build_array('balita','baduta')
                                    ),'tahun',
                                    json_build_object(
                                        'active','".$tahun."','data',json_build_array(".$string_tahun.")
                                    ),'sumber',
                                    json_build_object(
                                        'active','".$request->sumber."','data',json_build_array(".$string_sumber.")
                                    )
                                )
                            )  AS data
                        from (
                            SELECT 
                                DISTINCT sp.id, sp.kecamatan_kode,sp.kecamatan_nama,sp.geom,
                                (
                                    select 
                                        jsonb_agg(
                                            jsonb_build_object(
                                                'tahun', b.tahun,'area', 'kecamatan_nama','age',
                                                b.age,'sumber', b.sumber,'pb_u_sangat_pendek', b.pb_u_sangat_pendek,
                                                'pb_u_pendek', b.pb_u_pendek,'pb_u_stunting', b.pb_u_stunting,
                                                'pb_u_normal', b.pb_u_normal,'balita_ditimbang',b.balita_ditimbang,
                                                'jml',bl.jumlah_balita,'ps', CAST((b.pb_u_stunting / 100) * bl.jumlah_balita AS INT),
                                                'rse', b.rse
                                            )
                                        )
                                    from api.data_stunting b 
                                    left join api.data_balita bl 
                                        ON bl.provinsi_id = b.lokasi_id AND b.tahun = bl.thn
                                    where b.kecamatan_kode = a.kecamatan_kode  " . $where .
                                ") as data,  
                                (
                                    select 
                                        jsonb_agg(jsonb_build_object('nama',puskesmas))
                                    from api.data_puskesmas pusk 
                                    WHERE kode_kecamatan = a.kecamatan_kode AND sumber  = '$request->sumber' 
                                ) as puskesmas
                            FROM
                                api.data_stunting a
                            INNER JOIN api.kecamatan sp
                                ON sp.kecamatan_kode = a.kecamatan_kode
                            WHERE a.tingkat = 3" . $where .
                        ") as t(id,kecamatan_kode,kecamatan_nama,geom,data)";
            //$puskesmas =  DB::select("SELECT puskesmas FROM api.data_puskesmas LEFT JOIN api.kecamatan ON api.data_puskesmas.kode_kecamatan = kecamatan.kecamatan_kode WHERE sumber = '$request->sumber' AND b.kecamatan_kode = '$request->kecamatan'");
            }
        }
        $results = DB::select($query);
        //dd($results);
        //$decode = json_decode($results[0]->data);
        $tresult = json_decode($results[0]->data);
        //$results[0]->data['puskesmas'] = 'a';
        //if($tingkat == 1){
        //$where = " WHERE prov = '$$request->provinsi' ";
        //}else if($tingkat == 2){
        //$where = " WHERE prov = '$$request->provinsi' ";
        //}else{
        //$where = 
        //}
        //dd($tresult);
        return $this->returnJsonSuccessCheck("Data fetched successfully",$tresult );
    }

    public function dataWastingPartial(Request $request)
    {
        // $request->area ?  $where = $where . " AND age = '" . $request->age . "'" : $where ;
        $area=$request->area;
        $area=strtoupper($area);
        if(strtoupper($area) == 'PROVINSI'){
            $tingkat = 1;
        }else if(strtoupper($area) == 'KABUPATEN'){
            $tingkat = 2;
        }else{
            $tingkat = 3;
        }
        $where = " AND tingkat = $tingkat ";        
        $request->age ?  $where = $where . " AND age = '" . $request->age . "'" : $where ;
        $request->sumber ?  $where = $where . " AND sumber = '" . $request->sumber . "'" : $where ;
        $request->provinsi ?  $where = $where . " AND a.provinsi_kode  = '" . $request->provinsi . "'" : $where ;
        $request->kabupaten ?  $where = $where . " AND a.kabupaten_kode  = '" . $request->kabupaten . "'" : $where ;
        // set_time_limit(300);
        $tahun = NULL;
        if($request->has('tahun') && !empty($request->tahun)){
            $tahun = $request->tahun;
        }
        $qtahun = "SELECT tahun FROM api.data_wasting a WHERE tingkat = $tingkat $where  GROUP BY tahun ORDER BY tahun ASC";
        // echo $qtahun;
        $tahun_arr = DB::select($qtahun);
            $string_tahun = '';
        foreach($tahun_arr as $k => $v){
            $string_tahun .= "'$v->tahun',";
        }
        $string_tahun = rtrim($string_tahun, ", ");
        // echo $string_tahun;
        // exit;      
        $where = $request->tahun ? " AND tahun = " . $request->tahun : "" ;
        $var = json_encode(array('BALITA','BADUTA'),JSON_UNESCAPED_SLASHES);
        $sumber = json_encode(array("riskesdas","ssgi","psg"),JSON_UNESCAPED_SLASHES);
        $qsumber = "SELECT sumber FROM api.data_wasting WHERE tingkat = $tingkat  ";
        // if($request->has('tahun') && !empty($request->tahun)){
        //     $tahun = $request->tahun;
        //     $qsumber .= " AND tahun = ".$tahun;
        // }
        if($request->has('age') && !empty($request->age)){
            $qsumber .=  " AND age = '".$request->age."'";
        }
        if($request->has('provinsi') && !empty($request->provinsi)){
            $qsumber .=  " AND provinsi_kode = '".$request->provinsi."'";
        }
        if($request->has('kabupaten') && !empty($request->kabupaten)){
            $qsumber .=  " AND kabupaten_kode = '".$request->kabupaten."'";
        }
        $qsumber .= " GROUP BY sumber  ORDER BY sumber ASC ";
        //  echo $qtahun;
        $sumber_arr = DB::select($qsumber);
            $string_sumber = '';
        foreach($sumber_arr as $k => $v){
            $string_sumber .= "'$v->sumber',";
        }
        $string_sumber = rtrim($string_sumber, ", ");

        if($area == 'PROVINSI'){
            if($request->has('provinsi') && !empty($request->provinsi)){
                $where = '';
                $where = ' AND tingkat =  2 '; 
                $request->age ?  $where = $where . " AND age = '" . $request->age . "'" : $where ;
                // $request->age ?  $where = $where . " AND tingkat = 2 " : $where ;
                $request->sumber ?  $where = $where . " AND sumber = '" . $request->sumber . "'" : $where ;
                $request->provinsi ?  $where = $where . " AND a.provinsi_kode  = '" . $request->provinsi . "'" : $where ;

                $query = "select
                            json_build_object(
                                'type', 'FeatureCollection','features', 
                                json_agg(public.ST_AsGeoJSON(t.*)::json),'data',
                                json_build_object('age',
                                    json_build_object(
                                        'active','".$request->age."','data',
                                        json_build_array('balita','baduta')
                                    ),'tahun',
                                    json_build_object(
                                        'active','".$tahun."','data',
                                        json_build_array(".$string_tahun.")
                                    ),'sumber',
                                    json_build_object(
                                        'active','".$request->sumber."','data',
                                        json_build_array(".$string_sumber.")
                                    )
                                )
                            )  AS data
                        from  
                            (SELECT 
                                DISTINCT sp.id, sp.kabupaten_kode, sp.kabupaten_nama, sp.geom,
                                TRIM(sp.latitude), TRIM(sp.longitude),
                                (
                                    select 
                                        jsonb_agg(
                                            jsonb_build_object(
                                                'tahun', b.tahun,'area', 'kabupaten','age', 
                                                b.age,'sumber', b.sumber,'jml_a1', b.jml_a1,
                                                'jml_a2', b.jml_a2,'jml_a3', b.jml_a3,'jml',
                                                bl.jumlah_balita,'ps', 
                                                CAST((b.jml_a1 / 100) * bl.jumlah_balita AS INT)
                                            )
                                        )
                                    from api.data_wasting b  
                                    left join api.data_balita bl 
                                        ON bl.kabupaten_kode = b.kabupaten_kode AND b.tahun = bl.thn
                                    where b.lokasi_id= a.lokasi_id" . $where .
                                ") as data
                            FROM
                                api.data_wasting a
                                INNER JOIN api.kabupaten sp
                                    ON  sp.id = a.lokasi_id 
                            WHERE a.tingkat = 2" . $where .
                        ") as t(id,kabupaten_kode,kabupaten_nama,geom,latitude,longitude,data)";
            }else{
                $query = "select  
                            json_build_object(
                                'type', 'FeatureCollection','features',
                                json_agg(public.ST_AsGeoJSON(t.*)::json),
                                'data', 
                                json_build_object('age',
                                    json_build_object(
                                        'active','".$request->age."',
                                        'data',json_build_array('balita','baduta')
                                    ),'tahun',
                                    json_build_object(
                                        'active','".$tahun."',
                                        'data',json_build_array(".$string_tahun.")
                                    ),'sumber',
                                    json_build_object(
                                        'active','".$request->sumber."',
                                        'data',json_build_array(".$string_sumber.")
                                    )
                                )
                            )  AS data
                        from (
                            SELECT 
                                DISTINCT sp.id, sp.provinsi_kode, sp.provinsi_nama,sp.geom,
                                TRIM(sp.latitude),TRIM(sp.longitude),
                                (
                                    select 
                                        jsonb_agg(
                                            jsonb_build_object(
                                                'tahun', b.tahun,'area', '$request->area','age',
                                                b.age,'sumber', b.sumber,'jml_a1', b.jml_a1,'jml_a2',
                                                b.jml_a2,'jml_a3', b.jml_a3,'jml',bl.jumlah_balita,
                                                'ps', CAST((b.jml_a1 / 100) * bl.jumlah_balita AS INT)
                                            )
                                        )
                                    from api.data_wasting b   
                                    left join api.data_balita bl 
                                        ON bl.provinsi_id = b.lokasi_id AND b.tahun = bl.thn
                                    where b.lokasi_id= a.lokasi_id" . $where .
                                ") as data
                            FROM
                                api.data_wasting a
                                INNER JOIN api.provinsi sp
                                    ON sp.id = a.lokasi_id 
                                WHERE a.tingkat = 1" . $where .
                            ") as t(id,provinsi_kode,provinsi_nama,geom,latitude,longitude,data)";
            }    
        }else if($area == 'KABUPATEN'){
            $query = "select
                        json_build_object(
                            'type', 'FeatureCollection','features',
                            json_agg(public.ST_AsGeoJSON(t.*)::json),'data',
                            json_build_object('age',
                                json_build_object(
                                    'active','".$request->age."','data',
                                    json_build_array('balita','baduta')
                                ),'tahun',
                                json_build_object(
                                    'active','".$tahun."','data',
                                    json_build_array(".$string_tahun.")
                                ),'sumber',
                                json_build_object(
                                    'active','".$request->sumber."','data',
                                    json_build_array(".$string_sumber.")
                                )
                            )
                        )  AS data
                    from (
                        SELECT 
                            DISTINCT sp.id, sp.kabupaten_kode, sp.kabupaten_nama,sp.geom,
                            TRIM(sp.latitude),TRIM(sp.longitude),
                            (
                                select 
                                    jsonb_agg(
                                        jsonb_build_object(
                                            'tahun', b.tahun,'area', '$request->area','age',
                                            b.age,'sumber', b.sumber,'jml_a1', b.jml_a1,'jml_a2',
                                            b.jml_a2,'jml_a3', b.jml_a3,'jml',bl.jumlah_balita,'ps',
                                            CAST((b.jml_a1 / 100) * bl.jumlah_balita AS INT)
                                        )
                                    )
                                from api.data_wasting b  
                                left join api.data_balita bl 
                                    ON bl.kabupaten_kode = b.kabupaten_kode AND b.tahun = bl.thn
                                where b.lokasi_id= a.lokasi_id" . $where .
                            ") as data
                        FROM api.data_wasting a
                        INNER JOIN api.kabupaten sp
                            ON sp.id = a.lokasi_id 
                        WHERE a.tingkat = 2" . $where .
                    ") as t(id,kabupaten_kode,kabupaten_nama,geom,latitude,longitude,data)";
        
        }else if($area == 'KECAMATAN'){
            if($request->has('kabupaten') && !empty($request->kabupaten)){
                $where = '';
                $where = ' AND tingkat =  3 '; 
                $request->age ?  $where = $where . " AND age = '" . $request->age . "'" : $where ;
                // $request->age ?  $where = $where . " AND tingkat = 2 " : $where ;
                $request->sumber ?  $where = $where . " AND sumber = '" . $request->sumber . "'" : $where ;
                $request->kabupaten ?  $where = $where . " AND a.kabupaten_kode  = '" . $request->kabupaten . "'" : $where ;
                // echo 'here';
                // exit;
                $query = "select  
                            json_build_object(
                                'type', 'FeatureCollection','features', 
                                json_agg(
                                    public.ST_AsGeoJSON(t.*)::json),'data',
                                    json_build_object('age',
                                        json_build_object(
                                            'active','".$request->age."','data',
                                            json_build_array('balita','baduta')
                                        ),'tahun',
                                        json_build_object(
                                            'active','".$tahun."',
                                            'data',json_build_array(".$string_tahun.")
                                        ),'sumber',
                                        json_build_object(
                                            'active','".$request->sumber."',
                                            'data',json_build_array(".$string_sumber.")
                                        )
                                    )
                                )  AS data
                        from (
                            SELECT 
                                DISTINCT sp.id, sp.kecamatan_kode,sp.kecamatan_nama,sp.geom,
                                (
                                    select 
                                        jsonb_agg(
                                            jsonb_build_object(
                                                'tahun', b.tahun,'area', 'kecamatan_nama','age', b.age,
                                                'sumber', b.sumber,'jml_a1', b.jml_a1,'jml_a2', b.jml_a2,
                                                'jml_a3', b.jml_a3,'jml',bl.jumlah_balita,'ps', 
                                                CAST((b.jml_a1 / 100) * bl.jumlah_balita AS INT)
                                            )
                                        )
                                    from api.data_wasting b 
                                    left join api.data_balita bl 
                                        ON bl.kecamatan_kode = b.kecamatan_kode AND b.tahun = bl.thn
                                    where b.kecamatan_kode = a.kecamatan_kode  " . $where .
                                ") as data,  
                                (
                                    select 
                                        jsonb_agg(jsonb_build_object('nama',puskesmas))
                                    from api.data_puskesmas pusk 
                                    WHERE kode_kecamatan = a.kecamatan_kode AND sumber  = '$request->sumber' 
                                ) as puskesmas
                            FROM api.data_wasting a
                            INNER JOIN api.kecamatan sp
                                ON sp.kecamatan_kode = a.kecamatan_kode
                            WHERE a.tingkat = 3" . $where .
                        ") as t(id,kecamatan_kode,kecamatan_nama,geom,data)";
                        //  echo $query;
                        //  exit;
                        //$puskesmas =  DB::select("SELECT puskesmas FROM api.data_puskesmas LEFT JOIN api.kecamatan ON api.data_puskesmas.kode_kecamatan = kecamatan.kecamatan_kode WHERE sumber = '$request->sumber' AND parent_kode_kemendagri = '$request->kabupaten'");
            }else{
                $query = "select  
                            json_build_object(
                                'type', 'FeatureCollection','features', 
                                json_agg(public.ST_AsGeoJSON(t.*)::json),'data',
                                json_build_object('age',
                                    json_build_object(
                                        'active','".$request->age."',
                                        'data',json_build_array('balita','baduta')
                                    ),'tahun',
                                    json_build_object(
                                        'active','".$tahun."',
                                        'data',json_build_array(".$string_tahun.")
                                    ),'sumber',
                                    json_build_object(
                                        'active','".$request->sumber."',
                                        'data',json_build_array(".$string_sumber.")
                                    )
                                )
                            )  AS data
                        from (
                            SELECT 
                                DISTINCT sp.id, sp.kecamatan_kode, sp.kecamatan_nama,sp.geom,
                                (
                                    select 
                                        jsonb_agg(
                                            jsonb_build_object(
                                                'tahun', b.tahun,'area', 'kecamatan_nama','age', b.age,
                                                'sumber', b.sumber,'jml_a1', b.jml_a1,'jml_a2', b.jml_a2,
                                                'jml_a3', b.jml_a3,'jml',bl.jumlah_balita,'ps',
                                                CAST((b.jml_a1 / 100) * bl.jumlah_balita AS INT)
                                            )
                                        )
                                    from api.data_wasting b 
                                    left join api.data_balita bl 
                                        ON bl.kecamatan_kode = b.kecamatan_kode AND b.tahun = bl.thn
                                    where b.kecamatan_kode = a.kecamatan_kode  " . $where .
                                ") as data,  
                                (
                                    select 
                                        jsonb_agg(jsonb_build_object('nama',puskesmas))
                                    from api.data_puskesmas pusk 
                                    WHERE kode_kecamatan = a.kecamatan_kode AND sumber  = '$request->sumber' 
                                ) as puskesmas
                            FROM api.data_wasting a
                            INNER JOIN api.kecamatan sp
                                ON sp.kecamatan_kode = a.kecamatan_kode
                            WHERE a.tingkat = 3" . $where .
                        ") as t(id,kecamatan_kode,kecamatan_nama,geom,data)";
                //$puskesmas =  DB::select("SELECT puskesmas FROM api.data_puskesmas LEFT JOIN api.kecamatan ON api.data_puskesmas.kode_kecamatan = kecamatan.kecamatan_kode WHERE sumber = '$request->sumber' AND b.kecamatan_kode = '$request->kecamatan'");
            }
        }
        // return $this->returnJsonSuccessCheck("Data fetched successfully", json_decode($results[0]->data));
        $results = DB::select($query); 
        return $this->returnJsonSuccessCheck("Data fetched successfully", json_decode($results[0]->data));
    }

    public function dataUnderweightPartial(Request $request)
    {
        // $request->area ?  $where = $where . " AND age = '" . $request->age . "'" : $where ;
        // $request->area ?  $where = $where . " AND age = '" . $request->age . "'" : $where ;
        $area=$request->area;
        $area=strtoupper($area);
        if(strtoupper($area) == 'PROVINSI'){
            $tingkat = 1;
        }else if(strtoupper($area) == 'KABUPATEN'){
            $tingkat = 2;
        }else{
            $tingkat = 3;
        }
        $where = " AND tingkat = $tingkat ";
        $request->age ?  $where = $where . " AND age = '" . $request->age . "'" : $where ;
        $request->sumber ?  $where = $where . " AND sumber = '" . $request->sumber . "'" : $where ;
        $request->provinsi ?  $where = $where . " AND a.provinsi_kode  = '" . $request->provinsi . "'" : $where ;
        $request->kabupaten ?  $where = $where . " AND a.kabupaten_kode  = '" . $request->kabupaten . "'" : $where ;
        // set_time_limit(300);
        $tahun = NULL;
        if($request->has('tahun') && !empty($request->tahun)){
            $tahun = $request->tahun;
        }
        $qtahun = "SELECT tahun FROM api.data_underweight a WHERE tingkat = $tingkat $where  GROUP BY tahun ORDER BY tahun ASC";
        //  echo $qtahun;
        $tahun_arr = DB::select($qtahun);
            $string_tahun = '';
        foreach($tahun_arr as $k => $v){
            $string_tahun .= "'$v->tahun',";
        }
        $string_tahun = rtrim($string_tahun, ", ");
        // echo $string_tahun;
        // exit;
        $where = $request->tahun ? " AND tahun = " . $request->tahun : "" ;
        $var = json_encode(array('BALITA','BADUTA'),JSON_UNESCAPED_SLASHES);
        $sumber = json_encode(array("riskesdas","ssgi","psg"),JSON_UNESCAPED_SLASHES);
        $qsumber = "SELECT sumber FROM api.data_underweight WHERE tingkat = $tingkat  ";
        // if($request->has('tahun') && !empty($request->tahun)){
        //     $tahun = $request->tahun;
        //     $qsumber .= " AND tahun = ".$tahun;
        // }
        if($request->has('age') && !empty($request->age)){
            $qsumber .=  " AND age = '".$request->age."'";
        }
        if($request->has('provinsi') && !empty($request->provinsi)){
            $qsumber .=  " AND provinsi_kode = '".$request->provinsi."'";
        }
        if($request->has('kabupaten') && !empty($request->kabupaten)){
            $qsumber .=  " AND kabupaten_kode = '".$request->kabupaten."'";
        }
        $qsumber .= " GROUP BY sumber  ORDER BY sumber ASC ";
        //  echo $qtahun;
        $sumber_arr = DB::select($qsumber);
            $string_sumber = '';
        foreach($sumber_arr as $k => $v){
            $string_sumber .= "'$v->sumber',";
        }
        $string_sumber = rtrim($string_sumber, ", ");

        if($area == 'PROVINSI'){
            if($request->has('provinsi') && !empty($request->provinsi)){
                $where = '';
                $where = ' AND tingkat =  2 '; 
                $request->age ?  $where = $where . " AND age = '" . $request->age . "'" : $where ;
                // $request->age ?  $where = $where . " AND tingkat = 2 " : $where ;
                $request->sumber ?  $where = $where . " AND sumber = '" . $request->sumber . "'" : $where ;
                $request->provinsi ?  $where = $where . " AND a.provinsi_kode  = '" . $request->provinsi . "'" : $where ;

                $query = "select  
                            json_build_object(
                                'type', 'FeatureCollection','features', 
                                json_agg(public.ST_AsGeoJSON(t.*)::json),'data', 
                                json_build_object('age',
                                    json_build_object(
                                        'active','".$request->age."',
                                        'data',json_build_array('balita','baduta')
                                    ),'tahun',
                                    json_build_object(
                                        'active','".$tahun."',
                                        'data',json_build_array(".$string_tahun.")
                                    ),'sumber',
                                    json_build_object(
                                        'active','".$request->sumber."',
                                        'data',json_build_array(".$string_sumber.")
                                    )
                                )
                            )  AS data
                        from (
                            SELECT 
                                DISTINCT sp.id,sp.kabupaten_kode,sp.kabupaten_nama,sp.geom,
                                TRIM(sp.latitude),TRIM(sp.longitude),
                                (
                                    select 
                                        jsonb_agg(
                                            jsonb_build_object(
                                                'tahun', b.tahun,'area', 'kabupaten','age', 
                                                b.age,'sumber', b.sumber,'jml_a1', b.jml_a1,
                                                'jml_a2', b.jml_a2,'jml_a3', b.jml_a3,'jml',
                                                bl.jumlah_balita,'ps', 
                                                CAST((b.jml_a1 / 100) * bl.jumlah_balita AS INT)
                                            )
                                        )
                                    from api.data_underweight b  
                                    left join api.data_balita bl 
                                        ON bl.kabupaten_kode = b.kabupaten_kode AND b.tahun = bl.thn
                                    where b.lokasi_id= a.lokasi_id" . $where .
                                ") as data
                            FROM api.data_underweight a
                            INNER JOIN api.kabupaten sp
                                ON sp.id = a.lokasi_id 
                            WHERE a.tingkat = 2" . $where .
                        ") as t(id,kabupaten_kode,kabupaten_nama,geom,latitude,longitude,data)";
            }else{
                $query = "select  
                            json_build_object(
                                'type', 'FeatureCollection','features', 
                                json_agg(public.ST_AsGeoJSON(t.*)::json),'data', 
                                json_build_object('age',
                                    json_build_object(
                                        'active','".$request->age."',
                                        'data',json_build_array('balita','baduta')
                                    ),'tahun',
                                    json_build_object(
                                        'active','".$tahun."',
                                        'data',json_build_array(".$string_tahun.")
                                    ),'sumber',
                                    json_build_object(
                                        'active','".$request->sumber."',
                                        'data',json_build_array(".$string_sumber.")
                                    )
                                )
                            )  AS data
                        from (
                            SELECT 
                                DISTINCT sp.id, sp.provinsi_kode,sp.provinsi_nama,sp.geom,
                                TRIM(sp.latitude),TRIM(sp.longitude),
                                (
                                    select 
                                        jsonb_agg(
                                            jsonb_build_object(
                                                'tahun', b.tahun,'area', '$request->area','age', b.age,
                                                'sumber', b.sumber,'jml_a1', b.jml_a1,'jml_a2', b.jml_a2,
                                                'jml_a3', b.jml_a3,'jml',bl.jumlah_balita,'ps', 
                                                CAST((b.jml_a1 / 100) * bl.jumlah_balita AS INT)
                                            )
                                        )
                                    from api.data_underweight b  
                                    left join api.data_balita bl 
                                        ON bl.provinsi_id = b.lokasi_id AND b.tahun = bl.thn
                                    where b.lokasi_id= a.lokasi_id" . $where .
                                ") as data
                            FROM api.data_underweight a
                            INNER JOIN api.provinsi sp
                                ON sp.id = a.lokasi_id 
                            WHERE a.tingkat = 1" . $where .
                        ") as t(id,provinsi_kode,provinsi_nama,geom,latitude,longitude,data)";
            }    
        }else if($area == 'KABUPATEN'){
            $query = "select  
                        json_build_object(
                            'type', 'FeatureCollection','features', 
                            json_agg(public.ST_AsGeoJSON(t.*)::json),'data', 
                            json_build_object('age',
                                json_build_object(
                                    'active','".$request->age."',
                                    'data',json_build_array('balita','baduta')
                                ),'tahun',
                                json_build_object(
                                    'active','".$tahun."',
                                    'data',json_build_array(".$string_tahun.")
                                ),'sumber',
                                json_build_object(
                                    'active','".$request->sumber."',
                                    'data',json_build_array(".$string_sumber.")
                                )
                            )
                        )  AS data
                    from (
                        SELECT 
                            DISTINCT sp.id, sp.kabupaten_kode,sp.kabupaten_nama,sp.geom,
                            TRIM(sp.latitude),TRIM(sp.longitude),
                            (
                                select 
                                    jsonb_agg(
                                        jsonb_build_object(
                                            'tahun', b.tahun,'area', '$request->area','age', b.age,
                                            'sumber', b.sumber,'jml_a1', b.jml_a1,'jml_a2', b.jml_a2,
                                            'jml_a3', b.jml_a3,'jml',bl.jumlah_balita,'ps',
                                            CAST((b.jml_a1 / 100) * bl.jumlah_balita AS INT)
                                        )
                                    )
                                from api.data_underweight b  
                                left join api.data_balita bl 
                                    ON bl.kabupaten_kode = b.kabupaten_kode AND b.tahun = bl.thn
                                where b.lokasi_id= a.lokasi_id" . $where .
                            ") as data
                        FROM api.data_underweight a
                        INNER JOIN api.kabupaten sp
                            ON sp.id = a.lokasi_id 
                        WHERE a.tingkat = 2" . $where .
                    ") as t(id,kabupaten_kode,kabupaten_nama,geom,latitude,longitude,data)";
        } else if($area == 'KECAMATAN'){
            if($request->has('kabupaten') && !empty($request->kabupaten)){
                $where = '';
                $where = ' AND tingkat =  3 '; 
                $request->age ?  $where = $where . " AND age = '" . $request->age . "'" : $where ;
                // $request->age ?  $where = $where . " AND tingkat = 2 " : $where ;
                $request->sumber ?  $where = $where . " AND sumber = '" . $request->sumber . "'" : $where ;
                $request->kabupaten ?  $where = $where . " AND a.kabupaten_kode  = '" . $request->kabupaten . "'" : $where ;
                // echo 'here';
                // exit;
                $query = "select  
                            json_build_object(
                                'type', 'FeatureCollection','features', 
                                json_agg(public.ST_AsGeoJSON(t.*)::json),'data',
                                json_build_object('age',
                                    json_build_object(
                                        'active','".$request->age."',
                                        'data',json_build_array('balita','baduta')
                                    ),'tahun',
                                    json_build_object(
                                        'active','".$tahun."',
                                        'data',json_build_array(".$string_tahun.")
                                    ),'sumber',
                                    json_build_object(
                                        'active','".$request->sumber."',
                                        'data',json_build_array(".$string_sumber.")
                                    )
                                )
                            )  AS data
                        from (
                            SELECT 
                                DISTINCT sp.id, sp.kecamatan_kode,sp.kecamatan_nama,sp.geom,
                                (
                                    select 
                                        jsonb_agg(
                                            jsonb_build_object(
                                                'tahun', b.tahun,'area', 'kecamatan_nama','age', b.age,
                                                'sumber', b.sumber,'jml_a1', b.jml_a1,'jml_a2', b.jml_a2,
                                                'jml_a3', b.jml_a3,'jml',bl.jumlah_balita,'ps', 
                                                CAST((b.jml_a1 / 100) * bl.jumlah_balita AS INT)
                                            )
                                        )
                                    from api.data_underweight b 
                                    left join api.data_balita bl 
                                        ON bl.kecamatan_kode = b.kecamatan_kode AND b.tahun = bl.thn
                                    where b.kecamatan_kode = a.kecamatan_kode  " . $where .
                                ") as data,  
                                (
                                    select 
                                        jsonb_agg(jsonb_build_object('nama',puskesmas))
                                    from api.data_puskesmas pusk 
                                    WHERE kode_kecamatan = a.kecamatan_kode AND sumber  = '$request->sumber' 
                                ) as puskesmas
                            FROM api.data_underweight a
                            INNER JOIN api.kecamatan sp
                                ON  sp.kecamatan_kode = a.kecamatan_kode
                            WHERE a.tingkat = 3" . $where .
                        ") as t(id,kecamatan_kode,kecamatan_nama,geom,data)";
             //  echo $query;
             //  exit;
             //$puskesmas =  DB::select("SELECT puskesmas FROM api.data_puskesmas LEFT JOIN api.kecamatan ON api.data_puskesmas.kode_kecamatan = kecamatan.kecamatan_kode WHERE sumber = '$request->sumber' AND parent_kode_kemendagri = '$request->kabupaten'");
            }else{
                $query = "select  
                            json_build_object(
                                'type', 'FeatureCollection','features', 
                                json_agg(public.ST_AsGeoJSON(t.*)::json),'data', 
                                json_build_object('age',
                                    json_build_object(
                                        'active','".$request->age."',
                                        'data',json_build_array('balita','baduta')
                                    ),'tahun',
                                    json_build_object(
                                        'active','".$tahun."',
                                        'data',json_build_array(".$string_tahun.")
                                    ),'sumber',
                                    json_build_object(
                                        'active','".$request->sumber."',
                                        'data',json_build_array(".$string_sumber.")
                                    )
                                )
                            )  AS data
                        from (
                            SELECT 
                                DISTINCT sp.id,sp.kecamatan_kode, sp.kecamatan_nama, sp.geom,
                                (
                                    select 
                                        jsonb_agg(
                                            jsonb_build_object(
                                                'tahun', b.tahun,'area', 'kecamatan_nama','age', b.age,
                                                'sumber', b.sumber,'jml_a1', b.jml_a1,'jml_a2', b.jml_a2,
                                                'jml_a3', b.jml_a3,'jml',bl.jumlah_balita,'ps',
                                                CAST((b.jml_a1 / 100) * bl.jumlah_balita AS INT)
                                            )
                                        )
                                    from api.data_underweight b 
                                    left join api.data_balita bl 
                                        ON bl.kecamatan_kode = b.kecamatan_kode AND b.tahun = bl.thn
                                    where b.kecamatan_kode = a.kecamatan_kode  " . $where .
                                ") as data,  
                                (
                                    select 
                                        jsonb_agg(jsonb_build_object('nama',puskesmas))
                                    from api.data_puskesmas pusk WHERE kode_kecamatan = a.kecamatan_kode AND sumber  = '$request->sumber' 
                                ) as puskesmas
                            FROM api.data_underweight a
                            INNER JOIN api.kecamatan sp
                                ON  sp.kecamatan_kode = a.kecamatan_kode
                            WHERE a.tingkat = 3" . $where .
                        ") as t(id,kecamatan_kode,kecamatan_nama,geom,data)";
                    //$puskesmas =  DB::select("SELECT puskesmas FROM api.data_puskesmas LEFT JOIN api.kecamatan ON api.data_puskesmas.kode_kecamatan = kecamatan.kecamatan_kode WHERE sumber = '$request->sumber' AND b.kecamatan_kode = '$request->kecamatan'");
            }
        }
        $results = DB::select($query);
        return $this->returnJsonSuccessCheck("Data fetched successfully", json_decode($results[0]->data));
    }
}

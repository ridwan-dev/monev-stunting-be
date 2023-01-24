<?php

namespace App\Http\Controllers\Api\V1\Geodata;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\Pub\Lokasi;
use App\Models\Pub\LokasiPrioritas;
use App\Models\Pub\Provinsi;
use App\Models\Pub\Kabupaten;
use Illuminate\Support\Facades\DB;

class GeodataController extends BaseController
{
    public function __construct()
    {
        
    }

    public function kabupatenpublik(Request $request){


        $token = 'a7076abeac714665c2ed38a06c1492e1';
        if($request->token == $token){


        $where = $request->provinsi ? "WHERE provinsi_kode::int IN (" . implode(',', $request->provinsi) . ")" : "" ;
//         $results = DB::select("select json_build_object(
//             'type', 'FeatureCollection',
//             'features', json_agg(public.ST_AsGeoJSON(t.*)::json)
//         ) AS data
//     from  
//     (SELECT
//         sp.id, 
//         sp.kabupaten_kode,
//         sp.kabupaten_nama,
//         sp.provinsi_id,
//         sp.provinsi_kode,
//         sp.provinsi_nama,
//         sp.provinsi_nama_alias,
//         sp.kode_kemendagri,
//         sp.tahun_prioritas as prioritas,
//     FROM
//         api.kabupaten sp ". $where . " 
//     ) as t(id,kabupaten_kode,kabupaten_nama,provinsi_id, provinsi_kode,provinsi_nama,provinsi_nama_alias,kode_kemendagri, prioritas, geom)"); 

// return $this->returnJsonSuccessCheck("Data fetched successfully", json_decode($results[0]->data));
$results = DB::select("
SELECT
    sp.id, 
    sp.kabupaten_kode,
    sp.kabupaten_nama,
    sp.provinsi_id,
    sp.provinsi_kode,
    sp.provinsi_nama,
    sp.provinsi_nama_alias,
    sp.kode_kemendagri,
    sp.latitude,
    sp.longitude
FROM
    api.kabupaten sp  ".$where." ORDER BY sp.kabupaten_kode ASC"); 

return $this->returnJsonSuccessCheck("Data fetched successfully", $results);

}else{
            return $this->returnJsonError('Token Doesnt Match',401);
        }
    }

    public function provinsipublik(Request $request){


        $token = 'a7076abeac714665c2ed38a06c1492e1';
        if($request->token == $token){


    //         $results = DB::select("select json_build_object(
    //             'type', 'FeatureCollection',
    //             'features', json_agg(public.ST_AsGeoJSON(t.*)::json)
    //         ) AS data
    //     from  
    //     (SELECT
    //         sp.id, 
    //         sp.provinsi_kode,
    //         sp.provinsi_nama,
    //         sp.provinsi_nama_alias,
    //         sp.kode_kemendagri,
    //         sp.geom,
    //         sp.latitude,
    //         sp.longitude
    //     FROM
    //         api.provinsi sp
    //     ) as t(id,provinsi_kode,provinsi_nama,provinsi_nama_alias,kode_kemendagri,geom,latitude,longitude)"); 
   
    // return $this->returnJsonSuccessCheck("Data fetched successfully", json_decode($results[0]->data));


    $results = DB::select("
SELECT
    sp.id, 
    sp.provinsi_kode,
    sp.provinsi_nama,
    sp.provinsi_nama_alias,
    sp.kode_kemendagri,
    sp.latitude,
    sp.longitude
FROM
    api.provinsi sp"); 

return $this->returnJsonSuccessCheck("Data fetched successfully", $results);



        }else{
            return $this->returnJsonError('Token Doesnt Match',401);
        }
    }


    public function kecamatanpublik(Request $request){


        $token = 'a7076abeac714665c2ed38a06c1492e1';
        if($request->token == $token){
            $where = $request->kabupaten ? "WHERE parent_kode_kemendagri::int IN (" . implode(',', $request->kabupaten) . ")" : "" ;


            $results = DB::select("select json_build_object(
                'type', 'FeatureCollection',
                'features', json_agg(public.ST_AsGeoJSON(t.*)::json)
            ) AS data
        from  
        (SELECT
            sp.id, 
            sp.kecamatan_kode,
            sp.kecamatan_nama,
            sp.parent_kode_kemendagri as kabupaten_kode,
            sp.kabupaten_nama,
            sp.geom
        FROM
            api.kecamatan sp ". $where . "

        ) as t(id,kecamatan_kode,kecamatan_nama,kabupaten_kode,kabupaten_nama,geom)"); 
   
    return $this->returnJsonSuccessCheck("Data fetched successfully", json_decode($results[0]->data));



        }else{
            return $this->returnJsonError('Token Doesnt Match',401);
        }
    }


    public function provinsi(Request $request)
    {
        $results = DB::select("select json_build_object(
                    'type', 'FeatureCollection',
                    'features', json_agg(public.ST_AsGeoJSON(t.*)::json)
                ) AS data
            from  
            (SELECT
                sp.id, 
                sp.provinsi_kode,
                sp.provinsi_nama,
                sp.provinsi_nama_alias,
                sp.kode_kemendagri,
                sp.geom
            FROM
                api.provinsi sp
            ) as t(id,provinsi_kode,provinsi_nama,provinsi_nama_alias,kode_kemendagri,geom)"); 
       
        return $this->returnJsonSuccessCheck("Data fetched successfully", json_decode($results[0]->data));
    }

    public function kabupaten(Request $request)
    {
        $where = $request->provinsi ? "WHERE provinsi_id IN (" . implode(',', $request->provinsi) . ")" : "" ;
        $results = DB::select("select json_build_object(
                    'type', 'FeatureCollection',
                    'features', json_agg(public.ST_AsGeoJSON(t.*)::json)
                ) AS data
            from  
            (SELECT
                sp.id, 
                sp.kabupaten_kode,
                sp.kabupaten_nama,
                sp.provinsi_id,
                sp.provinsi_kode,
                sp.provinsi_nama,
                sp.provinsi_nama_alias,
                sp.kode_kemendagri,
                sp.tahun_prioritas as prioritas,
                sp.geom
            FROM
                api.kabupaten sp ". $where . "
            
            ) as t(id,kabupaten_kode,kabupaten_nama,provinsi_id, provinsi_kode,provinsi_nama,provinsi_nama_alias,kode_kemendagri, prioritas, geom)"); 
       
        return $this->returnJsonSuccessCheck("Data fetched successfully", json_decode($results[0]->data));
    }

    public function aggKabupaten(Request $request)
    {
        $where = $request->provinsi ? " WHERE provinsi_id IN (" . implode(',', $request->provinsi) . ")" : "" ;
        $results = DB::select("select json_build_object(
                    'type', 'FeatureCollection',
                    'features', json_agg(public.ST_AsGeoJSON(t.*)::json)
                ) AS data
            from  
            (SELECT DISTINCT
                sp.id, 
                sp.kabupaten_kode,
                sp.kabupaten_nama,
                sp.provinsi_id,
                sp.provinsi_kode,
                sp.provinsi_nama,
                sp.provinsi_nama_alias,
                sp.kode_kemendagri,
                sp.tahun_prioritas as prioritas,
                (SELECT 
                    jsonb_agg(jsonb_build_object(
                        'tahun', b.tahun,
                        'intervensi_id', b.intervensi_id,
                        'kementerian_id', b.kementerian_id
                    ))
                    from api.mv_renja_monev_agg b  
                    where b.kabupaten_id = sp.id) AS data,
                sp.geom
            FROM
                api.kabupaten sp INNER JOIN
                            api.mv_renja_monev_agg a
                    ON 
                            sp.id = a.kabupaten_id " . $where . "
            
            ) as t(id,kabupaten_kode,kabupaten_nama,provinsi_id, provinsi_kode,provinsi_nama,provinsi_nama_alias,kode_kemendagri, prioritas, data, geom)"); 
       
        return $this->returnJsonSuccessCheck("Data fetched successfully", json_decode($results[0]->data));
    }

    public function dataMonitoring(Request $request)
    {
        $where = $request->tahun ? " AND tahun = " . $request->tahun : "" ;
        $request->intervensi ?  $where = $where . " AND intervensi_id = '" . $request->intervensi . "'" : $where ;
        $request->kementerian ?  $where = $where . " AND kementerian_id = '" . $request->kementerian . "'" : $where ;
        $request->provinsi ?  $where = $where . " AND a.provinsi_id = '" . $request->provinsi . "'" : $where ;
        $request->kabupaten ?  $where = $where . " AND a.kabupaten_id = '" . $request->kabupaten . "'" : $where ;
        // set_time_limit(300);
        $results = DB::select("SELECT 
                    json_build_object(
                            'type', 'FeatureCollection',
                            'features', json_agg(public.ST_AsGeoJSON(t.*)::json)
                    ) AS data
                    FROM  
                    (SELECT DISTINCT
                            sp.id, 
                            sp.kabupaten_nama as nama,
                            sp.provinsi_kode,
                            sp.provinsi_nama,
                            sp.tahun_prioritas as prioritas,
                            sp.geom,
                            (select 
                            jsonb_agg(jsonb_build_object(
                                    'tahun', b.tahun,
                                    'intervensi_id', b.intervensi_id,
                                    'kementerian_id', b.kementerian_id,
                                    'alokasi', b.alokasi_1,
                                    'realisasi', b.realisasi,
                                    'intervensi', b.intervensi_nama,
                                    'kementerian', b.kementerian_nama
                                    ))
                            from api.mv_renja_monev_agg b
                            where b.kabupaten_id= a.kabupaten_id" . $where .
                            ") as data
                    FROM
                            api.kabupaten sp
                            INNER JOIN
                            api.mv_renja_monev_agg a
                    ON 
                            sp.id = a.kabupaten_id 
                    WHERE intervensi_id != 1" . $where .
                    ") as t(id,nama,provinsi_id,provinsi_nama,prioritas,geom,data)"); 
       

        return $this->returnJsonSuccessCheck("Data fetched successfully", json_decode($results[0]->data));
    }

    public function dataStunting(Request $request)
    {
        // $where = $request->tahun ? ' AND tahun IN (' . implode(',', $request->tahun) . ')' : '' ;
        $where = $request->tahun ? " AND tahun = " . $request->tahun : "" ;
        $request->sumber ?  $where = $where . " AND sumber = '" . $request->sumber . "'" : $where ;
        $request->age ?  $where = $where . " AND age = '" . $request->age . "'" : $where ;
        $request->provinsi ?  $where = $where . " AND a.lokasi_id = '" . $request->provinsi . "'" : $where ;
        $query = "select json_build_object(
            'type', 'FeatureCollection',
            'features', json_agg(public.ST_AsGeoJSON(t.*)::json)
        ) AS data
    from  
    (SELECT DISTINCT
        sp.id, 
        sp.provinsi_kode,
        sp.provinsi_nama,
        sp.provinsi_nama_alias,
        sp.geom,
        (select 
        jsonb_agg(jsonb_build_object(
            'tahun', b.tahun,
            'age', b.age,
            'sumber', b.sumber,
            'pb_u_sangat_pendek', b.pb_u_sangat_pendek,
            'pb_u_pendek', b.pb_u_pendek,
            'pb_u_stunting', b.pb_u_stunting,
            'pb_u_normal', b.pb_u_normal,
            'jumlah_balita',bl.jumlah_balita,
            'ps_balita', CAST((b.pb_u_stunting / 100) * bl.jumlah_balita AS INT),
            'ps_balita_s', (b.pb_u_stunting / 100)
            ))
        from api.data_stunting b left join api.data_balita bl 
        ON bl.provinsi_id = b.lokasi_id AND b.tahun = bl.thn
        where b.lokasi_id= a.lokasi_id" . $where .
        ") as data
    FROM
        api.provinsi sp
        INNER JOIN
        api.data_stunting a
    ON 
        sp.id::int = a.lokasi_id 
    WHERE a.kabupaten_kode IS NULL" . $where .
    ") as t(id,provinsi_kode,provinsi_nama,provinsi_nama_alias,geom,data)";

  
        $results = DB::select($query); 
       

        return $this->returnJsonSuccess("Data fetched successfully", json_decode($results[0]->data));
    }



    public function dataStuntingKab(Request $request)
    {
        // $where = $request->tahun ? ' AND tahun IN (' . implode(',', $request->tahun) . ')' : '' ;
        $where = $request->tahun ? " AND tahun = " . $request->tahun : "" ;
        $request->sumber ?  $where = $where . " AND sumber = '" . $request->sumber . "'" : $where ;
        $request->age ?  $where = $where . " AND age = '" . $request->age . "'" : $where ;
        $request->provinsi ?  $where = $where . " AND a.provinsi_kode = '" . $request->provinsi . "'" : $where ;
        $request->kabupaten ?  $where = $where ." AND a.kabupaten_kode = '" . $request->kabupaten . "'" : $where ;
       $query = "select json_build_object(
        'type', 'FeatureCollection',
        'features', json_agg(public.ST_AsGeoJSON(t.*)::json)
                ) AS data
            from  
            (SELECT DISTINCT
                sp.id, 
                sp.kabupaten_kode,
                sp.kabupaten_nama,
                sp.geom,
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
                api.kabupaten sp 
                INNER JOIN
                api.data_stunting a
            ON 
                sp.id::int = a.lokasi_id 
            WHERE a.tingkat = 2 " . $where .
            ") as t(id,kabupaten_kode,kabupaten_nama,geom,data)";


    
        $results = DB::select($query); 
       

        return $this->returnJsonSuccess("Data fetched successfully", json_decode($results[0]->data));
    }

    public function dataWasting(Request $request)
    {
        // $where = $request->tahun ? ' AND tahun IN (' . implode(',', $request->tahun) . ')' : '' ;
        $where = $request->tahun ? " AND tahun = " . $request->tahun : "" ;
        $request->sumber ?  $where = $where . " AND sumber = '" . $request->sumber . "'" : $where ;
        $request->age ?  $where = $where . " AND age = '" . $request->age . "'" : $where ;
        $request->provinsi ?  $where = $where . " AND a.lokasi_id = '" . $request->provinsi . "'" : $where ;

        $results = DB::select("select json_build_object(
                    'type', 'FeatureCollection',
                    'features', json_agg(public.ST_AsGeoJSON(t.*)::json)
                ) AS data
            from  
            (SELECT DISTINCT
                sp.id, 
                sp.provinsi_kode,
                sp.provinsi_nama,
                sp.provinsi_nama_alias,
                sp.geom,
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
                api.provinsi sp
                INNER JOIN
                api.data_wasting a
            ON 
                sp.id::int = a.lokasi_id 
            WHERE a.kabupaten_kode IS NULL" . $where .
            ") as t(id,provinsi_kode,provinsi_nama,provinsi_nama_alias,geom,data)"); 
       

        return $this->returnJsonSuccess("Data fetched successfully", json_decode($results[0]->data));
    }


    public function dataWastingKab(Request $request)
    {
        // $where = $request->tahun ? ' AND tahun IN (' . implode(',', $request->tahun) . ')' : '' ;
        $where = $request->tahun ? " AND tahun = " . $request->tahun : "" ;
        $request->sumber ?  $where = $where . " AND sumber = '" . $request->sumber . "'" : $where ;
        $request->age ?  $where = $where . " AND age = '" . $request->age . "'" : $where ;
        $request->provinsi ?  $where = $where . " AND a.provinsi_kode = '" . $request->provinsi . "'" : $where ;
        $request->kabupaten ?  $where = $where ." AND a.kabupaten_kode = '" . $request->kabupaten . "'" : $where ;
        $query = "select json_build_object(
            'type', 'FeatureCollection',
            'features', json_agg(public.ST_AsGeoJSON(t.*)::json)
        ) AS data
    from  
    (SELECT DISTINCT
        sp.id, 
        sp.kabupaten_kode,
        sp.kabupaten_nama,
        sp.geom,
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
        api.kabupaten sp
        INNER JOIN
        api.data_wasting a
    ON 
        sp.id::int = a.lokasi_id 
    WHERE a.tingkat = 2 " . $where .
    ") as t(id,kabupaten_kode,kabupaten_nama,geom,data)";

        $results = DB::select($query); 
       

        return $this->returnJsonSuccess("Data fetched successfully", json_decode($results[0]->data));
    }

    public function dataUnderweight(Request $request)
    {
        // $where = $request->tahun ? ' AND tahun IN (' . implode(',', $request->tahun) . ')' : '' ;
        $where = $request->tahun ? " AND tahun = " . $request->tahun : "" ;
        $request->sumber ?  $where = $where . " AND sumber = '" . $request->sumber . "'" : $where ;
        $request->age ?  $where = $where . " AND age = '" . $request->age . "'" : $where ;
        $request->provinsi ?  $where = $where . " AND a.lokasi_id = '" . $request->provinsi . "'" : $where ;

        $results = DB::select("select json_build_object(
                    'type', 'FeatureCollection',
                    'features', json_agg(public.ST_AsGeoJSON(t.*)::json)
                ) AS data
            from  
            (SELECT DISTINCT
                sp.id, 
                sp.provinsi_kode,
                sp.provinsi_nama,
                sp.provinsi_nama_alias,
                sp.geom,
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
                api.provinsi sp
                INNER JOIN
                api.data_underweight a
            ON 
                sp.id::int = a.lokasi_id 
            WHERE a.kabupaten_kode IS NULL" . $where .
            ") as t(id,provinsi_kode,provinsi_nama,provinsi_nama_alias,geom,data)"); 
       

        return $this->returnJsonSuccess("Data fetched successfully", json_decode($results[0]->data));
    }


    public function dataUnderweightKab(Request $request)
    {
        // $where = $request->tahun ? ' AND tahun IN (' . implode(',', $request->tahun) . ')' : '' ;
        $where = $request->tahun ? " AND tahun = " . $request->tahun : "" ;
        $request->sumber ?  $where = $where . " AND sumber = '" . $request->sumber . "'" : $where ;
        $request->age ?  $where = $where . " AND age = '" . $request->age . "'" : $where ;
        $request->provinsi ?  $where = $where . " AND a.lokasi_id = '" . $request->provinsi . "'" : $where ;
        $request->provinsi ?  $where = $where . " AND a.provinsi_kode = '" . $request->provinsi . "'" : $where ;
        $request->kabupaten ?  $where = $where ." AND a.kabupaten_kode = '" . $request->kabupaten . "'" : $where ;
       
        $results = DB::select("select json_build_object(
                    'type', 'FeatureCollection',
                    'features', json_agg(public.ST_AsGeoJSON(t.*)::json)
                ) AS data
            from  
            (SELECT DISTINCT
                sp.id, 
                sp.kabupaten_kode,
                sp.kabupaten_nama,
                sp.geom,
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
                api.kabupaten sp
                INNER JOIN
                api.data_underweight a
            ON 
                sp.id::int = a.lokasi_id 
            WHERE a.tingkat = 2 " . $where .
            ") as t(id,kabupaten_kode,kabupaten_nama,geom,data)"); 
       

        return $this->returnJsonSuccess("Data fetched successfully", json_decode($results[0]->data));
    }
    
    
    
    // Not yet implemented
    public function kabupatenX(Request $request, $provId = null)
    {
        $locations = Kabupaten::select('id', 'kabupaten_kode','kabupaten_nama','provinsi_id','provinsi_kode','provinsi_nama','parent_kode_kemendagri','kode_kemendagri')
                            ->selectRaw('array_to_json(tahun_prioritas) as tahun_prioritas')
                            ->where(function($q) use ($provId){
                                if($provId != null)
                                    $q->where('provinsi_id', $provId);
                            })
                            ->get();
        return $this->returnJsonSuccess("Kota/ Kabupaten fetched successfully", $locations);
    }

    
    public function lokasiPrioritas(Request $request, $tahun = 'all', $tematikId = 'all', $level = 'all'){
        $locations = LokasiPrioritas::with(['lokasi' => function($query){
            $query->select('id', 'lokasi_renja_id','parent_id_kemendagri', 'level', 'kode_kemendagri', 'nama', 'provinsi_id', 'provinsi_nama', 'kabupaten_id', 'kabupaten_nama', 'kecamatan_id', 'kecamatan_nama');
        }]);

        if($tahun != 'all'){
            $locations = $locations->where('tahun', $tahun);
        }

        if($tematikId != "all"){
            $locations = $locations->where('tematik_id', $tematikId);
        }

        if($level != 'all'){
            $locations = $locations->whereHas('lokasi', function ($query) use($level) {
                $query->where('level', $level);
            });
        }

        $locations = $locations->get();

        return $this->returnJsonSuccess("Lokasi Prioritas fetched successfully", $locations);
    }


    public function tesprioritas(){

        $run = \DB::table('api.kabupaten')->get();
        

      //  $dua = \DB::table('api.prioritas2023')->get();
        // $nama_kab = [];
        // foreach($dua as $k => $v){
        //     $nama_kab[] = strtolower($v->kab);
        // }
      
        foreach($run as $k => $v){


          //  if(in_array(strtolower($v->kabupaten_nama), $nama_kab)){
                $tahun = $v->tahun_prioritas;
                $rep = str_replace('{','',$tahun);
                $rep = str_replace('}','',$rep);
               $thn = explode(",",$rep);
                    $t = [];
               foreach($thn as $kal => $val){
                $val = (int) $val;
                    array_push($t,$val);
               }
               array_push($t,2023);

               //print_r($t);
              // echo $v->kabupaten_nama." <br />";
               $t = json_encode($t);
               $t = str_replace('[','{',$t);
                $t = str_replace(']','}',$t);
               $dt = array(
                'tahun_prioritas' => $t
               );

                $update = \DB::table('api.kabupaten')->where('id',$v->id)->update($dt);
            //}
         
                

         }



    }
}

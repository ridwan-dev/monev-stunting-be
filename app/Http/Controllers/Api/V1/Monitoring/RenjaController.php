<?php

namespace App\Http\Controllers\Api\V1\Monitoring;

use App\Http\Controllers\Api\BaseController;
use App\Models\Kinerja\RenjaTaggingRo;
use Illuminate\Http\Request;
use App\Models\Pub\Lokasi;
use App\Models\Pub\LokasiPrioritas;
use App\Models\Pub\Provinsi;
use App\Models\Pub\Kabupaten;
use App\Models\Kinerja\MvRenja;
use App\Models\Kinerja\KomponenRenja;
use Illuminate\Support\Facades\DB;
use App\Models\Kinerja\MvRenjaLokus;
use App\Models\Kinerja\RenjaTagging;
use App\Models\Kinerja\VRenja;
use App\Models\Kinerja\RenjaRoKeyword;
use App\Models\Kinerja\MvRenjaTematikKeyword;
use App\Models\Kinerja\MvRenjaTematikKeywordkomponen;
use App\Models\Kinerja\MvRenjaTematikKeywordSepakati;
use App\Models\Kinerja\RenjaUpdateDate;
use Illuminate\Support\Facades\Validator;

class RenjaController extends BaseController
{
    public function __construct()
    {
        // $this->middleware(
        //     [
        //         'auth:api'
        //     ]);
    }

    public function optionkementerian(Request $request){
        $kementerian = MvRenjaTematikKeywordSepakati::select('kementerian_kode', 'kementerian_nama')
        ->where('tahun', $request->tahun)
        ->groupBy('kementerian_kode', 'kementerian_nama')
        ->get();
        return $this->returnJsonSuccess("Data fetched successfully", $kementerian);
    }

    //region miftah done
    public function renjatagging(Request $request){

        $ditandai_list = $request->post('ditandai', []);
        $disepakati_list = $request->post('disepakati', []);
        //delete semua berdasarkan tahun & insert record baru
        $tahun = $request->post('tahun', date('Y'));
        RenjaTagging::where('tahun', $tahun)->delete();

        $records = [];
        foreach ($ditandai_list as $id_ro) {
            $records[$id_ro] = [
                'id_ro' => $id_ro,
                'tahun' => $tahun,
                'ditandai' => 1,
                'disepakati' => 0,
            ];
        }

        foreach ($disepakati_list as $id_ro) {
            if (isset($records[$id_ro])){ $records[$id_ro]['disepakati'] = 1; }
            else{
                $records[$id_ro] = [
                    'id_ro' => $id_ro,
                    'tahun' => $tahun,
                    'ditandai' => 0,
                    'disepakati' => 1,
                ];
            }
        }

        $result = RenjaTagging::insert($records);
        DB::statement("REFRESH MATERIALIZED VIEW renja.mv_krisna_renja_tematik_tagging");
        DB::statement("REFRESH MATERIALIZED VIEW renja.mv_krisna_renja_tematik_sepakati");
            
        return ($result)?
            $this->returnJsonSuccess("Success Insert", []):
            $this->returnJsonError("Failed Insert", []);

    }

    public function rointervensi(Request $request)
    {
        $record = [
            'id_ro' => $request->id_ro,
            'kode_intervensi' => $request->kode_intervensi,
            'tahun' => $request->tahun
        ];        
        $rules = [
            'id_ro' => 'bail|required',
            'kode_intervensi' => 'bail|required',
            'tahun' => 'required'
        ];
        $validator = Validator::make($record, $rules);

        if($validator->passes()){
            //cek sudah ada record ybs blm
            $is_exist = RenjaTaggingRo::select('id_ro')
                ->where(['id_ro'=>$record['id_ro'], 'tahun'=> $record['tahun']])->get()->count();
            if( $is_exist ){
                $result =  RenjaTaggingRo::where(['id_ro'=>$record['id_ro'], 'tahun'=> $record['tahun']])
                    ->update($record);
            }else{
                $result = RenjaTaggingRo::insert($record);
            }
            return ($result)?
                $this->returnJsonSuccess("Success Insert", []):
                $this->returnJsonError("Failed Insert", []);
        }
        return $this->returnJsonError('Failed Insert', 400, $validator->errors());
    }

    public function rokeyword(Request $request)
    {
        $record = [
            'keyword' => $request->keyword,
            'tahun' => $request->tahun
        ];        
        $rules = [
            'keyword' => 'required',
            'tahun' => 'required'
        ];
        $validator = Validator::make($record, $rules);
        if($validator->passes()){            
            if(!empty($request->id)){
                $is_exist = RenjaRoKeyword::find($request->id);
                if($is_exist && ($request->delete == 1)){
                    $is_exist->delete();
                    $result = true;
                    $msg = "Delete";
                }else{
                    $result =  RenjaRoKeyword::where(['id'=>$request->id, 'tahun'=> $record['tahun']])
                    ->update($record);
                    $msg = "Update";
                }
            }else{
                $result = RenjaRoKeyword::insert($record);
                $msg = "Insert";
            }            
            return ($result)?
                $this->returnJsonSuccess("Success ".$msg, []):
                $this->returnJsonError("Failed ".$msg, []);
        }
        return $this->returnJsonError('Failed', 400, $validator->errors());
    }

    public function listRoKeyword(Request $request)
    {
        $record = [
            'tahun' => $request->tahun
        ];        
        $rules = [
            'tahun' => 'required'
        ];

        $validator = Validator::make($record, $rules);
        if($validator->passes()){
            $dta = RenjaRoKeyword::where("tahun",$request->tahun)->get();
            if($dta->count()>0){
                $result =  $dta;
                return $this->returnJsonSuccess("Success Data fetched", [$result,"updated_at"=>RenjaUpdateDate::where("name","keyword")->first()->updated_at]);
            }
        }
        return $this->returnJsonError('Failed Data fetched', 400, $validator->errors());
    }

    public function rokeywordReload(){

        /*
            From keywords renja.krisnarenja_ro_keyword      kw
            1. Table renja.krisnarenja_t_lokasi_suboutput   a1
            2. Table renja.krisnarenja_ref_wilayah          c1
            3. Table renja.krisnarenja_t_soutput            b
            4. Table renja.krisnarenja_t_output             c 
            5. Table renja.krisnarenja_t_giat               d
            6. Table renja.krisnarenja_t_progout            e
            7. Table renja.krisnarenja_t_program            f
            8. Table api.ref_kementerian                    kl
            9. Table renja.krisnarenja_tagging_ro           aa
            10.Table api.ref_intervensi                     rif
        */ 

        $keywords = "";
        if(RenjaRoKeyword::count()>0){
            $keywords_arr = [];
            foreach (RenjaRoKeyword::all() as $row) {
                $keywords_arr[] = "(b.nmsoutput ~~* '%".$row->keyword."%'::text and b.tahun = '".$row->tahun."' )";
            }
            $keywords = " or ".implode(" or ",$keywords_arr);
        }

        $query1 = $this->queryRenja("mv_krisna_renja_tematik_keyword",$keywords) ; 
        $query2 = $this->queryRenja("renja_komponen",$keywords) ; 
        
        /* $adatabel = DB::select("
            SELECT EXISTS (
                select * from pg_matviews where matviewname = 'mv_krisna_renja_tematik_keyword'
            )
        ");
        $adatabel = $adatabel[0]->exists; */

        if(!$this->checkTable('mv_krisna_renja_tematik_keyword')){
            DB::statement("
                CREATE MATERIALIZED VIEW renja.mv_krisna_renja_tematik_keyword AS ".$query1."        
            ");
            DB::statement("
                CREATE MATERIALIZED VIEW renja.mv_krisna_renja_tematik_keyword_komponen AS ".$query2."        
            ");
        }else{ 

            if($this->checkTable('mv_krisna_renja_tematik_sepakati')){
                DB::statement("
                    DROP MATERIALIZED VIEW renja.mv_krisna_renja_tematik_sepakati;
                ");
            }
            if($this->checkTable('mv_krisna_renja_tematik_tagging')){
                DB::statement("
                    DROP MATERIALIZED VIEW renja.mv_krisna_renja_tematik_tagging;
                ");
            }
            if($this->checkTable('mv_krisna_renja_tematik_keyword_komponen')){
                DB::statement("
                    DROP MATERIALIZED VIEW renja.mv_krisna_renja_tematik_keyword_komponen;
                ");
            }
            if($this->checkTable('mv_krisna_renja_tematik_keyword')){
                DB::statement("
                    DROP MATERIALIZED VIEW renja.mv_krisna_renja_tematik_keyword;
                ");
            }
            DB::statement("
                CREATE MATERIALIZED VIEW renja.mv_krisna_renja_tematik_keyword AS ".$query1.";        
            ");

            
            DB::statement("
                CREATE MATERIALIZED VIEW renja.mv_krisna_renja_tematik_keyword_komponen AS ".$query2."        
            ");

            DB::statement("
                CREATE MATERIALIZED VIEW renja.mv_krisna_renja_tematik_tagging AS
                    SELECT * FROM renja.mv_krisna_renja_tematik_keyword_komponen aa
                    LEFT JOIN ( 
                        SELECT 
                            id_ro,ditandai,cast(tahun AS varchar) as thn
                        FROM renja.krisnarenja_tagging 
                        WHERE ditandai = 1 
                    ) bb
                        ON  ((aa.idro = bb.id_ro) AND (aa.tahun::varchar = bb.thn ))
                    WHERE bb.ditandai is not null                
            ");
            DB::statement("
                CREATE MATERIALIZED VIEW renja.mv_krisna_renja_tematik_sepakati AS
                    SELECT * FROM renja.mv_krisna_renja_tematik_keyword_komponen aa
                    LEFT JOIN ( 
                        SELECT 
                            id_ro,ditandai,disepakati,cast(tahun AS varchar) as thn
                        FROM renja.krisnarenja_tagging 
                        WHERE ditandai = 1 and disepakati = 1
                    ) bb
                        ON  ((aa.idro = bb.id_ro) AND (aa.tahun::varchar = bb.thn ))
                    WHERE 
                        bb.ditandai is not null 
                        AND disepakati is not null               
            ");
        };
        RenjaUpdateDate::where("name","keyword")->update(["name"=>"keyword"]);
        return $this->returnJsonSuccess("Success Reload Rincian Output By Keywords", ["updated_at"=>RenjaUpdateDate::where("name","keyword")->first()->updated_at]);
    }
    
    private function checkTable( $namaTable ){
        $adatabel = DB::select("
                SELECT EXISTS (
                    select * from pg_matviews where matviewname = '".$namaTable."'
                )
            ");
        return $adatabel = $adatabel[0]->exists;
    }
    private function queryRenja( $subdata, $addquery ){
        $query = "";
        if($subdata === "renja_komponen" ){
            $queryx = "
                SELECT
                    a.id,
                    a.thang,
                    a.tahun,
                    a.parent_id,
                    a.kddept AS kementerian_kode,
                    concat(a.kddept, a.kdunit, a.kdprogram, a.kdgiat, a.kdoutput, a.kdsoutput, a.kdkmpnen) AS kode_ro,
                    a.kdunit,
                    a.kdprogram AS program_kode,
                    a.kdgiat AS kegiatan_kode,
                    a.kdoutput AS output_kode,
                    a.kdsoutput AS suboutput_kode,
                    a.kdkmpnen AS komponen_kode,
                    a.nmkmpnen AS komponen_nama,
                    a.jenis_komponen,
                    a.indikator_pbj,
                    a.indikator_komponen,
                    a.satuan,
                    g.alokasi_0,
                    g.alokasi_1,
                    g.alokasi_2,
                    g.alokasi_3,
                    g.target_0,
                    g.target_1,
                    g.target_2,
                    g.target_3,
                    b.id AS idro,
                    b.nmsoutput AS suboutput_nama,
                    b.alokasi_total,
                    b.kdtema,
                    b.sat,
                    c.nmoutput AS output_nama,
                    c.satuan AS satuan_output,
                    c.alokasi_total AS alokasi_totaloutput,
                    c.lokasi,
                    d.nmgiat AS kegiatan_nama,
                    d.nmunit,
                    e.nmprogout,
                    f.nmprogram AS program_nama,
                    f.unit_kerja_eselon1,
                    kl.kementerian_nama,
                    kl.kementerian_nama_alias AS kementerian_nama_short,
                    ( 
                        SELECT jsonb_agg(d_1.*) AS jsonb_agg
                        FROM ( 
                            SELECT 
                                c1.kode AS kode_lokus,
                                c1.kewenangan,
                                c1.provinsi AS provinsi_lokus,
                                c1.kabupaten AS kabupaten_lokus,
                                c1.nama AS nama_lokus
                            FROM (
                                renja.krisnarenja_t_lokasi_suboutput a1
                                LEFT JOIN renja.krisnarenja_ref_wilayah c1 
                                    ON (((a1.wilayah_id = c1.id) AND ((a1.tahun)::text = (c1.tahun)::text)))
                                )
                            WHERE ((a1.parent_id = b.id) AND ((a1.tahun)::text = (b.tahun)::text))
                        )d_1
                    ) AS lokasi_ro
                FROM (
                        (
                            (
                                (
                                    (
                                        (
                                            (
                                                renja.krisnarenja_t_kmpnen a
                                                LEFT JOIN renja.krisnarenja_t_soutput b ON (((a.parent_id = b.id) AND (a.thang = b.thang)))
                                            )
                                            LEFT JOIN renja.krisnarenja_t_output c ON (((b.parent_id = c.id) AND ((b.tahun)::text = (c.tahun)::text)))
                                        )
                                        LEFT JOIN renja.krisnarenja_t_giat d ON (((c.parent_id = d.id) AND ((c.tahun)::text = (d.tahun)::text)))
                                    )
                                    LEFT JOIN renja.krisnarenja_t_progout e ON (((d.parent_id = e.id) AND ((d.tahun)::text = (e.tahun)::text)))
                                )
                                LEFT JOIN renja.krisnarenja_t_program f ON (((a.kdprogram = f.kdprogram) AND (a.kdunit = f.kdunit) AND (a.kddept = f.kddept) AND ((a.tahun)::text = (f.tahun)::text)))
                            )
                            LEFT JOIN api.ref_kementerian kl 
                                        ON (( a.kddept = (kl.kementerian_kode)::text ))
                        )
                        LEFT JOIN renja.krisnarenja_t_alokasi g ON (((a.id = g.komponen_id) AND ((a.tahun)::text = (g.tahun)::text)))
                    )
                WHERE (b.kdtema ~~* '%008%'::text)";
            $query = "
                SELECT
                    a.idro,
                    a.thang,
                    a.tahun,
                    b.parent_id,
                    a.kementerian_kode,
                    a.kode_ro,
                    b.kdunit,
                    a.program_kode,
                    a.kegiatan_kode,
                    a.output_kode,
                    a.suboutput_kode,
                    a.suboutput_nama,
                    a.alokasi_total,
                    a.kdtema,
                    a.sat,
                    a.output_nama,
                    a.satuan_output,
                    a.alokasi_totaloutput,
                    a.lokasi,
                    a.kegiatan_nama,
                    a.nmunit,
                    a.nmprogout,
                    a.program_nama,
                    a.unit_kerja_eselon1,
                    a.kementerian_nama,
                    a.kementerian_nama_alias,
                    b.kdkmpnen AS komponen_kode,
                    b.nmkmpnen AS komponen_nama,
                    b.jenis_komponen,
                    b.indikator_pbj,
                    b.indikator_komponen,
                    b.satuan,
                    g.alokasi_0,
                    g.alokasi_1,
                    g.alokasi_2,
                    g.alokasi_3,
                    g.target_0,
                    g.target_1,
                    g.target_2,
                    g.target_3,
                    ( 
                        SELECT jsonb_agg(d_1.*) AS jsonb_agg
                        FROM ( 
                            SELECT 
                                c1.kode AS kode_lokus,
                                c1.kewenangan,
                                c1.provinsi AS provinsi_lokus,
                                c1.kabupaten AS kabupaten_lokus,
                                c1.nama AS nama_lokus
                            FROM (
                                renja.krisnarenja_t_lokasi_suboutput a1
                                LEFT JOIN renja.krisnarenja_ref_wilayah c1 
                                    ON (((a1.wilayah_id = c1.id) AND ((a1.tahun)::text = (c1.tahun)::text)))
                                )
                            WHERE ((a1.parent_id = b.id) AND ((a1.tahun)::text = (b.tahun)::text))
                        )d_1
                    ) AS lokasi_ro
                FROM (
                        renja.mv_krisna_renja_tematik_keyword a                        
                        LEFT JOIN renja.krisnarenja_t_kmpnen b 
                            ON (((b.parent_id = a.idro) AND (a.thang = b.thang)))
                        LEFT JOIN renja.krisnarenja_t_alokasi g 
                            ON (((b.id = g.komponen_id) AND ((a.tahun)::text = (g.tahun)::text)))
                    )                
                ";   
                                                        
        }elseif($subdata === "mv_krisna_renja_tematik_keyword"){
            $query = "
            SELECT 
                b.id AS idro,
                b.tahun,
                b.thang,
                b.kddept AS kementerian_kode,
                concat(b.thang, b.kddept, b.kdprogram, b.kdgiat, b.kdoutput, b.kdsoutput) AS kode_ro,
                b.nmsoutput AS suboutput_nama,
                b.alokasi_total,
                b.kdtema,
                b.sat,
                b.kdprogram AS program_kode,
                b.kdoutput AS output_kode,
                b.kdgiat AS kegiatan_kode,
                b.kdsoutput AS suboutput_kode,
                c.nmoutput AS output_nama,
                c.satuan AS satuan_output,
                c.alokasi_total AS alokasi_totaloutput,
                c.lokasi,
                d.nmgiat AS kegiatan_nama,
                d.nmunit,
                e.nmprogout,
                f.nmprogram AS program_nama,
                f.unit_kerja_eselon1,
                kl.kementerian_nama,
                kl.kementerian_nama_alias,
                intv.kode_intervensi,
                intv.intervensi_nama,
                intv.tipe_id,
                intv.tipe_nama,
                ( 
                    SELECT 
                        jsonb_agg(d_1.*) AS jsonb_agg
                    FROM( 
                        SELECT 
                            c1.kode AS kode_lokus,
                            c1.kewenangan,
                            c1.provinsi AS provinsi_lokus,
                            c1.kabupaten AS kabupaten_lokus,
                            c1.nama AS nama_lokus
                        FROM (
                            renja.krisnarenja_t_lokasi_suboutput a1
                            LEFT JOIN renja.krisnarenja_ref_wilayah c1 
                                ON (( (a1.wilayah_id = c1.id) AND ((a1.tahun)::text = (c1.tahun)::text) ))
                            )
                        WHERE ( (a1.parent_id = b.id) AND ((a1.tahun)::text = (b.tahun)::text) )
                        ) d_1
                ) AS lokasi_ro
            FROM (
                    (
                        (
                            (
                                (
                                    (
                                        renja.krisnarenja_t_soutput b
                                        LEFT JOIN renja.krisnarenja_t_output c 
                                            ON (( (b.parent_id = c.id) AND ((b.tahun)::text = (c.tahun)::text)) ))
                                        LEFT JOIN renja.krisnarenja_t_giat d 
                                            ON (( (c.parent_id = d.id) AND ((c.tahun)::text = (d.tahun)::text)) ))
                                        LEFT JOIN renja.krisnarenja_t_progout e 
                                            ON (( (d.parent_id = e.id) AND ((d.tahun)::text = (e.tahun)::text)) ))
                                        LEFT JOIN renja.krisnarenja_t_program f 
                                            ON (( (b.kdprogram = f.kdprogram) AND (b.kdunit = f.kdunit) AND (b.kddept = f.kddept) AND ((b.tahun)::text = (f.tahun)::text) ))
                                    )
                                    LEFT JOIN api.ref_kementerian kl 
                                        ON (( b.kddept = (kl.kementerian_kode)::text ))
                                )
                            LEFT JOIN ( 
                                SELECT 
                                    aa.id,
                                    aa.id_ro,
                                    aa.kode_intervensi,
                                    aa.tahun,
                                    aa.created_at,
                                    aa.updated_at,
                                    rif.id,
                                    rif.intervensi_kode,
                                    rif.intervensi_nama,
                                    rif.tipe_id,
                                    rif.tipe_nama,
                                    rif.intervensi_nama_alias,
                                    rif.link,
                                    rif.deskripsi
                        FROM (
                            renja.krisnarenja_tagging_ro aa
                            LEFT JOIN api.ref_intervensi rif 
                                ON (( (aa.kode_intervensi)::text = (rif.intervensi_kode)::text   ))
                        )
                    ) intv (id, id_ro, kode_intervensi, tahun, created_at, updated_at, id_1, intervensi_kode, intervensi_nama, tipe_id, tipe_nama, intervensi_nama_alias, link, deskripsi) 
                        ON (( ((intv.id_ro)::text = b.id) AND ((intv.tahun)::text = (b.tahun)::text) ))
                )
            WHERE (b.kdtema ~~* '%008%'::text)"
            ." ".$addquery." ;";
        }
        return $query;
    }


    public function rotaggingReload(){
        /*
            JOIN renja.krisnarenja_tagging filter ditandai = 1 
            1. Table renja.mv_krisna_renja_tematik_keyword aa
        */
        $adatabel = DB::select("
                SELECT EXISTS (
                    select * from pg_matviews where matviewname = 'mv_krisna_renja_tematik_tagging'
                )
                ");
        $adatabel = $adatabel[0]->exists;

        if(!$adatabel){
            DB::statement("
                CREATE MATERIALIZED VIEW renja.mv_krisna_renja_tematik_tagging AS
                    SELECT * FROM renja.mv_krisna_renja_tematik_keyword aa
                    LEFT JOIN ( 
                        SELECT 
                            id_ro,ditandai,cast(tahun AS varchar) as thn
                        FROM renja.krisnarenja_tagging 
                        WHERE ditandai = 1 
                    ) bb
                        ON  ((aa.idro = bb.id_ro) AND (aa.tahun::varchar = bb.thn ))
                    WHERE bb.ditandai is not null                
        ");
        }else{
            DB::statement("REFRESH MATERIALIZED VIEW renja.mv_krisna_renja_tematik_tagging");
        };

        return $this->returnJsonSuccess("Success Reload Rincian Output By Tagging", []);
    }

    public function rosepakatiReload(){

        /*
            JOIN renja.krisnarenja_tagging filter ditandai = 1 && disepakati = 1
            1. Table renja.mv_krisna_renja_tematik_keyword aa
        */

        $adatabel = DB::select("
                SELECT EXISTS (
                    select * from pg_matviews where matviewname = 'mv_krisna_renja_tematik_sepakati'
                )
                ");
        $adatabel = $adatabel[0]->exists;

        if(!$adatabel){
            DB::statement("
                CREATE MATERIALIZED VIEW renja.mv_krisna_renja_tematik_sepakati AS
                    SELECT * FROM renja.mv_krisna_renja_tematik_keyword aa
                    LEFT JOIN ( 
                        SELECT 
                            id_ro,ditandai,disepakati,cast(tahun AS varchar) as thn
                        FROM renja.krisnarenja_tagging 
                        WHERE ditandai = 1 and disepakati = 1
                    ) bb
                        ON  ((aa.idro = bb.id_ro) AND (aa.tahun::varchar = bb.thn ))
                    WHERE 
                        bb.ditandai is not null 
                        AND disepakati is not null               
            ");
        }else{
            DB::statement("REFRESH MATERIALIZED VIEW renja.mv_krisna_renja_tematik_sepakati");
        };
        return $this->returnJsonSuccess("Success Reload Rincian Output By Sepakati", []);
    }


    public function getKrisnaRenja(Request $request){
        /* 
            Model KomponenRenja
        */
        ini_set('memory_limit','-1');
        $tahun = now()->year;
        $kl = [];
        $intervensi = [];
        $search = "";

        if($request->has('tahun') && !empty($request->tahun)){
            $tahun = $request->tahun;
        }
        if($request->has('kl') && !empty($request->kl)){
            $kl = $request->kl;
        }
        if($request->has('search') && !empty($request->search)){
            $search = strtolower($request->search);
        }
        //$allKementerian = KomponenRenja::select('kementerian_kode','kementerian_nama')->groupBy('kementerian_kode','kementerian_nama')->get();
        $allKementerian = MvRenjaTematikKeywordSepakati::select('kementerian_kode','kementerian_nama')->groupBy('kementerian_kode','kementerian_nama')->get();
        //$dataRenja = KomponenRenja::where(function($q) use($tahun, $kl){
        $dataRenja = MvRenjaTematikKeywordSepakati::where(function($q) use($tahun, $kl){
            if($tahun != "all"){
                $q->where('tahun', $tahun);
            }          
            if($kl != "all"){
                $q->whereIn('kementerian_kode', $kl);
            }            
        })
        ->where(function ($q) use($search){
            if(!empty($search)){
                $q->where(\DB::raw('LOWER(program_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kegiatan_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(output_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(suboutput_nama)'), 'LIKE', "%$search%");
            }
        })
        ->get();
        
        $renjaClone = clone $dataRenja;
        $kementerianCount = $renjaClone->pluck('kementerian_kode')->unique()->values()->count();        
        //$total_alokasi = KomponenRenja::select(\DB::raw('SUM(alokasi_totaloutput::numeric) as total_alokasi'))->where(function($q) use($tahun, $kl){
        $total_alokasi = MvRenjaTematikKeywordSepakati::select(\DB::raw('SUM(alokasi_totaloutput::numeric) as total_alokasi'))->where(function($q) use($tahun, $kl){
            if($tahun != "all"){
                $q->where('tahun', $tahun);
            }          
            if($kl != "all"){
                $q->whereIn('kementerian_kode', $kl);
            }            
        })
        ->where(function ($q) use($search){
            if(!empty($search)){
                $q->where(\DB::raw('LOWER(program_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kegiatan_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(output_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(suboutput_nama)'), 'LIKE', "%$search%");
            }
        })->first();

        $tile = new \stdClass;
        $tile->total_alokasi = $total_alokasi->total_alokasi;
        $komponen = [];
        $lsKomponen = $renjaClone->map->only(['tahun','kementerian_kode','program_kode', 'kegiatan_kode', 'output_kode', 'suboutput_kode','suboutput_nama','komponen_kode','komponen_nama'])->unique()->values();
        $tile = new \stdClass;
        $lsKementerian = $renjaClone->map->only(['tahun','kementerian_kode', 'kementerian_nama','kementerian_nama_short'])->unique()->values();
        $lsKementerian = $lsKementerian->map(function($objKementerian) use ($renjaClone){
        $objKementerian = (object)$objKementerian;

        $kinerjaAnggaranKementerian = $renjaClone->filter(function ($obj) use($objKementerian) {
            return $obj->kementerian_kode == $objKementerian->kementerian_kode;
        });

        $lsProgam = $kinerjaAnggaranKementerian->map->only(['tahun', 'program_kode', 'program_nama'])->unique()->values();
        $lsKegiatan = $kinerjaAnggaranKementerian->map->only(['tahun', 'kegiatan_kode', 'kegiatan_nama'])->unique()->values();
        $lsOutput = $kinerjaAnggaranKementerian->map->only(['tahun', 'output_kode', 'output_nama'])->unique()->values();
        $lsSubOutput = $kinerjaAnggaranKementerian->map->only(['tahun', 'idro', 'suboutput_nama'])->unique()->values();
        
        $objKementerian->kl_id = $objKementerian->kementerian_kode;
        $objKementerian->name = $objKementerian->kementerian_nama;
        $objKementerian->name_short = $objKementerian->kementerian_nama_short;
        unset($objKementerian->kementerian_nama_short);
        
        $objKementerian->alokasi_totaloutput = $kinerjaAnggaranKementerian->sum('alokasi_totaloutput');
        $objKementerian->keterangan = "";
        $objKementerian->jml_program = $lsProgam->count();
        $objKementerian->jml_kegiatan = $lsKegiatan->count();
        $objKementerian->jml_kro = $lsOutput->count();
        $objKementerian->jml_ro = $lsSubOutput->count();
        $objKementerian->posisi = 'KL';

        $objKementerian->_children = $lsProgam->map(function($objProgram) use($kinerjaAnggaranKementerian,$objKementerian){
            $objProgram = (object)$objProgram;
            $kinerjaAnggaranProgram = $kinerjaAnggaranKementerian->filter(function ($obj) use( $objProgram) {
                return $obj->program_kode == $objProgram->program_kode;
            })->values();

            $lsKegiatan = $kinerjaAnggaranProgram->map->only(['tahun', 'kegiatan_kode', 'kegiatan_nama'])->unique()->values();
            $lsOutput = $kinerjaAnggaranProgram->map->only(['tahun', 'output_kode', 'output_nama'])->unique()->values();
            $lsSubOutput = $kinerjaAnggaranProgram->map->only(['tahun', 'idro', 'suboutput_nama'])->unique()->values();

            $objProgram->kl_id = $objKementerian->kementerian_kode;
            $objProgram->program_id = $objProgram->program_kode;
            $objProgram->name = $objProgram->program_nama;
            $objProgram->alokasi_totaloutput = $kinerjaAnggaranProgram->sum('alokasi_totaloutput');
            $objProgram->keterangan = "";
            $objProgram->jml_program = 0;
            $objProgram->jml_kegiatan = $lsKegiatan->count();
            $objProgram->jml_kro = $lsOutput->count();
            $objProgram->jml_ro = $lsSubOutput->count();
            $objProgram->posisi = 'Program';
        
            $objProgram->_children = $lsKegiatan->map(function($objKegiatan) use($kinerjaAnggaranProgram, $objKementerian, $objProgram){
                $objKegiatan = (object)$objKegiatan;
                $kinerjaAnggaranKegiatan = $kinerjaAnggaranProgram->filter(function ($obj) use($objKegiatan) {
                    return $obj->kegiatan_kode == $objKegiatan->kegiatan_kode;
                });

                $lsOutput = $kinerjaAnggaranKegiatan->map->only(['tahun', 'output_kode', 'output_nama'])->unique()->values();
                //$lsSubOutput = $kinerjaAnggaranKegiatan->map->only(['tahun', 'suboutput_kode', 'suboutput_nama'])->unique()->values();
                $lsSubOutput = $kinerjaAnggaranKegiatan->map->only(['tahun', 'idro', 'suboutput_nama'])->unique()->values();

                $objKegiatan->kl_id = $objKementerian->kementerian_kode;
                $objKegiatan->program_id = $objProgram->program_kode;
                $objKegiatan->kegiatan_id = $objKegiatan->kegiatan_kode;
                $objKegiatan->name = $objKegiatan->kegiatan_nama;
                $objKegiatan->alokasi_totaloutput = $kinerjaAnggaranKegiatan->sum('alokasi_totaloutput');                
                $objKegiatan->keterangan = "";
                $objKegiatan->jml_program = 0;
                $objKegiatan->jml_kegiatan = 1;
                $objKegiatan->jml_kro = $lsOutput->count();
                $objKegiatan->jml_ro = $lsSubOutput->count();
                $objKegiatan->posisi = 'Kegiatan';
        
                $objKegiatan->_children = $lsOutput->map(function($objOutput) use($kinerjaAnggaranKegiatan, $objKementerian, $objProgram, $objKegiatan){
                    $objOutput = (object)$objOutput;
                    $kinerjaAnggaranOutput = $kinerjaAnggaranKegiatan->filter(function ($obj) use($objOutput) {
                        return $obj->output_kode == $objOutput->output_kode;                        
                    });
                    $lsSubOutput = $kinerjaAnggaranOutput->map->only(['tahun', 'suboutput_kode', 'suboutput_nama','alokasi_totaloutput','lokasi_ro'])->unique()->values();
    
                    $objOutput->kl_id = $objKementerian->kementerian_kode;
                    $objOutput->program_id = $objProgram->program_kode;
                    $objOutput->kegiatan_id = $objKegiatan->kegiatan_kode;
                    $objOutput->kro_id = $objOutput->output_kode;
                    $objOutput->name = $objOutput->output_nama;            
                    $objOutput->alokasi_totaloutput = (int) $kinerjaAnggaranOutput->sum('alokasi_totaloutput');
                    $objOutput->keterangan = "";
                    $objOutput->jml_program = 0;
                    $objOutput->jml_kegiatan = 0;
                    $objOutput->jml_kro = 1;
                    $objOutput->jml_ro = $lsSubOutput->count();
                    $objOutput->posisi = 'KRO';

                    $objSubOutputd = [];

                    $objOutput->_children = $lsSubOutput->map(function($objSubOutput) use ($kinerjaAnggaranOutput, $objKementerian, $objProgram, $objKegiatan, $objOutput){
                        $objSubOutput = (object) $objSubOutput;
                        $kinerjaAnggaranSubOutput = $kinerjaAnggaranOutput->filter(function ($obj) use($objSubOutput) {
                            return $obj->suboutput_kode == $objSubOutput->suboutput_kode;
                        });  
                        $lsKomponen = $kinerjaAnggaranSubOutput->map->only(['tahun', 'komponen_kode', 'komponen_nama','jenis_komponen','indikator_pbj','alokasi_0','alokasi_1','alokasi_2','alokasi_3','target_0','target_1','target_2','target_3','satuan','indikator_komponen'])->unique()->values();

                        $objSubOutput->tahun = $objSubOutput->tahun;
                        $objSubOutput->kl_id = $objKementerian->kementerian_kode;
                        $objSubOutput->program_id = $objProgram->program_kode;
                        $objSubOutput->kegiatan_id = $objKegiatan->kegiatan_kode;
                        $objSubOutput->kro_id = $objOutput->output_kode;
                        $objSubOutput->ro_id = $objSubOutput->suboutput_kode;
                        $objSubOutput->name = $objSubOutput->suboutput_nama;
                        $objSubOutput->alokasi_totaloutput = (int) $objSubOutput->alokasi_totaloutput;                    
                        $objSubOutput->keterangan = "";
                        $objSubOutput->jml_program = 0;
                        $objSubOutput->jml_kegiatan = 0;
                        $objSubOutput->jml_kro = 0;
                        $objSubOutput->jml_ro = 1;
                        $objSubOutput->jml_komponen = $lsKomponen->count();                        
                        $objSubOutput->lokasi_ro = json_decode($objSubOutput->lokasi_ro, true, JSON_UNESCAPED_SLASHES);
                        $objSubOutput->posisi = 'RO';

                        $objSubOutput->_children = $lsKomponen->map(function($objKomponen) use ($kinerjaAnggaranSubOutput, $objKementerian, $objProgram, $objKegiatan, $objOutput, $objSubOutput){
                            $objKomponen = (object) $objKomponen;
                            $kinerjaAnggaranKomponen = $kinerjaAnggaranSubOutput->filter(function ($obj) use($objKomponen) {
                                return $obj->komponen_kode == $objKomponen->komponen_kode;
                            });  

                            //dd($objKomponen);
                            $objKomponen->program_id = $objProgram->program_kode;
                            $objKomponen->kegiatan_id = $objKegiatan->kegiatan_kode;
                            $objKomponen->kro_id = $objOutput->output_kode;
                            $objKomponen->ro_id = $objSubOutput->suboutput_kode;
                            $objKomponen->name = $objKomponen->komponen_nama;
                            $objKomponen->komponen_jenis = $objKomponen->jenis_komponen;
                            $objKomponen->indikator_pbj = $objKomponen->indikator_pbj;
                            $objKomponen->satuan = $objKomponen->satuan;
                            $objKomponen->indikator_komponen = $objKomponen->indikator_komponen;
                            $objKomponen->posisi = 'Komponen';
                            $objKomponen->alokasi_totaloutput = $objKomponen->alokasi_0;
                            $objKomponen->alokasi_0 = $objKomponen->alokasi_0;
                            $objKomponen->alokasi_1 = $objKomponen->alokasi_1;
                            $objKomponen->alokasi_2 = $objKomponen->alokasi_2;
                            $objKomponen->alokasi_3 = $objKomponen->alokasi_3;
                            $objKomponen->target_0 = $objKomponen->target_0;
                            $objKomponen->target_1 = $objKomponen->target_1;
                            $objKomponen->target_2 = $objKomponen->target_2;
                            $objKomponen->target_3 = $objKomponen->target_3;
                            if( !is_null($objKomponen->komponen_nama)){
                                return $objKomponen;
                            }
                        });  
                        if( is_null($objSubOutput->_children[0])){
                            unset($objSubOutput->_children);
                        }
                        unset($objSubOutput->suboutput_kode);
                        unset($objSubOutput->suboutput_nama);
                        unset($objSubOutput->kode);
                        unset($objSubOutput->id);
                        return $objSubOutput;
                    })->values();
    
                    unset($objOutput->output_kode);
                    unset($objOutput->output_nama);    
                    return $objOutput;            
                    })->values();

                    unset($objKegiatan->kegiatan_kode);
                    unset($objKegiatan->kegiatan_nama);
                    return $objKegiatan;        
                })->values();

                $objProgram->jml_kro = $objProgram->_children->sum('jml_kro');
                unset($objProgram->program_kode);
                unset($objProgram->program_nama);
                return $objProgram;
            })->values();

            unset($objKementerian->kementerian_kode);
            unset($objKementerian->kementerian_nama);
            return $objKementerian;
        });

        $result = new \stdClass;
        $result->tile = $tile;
        $result->detail = $lsKementerian;
        return $this->returnJsonSuccess("Data fetched successfully", $result);
    }

    public function getKrisnaRenjaLokus(Request $request){
        ini_set('memory_limit','-1');

        $tahun = now()->year;
        $kl = [];
        $intervensi = [];
        $search = "";
        $level = '';
        $kode = '';
        if($request->has('tahun') && !empty($request->tahun)){
            $tahun = $request->tahun;
        }


        if($request->has('kl') && !empty($request->kl)){
            $kl = $request->kl;
        }

        if($request->has('search') && !empty($request->search)){
            $search = strtolower($request->search);
        }

        if($request->has('level') && !empty($request->level)){
            $level = $request->level;
            if($level == 'provinsi'){
                if($request->has('provinsi') && !empty($request->provinsi)){
                    $kode = $request->provinsi;
                }
            }else if($level == 'kabupaten'){
                if($request->has('kabupaten') && !empty($request->kabupaten)){
                    $kode = $request->kabupaten;
                }
            }
        }

    //  echo $kode;
      //exit;
        $dataRenja = MvRenjaLokus::where(function($q) use($tahun, $kl,$level,$kode){
            if($tahun != "all"){
                $q->where('tahun', $tahun);
            }

          
            if($kl != "all"){
                $q->whereIn('kementerian_kode', $kl);
            }

            if($level != 'all'){
                $q->where('level',$level);
            }

            if($kode != ''){
                $q->where('kode_lokus',$kode);
            }

            
        })
        ->where(function ($q) use($search){
            if(!empty($search)){
                $q->where(\DB::raw('LOWER(program_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kegiatan_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(output_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(suboutput_nama)'), 'LIKE', "%$search%");
             }
        })
        ->get();

       // dd($dataRenja);
        $renjaClone = clone $dataRenja;
        $kementerianCount = $renjaClone->pluck('kementerian_kode')->unique()->values()->count();
        $roIdArr = $renjaClone->pluck('parent_ro')->unique()->values();
        $komponen = KomponenRenja::whereIn('parent_id',$roIdArr)->get();
        $komponenCount = $komponen->pluck('id')->unique()->values()->count();

        $total_alokasi = MvRenjaLokus::select(\DB::raw('SUM(alokasi_totaloutput::numeric) as total_alokasi'))->where(function($q) use($tahun, $kl,$level,$kode){
            if($tahun != "all"){
                $q->where('tahun', $tahun);
            }

          
            if($kl != "all"){
                $q->whereIn('kementerian_kode', $kl);
            }

            if($level != 'all'){
                $q->where('level',$level);
            }

            if($kode != ''){
                $q->where('kode_lokus',$kode);
            }

            
        })
        ->where(function ($q) use($search){
            if(!empty($search)){
                $q->where(\DB::raw('LOWER(program_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kegiatan_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(output_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(suboutput_nama)'), 'LIKE', "%$search%");
             }
        })->first();
        $tile = new \stdClass;

        $tile->total_alokasi = $total_alokasi->total_alokasi;

      

        // dd($roIdArr);
        $lsRo = $renjaClone->map->only(['tahun','kementerian_kode','program_kode', 'kegiatan_kode', 'output_kode', 'suboutput_kode'])->unique()->values();
        $lsKementerian = $renjaClone->map->only(['tahun','kementerian_kode', 'kementerian_nama','kementerian_nama_short', 'nmlokasisoutput', 'lokasi', 'provinsi_lokus', 'kabupaten_lokus', 'nama_lokus'])->unique()->values();
       // $total_alokasi = 0;
        $lsKementerian = $lsKementerian->map(function($objKementerian) use ($renjaClone, $komponen,$total_alokasi){
            $objKementerian = (object)$objKementerian;
            $kinerjaAnggaranKementerian = $renjaClone->filter(function ($obj) use($objKementerian) {
                return $obj->kementerian_kode == $objKementerian->kementerian_kode;
            });
            $lsProgam = $kinerjaAnggaranKementerian->map->only(['tahun', 'program_kode', 'program_nama'])->unique()->values();
            $lsKegiatan = $kinerjaAnggaranKementerian->map->only(['tahun', 'kegiatan_kode', 'kegiatan_nama'])->unique()->values();
            $lsOutput = $kinerjaAnggaranKementerian->map->only(['tahun', 'output_kode', 'output_nama', 'nmlokasisoutput', 'lokasi', 'provinsi_lokus', 'kabupaten_lokus', 'nama_lokus'])->unique()->values();

            $objKementerian->kl_id = $objKementerian->kementerian_kode;
            $objKementerian->name = $objKementerian->kementerian_nama;
            $objKementerian->name_short = $objKementerian->kementerian_nama_short;
            unset($objKementerian->kementerian_nama_short);
            $objKementerian->alokasi_totaloutput = $kinerjaAnggaranKementerian->sum('alokasi_totaloutput');
            $objKementerian->keterangan = "";
            $objKementerian->jml_program = $lsProgam->count();
            $objKementerian->jml_kegiatan = $lsKegiatan->count();
            $objKementerian->jml_kro = $lsOutput->count();
            $objKementerian->jml_ro = $kinerjaAnggaranKementerian->count();
            $objKementerian->posisi = 'KL';

//            $objKementerian->lokasi = [
//                'nmlokasisoutput'   => $objKementerian->nmlokasisoutput,
//                'lokasi'            => $objKementerian->lokasi,
//                'provinsi_lokus'    => $objKementerian->provinsi_lokus,
//                'kabupaten_lokus'   => $objKementerian->kabupaten_lokus,
//                'nama_lokus'        => $objKementerian->nama_lokus
//            ];


                $objKementerian->_children = $lsProgam->map(function($objProgram) use($kinerjaAnggaranKementerian,$objKementerian, $komponen){
                    $objProgram = (object)$objProgram;
                    $kinerjaAnggaranProgram = $kinerjaAnggaranKementerian->filter(function ($obj) use( $objProgram) {
                        return $obj->program_kode == $objProgram->program_kode;
                    })->values();
    
                    $lsKegiatan = $kinerjaAnggaranProgram->map->only(['tahun', 'kegiatan_kode', 'kegiatan_nama'])->unique()->values();
                    $lsOutput   = $kinerjaAnggaranKementerian->map->only(['tahun', 'output_kode', 'output_nama', 'nmlokasisoutput', 'lokasi', 'provinsi_lokus', 'kabupaten_lokus', 'nama_lokus'])->unique()->values();

                    $objProgram->kl_id = $objKementerian->kementerian_kode;
                    $objProgram->program_id = $objProgram->program_kode;
                    $objProgram->name = $objProgram->program_nama;
    
                    $objProgram->alokasi_totaloutput = $kinerjaAnggaranProgram->sum('alokasi_totaloutput');
                    $objProgram->keterangan = "";
                    $objProgram->jml_program = 0;
                    $objProgram->jml_kegiatan = $lsKegiatan->count();
                    $objProgram->jml_kro = $lsOutput->count();
                    $objProgram->jml_ro = $kinerjaAnggaranProgram->count();
                    $objProgram->posisi = 'Program';

//                    $objProgram->lokasi = $objKementerian->lokasi;

                    $objProgram->_children = $lsKegiatan->map(function($objKegiatan) use($kinerjaAnggaranProgram, $objKementerian, $objProgram, $komponen){
                        $objKegiatan = (object)$objKegiatan;
                        $kinerjaAnggaranKegiatan = $kinerjaAnggaranProgram->filter(function ($obj) use($objKegiatan) {
                            return $obj->kegiatan_kode == $objKegiatan->kegiatan_kode;
                        });
        
                        $lsOutput = $kinerjaAnggaranKegiatan->map->only(['tahun', 'output_kode', 'output_nama', 'nmlokasisoutput', 'lokasi', 'kewenangan', 'kode_lokus', 'nama_lokus', 'provinsi_lokus', 'kabupaten_lokus'])->unique()->values();
        
                        $objKegiatan->kl_id = $objKementerian->kementerian_kode;
                        $objKegiatan->program_id = $objProgram->program_kode;
                        $objKegiatan->kegiatan_id = $objKegiatan->kegiatan_kode;
                        $objKegiatan->name = $objKegiatan->kegiatan_nama;
                        $objKegiatan->alokasi_totaloutput = $kinerjaAnggaranKegiatan->sum('alokasi_totaloutput');
                        
                        $objKegiatan->keterangan = "";
                        $objKegiatan->jml_program = 0;
                        $objKegiatan->jml_kegiatan = 0;
                        $objKegiatan->jml_kro = $lsOutput->count();
                        $objKegiatan->jml_ro = $kinerjaAnggaranKegiatan->count();
                        $objKegiatan->posisi = 'Kegiatan';

//                        $objKegiatan->lokasi = $objProgram->lokasi;
        
                        $objKegiatan->_children = $lsOutput->map(function($objOutput) use($kinerjaAnggaranKegiatan, $objKementerian, $objProgram, $objKegiatan, $komponen){
                            $objOutput = (object)$objOutput;
                            $kinerjaAnggaranOutput = $kinerjaAnggaranKegiatan->filter(function ($obj) use($objOutput) {
                                return $obj->output_kode == $objOutput->output_kode;
                                
                            });
            
                            $objOutput->kl_id = $objKementerian->kementerian_kode;
                            $objOutput->program_id = $objProgram->program_kode;
                            $objOutput->kegiatan_id = $objKegiatan->kegiatan_kode;
                            $objOutput->kro_id = $objOutput->output_kode;
                            $objOutput->name = $objOutput->output_nama;
            
                            $objOutput->alokasi_totaloutput =  (int) $kinerjaAnggaranOutput->sum('alokasi_totaloutput');
                          
                            $objOutput->keterangan = "";
                            $objOutput->jml_program = 0;
                            $objOutput->jml_kegiatan = 0;
                            $objOutput->jml_kro = 0;
                            $objOutput->jml_ro = $kinerjaAnggaranOutput->count();
                            $objOutput->posisi = 'KRO';

//                            $objOutput->lokasi = $objKegiatan->lokasi;
                              
                           $objSubOutputd = [];

                            $objOutput->_children = $kinerjaAnggaranOutput->map(function($objSubOutput) use (&$objSubOutputd,$komponen){
                                    $objSubOutput = (object) $objSubOutput;
                                     $objSubOutputd = [];                              
                            
                                    $objSubOutputd['tahun'] = $objSubOutput->tahun;
                                    $objSubOutputd['kementerian_kode'] = $objSubOutput->kementerian_kode;
                                    $objSubOutputd['kementerian_nama'] = $objSubOutput->kementerian_nama;
                                    $objSubOutputd['program_kode'] = $objSubOutput->program_kode;
                                    $objSubOutputd['program_nama'] = $objSubOutput->program_nama;
                                    $objSubOutputd['kegiatan_kode'] = $objSubOutput->kegiatan_kode;
                                    $objSubOutputd['kegiatan_nama'] = $objSubOutput->kegiatan_nama;
                                    $objSubOutputd['alokasi_totaloutput'] = $objSubOutput->alokasi_totaloutput;
                                    $objSubOutputd['keterangan'] = $objSubOutput->keterangan;

                                    $objSubOutputd['kl_id'] = $objSubOutput->kementerian_kode;


                                $objSubOutputd['program_id'] = $objSubOutput->program_kode;
                                $objSubOutputd['kegiatan_id'] = $objSubOutput->kegiatan_kode;
                                $objSubOutputd['kro_id'] = $objSubOutput->output_kode;
                                $objSubOutputd['ro_id'] = $objSubOutput->suboutput_kode;
                                $objSubOutputd['name'] = $objSubOutput->suboutput_nama;
                                $objSubOutputd['jml_program'] = 0;
                                $objSubOutputd['jml_kegiatan'] = 0;
                                $objSubOutputd['jml_kro'] = 0;
                                $objSubOutputd['jml_ro'] = 1;
                                $objSubOutputd['posisi'] = 'RO';

//                                $objSubOutputd['lokasi'] = $objSubOutput->lokasi;
                                $objSubOutputd['lokasi'] = [];


                                $objKomponend = [];

                                $objSubOutputd['_children'] = $komponen->map(function($objKomponen) use (&$objKomponend, &$objSubOutputd){

                                    $objKomponen = (object) $objKomponen;

                                    $objKomponend['name'] = $objKomponen->komponen_nama;
                                    $objKomponend['komponen_jenis'] = $objKomponen->jenis_komponen;
                                    $objKomponend['indikator_pbj'] = $objKomponen->indikator_pbj;
                                    $objKomponend['satuan'] = $objKomponen->satuan;
                                    $objKomponend['alokasi_totaloutput'] = $objKomponen->alokasi_0;
                                    $objKomponend['alokasi_0'] = $objKomponen->alokasi_0;
                                    $objKomponend['alokasi_1'] = $objKomponen->alokasi_1;
                                    $objKomponend['alokasi_2'] = $objKomponen->alokasi_2;
                                    $objKomponend['alokasi_3'] = $objKomponen->alokasi_3;
                                    $objKomponend['target_0'] = $objKomponen->target_0;
                                    $objKomponend['target_1'] = $objKomponen->target_1;
                                    $objKomponend['target_2'] = $objKomponen->target_2;
                                    $objKomponend['target_3'] = $objKomponen->target_3;
                                    $objKomponend['posisi'] = 'Komponen';

                                    $objSubOutputd['lokasi_ro'] = json_decode($objKomponen->lokasi_ro, true);

                                    return $objKomponend;

                    
                                   
                    

  
                                });



                
                                unset($objSubOutput->suboutput_kode);
                                unset($objSubOutput->suboutput_nama);
                                unset($objSubOutput->kode);
                                unset($objSubOutput->id);
                
                                return $objSubOutputd;
                
                            })->values();
            
                            unset($objOutput->output_kode);
                            unset($objOutput->output_nama);
            
                            return $objOutput;
            
                        })->values();
        
                        unset($objKegiatan->kegiatan_kode);
                        unset($objKegiatan->kegiatan_nama);
        
                        return $objKegiatan;
        
                    })->values();

                    $objProgram->jml_kro = $objProgram->_children->sum('jml_kro');
    
                    unset($objProgram->program_kode);
                    unset($objProgram->program_nama);
    
                    return $objProgram;
    
                })->values();







            unset($objKementerian->kementerian_kode);
            unset($objKementerian->kementerian_nama);

            return $objKementerian;

        });


       // dd($lsKementerian);

        $result = new \stdClass;
        // $tile = 'a';
        $result->tile = $tile;
        $result->detail = $lsKementerian;
       

        return $this->returnJsonSuccess("Data fetched successfully", $result);
    }



    public function kabupaten(Request $request)
    {
        //DB::statement("REFRESH MATERIALIZED VIEW renja.mv_krisna_renja_lokus");
        //dd($request);
        $where = '';
        $where .= $request->provinsi ? "AND provinsi_id IN (" . implode(',', $request->provinsi) . ")" : "" ;
        $where .= $request->tahun ? " AND rl.tahun::int IN (" . implode(",", $request->tahun) . ")" : "" ;
        //$where .= $request->tahun ? " AND rl.tahun ='".$request->tahun."' ": "" ;
        $where .= $request->kl ? " AND rl.kementerian_kode IN ('" . implode("','", $request->kl) . "')" : " AND rl.kementerian_kode IN ('0')" ;
        
        $query  ="
            select 
                json_build_object(
                    'type', 'FeatureCollection','features', json_agg(public.ST_AsGeoJSON(t.*)::json)
                ) AS data
            from(
                SELECT
                    sp.id, 
                    sp.kabupaten_kode,
                    sp.kabupaten_nama,
                    sp.provinsi_nama,
                    sp.provinsi_nama_alias,
                    sp.kode_kemendagri,
                    sp.geom
                FROM renja.mv_krisna_renja_lokus  rl 
                JOIN api.kabupaten sp 
                    ON rl.kode_lokus = sp.kabupaten_kode  AND rl.level = 'kabupaten' 
                WHERE 1=1 ". $where . "
                group by 
                    sp.id,sp.kabupaten_kode,sp.kabupaten_nama,sp.provinsi_nama,
                    sp.provinsi_nama_alias,sp.kode_kemendagri,sp.geom
            ) as t(id,kabupaten_kode,kabupaten_nama,provinsi_nama,provinsi_nama_alias,kode_kemendagri, geom)";
        //dd($query);
            $results = DB::select($query);
        return $this->returnJsonSuccessCheck("Data fetched successfully", json_decode($results[0]->data));
    }

    public function provinsi(Request $request)
    {
        $where = '';
        $where .= $request->tahun ? " AND rl.tahun::int IN (" . implode(',', $request->tahun) . ")" : "" ;
        
        $query  ="
            select 
                json_build_object(
                    'type', 'FeatureCollection','features', json_agg(public.ST_AsGeoJSON(t.*)::json)
                ) AS data
            from (
                SELECT
                    sp.id, 
                    sp.provinsi_kode,
                    sp.provinsi_nama,
                    sp.provinsi_nama_alias,
                    sp.kode_kemendagri,
                    sp.geom
                FROM renja.mv_krisna_renja_lokus  rl 
                JOIN api.provinsi sp 
                    ON rl.kode_lokus = sp.provinsi_kode  AND rl.level = 'provinsi' 
                WHERE 1=1 ". $where . "
                group by 
                    sp.id,sp.provinsi_kode,sp.provinsi_nama,sp.provinsi_nama_alias,sp.kode_kemendagri,sp.geom
            ) as t(id,provinsi_kode,provinsi_nama,provinsi_nama_alias,kode_kemendagri, geom)";

        $results = DB::select($query);       
        return $this->returnJsonSuccessCheck("Data fetched successfully", json_decode($results[0]->data));
    }

    public function kementerian(Request $request){
        $tahun = '';
        if($request->has('tahun') && !empty($request->tahun)){
            $tahun = $request->tahun;
        }

        $kementerian = DB::table('renja.mv_krisna_renja')->select('kementerian_kode','kementerian_nama','kementerian_nama_alias')->whereIn('tahun',$tahun)->distinct()->get();
        // $kementerian = DB::table('api.ref_kementerian')->whereIn('id',$kl_renja)->get();

        $kementerian->map(function ($obj) {
									$obj->text = $obj->kementerian_nama;
									$obj->short = $obj->kementerian_nama_alias;
									return $obj;
								})->toJson();

        return $this->returnJsonSuccess("Kementerian fetched successfully", $kementerian);
    }

    public function tahun(Request $request){
        $kementerian = DB::table('renja.mv_krisna_renja')->select('tahun')->distinct()->get();
        // $kementerian = DB::table('api.ref_kementerian')->whereIn('id',$kl_renja)->get();

        // $kementerian->map(function ($obj) {
		// 							$obj->text = $obj->kementerian_nama;
		// 							$obj->short = $obj->kementerian_nama_alias;
		// 							return $obj;
		// 						})->toJson();

        return $this->returnJsonSuccess("Kementerian fetched successfully", $kementerian);
    }

    public function listro(Request $request){

        $tahun = now()->year;
        $kl = [];
        //$intervensi = [];
        $search = "";
        if($request->has('tahun') && !empty($request->tahun)){
            $tahun = $request->tahun;
        }
        if($request->has('kl') && !empty($request->kl)){
            $kl = $request->kl;
        }
        if($request->has('search') && !empty($request->search)){
            $search = strtolower($request->search);
        }

         //exit;
        $dataRenja = VRenja::where(function($q) use($tahun, $kl){
            if($tahun != "all"){
                $q->where('tahun', $tahun);
            }        
            // if($kl != "all"){
            //     $q->whereIn('kementerian_kode', $kl);
            // }  
        })
        ->where(function ($q) use($search){
            if(!empty($search)){
                $q->where(\DB::raw('LOWER(program_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kegiatan_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(output_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(suboutput_nama)'), 'LIKE', "%$search%");
            }
        })
        ->get();

        $result = new \stdClass;
        // $tile = 'a';
        // $result->tile = $tile;
        $result->data = $dataRenja;
        return $this->returnJsonSuccess("Data fetched successfully", $result);
    }

    public function listroTagging(Request $request){
        $tahun = now()->year;
        $kl = [];
        //$intervensi = [];
        $search = "";
        if($request->has('tahun') && !empty($request->tahun)){
            $tahun = $request->tahun;
        }
        if($request->has('kl') && !empty($request->kl)){
            $kl = $request->kl;
        }
        if($request->has('search') && !empty($request->search)){
            $search = strtolower($request->search);
        }

         //exit;
        //$dataRenja = VRenja::where(function($q) use($tahun, $kl){
        $dataRenja = MvRenjaTematikKeyword::where(function($q) use($tahun, $kl){
            if($tahun != "all"){
                //$q->where('renja.v_krisna_renja.tahun', $tahun);
                $q->where('renja.mv_krisna_renja_tematik_keyword.tahun', $tahun);
            }  
        })
        ->leftJoin('renja.krisnarenja_tagging', function($join) use($tahun)
            {
                //$join->on('renja.krisnarenja_tagging.id_ro', '=', 'renja.v_krisna_renja.idro');
                $join->on('renja.krisnarenja_tagging.id_ro', '=', 'renja.mv_krisna_renja_tematik_keyword.idro');
                $join->where('renja.krisnarenja_tagging.tahun', '=', $tahun);            
            })
        ->where(function ($q) use($search){
            if(!empty($search)){
                $q->where(\DB::raw('LOWER(program_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kegiatan_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(output_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(suboutput_nama)'), 'LIKE', "%$search%");
            }    
        })
        ->get();
        $result = new \stdClass;
        $result->data = $dataRenja;
        return $this->returnJsonSuccess("Data fetched successfully", $result);
    }
}
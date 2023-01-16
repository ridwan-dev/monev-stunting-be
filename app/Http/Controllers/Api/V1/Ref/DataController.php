<?php

namespace App\Http\Controllers\Api\V1\Ref;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\Pub\Kementerian;
use App\Models\Pub\Intervensi;
use App\Models\Pub\Tematik;
use App\Models\Pub\Lokasi;
use App\Models\Pub\Menu;
use App\TematikKeyword;
use App\SatuanKomponen;
use Illuminate\Support\Facades\DB;

class DataController extends BaseController
{
    public function __construct()
    {
        $this->middleware(
            [
                'auth:api'
            ]);
    }

    public function index(Request $request)
    {
        return $this->returnJsonSuccess("Data Root");
    }

    public function kementerian(Request $request){
        $kl_renja = DB::table('api.dt_renja')->select('kementerian_id')->distinct()->pluck('kementerian_id');
        $kementerian = DB::table('api.ref_kementerian')->whereIn('id',$kl_renja)->get();

        $kementerian->map(function ($obj) {
									$obj->text = $obj->kementerian_nama;
									$obj->short = $obj->kementerian_nama_alias;
									return $obj;
								})->toJson();

        return $this->returnJsonSuccess("Kementerian fetched successfully", $kementerian);
    }

    public function intervensi(Request $request){
        $intervensi = Intervensi::all();

        return $this->returnJsonSuccess("Intervensi fetched successfully", $intervensi);
    }

    public function groupIntervensi(Request $request)
    {
        $results = DB::select("SELECT jsonb_agg(tp) AS data
            FROM (
			SELECT
				t.*,
				(SELECT jsonb_agg(d)
					FROM (
                        SELECT 
                        ari.id,
                        ari.intervensi_nama AS text,
                        ari.link,
                        ari.tipe_nama AS type
                        FROM api.ref_intervensi ari where ari.tipe_id = t.id
					) AS d) AS children
				FROM (SELECT DISTINCT tipe_id AS id, concat_ws(' ', 'INTERVENSI ',upper(tipe_nama)) as text FROM api.ref_intervensi ORDER BY id) AS t
	) as tp"); 
       
        return $this->returnJsonSuccess("Data fetched successfully", json_decode($results[0]->data));
    }

    public function tematik(Request $request){
        $tematik = Tematik::all();

        return $this->returnJsonSuccess("Tematik fetched successfully", $tematik);
    }

    public function lokasi(Request $request){
        $lokasi = new Lokasi;
        if($request->has('level') && $request->level != "all"){
            $lokasi = $lokasi->where('level', $request->level);
        }

        if($request->has('parentId') && $request->parentId != "all"){
            $lokasi = $lokasi->where('parent_id_kemendagri', $request->parentId);
        }

        $lokasi = $lokasi->select('id', 'level', 'kode_kemendagri', 'nama')->get();

        return $this->returnJsonSuccess("Lokasi fetched successfully", $lokasi);
    }

    public function menu(Request $request){
        $menu = new Menu;
        if($request->has('parentId') && $request->parentId != "all"){
            $menu = $menu->where('parent_id', $request->parentId);
        }else{
            $menu = $menu->whereNull('parent_id');
        }

        $menu = $menu
                ->orderBy('sort')
                ->get();

        return $this->returnJsonSuccess("Menu fetched successfully", $menu);

    }

    public function keywords(Request $request){
        $keywords = TematikKeyword::where('status', true)
                    ->get();

        return $this->returnJsonSuccess("Keywords fetched successfully", $keywords);
    }

    public function satuanKomponen(Request $request){
        $satuan = satuanKomponen::get();

        return $this->returnJsonSuccess("Satuan Komponen fetched successfully", $satuan);
    }

    public function tahun(Request $request){
        $results = DB::select('select distinct tahun as id, tahun as text  from api.mv_renja_monev order by tahun desc');

        return $this->returnJsonSuccess("Data fetched successfully", $results);
    }

    public function program(Request $request){
        $results = DB::select('select id as id, nama as text  from api.dt_renja');

        return $this->returnJsonSuccess("Data fetched successfully", $results);
    }


    public function intervensipost(Request $request){

        $validatedData = $request->validate([
            'intervensi_kode' => 'required|max:10|unique:pgsql.api.ref_intervensi',
            'intervensi_nama' => 'required',
            'intervensi_nama_alias' => 'required|max:5',
            'deskripsi' => 'required'
        ]);

        $intervensi_kode = $request->post('intervensi_kode');
        $intervensi_nama = $request->post('intervensi_nama');
        $tipe_id         = $request->post('tipe_id');
        $intervensi_nama_alias = $request->post('intervensi_nama_alias');
        $link            = $request->post('link');
        $deskripsi       = $request->post('deskripsi');

        if($tipe_id == 1){
            $tipe_nama = 'spesifik';
        }else if($tipe_id == 2){
            $tipe_nama = 'sensitif';
        }else if($tipe_id == 3){
            $tipe_nama = 'dukungan';
        }else{
            $tipe_nama = 'lainnya';
        }


        $data = array(
            'intervensi_kode' => $intervensi_kode,
            'intervensi_nama' => $intervensi_nama,
            'tipe_id'         => $tipe_id,
            'intervensi_nama_alias' => $intervensi_nama_alias,
            'tipe_nama'       => $tipe_nama,
            'link'            => $link,
            'deskripsi'       => $deskripsi
        );

        $id             = $request->post('id');
        if($id == ''){
            $id = Intervensi::orderBy('id','DESC')->first();
            $id = $id->id + 1;
           
            $data['id'] = $id;
            $intervensi     = Intervensi::insert($data);

        }else{

            $intervensi     = Intervensi::where('id',$id)->update($data);
        }

        if($intervensi){
            return $this->returnJsonSuccess("Success", []);
        }else{
            return $this->returnJsonError("Failed", []);

        }



    }

    public function intervensidelete(Request $request){

    

        $id             = $request->post('id');
    
            $intervensi     = Intervensi::find($id)->delete();
       

        if($intervensi){
            return $this->returnJsonSuccess("Success", []);
        }else{
            return $this->returnJsonError("Failed", []);

        }



    }
}

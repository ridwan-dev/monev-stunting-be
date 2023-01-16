<?php

namespace App\Http\Controllers\Api\V1\Ref;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\Pub\Lokasi;
use App\Models\Pub\LokasiPrioritas;
use App\Models\Pub\Provinsi;
use App\Models\Pub\Kabupaten;

class LocationController extends BaseController
{
    public function __construct()
    {
        // $this->middleware(
        //     [
        //         'auth:api'
        //     ]);
    }

    public function index(Request $request, $parentId = null)
    {
        $locations = new Lokasi;
        if($parentId != null){
            $locations = $locations->where('parent_id_kemendagri', $parentId);
        }

        $locations = $locations->get();

        return $this->returnJsonSuccess("Location fetched successfully", $locations);
    }

    public function provinsi(Request $request)
    {
        $locations = Provinsi::select('id', 'provinsi_kode','provinsi_nama','provinsi_nama_alias','kode_kemendagri')->get();
        return $this->returnJsonSuccess("Provinsi fetched successfully", $locations);
    }

    public function kotaKabupaten(Request $request, $provId = null)
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

    public function kecamatan(Request $request, $kotakabId = null)
    {
        $locations = Lokasi::where('level', 'sub-district')
                            ->where(function($q) use ($kotakabId){
                                if($kotakabId != null)
                                    $q->where('parent_id_kemendagri', $kotakabId);
                            })
                            ->get();
        return $this->returnJsonSuccess("Sub District fetched successfully", $locations);
    }

    public function desa(Request $request, $kecId)
    {
        $locations = Lokasi::where('level', 'village')->get();
        return $this->returnJsonSuccess("Village fetched successfully", $locations);
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
}

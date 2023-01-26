<?php

namespace App\Http\Controllers\Api\V1\Interkoneksi;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Libraries\Services\{
    DakService,
    Core\Exception as ServiceException
};
use App\Libraries\Services\Core\Auth;

use App\Models\Staging\KrisnaDak;
use App\Models\Staging\DakData;
use App\Models\Staging\DakPengadaan;
use App\Models\Staging\DakWilayahPemda;

class InterkoneksiKrisnaDakController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('service');
    }

    // public function getDataDak(Request $request, $tahun){
    //     try {
    //         $dakService = DakService::get(DakService::DAK_URL . "$tahun?apikey=68e3991e-ea2c-4f66-9450-fdeccfc6b279");
    //         ServiceException::on($dakService);
    //         $data = $dakService->data;
    //     } catch (\Exception $e) {
    //         $data = [];
    //     }

    //     if(count($data) > 0){
    //         KrisnaDak::truncate();
    //     }

    //     foreach($data as $komponen){
    //         $collection = new KrisnaDak();
    //         $collection->tahun = $tahun;
    //         $collection->id_detail_rincian = $komponen->id_detail_rincian;
    //         $collection->provinsi = $komponen->provinsi;
    //         $collection->pengusul = $komponen->pengusul;
    //         $collection->bidang = $komponen->bidang;

    //         $collection->sub_bidang = $komponen->sub_bidang;
    //         $collection->kementerian = $komponen->kementerian;
    //         $collection->menu = $komponen->menu;
    //         $collection->pn = $komponen->pn;
    //         $collection->pp = $komponen->pp;
    //         $collection->kp = $komponen->kp;
    //         $collection->tematik = $komponen->tematik;
    //         $collection->kewenangan = $komponen->kewenangan;
    //         $collection->jenis = $komponen->jenis;
    //         $collection->pelaksana = $komponen->pelaksana;
    //         $collection->rincian = $komponen->rincian;
    //         $collection->detail_rincian = $komponen->detail_rincian;
    //         $collection->status_detail = $komponen->status_detail;
    //         $collection->satuan = $komponen->satuan;
    //         $collection->prioritas = $komponen->prioritas;
    //         $collection->pengadaan_ids = $komponen->pengadaan_ids;
    //         $collection->komponen = $komponen->komponen;
    //         $collection->readiness_criteria = $komponen->readiness_criteria;
    //         $collection->keterangan = $komponen->keterangan;
    //         $collection->titik_koordinat = $komponen->titik_koordinat;
    //         $collection->volume_rk = $komponen->volume_rk;
    //         $collection->unit_cost_rk = $komponen->unit_cost_rk;
    //         $collection->nilai_rk = $komponen->nilai_rk;

    //         $collection->save();
    //     }



    //     return $this->returnJsonSuccess("Data stored successfully", $data);
    // }

    public function getDataDak(Request $request, $tahun){

        ///$apiKey = "bb48735d-e0ce-472b-b2c6-3f3bac1e6e5f";
        $apiKey = "724e62ca-5187-4d3b-8313-4565345ee72f";
        
        \DB::beginTransaction();

        try {
            try {
                $dakService = DakService::get(\Str::replaceArray('@', [$tahun, $apiKey], DakService::DAK_PEMDA));
                ServiceException::on($dakService);
                $dataPemda = $dakService->data;
            } catch (\Exception $e) {
                $dataPemda = [];
            }
    
            foreach($dataPemda as $pemda){
                $colPemda = DakWilayahPemda::where('tahun', $tahun)->where('id_pemda', $pemda->id)->first();
                if(!$colPemda){
                    $colPemda = new DakWilayahPemda();
                }else{
                    if(\Carbon::now()->toDateString() == $colPemda->updated_at->toDateString()){
                        continue;
                    }
                }

                $colPemda->tahun = $tahun;
                $colPemda->id_pemda = $pemda->id;
                $colPemda->parent_id = $pemda->parent_id;
                $colPemda->kode = $pemda->kode;
                $colPemda->nama = $pemda->nama;

                $colPemda->save();
                // dd($colPemda);
    
            }

    
            try {
                $dakService = DakService::get(\Str::replaceArray('@', [$tahun, $apiKey], DakService::DAK_PENGADAAN));
                ServiceException::on($dakService);
                $dataPengadaan = $dakService->data;
            } catch (\Exception $e) {
                $dataPengadaan = [];
            }
    
            foreach($dataPengadaan as $pengadaan){
                $colPengadaan = DakPengadaan::where('tahun', $tahun)->where('id_pengadaan', $pengadaan->id)->first();   
                if(!$colPengadaan)
                    $colPengadaan = new DakPengadaan();
                $colPengadaan->tahun = $tahun;
                $colPengadaan->id_pengadaan = $pengadaan->id;
                $colPengadaan->nama = $pengadaan->nama;

                $colPengadaan->save();
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();

            return $this->returnJsonError("Failed: ". $e, 500);
        }
    
        foreach($dataPemda as $pemda){
            $colPemda = DakWilayahPemda::where('tahun', $tahun)->where('id_pemda', $pemda->id)->first();
            if(!empty($colPemda->last_synced) && \Carbon::now()->toDateString() == $colPemda->last_synced->toDateString()){
                continue;
            }

            \DB::beginTransaction();
            try {

                try {
                    $dakService = DakService::get(\Str::replaceArray('@', [$tahun, $pemda->kode, $apiKey], DakService::DAK_URL));
                    ServiceException::on($dakService);
                    $data = $dakService->data;
                } catch (\Exception $e) {
                    $data = [];
                }
        
                foreach($data as $komponen){
                    $collection = new KrisnaDak();
                    $collection->tahun = $tahun;
                    $collection->kode_pemda = $pemda->kode;
                    $collection->id_detail_rincian = $komponen->id_detail_rincian;
                    $collection->bidang = $komponen->bidang;
                    $collection->sub_bidang = $komponen->sub_bidang;
                    $collection->kementerian = $komponen->kementerian;
                    $collection->menu_kegiatan = $komponen->menu_kegiatan;
                    $collection->pn = $komponen->pn;
                    $collection->pp = $komponen->pp;
                    $collection->tematik = $komponen->tematik;
                    $collection->kewenangan = $komponen->kewenangan;
                    $collection->jenis = $komponen->jenis;
                    $collection->pelaksana = $komponen->pelaksana;
                    $collection->rincian = $komponen->rincian;
                    $collection->detail_rincian = $komponen->detail_rincian;
                    $collection->status_detail = $komponen->status_detail;
                    $collection->satuan = $komponen->satuan;
                    $collection->volume_rk = $komponen->volume_rk;
                    $collection->unit_cost_rk = $komponen->unit_cost_rk;
                    $collection->nilai_rk = $komponen->nilai_rk;
                    $collection->pengadaan_ids = $komponen->pengadaan_ids;
                    $collection->komponens = $komponen->komponens;
                    $collection->criterias = $komponen->criterias;
                    $collection->keterangan = $komponen->keterangan;
                    $collection->coordinate = $komponen->coordinate;
        
                    $collection->save();
                    
                }

                $colPemda->last_synced = \Carbon::now();
                $colPemda->save();

                \DB::commit();
            } catch (\Exception $e) {
                \DB::rollback();

                return $this->returnJsonError("Failed: ". $e, 500);
            }

        }

        return $this->returnJsonSuccess("Data stored successfully");
    }
    
}

<?php

namespace App\Http\Controllers\Api\V1\Tagging;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Pub\TRenjakl;
use App\Models\Pub\RPeriode;
use App\Models\Spatial\PetaLokasi;
use App\Models\Pub\Lokasi;
use App\Models\Pub\Kementerian;
use App\Models\Pub\MvDmRenjakl;
use App\Models\Pub\MvRenjaBase;

class TaggingController extends BaseController
{
    public function __construct()
    {
        $this->middleware(
            [
                'auth:api'
            ]);
    }

    public function getTahun(Request $request){
        $tahun = TRenjakl::select('tahun as id', 'tahun as text')
                        ->where(function($query) use($request){
                            if($request->has('tahun')){
                                $query->where('tahun', $request->tahun);
                            }
                        })
                        ->groupBy('tahun')
                        ->get();
        return $this->returnJsonSuccess("Data fetched successfully", $tahun);
    }

    public function getKementerian(Request $request){
        $kementerian = TRenjakl::with(['kementerian'])->select('kementerian_id')
                        ->where(function($query) use($request){
                            if($request->has('tahun')){
                                $query->where('tahun', $request->tahun);
                            }
                        })
                        ->groupBy('kementerian_id')
                        ->get();

        $kementerian = $kementerian->map(function($obj){
            if($obj->kementerian != null){
                $obj->id = $obj->kementerian->id;
                $obj->text = $obj->kementerian->nama;
                $obj->short = $obj->kementerian->nama_pendek;
            }
            unset($obj->kementerian);
            unset($obj->kementerian_id);

            return $obj;
        });

        return $this->returnJsonSuccess("Data fetched successfully", $kementerian);
    }

    public function getDashboardTile(Request $request){
        $tahun = MvRenjaBase::max('tahun');

        if($request->has('tahun')){
            $tahun = $request->tahun;
        }


        $tahunBefore = MvRenjaBase::where('tahun', '<', $tahun)
                                ->max('tahun');

        $kementerianNow = MvRenjaBase::select('kementerian_id')
                                ->where('tahun', $tahun)
                                ->groupBy('kementerian_id')
                                ->count();
        $kementerianBefore = MvRenjaBase::select('kementerian_id')
                                ->where('tahun', $tahunBefore)
                                ->groupBy('kementerian_id')
                                ->count();

        $programNow = MvRenjaBase::select('program_id')
                                ->where('tahun', $tahun)
                                ->groupBy('program_id')
                                ->count();
        $programBefore = MvRenjaBase::select('program_id')
                                ->where('tahun', $tahunBefore)
                                ->groupBy('program_id')
                                ->count();
        
        $kegiatanNow = MvRenjaBase::select('kegiatan_id')
                                ->where('tahun', $tahun)
                                ->groupBy('kegiatan_id')
                                ->count();
        $kegiatanBefore = MvRenjaBase::select('kegiatan_id')
                                ->where('tahun', $tahunBefore)
                                ->groupBy('kegiatan_id')
                                ->count();

        $outputNow = MvRenjaBase::select('output_id')
                                ->where('tahun', $tahun)
                                ->groupBy('output_id')
                                ->count();
        $outputBefore = MvRenjaBase::select('output_id')
                                ->where('tahun', $tahunBefore)
                                ->groupBy('output_id')
                                ->count();

        $subOutputNow = MvRenjaBase::select('sub_output_id')
                                ->where('tahun', $tahun)
                                ->groupBy('sub_output_id')
                                ->count();
        $subOutputBefore = MvRenjaBase::select('sub_output_id')
                                ->where('tahun', $tahunBefore)
                                ->groupBy('sub_output_id')
                                ->count();

        $periode = RPeriode::where('tahun_anggaran', $tahun)
                            ->get();
        $periode = $periode->map(function($obj){
            $dateFrom = Carbon::parse($obj->periode_awal)->format('d/m/Y');
            $dateTo = Carbon::parse($obj->periode_akhir)->format('d/m/Y');
            $obj->text = $dateFrom." s/d ".$dateTo;

            unset($obj->periode_awal);
            unset($obj->periode_akhir);
            unset($obj->periode_tahun_anggaran);

            return $obj;
        });

        $anggaranAllTagging = MvRenjaBase::where('tahun', $tahun)
                                            ->sum('total_alokasi');
        $anggaranSystemTagging = MvRenjaBase::where('tahun', $tahun)
                                            ->where('tagging', 0)
                                            ->sum('total_alokasi');

        $anggaranKlTagging = MvRenjaBase::where('tahun', $tahun)
                                            ->where('tagging', 1)
                                            ->sum('total_alokasi');
        

        $data = new \stdClass;
        $data->total_kegiatan_stunting = new \stdClass;
        $data->total_kegiatan_stunting->kl = $kementerianNow;
        $data->total_kegiatan_stunting->kl_deviasi_tahun_sebelumnya = $kementerianNow - $kementerianBefore;
        $data->total_kegiatan_stunting->program = $programNow;
        $data->total_kegiatan_stunting->program_deviasi_tahun_sebelumnya = $programNow - $programBefore;
        $data->total_kegiatan_stunting->kegiatan = $kegiatanNow;
        $data->total_kegiatan_stunting->kegiatan_deviasi_tahun_sebelumnya = $kegiatanNow - $kegiatanBefore;
        $data->total_kegiatan_stunting->kro = $outputNow;
        $data->total_kegiatan_stunting->kro_deviasi_tahun_sebelumnya = $outputNow - $outputBefore;
        $data->total_kegiatan_stunting->ro = $subOutputNow;
        $data->total_kegiatan_stunting->ro_deviasi_tahun_sebelumnya = $subOutputNow - $subOutputBefore;

        $data->total_anggaran_stunting_tagging_kl = $anggaranKlTagging;
        $data->total_anggaran_stunting_tagging_kl_system = $anggaranAllTagging;
        $data->selisih_total_anggaran_stunting_tagging_kl_system = ($anggaranSystemTagging/$anggaranAllTagging) * 100;
        $data->total_anggaran_stunting_tagging_system_non_tagging_kl = $anggaranSystemTagging;
        $data->selisih_total_anggaran_stunting_non_tagging_kl = (($anggaranKlTagging-$anggaranSystemTagging)/$anggaranAllTagging) * 100;

        $data->tahun = $tahun;
        
        $data->data_version = $periode;

        return $this->returnJsonSuccess("Data fetched successfully", $data);

    }

    public function getPeta(Request $request){
        $tahun = MvRenjaBase::max('tahun');

        if($request->has('tahun')){
            $tahun = $request->tahun;
        }

        $peta = PetaLokasi::select('id', 'shape')
                            ->with(['properties'])
                            ->whereHas('kegiatan', function ($query) use($tahun) {
                                $query->where('tahun', $tahun);
                            })
                            ->get();
        
        $peta = $peta->map(function($obj){
            $ret = json_decode(json_encode($obj->shape), true);
            $ret['properties'] = $obj->properties;
            
            return $ret;
        });
        return $this->returnJsonSuccess("Data fetched successfully", $peta);
    }

    public function getPetaDetail(Request $request, $lokasiId){
        $tahun = MvRenjaBase::max('tahun');

        if($request->has('tahun')){
            $tahun = $request->tahun;
        }

        $data = MvDmRenjakl::with(['intervensi'])
                        ->where('id', $lokasiId)
                        ->get();

        $kementerian = $data->pluck('kementerian_nama', 'kementerian_id')->unique();

        $kementerian = $kementerian->map(function($valueKementerian, $keyKementerian) use($data){
            
            $dataKementerian = $data->filter(function ($value, $key) use ($keyKementerian) {
                return $value->kementerian_id == $keyKementerian;
            });

            $objKementerian = new \stdClass;
            $objKementerian->id = $keyKementerian;
            $objKementerian->nama = $valueKementerian;
            $objKementerian->anggaran = $dataKementerian->sum('alokasi');
            
            $intervensi = $dataKementerian->pluck('intervensi_nama', 'intervensi_id')->unique();

            $intervensi = $intervensi->map(function($valueIntervensi, $keyIntervensi) use($dataKementerian, $keyKementerian){
                $dataIntervensi = $dataKementerian->filter(function ($value, $key) use ($keyKementerian, $keyIntervensi) {
                    return $value->kementerian_id == $keyKementerian && $value->intervensi_id == $keyIntervensi;
                });

                $objIntervensi = new \stdClass;
                $objIntervensi->id = $keyIntervensi;
                $objIntervensi->nama = $valueIntervensi;
                if(count($dataIntervensi) >0){
                    $objIntervensi->jenis = $dataIntervensi[0]->intervensi->tipe;
                }else{
                    $objIntervensi->jenis = "";
                }
                $objIntervensi->anggaran = $dataIntervensi->sum('alokasi');

                return $objIntervensi;
            })->values();

            $objKementerian->intervensi = $intervensi;

            return $objKementerian;
        })->values();

        $lokasi = Lokasi::find($lokasiId);
        $lokasi->data = new \stdClass;
        $lokasi->data->anggaran = new \stdClass;
        $lokasi->data->anggaran->menurut_kl = 0;
        $lokasi->data->anggaran->menurut_sistem = 0;
        $lokasi->data->anggaran->menurut_sistem_non_tagging_kl = 0;
        $lokasi->data->kementerian = $kementerian;

        return $this->returnJsonSuccess("Data fetched successfully", $lokasi);
    }
}

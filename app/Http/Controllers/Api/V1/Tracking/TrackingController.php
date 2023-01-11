<?php

namespace App\Http\Controllers\Api\V1\Tracking;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\RealisasiPenurunanStunting;
use App\DumpRenjaKl;
use App\DumpRenjaKlIntervensi;
use App\Kementerian;
use App\Intervensi;
use App\Tematik;
use App\Lokasi;
use App\LokasiRenja;

class TrackingController extends BaseController
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
        $lokasi = new Lokasi;

        $kegiatan = DumpRenjaKl::with(['intervensi', 'realisasi', 'realisasi.satker']);
        if($request->has('kementerian')){
            $kegiatan->whereIn('kementerian_kode', $request->kementerian);
        }

        if($request->has('tahun')){
            $kegiatan->whereIn('tahun', $request->tahun);
        }

        if($request->has('intervensi')){
            $kegiatan->whereHas('intervensi', function ($query) use($request){
                $query->whereIn('kode', $request->intervensi);
            });
        }

        $kegiatan->whereHas('realisasi');

        $kegiatan = $kegiatan->get();
        // dd($kegiatan->where('kode', '2020.024.06.5832.008.001')->toArray());
        // $lokasiKegiatanProvinsi = $kegiatan->pluck('realisasi.satker.attrs.provinsi_id')->unique();
        $lokasiKegiatanKabupaten = $kegiatan->pluck('realisasi.satker.attrs.kabupaten_id')->unique();

        // $tahunKegiatan = $kegiatan->pluck('tahun');
        // var_dump($lokasiKegiatanKabupaten);

        // $allLokasiKegiatan = $lokasiKegiatanProvinsi;
        // $allLokasiKegiatan = $allLokasiKegiatan->merge($lokasiKegiatanKabupaten);
        $allLokasiKegiatan = $lokasiKegiatanKabupaten;

        $allLokasiKegiatan = LokasiRenja::select('id', 'parent_id', 'kode')
                                         ->whereIn('id', $allLokasiKegiatan)
                                         ->get();
        

        $allLokasiKegiatan = $allLokasiKegiatan->map(function($obj){
            // var_dump(substr($obj->kode, 2, 4)."-".$obj->kode);
            if(substr($obj->kode, 2, 4) == "00"){
                $lokasiPrioritas = Lokasi::select('lokasi_renja_id')
                                         ->with(['lokasiPrioritas'])
                                         ->whereHas('lokasiPrioritas')
                                         ->where('kode_bps', 'like', substr($obj->kode, 0, 2)."%")
                                         ->where('level', 'district')
                                         ->first();
                // var_dump($lokasiPrioritas->toArray());
                if($lokasiPrioritas != null){
                    $obj->id = $lokasiPrioritas->lokasi_renja_id;
                }
            }
            return $obj;
        });
        

        $allLokasiKegiatan = $allLokasiKegiatan->pluck('id');
        // dd($lokasiKegiatanKabupaten);
        

        if($request->has('parentLokasi') && $request->parentLokasi != "all"){
            //sementara ambil lokasi renja satker dimana lokasi ini belum tentu lokasi kegiatan
            $parentLokasi = Lokasi::where('kode_kemendagri', $request->parentLokasi)->first();
            $lokasi = $lokasi->where('parent_id_kemendagri', $parentLokasi->id);
        }else{
            if($request->has('defaultLevel')){
                if($request->defaultLevel == "district"){
                    $lokasi = $lokasi->where('level', 'district');
                }else{
                    $lokasi = $lokasi->where('level', 'province');
                }
            }else{
                $lokasi = $lokasi->where('level', 'province');
            }
        }

        // dd($allLokasiKegiatan);
        $lokasi = $lokasi->with('lokasiPrioritas')->whereIn('lokasi_renja_id', $allLokasiKegiatan);
        // dd($lokasi->get());
        $lokasi = $lokasi->get()->map(function($obj) use($request, $kegiatan, $allLokasiKegiatan){
            $newObj = new \StdClass;
            $newObj->type = "Feature";
            $newObj->geometry = $obj->shape;
            unset($obj->shape);

            if($obj->lokasiPrioritas != null){
                $obj->is_prioritas = true;
            }else{
                $obj->is_prioritas = false;
            }

            unset($obj->lokasiPrioritas);

            $newObj->properties = $obj;

            return $newObj;

            $kegiatanTempX = $kegiatan->filter(function ($value, $key) use ($obj) {
                $attrs = (object)$value->realisasi->satker->attrs;
                return ($attrs->provinsi_id == $obj->lokasi_renja_id) || ($attrs->kabupaten_id == $obj->lokasi_renja_id);
            });

            $tahunSeries = $kegiatanTempX->pluck('tahun')->unique();

            $lTahun = [];

            foreach($tahunSeries as $keyTahun => $valueTahun){
                $kegiatanTemp = $kegiatanTempX->filter(function ($value, $key) use ($obj, $valueTahun) {
                    return $value->tahun == $valueTahun;
                });

                $objTahun = new \stdClass;

                $kementerianList = $kegiatanTemp->pluck('kementerian_nama', 'kementerian_kode')->unique();
                $kegiatanGroupByKementerian = $kegiatanTemp->groupBy('kementerian_kode');

                $lKementerian = [];
                foreach ($kementerianList as $key => $value) {
                    $kegiatanByKementerian = $kegiatanGroupByKementerian[$key];
                    $kementerian = new \stdClass;
                    $kementerian->kementerian_kode = $key;
                    $kementerian->kementerian_nama = $value;

                    $totalAlokasiKementerian = 0;
                    $totalRealisasiKementerian = 0;

                    $intervensi = [];
                    foreach ($kegiatanByKementerian as $objKegiatanByKem) {
                        // dd($objKegiatanByKem->intervensi);
                        $intervensi = array_merge($intervensi, $objKegiatanByKem->intervensi->toArray());

                        $totalAlokasiKementerian += floatval($objKegiatanByKem->realisasi->alokasi_anggaran);
                        $totalRealisasiKementerian += floatval($objKegiatanByKem->realisasi->realisasi_anggaran);
                    }

                    $intervensi = collect($intervensi)->map(function($objIntervensi) use($kegiatanByKementerian){
                        $objIntervensi = (object)$objIntervensi;
                        unset($objIntervensi->pivot);

                        $totalAlokasi = 0;
                        $totalRealisasi = 0;
                        foreach($kegiatanByKementerian as $objKegiatanByKem){
                            if($objKegiatanByKem->intervensi[0]->id == $objIntervensi->id){
                                $totalAlokasi += floatval($objKegiatanByKem->realisasi->alokasi_anggaran);
                                $totalRealisasi += floatval($objKegiatanByKem->realisasi->realisasi_anggaran);
                            }
                        }

                        $objIntervensi->total_alokasi = $totalAlokasi;
                        $objIntervensi->total_realisasi = $totalRealisasi;


                        return $objIntervensi;
                    })
                    ->unique()
                    ->values();

                    $kementerian->total_alokasi = $totalAlokasiKementerian;
                    $kementerian->total_realisasi = $totalRealisasiKementerian;
                    $kementerian->intervensi = $intervensi;
                    $kementerian->detail = $kegiatanByKementerian;

                    $lKementerian[] = $kementerian;
                }

                $objTahun->tahun = $valueTahun;
                $objTahun->kementerian = $lKementerian;

                $lTahun[] = $objTahun;
            }

            //Only for akal2 an lokasi
            // if(count($kegiatanTemp) <= 0){
            //     $lokasiParent = Lokasi::find($obj->parent_id_bps);

            //     $kegiatanTemp = $kegiatan->filter(function ($value, $key) use ($lokasiParent) {
            //         $attrs = (object)$value->realisasi->satker->attrs;
            //         return ($attrs->provinsi_id == $lokasiParent->lokasi_renja_id);
            //     });
            // }

            $obj->data = $lTahun;
            // $obj->kegiatan = $kegiatanTemp->values()->all();

            return $obj;
        });

        // $lokasi = $lokasi->filter(function($value, $key) {
        //     return  $value != null && count($value->data) > 0;
        // });

        return $this->returnJsonSuccess("Data fetched successfully", $lokasi);
    }

    public function tahun(Request $request){
        $tahun = DumpRenjaKl::select('tahun')->groupBy('tahun')->orderBy('tahun')->get()->pluck('tahun');

        return $this->returnJsonSuccess("Data fetched successfully", $tahun);
    }

    public function byIntervensi(Request $request){

        $lokasi=[];
        if($request->has('lokasi')){
            //Ambil lokasi akal2an
            $kegiatan = DumpRenjaKl::with(['intervensi', 'realisasi', 'realisasi.satker']);
            if($request->has('kementerian')){
                $kegiatan->whereIn('kementerian_kode', $request->kementerian);
            }

            if($request->has('tahun')){
                $kegiatan->whereIn('tahun', $request->tahun);
            }

            $kegiatan->whereHas('realisasi');

            $kegiatan = $kegiatan->get();
            // dd($kegiatan->toArray());
            $lokasiKegiatanProvinsi = $kegiatan->pluck('realisasi.satker.attrs.provinsi_id')->unique();
            $lokasiKegiatanKabupaten = $kegiatan->pluck('realisasi.satker.attrs.kabupaten_id')->unique();
            // var_dump($lokasiKegiatanKabupaten);

            $allLokasiKegiatan = $lokasiKegiatanProvinsi;
            $allLokasiKegiatan = $allLokasiKegiatan->merge($lokasiKegiatanKabupaten);

            $allLokasiKegiatan = LokasiRenja::select('id', 'parent_id', 'kode')
                                            ->whereIn('id', $allLokasiKegiatan)
                                            ->get();
            

            $allLokasiKegiatan = $allLokasiKegiatan->map(function($obj){
                // var_dump(substr($obj->kode, 2, 4)."-".$obj->kode);
                $obj->lokasi_akalan = $obj->id;
                if(substr($obj->kode, 2, 4) == "00"){
                    $lokasiPrioritas = Lokasi::select('lokasi_renja_id')
                                            ->with(['lokasiPrioritas'])
                                            ->whereHas('lokasiPrioritas')
                                            ->where('kode_bps', 'like', substr($obj->kode, 0, 2)."%")
                                            ->where('level', 'district')
                                            ->first();
                    // var_dump($lokasiPrioritas->toArray());
                    if($lokasiPrioritas != null){
                        $obj->lokasi_akalan = $lokasiPrioritas->lokasi_renja_id;
                    }
                }
                return $obj;
            });

            $allLokasiKegiatan = $allLokasiKegiatan->pluck('id', 'lokasi_akalan');
            foreach ($request->lokasi as $key => $value) {
                // dd(array_key_exists($value, $allLokasiKegiatan));
                if(isset($allLokasiKegiatan[$value]))
                    $lokasi[] = $allLokasiKegiatan[$value];
            }

            //End of lokasi akal2an
        }

        // dd($lokasi);

        $intervensi = Intervensi::with(['kegiatan' => function($query) use($lokasi, $request){
                                            $query->whereHas('realisasi', function($queryRealisasi) use($lokasi, $request){
                                                $queryRealisasi->whereHas('satker', function($querySatker) use ($lokasi){
                                                    $querySatker->whereIn('attrs->provinsi_id', $lokasi);
                                                    $querySatker->orWhereIn('attrs->kabupaten_id', $lokasi);
                                                });
                                            });

                                            if($request->has('tahun')){
                                                $query->whereIn('tahun', $request->tahun);
                                            }
        
                                            if($request->has('kementerian')){
                                                $query->whereIn('kementerian_kode', $request->kementerian);
                                            }
                                        },
                                    'kegiatan.realisasi',
                                    'kegiatan.realisasi.satker'
                                    ])
                                ->get();
        
        $intervensi = $intervensi->filter(function ($value, $key) {
            return count($value->kegiatan) > 0;
        })->values();

        return $this->returnJsonSuccess("Data fetched successfully", $intervensi);
    }

    public function byKementerian(Request $request){

        $lokasi=[];
        if($request->has('lokasi')){
            //Ambil lokasi akal2an
            $kegiatan = DumpRenjaKl::with(['intervensi', 'realisasi', 'realisasi.satker']);
            if($request->has('kementerian')){
                $kegiatan->whereIn('kementerian_kode', $request->kementerian);
            }

            if($request->has('tahun')){
                $kegiatan->whereIn('tahun', $request->tahun);
            }

            $kegiatan->whereHas('realisasi');

            $kegiatan = $kegiatan->get();
            // dd($kegiatan->toArray());
            $lokasiKegiatanProvinsi = $kegiatan->pluck('realisasi.satker.attrs.provinsi_id')->unique();
            $lokasiKegiatanKabupaten = $kegiatan->pluck('realisasi.satker.attrs.kabupaten_id')->unique();
            // var_dump($lokasiKegiatanKabupaten);

            $allLokasiKegiatan = $lokasiKegiatanProvinsi;
            $allLokasiKegiatan = $allLokasiKegiatan->merge($lokasiKegiatanKabupaten);

            $allLokasiKegiatan = LokasiRenja::select('id', 'parent_id', 'kode')
                                            ->whereIn('id', $allLokasiKegiatan)
                                            ->get();
            

            $allLokasiKegiatan = $allLokasiKegiatan->map(function($obj){
                // var_dump(substr($obj->kode, 2, 4)."-".$obj->kode);
                $obj->lokasi_akalan = $obj->id;
                if(substr($obj->kode, 2, 4) == "00"){
                    $lokasiPrioritas = Lokasi::select('lokasi_renja_id')
                                            ->with(['lokasiPrioritas'])
                                            ->whereHas('lokasiPrioritas')
                                            ->where('kode_bps', 'like', substr($obj->kode, 0, 2)."%")
                                            ->where('level', 'district')
                                            ->first();
                    // var_dump($lokasiPrioritas->toArray());
                    if($lokasiPrioritas != null){
                        $obj->lokasi_akalan = $lokasiPrioritas->lokasi_renja_id;
                    }
                }
                return $obj;
            });

            $allLokasiKegiatan = $allLokasiKegiatan->pluck('id', 'lokasi_akalan');
            foreach ($request->lokasi as $key => $value) {
                // dd(array_key_exists($value, $allLokasiKegiatan));
                if(isset($allLokasiKegiatan[$value]))
                    $lokasi[] = $allLokasiKegiatan[$value];
            }

            //End of lokasi akal2an
        }

        // dd($lokasi);

        $kegiatan = DumpRenjaKl::with(['intervensi', 'realisasi', 'realisasi.satker']);
        if($request->has('kementerian')){
            $kegiatan->whereIn('kementerian_kode', $request->kementerian);
        }

        if($request->has('tahun')){
            $kegiatan->whereIn('tahun', $request->tahun);
        }

        if($request->has('intervensi')){
            $kegiatan->whereHas('intervensi', function ($query) use($request){
                $query->whereIn('kode', $request->intervensi);
            });
        }

        $kegiatan->whereHas('realisasi', function($queryRealisasi) use($lokasi, $request){
            $queryRealisasi->whereHas('satker', function($querySatker) use ($lokasi){
                $querySatker->whereIn('attrs->provinsi_id', $lokasi);
                $querySatker->orWhereIn('attrs->kabupaten_id', $lokasi);
            });
        });

        $kegiatan = $kegiatan->get();

        $lsKementerian = collect($kegiatan->pluck('kementerian_nama', 'kementerian_kode'));
        $kementerian = [];
        foreach($lsKementerian as $keyKementerian => $valueKementerian){
            $_kementerian = new \stdClass;

            $_kementerian->kementerian_kode = $keyKementerian;
            $_kementerian->kementerian_nama = $valueKementerian;

            $dataKegiatan = $kegiatan->filter(function ($value, $key) use($keyKementerian) {
                return $value->kementerian_kode == $keyKementerian;
            })->values();

            $totalAlokasi = 0;
            $totalRealisasi = 0;

            foreach($dataKegiatan as $keyKegiatan => $valueKegiatan){
                $totalAlokasi += floatval($valueKegiatan->realisasi->alokasi_anggaran);
                $totalRealisasi += floatval($valueKegiatan->realisasi->realisasi_anggaran);
            }

            $_kementerian->total_alokasi_anggaran = $totalAlokasi;
            $_kementerian->total_realisasi_anggaran = $totalRealisasi;

            $_kementerian->kegiatan = $dataKegiatan;

            $kementerian[] = $_kementerian;
        }

        return $this->returnJsonSuccess("Data fetched successfully", $kementerian);
    }

    public function byTahun(Request $request){

        $lokasi=[];
        if($request->has('lokasi')){
            //Ambil lokasi akal2an
            $kegiatan = DumpRenjaKl::with(['intervensi', 'realisasi', 'realisasi.satker']);
            if($request->has('kementerian')){
                $kegiatan->whereIn('kementerian_kode', $request->kementerian);
            }

            if($request->has('tahun')){
                $kegiatan->whereIn('tahun', $request->tahun);
            }

            $kegiatan->whereHas('realisasi');

            $kegiatan = $kegiatan->get();
            // dd($kegiatan->toArray());
            $lokasiKegiatanProvinsi = $kegiatan->pluck('realisasi.satker.attrs.provinsi_id')->unique();
            $lokasiKegiatanKabupaten = $kegiatan->pluck('realisasi.satker.attrs.kabupaten_id')->unique();
            // var_dump($lokasiKegiatanKabupaten);

            $allLokasiKegiatan = $lokasiKegiatanProvinsi;
            $allLokasiKegiatan = $allLokasiKegiatan->merge($lokasiKegiatanKabupaten);

            $allLokasiKegiatan = LokasiRenja::select('id', 'parent_id', 'kode')
                                            ->whereIn('id', $allLokasiKegiatan)
                                            ->get();
            

            $allLokasiKegiatan = $allLokasiKegiatan->map(function($obj){
                // var_dump(substr($obj->kode, 2, 4)."-".$obj->kode);
                $obj->lokasi_akalan = $obj->id;
                if(substr($obj->kode, 2, 4) == "00"){
                    $lokasiPrioritas = Lokasi::select('lokasi_renja_id')
                                            ->with(['lokasiPrioritas'])
                                            ->whereHas('lokasiPrioritas')
                                            ->where('kode_bps', 'like', substr($obj->kode, 0, 2)."%")
                                            ->where('level', 'district')
                                            ->first();
                    // var_dump($lokasiPrioritas->toArray());
                    if($lokasiPrioritas != null){
                        $obj->lokasi_akalan = $lokasiPrioritas->lokasi_renja_id;
                    }
                }
                return $obj;
            });

            $allLokasiKegiatan = $allLokasiKegiatan->pluck('id', 'lokasi_akalan');
            foreach ($request->lokasi as $key => $value) {
                // dd(array_key_exists($value, $allLokasiKegiatan));
                if(isset($allLokasiKegiatan[$value]))
                    $lokasi[] = $allLokasiKegiatan[$value];
            }

            //End of lokasi akal2an
        }

        // dd($lokasi);

        $kegiatan = DumpRenjaKl::with(['intervensi', 'realisasi', 'realisasi.satker']);
        if($request->has('kementerian')){
            $kegiatan->whereIn('kementerian_kode', $request->kementerian);
        }

        if($request->has('tahun')){
            $kegiatan->whereIn('tahun', $request->tahun);
        }

        if($request->has('intervensi')){
            $kegiatan->whereHas('intervensi', function ($query) use($request){
                $query->whereIn('kode', $request->intervensi);
            });
        }

        $kegiatan->whereHas('realisasi', function($queryRealisasi) use($lokasi, $request){
            $queryRealisasi->whereHas('satker', function($querySatker) use ($lokasi){
                $querySatker->whereIn('attrs->provinsi_id', $lokasi);
                $querySatker->orWhereIn('attrs->kabupaten_id', $lokasi);
            });
        });

        $kegiatan = $kegiatan->get();

        $lsTahun = collect($kegiatan->pluck('tahun'));
        $tahunData = [];
        foreach($lsTahun as $tahun){
            $_tahun = new \stdClass;

            $_tahun->tahun = $tahun;

            $dataKegiatan = $kegiatan->filter(function ($value, $key) use($tahun) {
                return $value->tahun == $tahun;
            })->values();

            $totalAlokasi = 0;
            $totalRealisasi = 0;

            foreach($dataKegiatan as $keyKegiatan => $valueKegiatan){
                $totalAlokasi += floatval($valueKegiatan->realisasi->alokasi_anggaran);
                $totalRealisasi += floatval($valueKegiatan->realisasi->realisasi_anggaran);
            }

            $_tahun->total_alokasi_anggaran = $totalAlokasi;
            $_tahun->total_realisasi_anggaran = $totalRealisasi;

            $_tahun->kegiatan = $dataKegiatan;

            $tahunData[] = $_tahun;
        }

        return $this->returnJsonSuccess("Data fetched successfully", $tahunData);
    }
}

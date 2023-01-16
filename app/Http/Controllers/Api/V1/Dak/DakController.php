<?php

namespace App\Http\Controllers\Api\V1\Dak;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\Staging\MvDak;
use Carbon\Carbon;
use App\Models\Staging\VDak;

class DakController extends BaseController
{
    public function tahun(Request $request){
        $tahun = MvDak::select('tahun')->groupBy('tahun')->get()->pluck('tahun');
        return $this->returnJsonSuccess("Data fetched successfully", $tahun);
    }


    public function kabupaten(Request $request){
      //  $kabupaten = MvDak::select('pemda_kode','pemda_nama',"CONCAT(prov_kode,'-',prov_nama)")->whereNotIn('pemda_kode',\DB::table('stagging.dak_filter_lokus')->where('tahun',$request->tahun)->pluck('pemda_kode'))->where(\DB::raw('SUBSTRING(pemda_kode,3,4)'), '!=', "00")->where('tahun',$request->tahun)->groupBy('pemda_kode','pemda_nama','prov_kode')->get();
        $kabupaten = MvDak::select(\DB::raw("pemda_kode,pemda_nama,CONCAT(prov_kode,'-',prov_nama) as provinsi"))->whereNotIn('pemda_kode',\DB::table('stagging.dak_filter_lokus')->where('tahun',$request->tahun)->pluck('pemda_kode'))->where(\DB::raw('SUBSTRING(pemda_kode,3,4)'), '!=', "00")->where('tahun',$request->tahun)->groupBy('pemda_kode','pemda_nama','provinsi')->get();
      return $this->returnJsonSuccess("Data fetched successfully", $kabupaten);
    }

    public function provinsi(Request $request){
        $provinsi = MvDak::select('prov_kode','prov_nama')->where('tahun',$request->tahun)->groupBy('prov_kode','prov_nama')->get();
        return $this->returnJsonSuccess("Data fetched successfully", $provinsi);
    }

    public function kementerian(Request $request){
        $kementerian = MvDak::select('kementerian_kode', 'kementerian_nama')->groupBy('kementerian_kode', 'kementerian_nama')->get();
        return $this->returnJsonSuccess("Data fetched successfully", $kementerian);
    }

    public function bidang(Request $request){
        $intervensi = MvDak::select('bidang_kode', 'bidang_nama')->groupBy('bidang_kode', 'bidang_nama')->get();
        return $this->returnJsonSuccess("Data fetched successfully", $intervensi);
    }


    public function dataByBidang(Request $request){
        $search = "";
        $tahun = $request->tahun;
        if($request->has('search') && !empty($request->search)){
            $search = strtolower($request->search);
        }

        $dataBidang = MvDak::select('tahun', 
                                'bidang_kode', 
                                'bidang_nama',
                                \DB::raw('SUM(nilai_rk) grand_total'))
        ->where(function($q) use($request){
            if($request->has('tahun')){
                $q->whereIn('tahun', $request->tahun);

                if($request->tahun[0] == '2021'){
                    $q->where('tematik_kode','02');
                    $q->whereNotIn(\DB::raw('CONCAT(bidang_kode,sub_bidang_kode)'),function($query) use ($request){
                        $query->select('kode')->from('stagging.dak_filter')->where('tahun',$request->tahun[0]);
                    });
                }
            }

            if($request->has('kl')){
                $q->whereIn('kementerian_kode', $request->kl);
            }

            if($request->has('bidang')){
                $q->whereIn('bidang_kode', $request->bidang);
            }
        })
        ->where(function ($q) use($search){
            if(!empty($search)){
                $q->where(\DB::raw('LOWER(bidang_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kementerian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(sub_bidang_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(menu_kegiatan_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pn_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pp_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(tematik_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kewenangan)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(jenis)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(rincian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(detail_rincian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pemda_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(prov_nama)'), 'LIKE', "%$search%");
             }
        })
        ->groupBy('bidang_kode', 'bidang_nama', 'tahun')
        ->get();
      //  echo $dataBidang;
      //   exit;

        return $this->returnJsonSuccess("Data fetched successfully", $dataBidang);

    }

    public function dataByTematik(Request $request){
        $search = "";
        $tahun = $request->tahun;
        if($request->has('search') && !empty($request->search)){
            $search = strtolower($request->search);
        }

        $data = MvDak::select('tahun', 
                'tematik_kode',
                'tematik_nama',
                'nilai_rk')
        ->where(function($q) use($request){
            if($request->has('tahun')){
                $q->whereIn('tahun', $request->tahun);
                if($request->tahun[0] == '2021'){
                    $q->where('tematik_kode','02');
                    $q->whereNotIn(\DB::raw('CONCAT(bidang_kode,sub_bidang_kode)'),function($query) use ($request){
                        $query->select('kode')->from('stagging.dak_filter')->where('tahun',$request->tahun[0]);
                    });
                }
            }

            if($request->has('kl')){
                $q->whereIn('kementerian_kode', $request->kl);
            }

            if($request->has('bidang')){
                $q->whereIn('bidang_kode', $request->bidang);
            }
        })
        ->where(function ($q) use($search){
            if(!empty($search)){
                $q->where(\DB::raw('LOWER(bidang_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kementerian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(sub_bidang_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(menu_kegiatan_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pn_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pp_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(tematik_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kewenangan)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(jenis)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(rincian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(detail_rincian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pemda_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(prov_nama)'), 'LIKE', "%$search%");
             }
        })
        ->get();

        $dataClone = clone $data;

        $dataTematik = $dataClone->map->only(['tematik_kode', 'tematik_nama'])->unique()->values();

        $dataTematik = $dataTematik->map(function($objDataTematik) use ($dataClone, $tahun){
            $_dataTematik = $dataClone->filter(function ($obj) use($objDataTematik) {
                return $obj->tematik_kode == $objDataTematik['tematik_kode'] && 
                        $obj->tematik_nama == $objDataTematik['tematik_nama'];
            });
            foreach($tahun as $vTahun){
                $dataTahunTematik = $_dataTematik->filter(function ($obj) use($objDataTematik, $vTahun) {
                    return $obj->tahun == $vTahun;
                });
                $objDataTematik['nilai_rk_'.$vTahun] = $dataTahunTematik->sum('nilai_rk');
            }

            $objDataTematik['grand_total'] = $_dataTematik->sum('nilai_rk');

            return $objDataTematik;
        });

        return $this->returnJsonSuccess("Data fetched successfully", $dataTematik);

    }

    public function dataByProvinsiPemda(Request $request){
        $search = "";
        $tahun = $request->tahun;
        if($request->has('search') && !empty($request->search)){
            $search = strtolower($request->search);
        }

        $data = MvDak::select('tahun', 
                'nilai_rk',
                'pemda_kode',
                'pemda_nama',
                'prov_kode',
                'prov_nama')
        ->where(function($q) use($request){
            if($request->has('tahun')){
                $q->whereIn('tahun', $request->tahun);
                if($request->tahun[0] == '2021'){
                    $q->where('tematik_kode','02');
                    $q->whereNotIn(\DB::raw('CONCAT(bidang_kode,sub_bidang_kode)'),function($query) use ($request){
                        $query->select('kode')->from('stagging.dak_filter')->where('tahun',$request->tahun[0]);
                    });
                }
            }

            if($request->has('kl')){
                $q->whereIn('kementerian_kode', $request->kl);
            }

            if($request->has('bidang')){
                $q->whereIn('bidang_kode', $request->bidang);
            }
        })
        ->where(function ($q) use($search){
            if(!empty($search)){
                $q->where(\DB::raw('LOWER(bidang_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kementerian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(sub_bidang_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(menu_kegiatan_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pn_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pp_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(tematik_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kewenangan)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(jenis)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(rincian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(detail_rincian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pemda_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(prov_nama)'), 'LIKE', "%$search%");
             }
        })
        ->get();

        $dataClone = clone $data;

        $dataProv = $dataClone->map->only(['prov_kode', 'prov_nama'])->unique()->values();

        $dataProv = $dataProv->map(function($objDataProv) use ($dataClone, $tahun){
            $_dataProv = $dataClone->filter(function ($obj) use($objDataProv) {
                return $obj->prov_kode == $objDataProv['prov_kode'] && 
                        $obj->prov_nama == $objDataProv['prov_nama'];
            });

            $dataPemda = $_dataProv->map->only(['pemda_kode', 'pemda_nama'])->unique()->values();

            foreach($tahun as $vTahun){
                $dataTahunProv = $_dataProv->filter(function ($obj) use($objDataProv, $vTahun) {
                    return $obj->tahun == $vTahun;
                });
                $objDataProv['nilai_rk_'.$vTahun] = $dataTahunProv->sum('nilai_rk');
            }

            $objDataProv['grand_total'] = $_dataProv->sum('nilai_rk');

            $objDataProv['_children'] = $dataPemda->map(function($objDataPemda) use ($_dataProv, $tahun){
                $_dataPemda = $_dataProv->filter(function ($obj) use($objDataPemda) {
                    return $obj->pemda_kode == $objDataPemda['pemda_kode'] && 
                            $obj->pemda_nama == $objDataPemda['pemda_nama'];
                });

                foreach($tahun as $vTahun){
                    $dataTahunProv = $_dataPemda->filter(function ($obj) use($objDataPemda, $vTahun) {
                        return $obj->tahun == $vTahun;
                    });
                    $objDataPemda['nilai_rk_'.$vTahun] = $dataTahunProv->sum('nilai_rk');
                }
    
                $objDataPemda['grand_total'] = $_dataPemda->sum('nilai_rk');
                
                return $objDataPemda;
            });

            return $objDataProv;
        });

        return $this->returnJsonSuccess("Data fetched successfully", $dataProv);

    }

    public function dataByKementerianTingpel(Request $request){

        $search = "";
        $tahun = $request->tahun;
        if($request->has('search') && !empty($request->search)){
            $search = strtolower($request->search);
        }

        $data = MvDak::select('tahun', 
                'kementerian_kode',
                'kementerian_nama',
                'nilai_rk',
                'tingkat_pelaksanaan')
        ->where(function($q) use($request){
            if($request->has('tahun')){
                $q->whereIn('tahun', $request->tahun);
                if($request->tahun[0] == '2021'){
                    $q->where('tematik_kode','02');
                    $q->whereNotIn(\DB::raw('CONCAT(bidang_kode,sub_bidang_kode)'),function($query) use ($request){
                        $query->select('kode')->from('stagging.dak_filter')->where('tahun',$request->tahun[0]);
                    });
                }
            }

            if($request->has('kl')){
                $q->whereIn('kementerian_kode', $request->kl);
            }

            if($request->has('bidang')){
                $q->whereIn('bidang_kode', $request->bidang);
            }
        })
        ->where(function ($q) use($search){
            if(!empty($search)){
                $q->where(\DB::raw('LOWER(bidang_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kementerian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(sub_bidang_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(menu_kegiatan_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pn_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pp_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(tematik_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kewenangan)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(jenis)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(rincian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(detail_rincian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pemda_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(prov_nama)'), 'LIKE', "%$search%");
             }
        })
        ->get();

        $dataClone = clone $data;

        $dataKementerian = $dataClone->map->only(['kementerian_kode', 'kementerian_nama'])->unique()->values();

        $dataKementerian = $dataKementerian->map(function($objDataKementerian) use ($dataClone, $tahun){
            $_dataKementerian = $dataClone->filter(function ($obj) use($objDataKementerian) {
                return $obj->kementerian_kode == $objDataKementerian['kementerian_kode'] && 
                        $obj->kementerian_nama == $objDataKementerian['kementerian_nama'];
            });

            $dataTingpel = $_dataKementerian->map->only(['tingkat_pelaksanaan'])->unique()->values();

            foreach($tahun as $vTahun){
                $dataTahunProv = $_dataKementerian->filter(function ($obj) use($objDataKementerian, $vTahun) {
                    return $obj->tahun == $vTahun;
                });
                $objDataKementerian['nilai_rk_'.$vTahun] = $dataTahunProv->sum('nilai_rk');
            }

            $objDataKementerian['grand_total'] = $_dataKementerian->sum('nilai_rk');

            $objDataKementerian['_children'] = $dataTingpel->map(function($objDataTingpel) use ($_dataKementerian, $tahun){
                $_dataTingpel = $_dataKementerian->filter(function ($obj) use($objDataTingpel) {
                    return $obj->tingkat_pelaksanaan == $objDataTingpel['tingkat_pelaksanaan'];
                });

                foreach($tahun as $vTahun){
                    $dataTahunProv = $_dataTingpel->filter(function ($obj) use($objDataTingpel, $vTahun) {
                        return $obj->tahun == $vTahun;
                    });
                    $objDataTingpel['nilai_rk_'.$vTahun] = $dataTahunProv->sum('nilai_rk');
                }
    
                $objDataTingpel['grand_total'] = number_format($_dataTingpel->sum('nilai_rk'),0,',','.');
                
                return $objDataTingpel;
            });

            return $objDataKementerian;
        });

        return $this->returnJsonSuccess("Data fetched successfully", $dataKementerian);

    }

    public function dataByTingpel(Request $request){
        $search = "";
        $tahun = $request->tahun;
        if($request->has('search') && !empty($request->search)){
            $search = strtolower($request->search);
        }

        $data = MvDak::select('tahun', 
                'nilai_rk',
                'tingkat_pelaksanaan')
        ->where(function($q) use($request){
            if($request->has('tahun')){
                $q->whereIn('tahun', $request->tahun);
                if($request->tahun[0] == '2021'){
                    $q->where('tematik_kode','02');
                    $q->whereNotIn(\DB::raw('CONCAT(bidang_kode,sub_bidang_kode)'),function($query) use ($request){
                        $query->select('kode')->from('stagging.dak_filter')->where('tahun',$request->tahun[0]);
                    });
                }
            }

            if($request->has('kl')){
                $q->whereIn('kementerian_kode', $request->kl);
            }

            if($request->has('bidang')){
                $q->whereIn('bidang_kode', $request->bidang);
            }
        })
        ->where(function ($q) use($search){
            if(!empty($search)){
                $q->where(\DB::raw('LOWER(bidang_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kementerian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(sub_bidang_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(menu_kegiatan_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pn_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pp_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(tematik_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kewenangan)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(jenis)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(rincian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(detail_rincian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pemda_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(prov_nama)'), 'LIKE', "%$search%");
             }
        })
        ->get();

        $dataClone = clone $data;

        $dataTingpel = $dataClone->map->only(['tingkat_pelaksanaan'])->unique()->values();

        $dataTingpel = $dataTingpel->map(function($objDataTingpel) use ($dataClone, $tahun){
            $_dataTingpel = $dataClone->filter(function ($obj) use($objDataTingpel) {
                return $obj->tingkat_pelaksanaan == $objDataTingpel['tingkat_pelaksanaan'];
            });
            foreach($tahun as $vTahun){
                $dataTahunTematik = $_dataTingpel->filter(function ($obj) use($objDataTingpel, $vTahun) {
                    return $obj->tahun == $vTahun;
                });
                $objDataTingpel['nilai_rk_'.$vTahun] = $dataTahunTematik->sum('nilai_rk');
            }

            $objDataTingpel['grand_total'] = $_dataTingpel->sum('nilai_rk');

            return $objDataTingpel;
        });

        return $this->returnJsonSuccess("Data fetched successfully", $dataTingpel);

    }

    public function dataByKementerian(Request $request){
        $search = "";
        $tahun = $request->tahun;
        if($request->has('search') && !empty($request->search)){
            $search = strtolower($request->search);
        }

        $data = MvDak::select('tahun', 
                'nilai_rk',
                'kementerian_kode',
                'kementerian_nama')
        ->where(function($q) use($request){
            if($request->has('tahun')){
                $q->whereIn('tahun', $request->tahun);
                if($request->tahun[0] == '2021'){
                    $q->where('tematik_kode','02');
                    $q->whereNotIn(\DB::raw('CONCAT(bidang_kode,sub_bidang_kode)'),function($query) use ($request){
                        $query->select('kode')->from('stagging.dak_filter')->where('tahun',$request->tahun[0]);
                    });
                }
            }

            if($request->has('kl')){
                $q->whereIn('kementerian_kode', $request->kl);
            }

            if($request->has('bidang')){
                $q->whereIn('bidang_kode', $request->bidang);
            }
        })
        ->where(function ($q) use($search){
            if(!empty($search)){
                $q->where(\DB::raw('LOWER(bidang_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kementerian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(sub_bidang_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(menu_kegiatan_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pn_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pp_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(tematik_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kewenangan)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(jenis)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(rincian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(detail_rincian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pemda_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(prov_nama)'), 'LIKE', "%$search%");
             }
        })
        ->get();

        $dataClone = clone $data;

        $dataTingpel = $dataClone->map->only(['kementerian_kode', 'kementerian_nama'])->unique()->values();

        $dataTingpel = $dataTingpel->map(function($objDataTingpel) use ($dataClone, $tahun){

            $_dataTingpel = $dataClone->filter(function ($obj) use($objDataTingpel) {
                return $obj->kementerian_kode == $objDataTingpel['kementerian_kode'] && $obj->kementerian_nama == $objDataTingpel['kementerian_nama'];
            });
            foreach($tahun as $vTahun){
                $dataTahunTematik = $_dataTingpel->filter(function ($obj) use($objDataTingpel, $vTahun) {
                    return $obj->tahun == $vTahun;
                });
                $objDataTingpel['nilai_rk_'.$vTahun] = $dataTahunTematik->sum('nilai_rk');
            }

            
            $objDataTingpel['grand_total'] = $_dataTingpel->sum('nilai_rk'); //number_format($_dataTingpel->sum('nilai_rk'),0,',','.');

            if($objDataTingpel['kementerian_kode'] == "024"){
                $objDataTingpel['kementerian_nama'] = "KEMKES";
            }else if($objDataTingpel['kementerian_kode'] == "033"){
                $objDataTingpel['kementerian_nama'] = "PUPR";
            }else if($objDataTingpel['kementerian_kode'] == "068"){
                $objDataTingpel['kementerian_nama'] = "BKKBN";
            }else if($objDataTingpel['kementerian_kode'] == "029"){
                $objDataTingpel['kementerian_nama'] = "KLHK";
            }

            return $objDataTingpel;
        })->sortBy([
            fn ($a, $b) => $a['grand_total'] < $b['grand_total']
        ]);

        return $this->returnJsonSuccess("Data fetched successfully", $dataTingpel);

    }

    public function dataByTahun(Request $request){
        $search = "";
        $tahun = $request->tahun;
        if($request->has('search') && !empty($request->search)){
            $search = strtolower($request->search);
        }


    
        // $data = MvDak::select('tahun', 
        //         \DB::raw('SUM(nilai_rk) nilai_rk'),
        //         \DB::raw('0 realisasi'))
        // ->where(function($q) use($request){
        //     if($request->has('tahun')){
        //         $q->whereIn('tahun', $request->tahun);
        //         if($request->tahun[0] == '2021'){
        //             $q->where('tematik_kode','02');
        //             $q->whereNotIn(\DB::raw('CONCAT(bidang_kode,sub_bidang_kode)'),function($query) use ($request){
        //                 $query->select('kode')->from('stagging.dak_filter')->where('tahun',$request->tahun[0]);
        //             });
        //         }
        //     }

        //     if($request->has('kl')){
        //         $q->whereIn('kementerian_kode', $request->kl);
        //     }

        //     if($request->has('bidang')){
        //         $q->whereIn('bidang_kode', $request->bidang);
        //     }
        // })
        // ->where(function ($q) use($search){
        //     if(!empty($search)){
        //         $q->where(\DB::raw('LOWER(bidang_nama)'), 'LIKE', "%$search%");
        //         $q->orWhere(\DB::raw('LOWER(kementerian_nama)'), 'LIKE', "%$search%");
        //         $q->orWhere(\DB::raw('LOWER(sub_bidang_nama)'), 'LIKE', "%$search%");
        //         $q->orWhere(\DB::raw('LOWER(menu_kegiatan_nama)'), 'LIKE', "%$search%");
        //         $q->orWhere(\DB::raw('LOWER(pn_nama)'), 'LIKE', "%$search%");
        //         $q->orWhere(\DB::raw('LOWER(pp_nama)'), 'LIKE', "%$search%");
        //         $q->orWhere(\DB::raw('LOWER(tematik_nama)'), 'LIKE', "%$search%");
        //         $q->orWhere(\DB::raw('LOWER(kewenangan)'), 'LIKE', "%$search%");
        //         $q->orWhere(\DB::raw('LOWER(jenis)'), 'LIKE', "%$search%");
        //         $q->orWhere(\DB::raw('LOWER(rincian_nama)'), 'LIKE', "%$search%");
        //         $q->orWhere(\DB::raw('LOWER(detail_rincian_nama)'), 'LIKE', "%$search%");
        //         $q->orWhere(\DB::raw('LOWER(pemda_nama)'), 'LIKE', "%$search%");
        //         $q->orWhere(\DB::raw('LOWER(prov_nama)'), 'LIKE', "%$search%");
        //      }
        // })
        // ->groupBy('tahun')->get();

        $data = \DB::table('stagging.v_rekap_dak')->orderBy('tahun','DESC')->get();
        //dd($data);

        $max = $data->max('nilai_rk');
        $res = new \stdClass;
        $res->max = $max;
        $res->chart_data = $data;


     //   dd($res);

        return $this->returnJsonSuccess("Data fetched successfully", $res);
    }

    public function getPetaDak(Request $request){
        $search = "";
        $tahun = $request->tahun;
        if($request->has('search') && !empty($request->search)){
            $search = strtolower($request->search);
        }

        // echo $tahun[0];
        // exit;

        $data = MvDak::select('id', 
                                'tahun',
                                'lon',
                                'lat',
                                'jenis',
                                'bidang_nama',
                                'sub_bidang_nama',
                                'menu_kegiatan_nama',
                                'rincian_nama',
                                'detail_rincian_nama',
                                'volume_rk',
                                'satuan',
                                'unit_cost_rk',
                                'nilai_rk',
                                'pemda_kode')
        ->with('mappingLokasi', 'mappingLokasi.lokasi')
        ->where(function($q) use($request){
            if($request->has('tahun')){
                $q->whereIn('tahun', $request->tahun);
            }

            if($request->has('kl')){
                $q->whereIn('kementerian_kode', $request->kl);
            }

            if($request->has('bidang')){
                $q->whereIn('bidang_kode', $request->bidang);
            }
        })
        ->whereNotNull('lat')
        ->whereNotNull('lon')
        ->where(function($q) use ($request){
            if($request->has('tahun')){
                $q->whereIn('tahun',$request->tahun);
                //$tahun = $request->tahun;
                $tahun = $request->tahun[0];
                if($request->tahun[0] == '2021'){
                    $q->where('tematik_kode','02');
                    $q->whereNotIn(\DB::raw('CONCAT(bidang_kode,sub_bidang_kode)'),function($query) use ($request){
                        $query->select('kode')->from('stagging.dak_filter')->where('tahun',$request->tahun[0]);
                    });
                }
            }
        })
        ->where(function ($q) use($search){
            if(!empty($search)){
                $q->where(\DB::raw('LOWER(bidang_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kementerian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(sub_bidang_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(menu_kegiatan_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pn_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pp_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(tematik_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kewenangan)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(jenis)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(rincian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(detail_rincian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pemda_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(prov_nama)'), 'LIKE', "%$search%");
             }
        })->get();

        $features = $data->map(function($objData)    {
            $objData = (object)$objData;
            $retData = new \stdClass;
            $retData->type = "Feature";
            $retData->geometry = new \stdClass;
            $retData->geometry->type = "Point";
            $retData->geometry->coordinates = [
                $objData->lon,
                $objData->lat
            ];
            $retData->properties = new \stdClass;
            $retData->properties->id = $objData->id;
            $retData->properties->tahun = $objData->tahun;
            $retData->properties->jenis = $objData->jenis;
            $retData->properties->bidang_nama = $objData->bidang_nama;
            $retData->properties->sub_bidang_nama = $objData->sub_bidang_nama;
            $retData->properties->menu_kegiatan_nama = $objData->menu_kegiatan_nama;
            $retData->properties->rincian_nama = $objData->rincian_nama;
            $retData->properties->detail_rincian_nama = $objData->detail_rincian_nama;
            $retData->properties->volume_rk = $objData->volume_rk;
            $retData->properties->satuan = $objData->satuan;
            $retData->properties->unit_cost_rk = $objData->unit_cost_rk;
            $retData->properties->nilai_rk = $objData->nilai_rk;
            $retData->properties->pemda_kode = $objData->mappingLokasi['lokasi']['kode_bps'];
            $retData->properties->pemda_nama = $objData->mappingLokasi['lokasi']['nama'];
            if($objData->mappingLokasi['lokasi']['provinsi_id']){
                $retData->properties->provinsi_kode = $objData->mappingLokasi['lokasi']['provinsi_id'];
                $retData->properties->provinsi_nama = $objData->mappingLokasi['lokasi']['provinsi_nama'];
            }else{
                $retData->properties->provinsi_kode = $objData->mappingLokasi['lokasi']['kode_bps'];
                $retData->properties->provinsi_nama = $objData->mappingLokasi['lokasi']['nama'];
            }

            return $retData;
        });

        $res = new \stdClass;
        $res->type = "FeatureCollection";
        $res->features = $features;

        return $this->returnJsonSuccessCheck("Data fetched successfully", $res);
    }

    public function dataTotalDak(Request $request){
        // $data = MvDak::select(\DB::raw('SUM(nilai_rk) total'))
        // // ->where(function($q) use($request){
        // //     if($request->has('tahun')){
        // //         $q->whereIn('tahun', $request->tahun);
        // //         if($request->tahun[0] == '2021'){
        // //             $q->where('tematik_kode','02');
        // //         }
        // //     }
        // // })
        // ->where(function($q) use ($request){
        //     if($request->has('tahun')){
        //         //$tahun = $request->tahun;
        //         $q->whereIn('tahun',$request->tahun);
        //         $tahun = $request->tahun[0];
        //         if($request->tahun[0] == '2021'){
        //             $q->where('tematik_kode','02');
        //             $q->whereNotIn(\DB::raw('CONCAT(bidang_kode,sub_bidang_kode)'),function($query) use ($request){
        //                 $query->select('kode')->from('stagging.dak_filter')->where('tahun',$request->tahun[0]);
        //             });
        //         }
        //     }
        // })
      //  ->first();

      $tahun = $request->tahun[0];
    //   echo $tahun;
    //   exit;
      $data = VDak::select('nilai_rk as total')->where('tahun',$tahun)->first();
      // echo $data;
      //exit;

        return $this->returnJsonSuccess("Data fetched successfully", $data->total);
    }

    public function getOnePage(Request $request){
        $search = "";
        $tahun = $request->tahun;
        if($request->has('search') && !empty($request->search)){
            $search = strtolower($request->search);
        }

        $dataTingpel = MvDak::select(
                \DB::raw('SUM(nilai_rk) grand_total'),
                'tingkat_pelaksanaan')
        ->where(function($q) use($request){
            if($request->has('tahun')){
                $q->whereIn('tahun', $request->tahun);
                if($request->tahun[0] == '2021'){
                    $q->where('tematik_kode','02');
                    $q->whereNotIn(\DB::raw('CONCAT(bidang_kode,sub_bidang_kode)'),function($query) use ($request){
                        $query->select('kode')->from('stagging.dak_filter')->where('tahun',$request->tahun[0]);
                    });
                }
            }

            if($request->has('kl')){
                $q->whereIn('kementerian_kode', $request->kl);
            }

            if($request->has('bidang')){
                $q->whereIn('bidang_kode', $request->bidang);
            }
        })
        ->where(function ($q) use($search){
            if(!empty($search)){
                $q->where(\DB::raw('LOWER(bidang_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kementerian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(sub_bidang_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(menu_kegiatan_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pn_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pp_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(tematik_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kewenangan)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(jenis)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(rincian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(detail_rincian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pemda_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(prov_nama)'), 'LIKE', "%$search%");
             }
        })
        ->groupBy('tingkat_pelaksanaan')
        ->get();

        $dataKementerian = MvDak::select(
                \DB::raw('SUM(nilai_rk) grand_total'),
                'kementerian_kode',
                'kementerian_nama')
        ->where(function($q) use($request){
            if($request->has('tahun')){
                $q->whereIn('tahun', $request->tahun);
                if($request->tahun[0] == '2021'){
                    $q->where('tematik_kode','02');
                    $q->whereNotIn(\DB::raw('CONCAT(bidang_kode,sub_bidang_kode)'),function($query) use ($request){
                        $query->select('kode')->from('stagging.dak_filter')->where('tahun',$request->tahun[0]);
                    });
                }
            }

            if($request->has('kl')){
                $q->whereIn('kementerian_kode', $request->kl);
            }

            if($request->has('bidang')){
                $q->whereIn('bidang_kode', $request->bidang);
            }
        })
        ->where(function ($q) use($search){
            if(!empty($search)){
                $q->where(\DB::raw('LOWER(bidang_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kementerian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(sub_bidang_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(menu_kegiatan_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pn_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pp_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(tematik_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kewenangan)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(jenis)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(rincian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(detail_rincian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pemda_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(prov_nama)'), 'LIKE', "%$search%");
             }
        })
        ->groupBy('kementerian_kode', 'kementerian_nama')
        ->orderByDesc(\DB::raw('SUM(nilai_rk)'))
        ->take(3)
        ->get();

        $dataKementerian = $dataKementerian->map(function($objDataTingpel){

            if($objDataTingpel['kementerian_kode'] == "024"){
                $objDataTingpel['kementerian_nama'] = "KEMKES";
            }else if($objDataTingpel['kementerian_kode'] == "033"){
                $objDataTingpel['kementerian_nama'] = "PUPR";
            }else if($objDataTingpel['kementerian_kode'] == "068"){
                $objDataTingpel['kementerian_nama'] = "BKKBN";
            }else if($objDataTingpel['kementerian_kode'] == "029"){
                $objDataTingpel['kementerian_nama'] = "KLHK";
            }

            return $objDataTingpel;
        });

        $dataProv = MvDak::select(
                \DB::raw('SUM(nilai_rk) grand_total'),
                'prov_kode',
                \DB::raw("replace(prov_nama,'Provinsi ','') prov_nama"))
        ->where(function($q) use($request){
            if($request->has('tahun')){
                $q->whereIn('tahun', $request->tahun);
                if($request->tahun[0] == '2021'){
                    $q->where('tematik_kode','02');
                    $q->whereNotIn(\DB::raw('CONCAT(bidang_kode,sub_bidang_kode)'),function($query) use ($request){
                        $query->select('kode')->from('stagging.dak_filter')->where('tahun',$request->tahun[0]);
                    });
                }
            }

            if($request->has('kl')){
                $q->whereIn('kementerian_kode', $request->kl);
            }

            if($request->has('bidang')){
                $q->whereIn('bidang_kode', $request->bidang);
            }
        })
        ->where('tingkat_pelaksanaan', 'Provinsi')
        ->where(function ($q) use($search){
            if(!empty($search)){
                $q->where(\DB::raw('LOWER(bidang_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kementerian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(sub_bidang_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(menu_kegiatan_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pn_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pp_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(tematik_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kewenangan)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(jenis)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(rincian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(detail_rincian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pemda_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(prov_nama)'), 'LIKE', "%$search%");
             }
        })
        ->groupBy('prov_kode', 'prov_nama')
        ->orderByDesc(\DB::raw('SUM(nilai_rk)'))
        ->take(5)
        ->get();

        $dataPemda = MvDak::select(
                \DB::raw('SUM(nilai_rk) grand_total'),
                'pemda_kode',
                \DB::raw("replace(replace(pemda_nama,'Kota ',''), 'Kab. ', '') pemda_nama"))
        ->where(function($q) use($request){
            if($request->has('tahun')){
                $q->whereIn('tahun', $request->tahun);
                if($request->tahun[0] == '2021'){
                    $q->where('tematik_kode','02');
                    $q->whereNotIn(\DB::raw('CONCAT(bidang_kode,sub_bidang_kode)'),function($query) use ($request){
                        $query->select('kode')->from('stagging.dak_filter')->where('tahun',$request->tahun[0]);
                    });
                }
            }

            if($request->has('kl')){
                $q->whereIn('kementerian_kode', $request->kl);
            }

            if($request->has('bidang')){
                $q->whereIn('bidang_kode', $request->bidang);
            }
        })
        ->where('tingkat_pelaksanaan', 'Kota/ Kabupaten')
        ->where(function ($q) use($search){
            if(!empty($search)){
                $q->where(\DB::raw('LOWER(bidang_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kementerian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(sub_bidang_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(menu_kegiatan_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pn_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pp_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(tematik_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kewenangan)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(jenis)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(rincian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(detail_rincian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pemda_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(prov_nama)'), 'LIKE', "%$search%");
             }
        })
        ->groupBy('pemda_kode', 'pemda_nama')
        ->orderByDesc(\DB::raw('SUM(nilai_rk)'))
        ->take(5)
        ->get();

        $dataProvAll = MvDak::select(
            'prov_kode',
            'prov_nama')
        ->where(function($q) use($request){
            if($request->has('tahun')){
                $q->whereIn('tahun', $request->tahun);
            }

            if($request->has('kl')){
                $q->whereIn('kementerian_kode', $request->kl);
            }

            if($request->has('bidang')){
                $q->whereIn('bidang_kode', $request->bidang);
            }
        })
        ->where('tingkat_pelaksanaan', 'Provinsi')
        ->where(function ($q) use($search){
            if(!empty($search)){
                $q->where(\DB::raw('LOWER(bidang_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kementerian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(sub_bidang_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(menu_kegiatan_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pn_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pp_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(tematik_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kewenangan)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(jenis)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(rincian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(detail_rincian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pemda_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(prov_nama)'), 'LIKE', "%$search%");
             }
        })
        ->groupBy('prov_kode', 'prov_nama')
        ->get()
        ->count();

        $dataPemdaAll = MvDak::select(
            'pemda_kode',
            'pemda_nama')
        ->where(function($q) use($request){
            if($request->has('tahun')){
                $q->whereIn('tahun', $request->tahun);
                if($request->tahun[0] == '2021'){
                    $q->where('tematik_kode','02');
                    $q->whereNotIn(\DB::raw('CONCAT(bidang_kode,sub_bidang_kode)'),function($query) use ($request){
                        $query->select('kode')->from('stagging.dak_filter')->where('tahun',$request->tahun[0]);
                    });
                }
            }

            if($request->has('kl')){
                $q->whereIn('kementerian_kode', $request->kl);
            }

            if($request->has('bidang')){
                $q->whereIn('bidang_kode', $request->bidang);
            }
        })
        ->where('tingkat_pelaksanaan', 'Kota/ Kabupaten')
        ->where(function ($q) use($search){
            if(!empty($search)){
                $q->where(\DB::raw('LOWER(bidang_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kementerian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(sub_bidang_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(menu_kegiatan_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pn_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pp_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(tematik_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kewenangan)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(jenis)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(rincian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(detail_rincian_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(pemda_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(prov_nama)'), 'LIKE', "%$search%");
             }
        })
        ->groupBy('pemda_kode', 'pemda_nama')
        ->get()
        ->count();

        // $data3TahunTerakhir = MvDak::select(
        //         \DB::raw('SUM(nilai_rk) grand_total'),
        //         'tahun')
        // ->where(function($q) use($request){

        //     if($request->has('kl')){
        //         $q->whereIn('kementerian_kode', $request->kl);
        //     }

        //     if($request->has('bidang')){
        //         $q->whereIn('bidang_kode', $request->bidang);
        //     }
        // })
        // ->where(function ($q) use($search){
        //     if(!empty($search)){
        //         $q->where(\DB::raw('LOWER(bidang_nama)'), 'LIKE', "%$search%");
        //         $q->orWhere(\DB::raw('LOWER(kementerian_nama)'), 'LIKE', "%$search%");
        //         $q->orWhere(\DB::raw('LOWER(sub_bidang_nama)'), 'LIKE', "%$search%");
        //         $q->orWhere(\DB::raw('LOWER(menu_kegiatan_nama)'), 'LIKE', "%$search%");
        //         $q->orWhere(\DB::raw('LOWER(pn_nama)'), 'LIKE', "%$search%");
        //         $q->orWhere(\DB::raw('LOWER(pp_nama)'), 'LIKE', "%$search%");
        //         $q->orWhere(\DB::raw('LOWER(tematik_nama)'), 'LIKE', "%$search%");
        //         $q->orWhere(\DB::raw('LOWER(kewenangan)'), 'LIKE', "%$search%");
        //         $q->orWhere(\DB::raw('LOWER(jenis)'), 'LIKE', "%$search%");
        //         $q->orWhere(\DB::raw('LOWER(rincian_nama)'), 'LIKE', "%$search%");
        //         $q->orWhere(\DB::raw('LOWER(detail_rincian_nama)'), 'LIKE', "%$search%");
        //         $q->orWhere(\DB::raw('LOWER(pemda_nama)'), 'LIKE', "%$search%");
        //         $q->orWhere(\DB::raw('LOWER(prov_nama)'), 'LIKE', "%$search%");
        //      }
        // })
        // ->groupBy('tahun')
        // ->orderByDesc('tahun')
        // ->take(3)
        // ->get();

        $data3TahunTerakhir = VDak::select('nilai_rk as grand_total','tahun')->orderByDesc('tahun')->take(3)->get();

        $data3TahunTerakhir = $data3TahunTerakhir->map(function($obj) use($data3TahunTerakhir){
            $_tahunBefore = $data3TahunTerakhir->filter(function($objFilter) use($obj){
                return $objFilter->tahun == $obj->tahun-1;
            })->first();
            if($_tahunBefore != null){
                $obj->grand_total_before = $_tahunBefore->grand_total;
                $obj->seilsih_before = ($obj->grand_total - $obj->grand_total_before) / $obj->grand_total_before * 100;
            }else{
                $obj->grand_total_before = null;
                $obj->seilsih_before = null;
            }
            return $obj;
        });

        $tahun = $this->tahun($request)->original['data'];
        $kabupaten = $this->kabupaten($request)->original['data'];
        $provinsi = $this->provinsi($request)->original['data'];
        $totalDak = $this->dataTotalDak($request)->original['data'];
        $requestClone = clone $request;
        $requestClone->tahun = array($request->tahun[0]-1);
        $totalDakBefore = $this->dataTotalDak($requestClone)->original['data'];
        $request->search = "";
        $tingpel = $dataTingpel;
        $kl = $this->kementerian($request)->original['data'];
        $dataByKl = $dataKementerian;

        $res = new \stdClass;
        $res->tahun = $tahun;
        $res->total_dak = $totalDak;
        $res->total_dak_before = $totalDakBefore;
        $res->tingpel = $tingpel;
        $res->kl_only = $kl;
        $res->data_kl = $dataByKl;
        $res->top_5_pemda = $dataPemda;
        $res->top_5_prov = $dataProv;
        $res->pemda_count_all = $dataPemdaAll;
        $res->prov_count_all = $dataProvAll;
        $res->data_3_tahun = $data3TahunTerakhir;

        $res->pemda = $kabupaten;
        $res->provinsi = $provinsi;
        $res->totalpemda = count($kabupaten);


        return $this->returnJsonSuccess("Data fetched successfully", $res);
    }

}

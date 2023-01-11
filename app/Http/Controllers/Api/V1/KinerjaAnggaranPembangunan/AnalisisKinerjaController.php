<?php

namespace App\Http\Controllers\Api\V1\KinerjaAnggaranPembangunan;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\Kinerja\AnalisisKinerja;
use App\Models\Kinerja\MvForm2;

use Carbon\Carbon;

class AnalisisKinerjaController extends BaseController
{
    public function tahunSemester(Request $request){
        $tahun = AnalisisKinerja::select('tahun', 'semester')->groupBy('tahun', 'semester')->get();
        return $this->returnJsonSuccess("Data fetched successfully", $tahun);
    }

    public function kementerian(Request $request){
        $kementerian = AnalisisKinerja::select('kementerian_kode', 'kementerian_nama')->groupBy('kementerian_kode', 'kementerian_nama')->get();
        return $this->returnJsonSuccess("Data fetched successfully", $kementerian);
    }

    public function intervensi(Request $request){
        $intervensi = AnalisisKinerja::select('intervensi_kode', 'intervensi_nama')->groupBy('intervensi_kode', 'intervensi_nama')->get();
        return $this->returnJsonSuccess("Data fetched successfully", $intervensi);
    }

    public function getData(Request $request){
        $tahun = now()->year;
        $bulan = now()->month;
        $semester = ($bulan/6) <= 6 ? 1 : 2;

        $kl = [];
        $intervensi = [];
        $search = "";

        if($request->has('tahun') && !empty($request->tahun)){
            $tahun = $request->tahun;
        }

        if($request->has('semester') && !empty($request->semester)){
            $semester = $request->semester;
        }

        if($request->has('kl') && !empty($request->kl)){
            $kl = $request->kl;
        }

        if($request->has('intervensi') && !empty($request->intervensi)){
            $intervensi = $request->intervensi;
        }

        if($request->has('search') && !empty($request->search)){
            $search = strtolower($request->search);
        }

        $allKementerian = AnalisisKinerja::select('kementerian_kode', 'kementerian_nama')->groupBy('kementerian_kode', 'kementerian_nama')->get();
        $allKinerjaPembangunan = AnalisisKinerja::all();

        $dataKinerjaPembangunan = AnalisisKinerja::where(function($q) use($tahun, $bulan, $semester, $kl, $intervensi){
            if($tahun != "all"){
                $q->where('tahun', $tahun);
            }

            if($semester != "all"){
                $q->where('semester', $semester);
            }

            if($kl != "all"){
                $q->whereIn('kementerian_kode', $kl);
            }

            if($intervensi != "all"){
                $q->whereIn('intervensi_kode', $intervensi);
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

        $dataMvForm2 = MvForm2::where(function($q) use($tahun, $bulan, $semester, $kl, $intervensi){
            if($tahun != "all"){
                $q->where('tahun', $tahun);
            }

            if($semester != "all"){
                $q->where('semester', $semester);
            }

            if($kl != "all"){
                $q->whereIn('kementerian_kode', $kl);
            }

            if($intervensi != "all"){
                $q->whereIn('intervensi_kode', $intervensi);
            }
        })->where(function ($q) use($search){
            if(!empty($search)){
               $q->where(\DB::raw('LOWER(program_nama)'), 'LIKE', "%$search%");
               $q->orWhere(\DB::raw('LOWER(kegiatan_nama)'), 'LIKE', "%$search%");
               $q->orWhere(\DB::raw('LOWER(output_nama)'), 'LIKE', "%$search%");
               $q->orWhere(\DB::raw('LOWER(suboutput_nama)'), 'LIKE', "%$search%");
            }
        })->get();;


        $mvForm2Clone = clone $dataMvForm2;

      //  dd($mvForm2Clone);


        $tile = new \stdClass;
        $kesesuaianLokusRo = new \stdClass;
        $kesesuaianLokusRo->total_ro = $mvForm2Clone->count();
       
        $kesesuaianLokusRo->dilaksanakan_lokasi_prioritas = $mvForm2Clone->filter(function ($obj) {
            return $obj->lokasi_prioritas;
        })->count();

        $kesesuaianLokusRo->dilaksanakan_level_pusat = $mvForm2Clone->filter(function ($obj) {
            return $obj->lokasi_pusat;
        })->count();

        $kesesuaianLokusRo->dilaksanakan_level_provinsi = $mvForm2Clone->filter(function ($obj) {
            return $obj->lokasi_provinsi;
        })->count();

        $kesesuaianLokusRo->dilaksanakan_level_kota = $mvForm2Clone->filter(function ($obj) {
            return $obj->lokasi_prioritas || $obj->non_lokasi_prioritas;
        })->count();

        $kesesuaianLokusRo->menyasar_mt_360_kota = $mvForm2Clone->filter(function ($obj) {
            return $obj->lokasi_prioritas_desc > 360;
        })->count();

        $kesesuaianLokusRo->menyasar_lte_360_kota = $mvForm2Clone->filter(function ($obj) {
            return $obj->lokasi_prioritas_desc <= 360;
        })->count();

        $kesesuaianLokusRo->menyasar_mt_360_kota_kota_non_lokus = $mvForm2Clone->filter(function ($obj) {
            return $obj->lokasi_prioritas_desc > 360 && $obj->non_lokasi_prioritas;
        })->count();

        $kesesuaianLokusRo->menyasar_mt_360_kota_kota_none_non_lokus = $mvForm2Clone->filter(function ($obj) {
            return $obj->lokasi_prioritas_desc > 360 && !$obj->non_lokasi_prioritas;
        })->count();

        $kesesuaianLokusRo->menyasar_lte_360_kota_kota_non_lokus = $mvForm2Clone->filter(function ($obj) {
            return $obj->lokasi_prioritas_desc <= 360 && $obj->non_lokasi_prioritas;
        })->count();

        $kesesuaianLokusRo->menyasar_lte_360_kota_kota_none_non_lokus = $mvForm2Clone->filter(function ($obj) {
            return $obj->lokasi_prioritas_desc <= 360 && !$obj->non_lokasi_prioritas;
        })->count();

        

        $lokusIntervensi = MvForm2::select('intervensi_kode',
                                                    'intervensi_nama',
                                                    \DB::raw("COUNT(CASE WHEN lokasi_prioritas OR non_lokasi_prioritas THEN 1 END) as lokasi_kota"),
                                                    \DB::raw("COUNT(CASE WHEN lokasi_provinsi THEN 1 END) as lokasi_provinsi"),
                                                    \DB::raw("COUNT(CASE WHEN lokasi_pusat THEN 1 END) as lokasi_pusat"))
                                            ->groupBy('intervensi_kode', 'intervensi_nama')
        ->where(function($q) use($tahun, $bulan, $semester, $kl, $intervensi){
            if($tahun != "all"){
                $q->where('tahun', $tahun);
            }

            if($semester != "all"){
                $q->where('semester', $semester);
            }

            if($kl != "all"){
                $q->whereIn('kementerian_kode', $kl);
            }

            if($intervensi != "all"){
                $q->whereIn('intervensi_kode', $intervensi);
            }
        })->where(function ($q) use($search){
            if(!empty($search)){
               $q->where(\DB::raw('LOWER(program_nama)'), 'LIKE', "%$search%");
               $q->orWhere(\DB::raw('LOWER(kegiatan_nama)'), 'LIKE', "%$search%");
               $q->orWhere(\DB::raw('LOWER(output_nama)'), 'LIKE', "%$search%");
               $q->orWhere(\DB::raw('LOWER(suboutput_nama)'), 'LIKE', "%$search%");
            }
        })->get();

      //  dd($lokusIntervensi);


      $konvergensiSasaran = MvForm2::select(
        \DB::raw("COUNT(CASE WHEN sasaran_penting THEN 1 END) as sasaran_penting"),
        \DB::raw("COUNT(CASE WHEN sasaran_prioritas THEN 1 END) as sasaran_prioritas"),
        \DB::raw("COUNT(CASE WHEN sasaran_lainnya THEN 1 END) as sasaran_lainnya"))
->where(function($q) use($tahun, $bulan, $semester, $kl, $intervensi){
if($tahun != "all"){
$q->where('tahun', $tahun);
}

if($semester != "all"){
$q->where('semester', $semester);
}

if($kl != "all"){
$q->whereIn('kementerian_kode', $kl);
}

if($intervensi != "all"){
$q->whereIn('intervensi_kode', $intervensi);
}
})->where(function ($q) use($search){
    if(!empty($search)){
       $q->where(\DB::raw('LOWER(program_nama)'), 'LIKE', "%$search%");
       $q->orWhere(\DB::raw('LOWER(kegiatan_nama)'), 'LIKE', "%$search%");
       $q->orWhere(\DB::raw('LOWER(output_nama)'), 'LIKE', "%$search%");
       $q->orWhere(\DB::raw('LOWER(suboutput_nama)'), 'LIKE', "%$search%");
    }
})->first();

$sasaranPrioritas = new \stdClass;
$sasaranPrioritas->category = "Sasaran Prioritas";
$sasaranPrioritas->value = $konvergensiSasaran->sasaran_prioritas;

$sasaranPenting = new \stdClass;
$sasaranPenting->category = "Sasaran Penting";
$sasaranPenting->value = $konvergensiSasaran->sasaran_penting;

$sasaranLainnya = new \stdClass;
$sasaranLainnya->category = "Sasaran Lainnya";
$sasaranLainnya->value = $konvergensiSasaran->sasaran_lainnya;

$dataKonvergensiSasaran = [];
$dataKonvergensiSasaran[] = $sasaranPrioritas;
$dataKonvergensiSasaran[] = $sasaranPenting;
$dataKonvergensiSasaran[] = $sasaranLainnya;

$pelaksanaanKoordinasi = MvForm2::select(
                                    \DB::raw("COUNT(CASE WHEN koord_kl OR koord_pemda OR koord_non THEN 1 END) as ada_koordinasi"),
                                    \DB::raw("COUNT(CASE WHEN koord_kl = false AND koord_pemda = false AND koord_non = false THEN 1 END) as tidak_ada_koordinasi"),
                                    \DB::raw("COUNT(CASE WHEN koord_kl IS NULL AND koord_pemda IS NULL AND koord_non IS NULL THEN 1 END) as na_koordinasi"))
->where(function($q) use($tahun, $bulan, $semester, $kl, $intervensi){
if($tahun != "all"){
$q->where('tahun', $tahun);
}

if($semester != "all"){
$q->where('semester', $semester);
}

if($kl != "all"){
$q->whereIn('kementerian_kode', $kl);
}

if($intervensi != "all"){
$q->whereIn('intervensi_kode', $intervensi);
}
})->where(function ($q) use($search){
    if(!empty($search)){
       $q->where(\DB::raw('LOWER(program_nama)'), 'LIKE', "%$search%");
       $q->orWhere(\DB::raw('LOWER(kegiatan_nama)'), 'LIKE', "%$search%");
       $q->orWhere(\DB::raw('LOWER(output_nama)'), 'LIKE', "%$search%");
       $q->orWhere(\DB::raw('LOWER(suboutput_nama)'), 'LIKE', "%$search%");
    }
})->first();

$konvergensiSasaran = MvForm2::select(
                                    \DB::raw("COUNT(CASE WHEN sasaran_penting THEN 1 END) as sasaran_penting"),
                                    \DB::raw("COUNT(CASE WHEN sasaran_prioritas THEN 1 END) as sasaran_prioritas"),
                                    \DB::raw("COUNT(CASE WHEN sasaran_lainnya THEN 1 END) as sasaran_lainnya"))
->where(function($q) use($tahun, $bulan, $semester, $kl, $intervensi){
if($tahun != "all"){
$q->where('tahun', $tahun);
}

if($semester != "all"){
$q->where('semester', $semester);
}

if($kl != "all"){
$q->whereIn('kementerian_kode', $kl);
}

if($intervensi != "all"){
$q->whereIn('intervensi_kode', $intervensi);
}
})->where(function ($q) use($search){
    if(!empty($search)){
       $q->where(\DB::raw('LOWER(program_nama)'), 'LIKE', "%$search%");
       $q->orWhere(\DB::raw('LOWER(kegiatan_nama)'), 'LIKE', "%$search%");
       $q->orWhere(\DB::raw('LOWER(output_nama)'), 'LIKE', "%$search%");
       $q->orWhere(\DB::raw('LOWER(suboutput_nama)'), 'LIKE', "%$search%");
    }
})->first();

$adaKoordinasi = new \stdClass;
$adaKoordinasi->category = "Ada Koordinasi";
$adaKoordinasi->value = $pelaksanaanKoordinasi->ada_koordinasi;

$tidakAdaKoordinasi = new \stdClass;
$tidakAdaKoordinasi->category = "Tidak Ada Koordinasi";
$tidakAdaKoordinasi->value = $pelaksanaanKoordinasi->tidak_ada_koordinasi;

$naKoordinasi = new \stdClass;
$naKoordinasi->category = "N/A";
$naKoordinasi->value = $pelaksanaanKoordinasi->na_koordinasi;

$dataPelaksanaanKoordinasi1 = [];
$dataPelaksanaanKoordinasi1[] = $adaKoordinasi;
$dataPelaksanaanKoordinasi1[] = $tidakAdaKoordinasi;
$dataPelaksanaanKoordinasi1[] = $naKoordinasi;


$pelaksanaanKoordinasi = MvForm2::select(
                                    \DB::raw("COUNT(CASE WHEN koord_kl THEN 1 END) as kl"),
                                    \DB::raw("COUNT(CASE WHEN koord_pemda THEN 1 END) as pemda"),
                                    \DB::raw("COUNT(CASE WHEN koord_non THEN 1 END) as non"),
                                    \DB::raw("COUNT(CASE WHEN koord_kl AND koord_pemda AND koord_non = false  THEN 1 END) as kl_pemda"),
                                    \DB::raw("COUNT(CASE WHEN koord_kl AND koord_pemda = false AND koord_non  THEN 1 END) as kl_non"),
                                    \DB::raw("COUNT(CASE WHEN koord_kl = false AND koord_pemda AND koord_non  THEN 1 END) as pemda_non"),
                                    \DB::raw("COUNT(CASE WHEN koord_kl AND koord_pemda AND koord_non THEN 1 END) as kl_pemda_non"),
                                    )
->where(function($q) use($tahun, $bulan, $semester, $kl, $intervensi){
if($tahun != "all"){
$q->where('tahun', $tahun);
}

if($semester != "all"){
$q->where('semester', $semester);
}

if($kl != "all"){
$q->whereIn('kementerian_kode', $kl);
}

if($intervensi != "all"){
$q->whereIn('intervensi_kode', $intervensi);
}
})->where(function ($q) use($search){
    if(!empty($search)){
       $q->where(\DB::raw('LOWER(program_nama)'), 'LIKE', "%$search%");
       $q->orWhere(\DB::raw('LOWER(kegiatan_nama)'), 'LIKE', "%$search%");
       $q->orWhere(\DB::raw('LOWER(output_nama)'), 'LIKE', "%$search%");
       $q->orWhere(\DB::raw('LOWER(suboutput_nama)'), 'LIKE', "%$search%");
    }
})->first();

$dataPelaksanaanKoordinasi2 = [];

$data = new \stdClass;
$data->name = "Pemda";
$data->value = $pelaksanaanKoordinasi->pemda;
$dataPelaksanaanKoordinasi2[] = $data;

$data = new \stdClass;
$data->name = "K/L Lain";
$data->value = $pelaksanaanKoordinasi->kl;
$dataPelaksanaanKoordinasi2[] = $data;

$data = new \stdClass;
$data->name = "Non-Pemerintah";
$data->value = $pelaksanaanKoordinasi->non;
$dataPelaksanaanKoordinasi2[] = $data;

$data = new \stdClass;
$data->name = "K/L Lain, Pemda";
$data->value = $pelaksanaanKoordinasi->kl_pemda;
$data->sets = [
"K/L Lain",
"Pemda"
];
$dataPelaksanaanKoordinasi2[] = $data;

$data = new \stdClass;
$data->name = "K/L Lain, Non-Pemerintah";
$data->value = $pelaksanaanKoordinasi->kl_non;
$data->sets = [
"K/L Lain",
"Non-Pemerintah"
];
$dataPelaksanaanKoordinasi2[] = $data;

$data = new \stdClass;
$data->name = "Pemda, Non-Pemerintah";
$data->value = $pelaksanaanKoordinasi->pemda_non;
$data->sets = [
"Pemda",
"Non-Pemerintah"
];
$dataPelaksanaanKoordinasi2[] = $data;

$data = new \stdClass;
$data->name = "K/L Lain, Pemda, Non-Pemerintah";
$data->value = $pelaksanaanKoordinasi->kl_pemda_non;
$data->sets = [
"K/L Lain",
"Pemda",
"Non-Pemerintah"
];
$dataPelaksanaanKoordinasi2[] = $data;


$tile->kesesuaianLokus = $kesesuaianLokusRo;
$tile->lokusIntervensi = $lokusIntervensi;
$tile->konvergensiSasaran = $dataKonvergensiSasaran;
$tile->pelaksanaanKoordinasi1 = $dataPelaksanaanKoordinasi1;
$tile->pelaksanaanKoordinasi2 = $dataPelaksanaanKoordinasi2;





        $kinerjaAnggaranClone = clone $dataKinerjaPembangunan;

        $lsKementerian = $kinerjaAnggaranClone->map->only(['tahun', 'semester', 'kementerian_kode', 'kementerian_nama'])->unique()->values();

        $lsKementerian = $lsKementerian->map(function($objKementerian) use ($kinerjaAnggaranClone){
            $objKementerian = (object)$objKementerian;
            $kinerjaAnggaranKementerian = $kinerjaAnggaranClone->filter(function ($obj) use($objKementerian) {
                return $obj->kementerian_kode == $objKementerian->kementerian_kode;
            });

            $lsIntervensi = $kinerjaAnggaranKementerian->map->only(['tahun', 'semester', 'intervensi_kode', 'intervensi_nama'])->unique()->values();
            $lsProgam = $kinerjaAnggaranKementerian->map->only(['tahun', 'semester', 'program_kode', 'program_nama'])->unique()->values();
            $lsKegiatan = $kinerjaAnggaranKementerian->map->only(['tahun', 'semester', 'kegiatan_kode', 'kegiatan_nama'])->unique()->values();
            $lsOutput = $kinerjaAnggaranKementerian->map->only(['tahun', 'semester', 'output_kode', 'output_nama'])->unique()->values();

            $objKementerian->kl_id = $objKementerian->kementerian_kode;
            $objKementerian->name = $objKementerian->kementerian_nama;
            $objKementerian->aktivitas = "";
            $objKementerian->analisis_gap_ka = "";
            $objKementerian->analisis_gap_ko = "";
            $objKementerian->penghematan = "";
            $objKementerian->target_turun = "";
            $objKementerian->keterangan = "";
            $objKementerian->reviu = "";
            $objKementerian->rekomendasi = "";

            $objKementerian->jml_program = $lsProgam->count();
            $objKementerian->jml_kegiatan = $lsKegiatan->count();
            $objKementerian->jml_kro = $lsOutput->count();
            $objKementerian->jml_ro = $kinerjaAnggaranKementerian->count();

            $objKementerian->_children = $lsIntervensi->map(function($objIntervensi) use($kinerjaAnggaranKementerian, $objKementerian){
                $objIntervensi = (object)$objIntervensi;
                $kinerjaAnggaranIntervensi = $kinerjaAnggaranKementerian->filter(function ($obj) use($objIntervensi) {
                    return $obj->intervensi_kode == $objIntervensi->intervensi_kode;
                });

                $lsProgam = $kinerjaAnggaranIntervensi->map->only(['tahun', 'semester', 'program_kode', 'program_nama'])->unique()->values();

                $objIntervensi->kl_id = $objKementerian->kementerian_kode;
                $objIntervensi->intervensi_id = $objIntervensi->intervensi_kode;
                $objIntervensi->name = $objIntervensi->intervensi_nama;

                $objIntervensi->aktivitas = "";
                $objIntervensi->analisis_gap_ka = "";
                $objIntervensi->analisis_gap_ko = "";
                $objIntervensi->penghematan = "";
                $objIntervensi->target_turun = "";
                $objIntervensi->keterangan = "";
                $objIntervensi->reviu = "";
                $objIntervensi->rekomendasi = "";

                $objIntervensi->jml_program = $lsProgam->count();
                $objIntervensi->jml_kegiatan = 0;
                $objIntervensi->jml_kro = 0;
                $objIntervensi->jml_ro = $kinerjaAnggaranIntervensi->count();

                $objIntervensi->_children = $lsProgam->map(function($objProgram) use($kinerjaAnggaranIntervensi, $objKementerian, $objIntervensi){
                    $objProgram = (object)$objProgram;
                    $kinerjaAnggaranProgram = $kinerjaAnggaranIntervensi->filter(function ($obj) use( $objProgram) {
                        return $obj->program_kode == $objProgram->program_kode;
                    })->values();
    
                    $lsKegiatan = $kinerjaAnggaranProgram->map->only(['tahun', 'semester', 'kegiatan_kode', 'kegiatan_nama'])->unique()->values();
    
                    $objProgram->kl_id = $objKementerian->kementerian_kode;
                    $objProgram->intervensi_id = $objIntervensi->intervensi_kode;
                    $objProgram->program_id = $objProgram->program_kode;
                    $objProgram->name = $objProgram->program_nama;
    
                    $objProgram->aktivitas = "";
                    $objProgram->analisis_gap_ka = "";
                    $objProgram->analisis_gap_ko = "";
                    $objProgram->penghematan = "";
                    $objProgram->target_turun = "";
                    $objProgram->keterangan = "";
                    $objProgram->reviu = "";
                    $objProgram->rekomendasi = "";

                    $objProgram->jml_program = 0;
                    $objProgram->jml_kegiatan = $lsKegiatan->count();
                    $objProgram->jml_kro = 0;
                    $objProgram->jml_ro = $kinerjaAnggaranProgram->count();
    
                    $objProgram->_children = $lsKegiatan->map(function($objKegiatan) use($kinerjaAnggaranProgram, $objKementerian, $objIntervensi, $objProgram){
                        $objKegiatan = (object)$objKegiatan;
                        $kinerjaAnggaranKegiatan = $kinerjaAnggaranProgram->filter(function ($obj) use($objKegiatan) {
                            return $obj->kegiatan_kode == $objKegiatan->kegiatan_kode;
                        });
        
                        $lsOutput = $kinerjaAnggaranKegiatan->map->only(['tahun', 'semester', 'output_kode', 'output_nama'])->unique()->values();
        
                        $objKegiatan->kl_id = $objKementerian->kementerian_kode;
                        $objKegiatan->intervensi_id = $objIntervensi->intervensi_kode;
                        $objKegiatan->program_id = $objProgram->program_kode;
                        $objKegiatan->kegiatan_id = $objKegiatan->kegiatan_kode;
                        $objKegiatan->name = $objKegiatan->kegiatan_nama;
        
                        $objKegiatan->aktivitas = "";
                        $objKegiatan->analisis_gap_ka = "";
                        $objKegiatan->analisis_gap_ko = "";
                        $objKegiatan->penghematan = "";
                        $objKegiatan->target_turun = "";
                        $objKegiatan->keterangan = "";
                        $objKegiatan->reviu = "";
                        $objKegiatan->rekomendasi = "";

                        $objKegiatan->jml_program = 0;
                        $objKegiatan->jml_kegiatan = 0;
                        $objKegiatan->jml_kro = $lsOutput->count();
                        $objKegiatan->jml_ro = $kinerjaAnggaranKegiatan->count();
        
                        $objKegiatan->_children = $lsOutput->map(function($objOutput) use($kinerjaAnggaranKegiatan, $objKementerian, $objIntervensi, $objProgram, $objKegiatan){
                            $objOutput = (object)$objOutput;
                            $kinerjaAnggaranOutput = $kinerjaAnggaranKegiatan->filter(function ($obj) use($objOutput) {
                                return $obj->output_kode == $objOutput->output_kode;
                            });
            
                            $objOutput->kl_id = $objKementerian->kementerian_kode;
                            $objOutput->intervensi_id = $objIntervensi->intervensi_kode;
                            $objOutput->program_id = $objProgram->program_kode;
                            $objOutput->kegiatan_id = $objKegiatan->kegiatan_kode;
                            $objOutput->kro_id = $objOutput->output_kode;
                            $objOutput->name = $objOutput->output_nama;
            
                            $objOutput->aktivitas = "";
                            $objOutput->analisis_gap_ka = "";
                            $objOutput->analisis_gap_ko = "";
                            $objOutput->penghematan = "";
                            $objOutput->target_turun = "";
                            $objOutput->keterangan = "";
                            $objOutput->reviu = "";
                            $objOutput->rekomendasi = "";

                            $objOutput->jml_program = 0;
                            $objOutput->jml_kegiatan = 0;
                            $objOutput->jml_kro = 0;
                            $objOutput->jml_ro = $kinerjaAnggaranOutput->count();
            
                            $objOutput->_children = $kinerjaAnggaranOutput->map(function($objSubOutput){
                                
                                $objSubOutput->kl_id = $objSubOutput->kementerian_kode;
                                $objSubOutput->intervensi_id = $objSubOutput->intervensi_kode;
                                $objSubOutput->program_id = $objSubOutput->program_kode;
                                $objSubOutput->kegiatan_id = $objSubOutput->kegiatan_kode;
                                $objSubOutput->kro_id = $objSubOutput->output_kode;
                                $objSubOutput->ro_id = $objSubOutput->suboutput_kode;
                                $objSubOutput->name = $objSubOutput->suboutput_nama;

                                $objSubOutput->jml_program = 0;
                                $objSubOutput->jml_kegiatan = 0;
                                $objSubOutput->jml_kro = 0;
                                $objSubOutput->jml_ro = 1;
                
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

                $objIntervensi->jml_kegiatan = $objIntervensi->_children->sum('jml_kegiatan');
                $objIntervensi->jml_kro = $objIntervensi->_children->sum('jml_kro');

                unset($objIntervensi->intervensi_kode);
                unset($objIntervensi->intervensi_nama);

                return $objIntervensi;

            })->values();

            // $objKementerian->jml_program = $objKementerian->_children->sum('jml_program');
            // $objKementerian->jml_kegiatan = $objKementerian->_children->sum('jml_kegiatan');
            $objKementerian->jml_kro = $objKementerian->_children->sum('jml_kro');

            unset($objKementerian->kementerian_kode);
            unset($objKementerian->kementerian_nama);

            return $objKementerian;

        });

    // dd($tile);
        
        $result = new \stdClass;
        $result->tile = $tile;
        $result->detail = $lsKementerian;

        return $this->returnJsonSuccess("Data fetched successfully", $result);
    }

    public function pembagi($a, $b){
        return $b == 0 ? 0 : ($a / $b);
    }

}

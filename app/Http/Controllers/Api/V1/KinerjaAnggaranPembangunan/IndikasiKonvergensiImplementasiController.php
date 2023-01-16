<?php

namespace App\Http\Controllers\Api\V1\KinerjaAnggaranPembangunan;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\Kinerja\IndikasiKonvergensiImplementasi;
use App\Models\Kinerja\MvForm2;
use Carbon\Carbon;

class IndikasiKonvergensiImplementasiController extends BaseController
{
    public function tahunSemester(Request $request){
        $tahun = IndikasiKonvergensiImplementasi::select('tahun', 'semester')->groupBy('tahun', 'semester')->get();
        return $this->returnJsonSuccess("Data fetched successfully", $tahun);
    }

    public function kementerian(Request $request){
        $kementerian = IndikasiKonvergensiImplementasi::select('kementerian_kode', 'kementerian_nama')->groupBy('kementerian_kode', 'kementerian_nama')->get();
        return $this->returnJsonSuccess("Data fetched successfully", $kementerian);
    }

    public function intervensi(Request $request){
        $intervensi = IndikasiKonvergensiImplementasi::select('intervensi_kode', 'intervensi_nama')->groupBy('intervensi_kode', 'intervensi_nama')->get();
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

        $dataKinerjaPembangunan = IndikasiKonvergensiImplementasi::where(function($q) use($tahun, $bulan, $semester, $kl, $intervensi){
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
        })
        ->get();

        $kinerjaAnggaranClone = clone $dataKinerjaPembangunan;
        $mvForm2Clone = clone $dataMvForm2;

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
            $objKementerian->lokasi_prioritas = "";
            $objKementerian->lokasi_prioritas_desc = "";
            $objKementerian->non_lokasi_prioritas = "";
            $objKementerian->non_lokasi_prioritas_desc = "";
            $objKementerian->total_lokasi = "";
            $objKementerian->intervensi = "";
            $objKementerian->satuan = "";
            $objKementerian->sasaran_prioritas = "";
            $objKementerian->sasaran_prioritas_jml = "";
            $objKementerian->sasaran_penting = "";
            $objKementerian->sasaran_penting_jml = "";
            $objKementerian->sasaran_lainnya = "";
            $objKementerian->sasaran_lainnya_1 = "";
            $objKementerian->sasaran_lainnya_jml = "";
            $objKementerian->koord_kl = "";
            $objKementerian->koord_pemda = "";
            $objKementerian->koord_non = "";

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

                $objIntervensi->lokasi_prioritas = "";
                $objIntervensi->lokasi_prioritas_desc = "";
                $objIntervensi->non_lokasi_prioritas = "";
                $objIntervensi->non_lokasi_prioritas_desc = "";
                $objIntervensi->total_lokasi = "";
                $objIntervensi->intervensi = "";
                $objIntervensi->satuan = "";
                $objIntervensi->sasaran_prioritas = "";
                $objIntervensi->sasaran_prioritas_jml = "";
                $objIntervensi->sasaran_penting = "";
                $objIntervensi->sasaran_penting_jml = "";
                $objIntervensi->sasaran_lainnya = "";
                $objIntervensi->sasaran_lainnya_1 = "";
                $objIntervensi->sasaran_lainnya_jml = "";
                $objIntervensi->koord_kl = "";
                $objIntervensi->koord_pemda = "";
                $objIntervensi->koord_non = "";

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
    
                    $objProgram->lokasi_prioritas = "";
                    $objProgram->lokasi_prioritas_desc = "";
                    $objProgram->non_lokasi_prioritas = "";
                    $objProgram->non_lokasi_prioritas_desc = "";
                    $objProgram->total_lokasi = "";
                    $objProgram->intervensi = "";
                    $objProgram->satuan = "";
                    $objProgram->sasaran_prioritas = "";
                    $objProgram->sasaran_prioritas_jml = "";
                    $objProgram->sasaran_penting = "";
                    $objProgram->sasaran_penting_jml = "";
                    $objProgram->sasaran_lainnya = "";
                    $objProgram->sasaran_lainnya_1 = "";
                    $objProgram->sasaran_lainnya_jml = "";
                    $objProgram->koord_kl = "";
                    $objProgram->koord_pemda = "";
                    $objProgram->koord_non = "";

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
        
                        $objKegiatan->lokasi_prioritas = "";
                        $objKegiatan->lokasi_prioritas_desc = "";
                        $objKegiatan->non_lokasi_prioritas = "";
                        $objKegiatan->non_lokasi_prioritas_desc = "";
                        $objKegiatan->total_lokasi = "";
                        $objKegiatan->intervensi = "";
                        $objKegiatan->satuan = "";
                        $objKegiatan->sasaran_prioritas = "";
                        $objKegiatan->sasaran_prioritas_jml = "";
                        $objKegiatan->sasaran_penting = "";
                        $objKegiatan->sasaran_penting_jml = "";
                        $objKegiatan->sasaran_lainnya = "";
                        $objKegiatan->sasaran_lainnya_1 = "";
                        $objKegiatan->sasaran_lainnya_jml = "";
                        $objKegiatan->koord_kl = "";
                        $objKegiatan->koord_pemda = "";
                        $objKegiatan->koord_non = "";

                        $objKegiatan->jml_program = 0;
                        $objKegiatan->jml_kegiatan = 0;
                        $objKegiatan->jml_kro = $lsOutput->count();;
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
            
                            $objOutput->lokasi_prioritas = "";
                            $objOutput->lokasi_prioritas_desc = "";
                            $objOutput->non_lokasi_prioritas = "";
                            $objOutput->non_lokasi_prioritas_desc = "";
                            $objOutput->total_lokasi = "";
                            $objOutput->intervensi = "";
                            $objOutput->satuan = "";
                            $objOutput->sasaran_prioritas = "";
                            $objOutput->sasaran_prioritas_jml = "";
                            $objOutput->sasaran_penting = "";
                            $objOutput->sasaran_penting_jml = "";
                            $objOutput->sasaran_lainnya = "";
                            $objOutput->sasaran_lainnya_1 = "";
                            $objOutput->sasaran_lainnya_jml = "";
                            $objOutput->koord_kl = "";
                            $objOutput->koord_pemda = "";
                            $objOutput->koord_non = "";

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


        $result = new \stdClass;
        $result->tile = $tile;
        $result->detail = $lsKementerian;

        return $this->returnJsonSuccess("Data fetched successfully", $result);
    }

    public function chart1(Request $request){
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

        $tile = new \stdClass;
        $tile->total_ro = $mvForm2Clone->count();
        $tile->dilaksanakan_lokasi_prioritas = $mvForm2Clone->filter(function ($obj) {
            return $obj->lokasi_prioritas;
        })->count();

        $tile->dilaksanakan_level_pusat = $mvForm2Clone->filter(function ($obj) {
            return $obj->lokasi_pusat;
        })->count();

        $tile->dilaksanakan_level_provinsi = $mvForm2Clone->filter(function ($obj) {
            return $obj->lokasi_provinsi;
        })->count();

        $tile->dilaksanakan_level_kota = $mvForm2Clone->filter(function ($obj) {
            return $obj->lokasi_kota;
        })->count();

        $tile->menyasar_mt_360_kota = $mvForm2Clone->filter(function ($obj) {
            return $obj->lokasi_prioritas_desc > 360;
        })->count();

        $tile->menyasar_lte_360_kota = $mvForm2Clone->filter(function ($obj) {
            return $obj->lokasi_prioritas_desc <= 360;
        })->count();

        $tile->menyasar_mt_360_kota_kota_non_lokus = $mvForm2Clone->filter(function ($obj) {
            return $obj->lokasi_prioritas_desc > 360 && $obj->non_lokasi_prioritas;
        })->count();

        $tile->menyasar_mt_360_kota_kota_none_non_lokus = $mvForm2Clone->filter(function ($obj) {
            return $obj->lokasi_prioritas_desc > 360 && !$obj->non_lokasi_prioritas;
        })->count();

        $tile->menyasar_lte_360_kota_kota_non_lokus = $mvForm2Clone->filter(function ($obj) {
            return $obj->lokasi_prioritas_desc <= 360 && $obj->non_lokasi_prioritas;
        })->count();

        $tile->menyasar_lte_360_kota_kota_none_non_lokus = $mvForm2Clone->filter(function ($obj) {
            return $obj->lokasi_prioritas_desc <= 360 && !$obj->non_lokasi_prioritas;
        })->count();

        return $this->returnJsonSuccess("Data fetched successfully", $tile);
    }

    public function chart2(Request $request){
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
        $dataKinerjaPembangunan = MvForm2::select('intervensi_kode',
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


        return $this->returnJsonSuccess("Data fetched successfully", $dataKinerjaPembangunan);
    }

    public function chart3(Request $request){
        $tahun = now()->year;
        $bulan = now()->month;
        $semester = ($bulan/6) <= 6 ? 1 : 2;

        $kl = [];
        $intervensi = [];
        $search = '';

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
            $search = $request->search;
        }

        $dataKinerjaPembangunan = MvForm2::select(
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
        $sasaranPrioritas->value = $dataKinerjaPembangunan->sasaran_prioritas;

        $sasaranPenting = new \stdClass;
        $sasaranPenting->category = "Sasaran Penting";
        $sasaranPenting->value = $dataKinerjaPembangunan->sasaran_penting;

        $sasaranLainnya = new \stdClass;
        $sasaranLainnya->category = "Sasaran Lainnya";
        $sasaranLainnya->value = $dataKinerjaPembangunan->sasaran_lainnya;

        $data = [];
        $data[] = $sasaranPrioritas;
        $data[] = $sasaranPenting;
        $data[] = $sasaranLainnya;


        return $this->returnJsonSuccess("Data fetched successfully", $data);
    }

    public function chart4(Request $request){
        $tahun = now()->year;
        $bulan = now()->month;
        $semester = ($bulan/6) <= 6 ? 1 : 2;

        $kl = [];
        $intervensi = [];
        $search = '';

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
            $search = $request->search;
        }

        $dataKinerjaPembangunan = MvForm2::select(
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

        $adaKoordinasi = new \stdClass;
        $adaKoordinasi->category = "Ada Koordinasi";
        $adaKoordinasi->value = $dataKinerjaPembangunan->ada_koordinasi;

        $tidakAdaKoordinasi = new \stdClass;
        $tidakAdaKoordinasi->category = "Tidak Ada Koordinasi";
        $tidakAdaKoordinasi->value = $dataKinerjaPembangunan->tidak_ada_koordinasi;

        $naKoordinasi = new \stdClass;
        $naKoordinasi->category = "N/A";
        $naKoordinasi->value = $dataKinerjaPembangunan->na_koordinasi;

        $data = [];
        $data[] = $adaKoordinasi;
        $data[] = $tidakAdaKoordinasi;
        $data[] = $naKoordinasi;


        return $this->returnJsonSuccess("Data fetched successfully", $data);
    }

    public function chart5(Request $request){
        $tahun = now()->year;
        $bulan = now()->month;
        $semester = ($bulan/6) <= 6 ? 1 : 2;

        $kl = [];
        $intervensi = [];
        $search = '';

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
            $search = $request->search;
        }

        // $dataKinerjaPembangunan = MvForm2::select(
        //                                             \DB::raw("COUNT(CASE WHEN koord_kl AND koord_pemda = false AND koord_non = false THEN 1 END) as kl"),
        //                                             \DB::raw("COUNT(CASE WHEN koord_kl = false AND koord_pemda AND koord_non = false THEN 1 END) as pemda"),
        //                                             \DB::raw("COUNT(CASE WHEN koord_kl = false AND koord_pemda = false AND koord_non THEN 1 END) as non"),
        //                                             \DB::raw("COUNT(CASE WHEN koord_kl AND koord_pemda AND koord_non = false  THEN 1 END) as kl_pemda"),
        //                                             \DB::raw("COUNT(CASE WHEN koord_kl AND koord_pemda = false AND koord_non  THEN 1 END) as kl_non"),
        //                                             \DB::raw("COUNT(CASE WHEN koord_kl = false AND koord_pemda AND koord_non  THEN 1 END) as pemda_non"),
        //                                             \DB::raw("COUNT(CASE WHEN koord_kl AND koord_pemda AND koord_non THEN 1 END) as kl_pemda_non"),
        //                                             )
        $dataKinerjaPembangunan = MvForm2::select(
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

        $result = [];

        $data = new \stdClass;
        $data->name = "Pemda";
        $data->value = $dataKinerjaPembangunan->pemda;
        $result[] = $data;

        $data = new \stdClass;
        $data->name = "K/L Lain";
        $data->value = $dataKinerjaPembangunan->kl;
        $result[] = $data;

        $data = new \stdClass;
        $data->name = "Non-Pemerintah";
        $data->value = $dataKinerjaPembangunan->non;
        $result[] = $data;

        $data = new \stdClass;
        $data->name = "K/L Lain, Pemda";
        $data->value = $dataKinerjaPembangunan->kl_pemda;
        $data->sets = [
            "K/L Lain",
            "Pemda"
        ];
        $result[] = $data;

        $data = new \stdClass;
        $data->name = "K/L Lain, Non-Pemerintah";
        $data->value = $dataKinerjaPembangunan->kl_non;
        $data->sets = [
            "K/L Lain",
            "Non-Pemerintah"
        ];
        $result[] = $data;

        $data = new \stdClass;
        $data->name = "Pemda, Non-Pemerintah";
        $data->value = $dataKinerjaPembangunan->pemda_non;
        $data->sets = [
            "Pemda",
            "Non-Pemerintah"
        ];
        $result[] = $data;

        $data = new \stdClass;
        $data->name = "K/L Lain, Pemda, Non-Pemerintah";
        $data->value = $dataKinerjaPembangunan->kl_pemda_non;
        $data->sets = [
            "K/L Lain",
            "Pemda",
            "Non-Pemerintah"
        ];
        $result[] = $data;


        return $this->returnJsonSuccess("Data fetched successfully", $result);
    }

    public function pembagi($a, $b){
        return $b == 0 ? 0 : ($a / $b);
    }

}

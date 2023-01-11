<?php

namespace App\Http\Controllers\Api\V1\KinerjaAnggaranPembangunan;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\Kinerja\KinerjaAnggaran;
use App\Models\Kinerja\PerkembanganPenandaan;
//use App\Models\Kinerja\MvRenjaTematikKeywordSepakati;
use Carbon\Carbon;

class KinerjaAnggaranController extends BaseController
{
    public function tahunSemester(Request $request){
        $tahun = KinerjaAnggaran::select('tahun', 'semester')->groupBy('tahun', 'semester')->get();
        return $this->returnJsonSuccess("Data fetched successfully", $tahun);
    }

    public function kementerian(Request $request){
        $kementerian = KinerjaAnggaran::select('kementerian_kode', 'kementerian_nama')->groupBy('kementerian_kode', 'kementerian_nama')->get();
        return $this->returnJsonSuccess("Data fetched successfully", $kementerian);
    }

    public function intervensi(Request $request){
        $intervensi = KinerjaAnggaran::select('intervensi_kode', 'intervensi_nama')->groupBy('intervensi_kode', 'intervensi_nama')->get();
        return $this->returnJsonSuccess("Data fetched successfully", $intervensi);
    }

    public function getKinerjaAnggaran(Request $request){
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

        $allKementerian = KinerjaAnggaran::select('kementerian_kode', 'kementerian_nama')->groupBy('kementerian_kode', 'kementerian_nama')->get();
        $allKinerjaAnggaran = KinerjaAnggaran::all();

        $dataKinerjaAnggaran = KinerjaAnggaran::where(function($q) use($tahun, $bulan, $semester, $kl, $intervensi){
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

        $dataPerkembanganPenandaan = PerkembanganPenandaan::where(function($q) use($tahun, $bulan, $semester, $kl, $intervensi){
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
        })->get();

        $kinerjaAnggaranClone = clone $dataPerkembanganPenandaan;
        $kinerjaAnggaranTagging = clone $dataPerkembanganPenandaan;
        $kementerianCount = $kinerjaAnggaranClone->pluck('kementerian_kode')->unique()->values()->count();

        $kinerjaAnggaranTagging = $kinerjaAnggaranTagging->filter(function ($obj) {
            return $obj->status_tagging == "Tagging";
        })->values();

        $lsRo = $kinerjaAnggaranClone->map->only(['tahun', 'semester', 'kementerian_kode', 'intervensi_kode', 'program_kode', 'kegiatan_kode', 'output_kode', 'suboutput_kode'])->unique()->values();

        $lsRoTagging = $kinerjaAnggaranTagging->map->only(['tahun', 'semester', 'kementerian_kode', 'intervensi_kode', 'program_kode', 'kegiatan_kode', 'output_kode', 'suboutput_kode'])->unique()->values();
        $lsRoTeridentifikasi = $kinerjaAnggaranClone->map->only(['tahun', 'semester', 'kementerian_kode', 'intervensi_kode', 'program_kode', 'kegiatan_kode', 'output_kode', 'suboutput_kode'])->unique()->values();
        
        $roIntervensiSpesifik = $lsRoTagging->filter(function ($obj) {
            $obj = (object)$obj;
            return $obj->intervensi_kode == "A";
        });

        $_roIntervensiSpesifik = $lsRoTeridentifikasi->filter(function ($obj) {
            $obj = (object)$obj;
            return $obj->intervensi_kode == "A";
        });

        $roIntervensiSensitif = $lsRoTagging->filter(function ($obj) {
            $obj = (object)$obj;
            return $obj->intervensi_kode == "B";
        });

        $_roIntervensiSensitif = $lsRoTeridentifikasi->filter(function ($obj) {
            $obj = (object)$obj;
            return $obj->intervensi_kode == "B";
        });

        $roIntervensiPendamping = $lsRoTagging->filter(function ($obj) {
            $obj = (object)$obj;
            return $obj->intervensi_kode == "C";
        });

        $_roIntervensiPendamping = $lsRoTeridentifikasi->filter(function ($obj) {
            $obj = (object)$obj;
            return $obj->intervensi_kode == "C";
        });

        $realisasiTagging = new \stdClass;
        $realisasiTagging->all =  array(
            "teridentifikasi" => $kinerjaAnggaranClone->count(),
            "tagging" => $kinerjaAnggaranTagging->count(),
            'intervensi_kode' => 'ALL'
        );
        $realisasiTagging->spesifik = array(
            "teridentifikasi" => $_roIntervensiSpesifik->count(),
            "tagging" => $roIntervensiSpesifik->count(),
            'intervensi_kode' => 'A'

        );
        $realisasiTagging->sensitif = array(
            "teridentifikasi" => $_roIntervensiSensitif->count(),
            "tagging" => $roIntervensiSensitif->count(),
            'intervensi_kode' => 'B'

        );
        $realisasiTagging->pendamping = array(
            "teridentifikasi" => $_roIntervensiPendamping->count(),
            "tagging" => $roIntervensiPendamping->count(),
            'intervensi_kode' => 'C'

        );




        $kinerjaAnggaranClone = clone $dataKinerjaAnggaran;
        $kementerianCount = $kinerjaAnggaranClone->pluck('kementerian_kode')->unique()->count();
        
        $roIntervensiSpesifik = $kinerjaAnggaranClone->filter(function ($obj) {
            return $obj->intervensi_kode == "A";
        });

        $roIntervensiSensitif = $kinerjaAnggaranClone->filter(function ($obj) {
            return $obj->intervensi_kode == "B";
        });

        $roIntervensiPendamping = $kinerjaAnggaranClone->filter(function ($obj) {
            return $obj->intervensi_kode == "C";
        });
        
        
        $tile = new \stdClass;

        $tile->realisasi_tagging = $realisasiTagging;

        $perkembangan_tagging_dan_pagu = new \stdClass;
        $perkembangan_tagging_dan_pagu->c_kl = $kementerianCount;
        $perkembangan_tagging_dan_pagu->p_kl = $kementerianCount / $allKementerian->count() * 100;
        $perkembangan_tagging_dan_pagu->tot_ro = $roIntervensiSpesifik->count() + $roIntervensiSensitif->count() + $roIntervensiPendamping->count();
        $perkembangan_tagging_dan_pagu->spesifik_ro = $roIntervensiSpesifik->count();
        $perkembangan_tagging_dan_pagu->sesnsitif_ro = $roIntervensiSensitif->count();
        $perkembangan_tagging_dan_pagu->pendamping_ro = $roIntervensiPendamping->count();
        $perkembangan_tagging_dan_pagu->pagu_dokumen_ringkasan = $kinerjaAnggaranClone->sum('alokasi_0');
        $perkembangan_tagging_dan_pagu->pagu_awal_dipa = $kinerjaAnggaranClone->sum('alokasi_1');
        $perkembangan_tagging_dan_pagu->pagu_harian_dipa = $kinerjaAnggaranClone->sum('alokasi_2');

        $data_intervensi = new \stdClass;

        $data_intervensi->sensitif_p_realisasi_terhadap_pagu_awal = $this->pembagi($roIntervensiSensitif->sum('alokasi_realisasi'), $roIntervensiSensitif->sum('alokasi_1'));
        $data_intervensi->sensitif_p_realisasi_terhadap_pagu_harian = $this->pembagi($roIntervensiSensitif->sum('alokasi_realisasi'), $roIntervensiSensitif->sum('alokasi_2'));
        $data_intervensi->sensitif_level_output_pagu_dokumen_ringkasan = $roIntervensiSensitif->sum('alokasi_0');
        $data_intervensi->sensitif_level_output_pagu_awal_dipa = $roIntervensiSensitif->sum('alokasi_1');
        $data_intervensi->sensitif_level_output_harian_dipa = $roIntervensiSensitif->sum('alokasi_2');
        $data_intervensi->sensitif_level_output_realisasi = $roIntervensiSensitif->sum('alokasi_realisasi');
        $data_intervensi->sensitif_analisis_lanjutan_pagu_dokumen_ringkasan = $roIntervensiSensitif->sum('anl_alokasi_0');
        $data_intervensi->sensitif_analisis_lanjutan_pagu_awal_dipa = $roIntervensiSensitif->sum('anl_alokasi_1');
        $data_intervensi->sensitif_analisis_lanjutan_harian_dipa = $roIntervensiSensitif->sum('anl_alokasi_2');
        $data_intervensi->sensitif_analisis_lanjutan_ralisasi = $roIntervensiSensitif->sum('anl_alokasi_realisasi');

        $data_intervensi->spesifik_p_realisasi_terhadap_pagu_awal = $this->pembagi($roIntervensiSpesifik->sum('alokasi_realisasi'), $roIntervensiSpesifik->sum('alokasi_1'));
        $data_intervensi->spesifik_p_realisasi_terhadap_pagu_harian = $this->pembagi($roIntervensiSpesifik->sum('alokasi_realisasi'), $roIntervensiSpesifik->sum('alokasi_2'));
        $data_intervensi->spesifik_level_output_pagu_dokumen_ringkasan = $roIntervensiSpesifik->sum('alokasi_0');
        $data_intervensi->spesifik_level_output_pagu_awal_dipa = $roIntervensiSpesifik->sum('alokasi_1');
        $data_intervensi->spesifik_level_output_harian_dipa = $roIntervensiSpesifik->sum('alokasi_2');
        $data_intervensi->spesifik_level_output_realisasi = $roIntervensiSpesifik->sum('alokasi_realisasi');
        $data_intervensi->spesifik_analisis_lanjutan_pagu_dokumen_ringkasan = $roIntervensiSpesifik->sum('anl_alokasi_0');
        $data_intervensi->spesifik_analisis_lanjutan_pagu_awal_dipa = $roIntervensiSpesifik->sum('anl_alokasi_1');
        $data_intervensi->spesifik_analisis_lanjutan_harian_dipa = $roIntervensiSpesifik->sum('anl_alokasi_2');
        $data_intervensi->spesifik_analisis_lanjutan_ralisasi = $roIntervensiSpesifik->sum('anl_alokasi_realisasi');

        $data_intervensi->pendamping_p_realisasi_terhadap_pagu_awal = $this->pembagi($roIntervensiPendamping->sum('alokasi_realisasi'), $roIntervensiPendamping->sum('alokasi_1'));
        $data_intervensi->pendamping_p_realisasi_terhadap_pagu_harian = $this->pembagi($roIntervensiPendamping->sum('alokasi_realisasi'), $roIntervensiPendamping->sum('alokasi_2'));
        $data_intervensi->pendamping_level_output_pagu_dokumen_ringkasan = $roIntervensiPendamping->sum('alokasi_0');
        $data_intervensi->pendamping_level_output_pagu_awal_dipa = $roIntervensiPendamping->sum('alokasi_1');
        $data_intervensi->pendamping_level_output_harian_dipa = $roIntervensiPendamping->sum('alokasi_2');
        $data_intervensi->pendamping_level_output_realisasi = $roIntervensiPendamping->sum('alokasi_realisasi');
        $data_intervensi->pendamping_analisis_lanjutan_pagu_dokumen_ringkasan = $roIntervensiPendamping->sum('anl_alokasi_0');
        $data_intervensi->pendamping_analisis_lanjutan_pagu_awal_dipa = $roIntervensiPendamping->sum('anl_alokasi_1');
        $data_intervensi->pendamping_analisis_lanjutan_harian_dipa = $roIntervensiPendamping->sum('anl_alokasi_2');
        $data_intervensi->pendamping_analisis_lanjutan_ralisasi = $roIntervensiPendamping->sum('anl_alokasi_realisasi');

        $capaianRoByCapaianOutput = KinerjaAnggaran::select(
            \DB::raw('SUM(case WHEN prsn_anl_output > 0.9 THEN 1 ELSE 0 END) r1'),
            \DB::raw('SUM(case WHEN prsn_anl_output <= 0.9 AND prsn_anl_output >0.7 THEN 1 ELSE 0 END) r2'),
            \DB::raw('SUM(case WHEN prsn_anl_output <= 0.7 AND prsn_anl_output >0.5 THEN 1 ELSE 0 END) r3'),
            \DB::raw('SUM(case WHEN prsn_anl_output <= 0.5 AND prsn_anl_output >0.3 THEN 1 ELSE 0 END) r4'),
            \DB::raw('SUM(case WHEN coalesce(prsn_anl_output,0) <= 0.3 AND coalesce(prsn_anl_output,0) >=0 THEN 1 ELSE 0 END) r5'),
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

        $capaianRoByKinerjaAnggaran = KinerjaAnggaran::select(
            \DB::raw('SUM(case WHEN prsn_anl_realisasi > 0.9 THEN 1 ELSE 0 END) r1'),
            \DB::raw('SUM(case WHEN prsn_anl_realisasi <= 0.9 AND prsn_anl_realisasi >0.7 THEN 1 ELSE 0 END) r2'),
            \DB::raw('SUM(case WHEN prsn_anl_realisasi <= 0.7 AND prsn_anl_realisasi >0.5 THEN 1 ELSE 0 END) r3'),
            \DB::raw('SUM(case WHEN prsn_anl_realisasi <= 0.5 AND prsn_anl_realisasi >0.3 THEN 1 ELSE 0 END) r4'),
            \DB::raw('SUM(case WHEN coalesce(prsn_anl_realisasi,0) <= 0.3 AND coalesce(prsn_anl_realisasi,0) >=0 THEN 1 ELSE 0 END) r5'),
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

        $capaianRo = new \stdClass;
        $capaianRo->capaianOutputByCapaianOutput = $capaianRoByCapaianOutput;
        $capaianRo->capaianRoByKinerjaAnggaran = $capaianRoByKinerjaAnggaran;
        // $capaianRo->parameterdetail = ['91','71','51','31','0'];

        $parameterRo = new \stdClass;
        $parameterRo = ['p1' => '91','p2' => '71','p3' => '51','p4' => '31','p5' => '0'];

        $tile->perkembangan_tagging_dan_pagu = $perkembangan_tagging_dan_pagu;
        $tile->data_intervensi = $data_intervensi;
        $tile->capaian_ro = $capaianRo;
        $tile->parameterCapaianKinerja = $parameterRo;

        $lsKementerian = $kinerjaAnggaranClone->map->only(['tahun', 'semester', 'kementerian_kode', 'kementerian_nama','kementerian_nama_short'])->unique()->values();


        $lsKementerian = $lsKementerian->map(function($objKementerian) use ($kinerjaAnggaranClone){
            $objKementerian = (object)$objKementerian;
            $kinerjaAnggaranKementerian = $kinerjaAnggaranClone->filter(function ($obj) use($objKementerian) {
                return $obj->kementerian_kode == $objKementerian->kementerian_kode;
            });



            $lsIntervensi = $kinerjaAnggaranKementerian->map->only(['tahun', 'semester', 'intervensi_kode', 'intervensi_nama'])->unique()->values();
            $lsProgam = $kinerjaAnggaranKementerian->map->only(['tahun', 'semester', 'program_kode', 'program_nama'])->unique()->values();
            $lsKegiatan = $kinerjaAnggaranKementerian->map->only(['tahun', 'semester', 'kegiatan_kode', 'kegiatan_nama'])->unique()->values();
            $lsOutput = $kinerjaAnggaranKementerian->map->only(['tahun', 'semester', 'output_kode', 'output_nama'])->unique()->values();
           // dd($lsOutput);

            $objKementerian->kl_id = $objKementerian->kementerian_kode;
            $objKementerian->name = $objKementerian->kementerian_nama;
            $objKementerian->name_short = $objKementerian->kementerian_nama_short;

            unset($objKementerian->kementerian_nama_short);

            $objKementerian->alokasi_0 = $kinerjaAnggaranKementerian->sum('alokasi_0');
            $objKementerian->alokasi_1 = $kinerjaAnggaranKementerian->sum('alokasi_1');
            $objKementerian->alokasi_2 = $kinerjaAnggaranKementerian->sum('alokasi_2');
            $objKementerian->alokasi_realisasi = $kinerjaAnggaranKementerian->sum('alokasi_realisasi');
            // $objKementerian->prsn_realisasi = $kinerjaAnggaranKementerian->avg('prsn_realisasi');
            $objKementerian->prsn_realisasi = $this->pembagi($objKementerian->alokasi_realisasi, $objKementerian->alokasi_2);

            $objKementerian->volume_0 = "";
            $objKementerian->volume_1 = "";
            $objKementerian->volume_2 = "";
            $objKementerian->volume_realisasi = "";
            $objKementerian->satuan = "";
            $objKementerian->prsen_output = (string) number_format($kinerjaAnggaranKementerian->avg('prsen_output'),4);

            $objKementerian->anl_alokasi_0 = $kinerjaAnggaranKementerian->sum('anl_alokasi_0');
            $objKementerian->anl_alokasi_1 = $kinerjaAnggaranKementerian->sum('anl_alokasi_1');
            $objKementerian->anl_alokasi_2 = $kinerjaAnggaranKementerian->sum('anl_alokasi_2');
            $objKementerian->anl_alokasi_rpd = $kinerjaAnggaranKementerian->sum('anl_alokasi_rpd');
            $objKementerian->anl_alokasi_realisasi = $kinerjaAnggaranKementerian->sum('anl_alokasi_realisasi');
            // $objKementerian->prsn_anl_realisasi = $kinerjaAnggaranKementerian->sum('prsn_anl_realisasi');
            $objKementerian->prsn_anl_realisasi = $this->pembagi($objKementerian->anl_alokasi_realisasi, $objKementerian->anl_alokasi_2);
            // $objKementerian->prsn_anl_realisasi_rpd = $kinerjaAnggaranKementerian->avg('prsn_anl_realisasi_rpd');
            $objKementerian->prsn_anl_realisasi_rpd =$this->pembagi($objKementerian->anl_alokasi_realisasi, $objKementerian->anl_alokasi_rpd);

            $objKementerian->anl_volume_0 = "";
            $objKementerian->anl_volume_1 = "";
            $objKementerian->anl_volume_2 = "";
            $objKementerian->anl_volume_realisasi = "";
            $objKementerian->satuan2 = "";

            $objKementerian->prsn_anl_output = ($kinerjaAnggaranKementerian->avg('prsn_anl_output') == null) ? 0 :  (string) number_format($kinerjaAnggaranKementerian->avg('prsn_anl_output'),4);

            // $objKementerian->kinerja_umum = $kinerjaAnggaranKementerian->avg('kinerja_umum');
            $objKementerian->kinerja_umum = $this->pembagi($objKementerian->prsn_anl_output, $objKementerian->prsn_anl_realisasi, false);

            $objKementerian->keterangan = "";
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

                $objIntervensi->alokasi_0 = $kinerjaAnggaranIntervensi->sum('alokasi_0');
                $objIntervensi->alokasi_1 = $kinerjaAnggaranIntervensi->sum('alokasi_1');
                $objIntervensi->alokasi_2 = $kinerjaAnggaranIntervensi->sum('alokasi_2');
                $objIntervensi->alokasi_realisasi = $kinerjaAnggaranIntervensi->sum('alokasi_realisasi');
                // $objIntervensi->prsn_realisasi = $kinerjaAnggaranIntervensi->avg('prsn_realisasi');
                $objIntervensi->prsn_realisasi = $this->pembagi($objIntervensi->alokasi_realisasi, $objIntervensi->alokasi_2);

                $objIntervensi->volume_0 = "";
                $objIntervensi->volume_1 = "";
                $objIntervensi->volume_2 = "";
                $objIntervensi->volume_realisasi = "";
                $objIntervensi->satuan = "";
                $objIntervensi->prsen_output = (string) number_format($kinerjaAnggaranIntervensi->avg('prsen_output'),4);

                $objIntervensi->anl_alokasi_0 = $kinerjaAnggaranIntervensi->sum('anl_alokasi_0');
                $objIntervensi->anl_alokasi_1 = $kinerjaAnggaranIntervensi->sum('anl_alokasi_1');
                $objIntervensi->anl_alokasi_2 = $kinerjaAnggaranIntervensi->sum('anl_alokasi_2');
                $objIntervensi->anl_alokasi_rpd = $kinerjaAnggaranIntervensi->sum('anl_alokasi_rpd');
                $objIntervensi->anl_alokasi_realisasi = $kinerjaAnggaranIntervensi->sum('anl_alokasi_realisasi');
                // $objIntervensi->prsn_anl_realisasi = $kinerjaAnggaranIntervensi->sum('prsn_anl_realisasi');
                $objIntervensi->prsn_anl_realisasi = $this->pembagi($objIntervensi->anl_alokasi_realisasi, $objIntervensi->anl_alokasi_2);
                // $objIntervensi->prsn_anl_realisasi_rpd = $kinerjaAnggaranIntervensi->avg('prsn_anl_realisasi_rpd');
                $objIntervensi->prsn_anl_realisasi_rpd = $this->pembagi($objIntervensi->anl_alokasi_realisasi, $objIntervensi->anl_alokasi_rpd);

                $objIntervensi->anl_volume_0 = "";
                $objIntervensi->anl_volume_1 = "";
                $objIntervensi->anl_volume_2 = "";
                $objIntervensi->anl_volume_realisasi = "";
                $objIntervensi->satuan2 = "";

                $objIntervensi->prsn_anl_output = ($kinerjaAnggaranIntervensi->avg('prsn_anl_output') == null) ? 0 : (string) number_format($kinerjaAnggaranIntervensi->avg('prsn_anl_output'),4);

                // $objIntervensi->kinerja_umum = $kinerjaAnggaranIntervensi->avg('kinerja_umum');
                $objIntervensi->kinerja_umum = $this->pembagi($objIntervensi->prsn_anl_output, $objIntervensi->prsn_anl_realisasi, false);

                $objIntervensi->keterangan = "testing iqbal";
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
    
                    $objProgram->alokasi_0 = $kinerjaAnggaranProgram->sum('alokasi_0');
                    $objProgram->alokasi_1 = $kinerjaAnggaranProgram->sum('alokasi_1');
                    $objProgram->alokasi_2 = $kinerjaAnggaranProgram->sum('alokasi_2');
                    $objProgram->alokasi_realisasi = $kinerjaAnggaranProgram->sum('alokasi_realisasi');
                    // $objProgram->prsn_realisasi = $kinerjaAnggaranProgram->avg('prsn_realisasi');
                    $objProgram->prsn_realisasi = $this->pembagi($objProgram->alokasi_realisasi, $objProgram->alokasi_2);

                    $objProgram->volume_0 = "";
                    $objProgram->volume_1 = "";
                    $objProgram->volume_2 = "";
                    $objProgram->volume_realisasi = "";
                    $objProgram->satuan = "";
                    $objProgram->prsen_output = (string) number_format($kinerjaAnggaranProgram->avg('prsen_output'),4);

                    $objProgram->anl_alokasi_0 = $kinerjaAnggaranProgram->sum('anl_alokasi_0');
                    $objProgram->anl_alokasi_1 = $kinerjaAnggaranProgram->sum('anl_alokasi_1');
                    $objProgram->anl_alokasi_2 = $kinerjaAnggaranProgram->sum('anl_alokasi_2');
                    $objProgram->anl_alokasi_rpd = $kinerjaAnggaranProgram->sum('anl_alokasi_rpd');
                    $objProgram->anl_alokasi_realisasi = $kinerjaAnggaranProgram->sum('anl_alokasi_realisasi');
                    // $objProgram->prsn_anl_realisasi = $kinerjaAnggaranProgram->sum('prsn_anl_realisasi');
                    $objProgram->prsn_anl_realisasi = $this->pembagi($objProgram->anl_alokasi_realisasi, $objProgram->anl_alokasi_2);
                    // $objProgram->prsn_anl_realisasi_rpd = $kinerjaAnggaranProgram->avg('prsn_anl_realisasi_rpd');
                    $objProgram->prsn_anl_realisasi_rpd = $this->pembagi($objProgram->anl_alokasi_realisasi, $objProgram->anl_alokasi_rpd);

                    $objProgram->anl_volume_0 = "";
                    $objProgram->anl_volume_1 = "";
                    $objProgram->anl_volume_2 = "";
                    $objProgram->anl_volume_realisasi = "";
                    $objProgram->satuan2 = "";

                    $objProgram->prsn_anl_output = ($kinerjaAnggaranProgram->avg('prsn_anl_output') == null)? 0 : (string) number_format($kinerjaAnggaranProgram->avg('prsn_anl_output'),4);

                    // $objProgram->kinerja_umum = $kinerjaAnggaranProgram->avg('kinerja_umum');
                    $objProgram->kinerja_umum = $this->pembagi($objProgram->prsn_anl_output, $objProgram->prsn_anl_realisasi, false);

                    $objProgram->keterangan = "";
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
        
                        $objKegiatan->alokasi_0 = $kinerjaAnggaranKegiatan->sum('alokasi_0');
                        $objKegiatan->alokasi_1 = $kinerjaAnggaranKegiatan->sum('alokasi_1');
                        $objKegiatan->alokasi_2 = $kinerjaAnggaranKegiatan->sum('alokasi_2');
                        $objKegiatan->alokasi_realisasi = $kinerjaAnggaranKegiatan->sum('alokasi_realisasi');
                        // $objKegiatan->prsn_realisasi = $kinerjaAnggaranKegiatan->avg('prsn_realisasi');
                        $objKegiatan->prsn_realisasi = $this->pembagi($objKegiatan->alokasi_realisasi, $objKegiatan->alokasi_2);

                        $objKegiatan->volume_0 = "";
                        $objKegiatan->volume_1 = "";
                        $objKegiatan->volume_2 = "";
                        $objKegiatan->volume_realisasi = "";
                        $objKegiatan->satuan = "";
                        $objKegiatan->prsen_output = (string) number_format($kinerjaAnggaranKegiatan->avg('prsen_output'),4);

                        $objKegiatan->anl_alokasi_0 = $kinerjaAnggaranKegiatan->sum('anl_alokasi_0');
                        $objKegiatan->anl_alokasi_1 = $kinerjaAnggaranKegiatan->sum('anl_alokasi_1');
                        $objKegiatan->anl_alokasi_2 = $kinerjaAnggaranKegiatan->sum('anl_alokasi_2');
                        $objKegiatan->anl_alokasi_rpd = $kinerjaAnggaranKegiatan->sum('anl_alokasi_rpd');
                        $objKegiatan->anl_alokasi_realisasi = $kinerjaAnggaranKegiatan->sum('anl_alokasi_realisasi');
                        // $objKegiatan->prsn_anl_realisasi = $kinerjaAnggaranKegiatan->sum('prsn_anl_realisasi');
                        $objKegiatan->prsn_anl_realisasi = $this->pembagi($objKegiatan->anl_alokasi_realisasi, $objKegiatan->anl_alokasi_2);
                        // $objKegiatan->prsn_anl_realisasi_rpd = $kinerjaAnggaranKegiatan->avg('prsn_anl_realisasi_rpd');
                        $objKegiatan->prsn_anl_realisasi_rpd = $this->pembagi($objKegiatan->anl_alokasi_realisasi, $objKegiatan->anl_alokasi_rpd);

                        $objKegiatan->anl_volume_0 = "";
                        $objKegiatan->anl_volume_1 = "";
                        $objKegiatan->anl_volume_2 = "";
                        $objKegiatan->anl_volume_realisasi = "";
                        $objKegiatan->satuan2 = "";

                        $objKegiatan->prsn_anl_output = ($kinerjaAnggaranKegiatan->avg('prsn_anl_output') == null)? 0 : (string) number_format($kinerjaAnggaranKegiatan->avg('prsn_anl_output'),4);

                        // $objKegiatan->kinerja_umum = $kinerjaAnggaranKegiatan->avg('kinerja_umum');
                        $objKegiatan->kinerja_umum = $this->pembagi($objKegiatan->prsn_anl_output, $objKegiatan->prsn_anl_realisasi, false);

                        $objKegiatan->keterangan = "";
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
            
                            $objOutput->alokasi_0 = $kinerjaAnggaranOutput->sum('alokasi_0');
                            $objOutput->alokasi_1 = $kinerjaAnggaranOutput->sum('alokasi_1');
                            $objOutput->alokasi_2 = $kinerjaAnggaranOutput->sum('alokasi_2');
                            $objOutput->alokasi_realisasi = $kinerjaAnggaranOutput->sum('alokasi_realisasi');
                            // $objOutput->prsn_realisasi = $kinerjaAnggaranOutput->avg('prsn_realisasi');
                            $objOutput->prsn_realisasi = $this->pembagi($objOutput->alokasi_realisasi, $objOutput->alokasi_2);

                            $objOutput->volume_0 = "";
                            $objOutput->volume_1 = "";
                            $objOutput->volume_2 = "";
                            $objOutput->volume_realisasi = "";
                            $objOutput->satuan = "";
                            $objOutput->prsen_output = (string) number_format($kinerjaAnggaranOutput->avg('prsen_output'),4);

                            $objOutput->anl_alokasi_0 = $kinerjaAnggaranOutput->sum('anl_alokasi_0');
                            $objOutput->anl_alokasi_1 = $kinerjaAnggaranOutput->sum('anl_alokasi_1');
                            $objOutput->anl_alokasi_2 = $kinerjaAnggaranOutput->sum('anl_alokasi_2');
                            $objOutput->anl_alokasi_rpd = $kinerjaAnggaranOutput->sum('anl_alokasi_rpd');
                            $objOutput->anl_alokasi_realisasi = $kinerjaAnggaranOutput->sum('anl_alokasi_realisasi');
                            // $objOutput->prsn_anl_realisasi = $kinerjaAnggaranOutput->sum('prsn_anl_realisasi');
                            $objOutput->prsn_anl_realisasi = $this->pembagi($objOutput->anl_alokasi_realisasi, $objOutput->anl_alokasi_2);
                            // $objOutput->prsn_anl_realisasi_rpd = $kinerjaAnggaranOutput->avg('prsn_anl_realisasi_rpd');
                            $objOutput->prsn_anl_realisasi_rpd = $this->pembagi($objOutput->anl_alokasi_realisasi, $objOutput->anl_alokasi_rpd);

                            $objOutput->anl_volume_0 = "";
                            $objOutput->anl_volume_1 = "";
                            $objOutput->anl_volume_2 = "";
                            $objOutput->anl_volume_realisasi = "";
                            $objOutput->satuan2 = "";
                            $objOutput->prsn_anl_output = ($kinerjaAnggaranOutput->avg('prsn_anl_output') == null) ? "0" : (string) number_format($kinerjaAnggaranOutput->avg('prsn_anl_output'),4);

                            // $objOutput->kinerja_umum = $kinerjaAnggaranOutput->avg('kinerja_umum');
                            $objOutput->kinerja_umum = $this->pembagi($objOutput->prsn_anl_output, $objOutput->prsn_anl_realisasi, false);

                            $objOutput->keterangan = "";
                            $objOutput->jml_program = 0;
                            $objOutput->jml_kegiatan = 0;
                            $objOutput->jml_kro = 0;
                            $objOutput->jml_ro = $kinerjaAnggaranOutput->count();
                            // echo "<pre>";
                            // print_r($kinerjaAnggaranOutput);
                            // exit;
                            
                            //$prsn_anl_output = 0;
                         //   $input = '';
                           // echo $kinerjaAnggaranOutput->sum('prsn_anl_output');
                           // exit;
                           $objSubOutputd = [];
                            $objOutput->_children = $kinerjaAnggaranOutput->map(function($objSubOutput) use (&$objSubOutputd){
                                    $objSubOutput = (object) $objSubOutput;
                                $objSubOutputd = [];
                                
                            
                                    $objSubOutputd['tahun'] = $objSubOutput->tahun;
                                    $objSubOutputd['intervensi_kode'] = $objSubOutput->intervensi_kode;
                                    $objSubOutputd['intervensi_nama'] = $objSubOutput->intervensi_nama;
                                    $objSubOutputd['kementerian_kode'] = $objSubOutput->kementerian_kode;
                                    $objSubOutputd['kementerian_nama'] = $objSubOutput->kementerian_nama;
                                    $objSubOutputd['program_kode'] = $objSubOutput->program_kode;
                                    $objSubOutputd['program_nama'] = $objSubOutput->program_nama;
                                    $objSubOutputd['kegiatan_kode'] = $objSubOutput->kegiatan_kode;
                                    $objSubOutputd['kegiatan_nama'] = $objSubOutput->kegiatan_nama;
                                    $objSubOutputd['alokasi_0'] = $objSubOutput->alokasi_0;
                                    $objSubOutputd['alokasi_1'] = $objSubOutput->alokasi_1;
                                    $objSubOutputd['alokasi_2'] = $objSubOutput->alokasi_2;
                                    $objSubOutputd['alokasi_realisasi'] = $objSubOutput->alokasi_realisasi;
                                    $objSubOutputd['volume_0'] = $objSubOutput->volume_0;
                                    $objSubOutputd['volume_1'] = $objSubOutput->volume_1;
                                    $objSubOutputd['volume_2'] = $objSubOutput->volume_2;
                                    $objSubOutputd['volume_realisasi'] = $objSubOutput->volume_realisasi;
                                    $objSubOutputd['satuan'] = $objSubOutput->satuan;
                                    $objSubOutputd['anl_alokasi_0'] = $objSubOutput->anl_alokasi_0;
                                    $objSubOutputd['anl_alokasi_1'] = $objSubOutput->anl_alokasi_1;
                                    $objSubOutputd['anl_alokasi_2'] = $objSubOutput->anl_alokasi_2;
                                    $objSubOutputd['anl_alokasi_rpd'] = $objSubOutput->anl_alokasi_rpd;
                                    $objSubOutputd['anl_alokasi_realisasi'] = $objSubOutput->anl_alokasi_realisasi;
                                    $objSubOutputd['anl_volume_0'] = $objSubOutput->anl_volume_0;
                                    $objSubOutputd['anl_volume_1'] = $objSubOutput->anl_volume_1;
                                    $objSubOutputd['anl_volume_2'] = $objSubOutput->anl_volume_2;
                                    $objSubOutputd['anl_volume_realisasi'] = $objSubOutput->anl_volume_realisasi;
                                    $objSubOutputd['satuan2'] = $objSubOutput->satuan2;
                                    $objSubOutputd['prsn_realisasi'] = $objSubOutput->prsn_realisasi;
                                    $objSubOutputd['prsen_output'] = $objSubOutput->prsen_output;
                                    $objSubOutputd['prsn_anl_realisasi'] =  (string)  $objSubOutput->prsn_anl_realisasi;
                                    $objSubOutputd['prsn_anl_realisasi_rpd'] = $objSubOutput->prsn_anl_realisasi_rpd;
                                    $objSubOutputd['prsn_anl_output'] = (string) $objSubOutput->prsn_anl_output;
                                    $objSubOutputd['kinerja_umum'] = $objSubOutput->kinerja_umum;
                                    $objSubOutputd['semester'] = $objSubOutput->semester;
                                    $objSubOutputd['keterangan'] = $objSubOutput->keterangan;

                                    $objSubOutputd['kl_id'] = $objSubOutput->kementerian_kode;
                                    $objSubOutputd['intervensi_id'] = $objSubOutput->intervensi_kode;


                                $objSubOutputd['program_id'] = $objSubOutput->program_kode;
                                $objSubOutputd['kegiatan_id'] = $objSubOutput->kegiatan_kode;
                                $objSubOutputd['kro_id'] = $objSubOutput->output_kode;
                                $objSubOutputd['ro_id'] = $objSubOutput->suboutput_kode;
                                $objSubOutputd['name'] = $objSubOutput->suboutput_nama;

                                // $objSubOutput->prsn_anl_output =  (string) $prsn_anl_output;
                                
                                $objSubOutputd['testing'] = strval($objSubOutput->prsn_anl_output);
                                $objSubOutputd['jml_program'] = 0;
                                $objSubOutputd['jml_kegiatan'] = 0;
                                $objSubOutputd['jml_kro'] = 0;
                                $objSubOutputd['jml_ro'] = 1;
                
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


     //   dd($lsKementerian);

        $result = new \stdClass;
        $result->tile = $tile;
        $result->detail = $lsKementerian;
       

        return $this->returnJsonSuccess("Data fetched successfully", $result);
    }

    public function chart1Hap(Request $request){
        $tahun = now()->year;
        $bulan = now()->month;
        $semester = ($bulan/6) <= 6 ? 1 : 2;

        $kl = [];
        $intervensi = [];

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

        $maxAmount = KinerjaAnggaran::select(\DB::raw("GREATEST(MAX(alokasi_0), MAX(alokasi_1), MAX(alokasi_2), MAX(alokasi_realisasi)) max"))
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
                                        })
                                        ->first();

        $dataKementerian = KinerjaAnggaran::select('kementerian_kode', 'kementerian_nama')
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
                                            })
                                            ->groupBy('kementerian_kode', 'kementerian_nama')
                                            ->get();
        
        $result = [];
        foreach($dataKementerian as $kementerian){
            $resKementerian = new \stdClass;
            $resKementerian->kementerian_kode = $kementerian->kementerian_kode;
            $resKementerian->kementerian_nama = $kementerian->kementerian_nama;

            $dataIntervensi = KinerjaAnggaran::select('intervensi_kode', 'intervensi_nama')
                                            ->where(function($q) use($tahun, $bulan, $semester, $kl, $intervensi, $kementerian){
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

                                                $q->where('kementerian_kode', $kementerian->kementerian_kode);
                                            })
                                            ->groupBy('intervensi_kode', 'intervensi_nama')
                                            ->get();
            $lIntervensi = [];
            foreach($dataIntervensi as $objIntervensi){
                $resIntervensi = new \stdClass;
                $resIntervensi->intervensi_kode = $objIntervensi->intervensi_kode;
                $resIntervensi->intervensi_nama = $objIntervensi->intervensi_nama;

                $dataKinerjaAnggaran = KinerjaAnggaran::select(
                    'tahun',
                    'semester',
                    'intervensi_kode',
                    \DB::raw("CASE WHEN intervensi_kode = 'A' THEN 'Spesifik' WHEN intervensi_kode='B' THEN 'Sensitif' ELSE 'Dukungan' END intervensi_nama"),
                    'kementerian_kode',
                    'kementerian_nama',
                    'kementerian_nama_short',
                    \DB::raw("SUM(alokasi_0) as alokasi_0"),
                    \DB::raw("SUM(alokasi_1) as alokasi_1"),
                    \DB::raw("SUM(alokasi_2) as alokasi_2"),
                    \DB::raw("SUM(alokasi_realisasi) as alokasi_realisasi"),
                    \DB::raw("CASE WHEN coalesce(SUM(alokasi_realisasi), 0) <= 0 THEN NULL ELSE SUM(alokasi_realisasi) / SUM(alokasi_2) END as prsn_realisasi"),
                    \DB::raw("AVG(prsen_output) as prsen_output"),
                    \DB::raw("SUM(anl_alokasi_0) as anl_alokasi_0"),
                    \DB::raw("SUM(anl_alokasi_1) as anl_alokasi_1"),
                    \DB::raw("SUM(anl_alokasi_2) as anl_alokasi_2"),
                    \DB::raw("SUM(anl_alokasi_rpd) as anl_alokasi_rpd"),
                    \DB::raw("SUM(anl_alokasi_realisasi) as anl_alokasi_realisasi"),
                    \DB::raw("CASE WHEN coalesce(SUM(anl_alokasi_2), 0) <= 0 THEN NULL ELSE SUM(anl_alokasi_realisasi) / SUM(anl_alokasi_2) END as prsn_anl_realisasi"),
                    \DB::raw("CASE WHEN coalesce(SUM(anl_alokasi_rpd), 0) <= 0 THEN NULL ELSE SUM(anl_alokasi_realisasi) / SUM(anl_alokasi_rpd) END as prsn_anl_realisasi_rpd"),
                    \DB::raw("AVG(prsn_anl_output) as prsn_anl_output"),
                    \DB::raw("COUNT(0) as jml_ro")
                )
                ->where(function($q) use($tahun, $bulan, $semester, $kl, $intervensi, $objIntervensi, $kementerian){
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

                    $q->where('kementerian_kode', $kementerian->kementerian_kode);
                    $q->where('intervensi_kode', $objIntervensi->intervensi_kode);
                })
                ->groupBy('tahun', 'semester', 'intervensi_kode', 'intervensi_nama', 'kementerian_kode', 'kementerian_nama', 'kementerian_nama_short')
                ->first();

                $resIntervensi->data = $dataKinerjaAnggaran;

                $lIntervensi[] = $resIntervensi;
            }

            $resKementerian->data = $lIntervensi;

            $result[] = $resKementerian;
        }

        $return = new \stdClass;
        $return->maxAmount = $maxAmount->max;
        $return->result = $result;

        return $this->returnJsonSuccess("Data fetched successfully", $return);

    }

    public function chart1(Request $request){
        $tahun = now()->year;
        $bulan = now()->month;
        $semester = ($bulan/6) <= 6 ? 1 : 2;

        $kl = [];
        $intervensi = [];

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

        $dataKinerjaAnggaran = KinerjaAnggaran::select(
            'tahun',
            'semester',
            'intervensi_kode',
            'intervensi_nama',
            'kementerian_kode',
            'kementerian_nama',
            \DB::raw("SUM(alokasi_0) as alokasi_0"),
            \DB::raw("SUM(alokasi_1) as alokasi_1"),
            \DB::raw("SUM(alokasi_2) as alokasi_2"),
            \DB::raw("SUM(alokasi_realisasi) as alokasi_realisasi"),
            \DB::raw("CASE WHEN coalesce(SUM(alokasi_realisasi), 0) <= 0 THEN NULL ELSE SUM(alokasi_realisasi) / SUM(alokasi_realisasi) END as prsn_realisasi"),
            \DB::raw("AVG(prsen_output) as prsen_output"),
            \DB::raw("SUM(anl_alokasi_0) as anl_alokasi_0"),
            \DB::raw("SUM(anl_alokasi_1) as anl_alokasi_1"),
            \DB::raw("SUM(anl_alokasi_2) as anl_alokasi_2"),
            \DB::raw("SUM(anl_alokasi_rpd) as anl_alokasi_rpd"),
            \DB::raw("SUM(anl_alokasi_realisasi) as anl_alokasi_realisasi"),
            \DB::raw("CASE WHEN coalesce(SUM(anl_alokasi_2), 0) <= 0 THEN NULL ELSE SUM(anl_alokasi_realisasi) / SUM(anl_alokasi_2) END as prsn_anl_realisasi"),
            \DB::raw("CASE WHEN coalesce(SUM(anl_alokasi_rpd), 0) <= 0 THEN NULL ELSE SUM(anl_alokasi_realisasi) / SUM(anl_alokasi_rpd) END as prsn_anl_realisasi_rpd"),
            \DB::raw("AVG(prsn_anl_output) as prsn_anl_output"),
            \DB::raw("COUNT(0) as jml_ro")
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
        })
        ->groupBy('tahun', 'semester', 'intervensi_kode', 'intervensi_nama', 'kementerian_kode', 'kementerian_nama')
        ->get();

        return $this->returnJsonSuccess("Data fetched successfully", $dataKinerjaAnggaran);

    }

    public function pembagi($a, $b, $overideMore100 = true){
        if($b == 0){
            return 0;
        }

        if($overideMore100 && ($a / $b) > 1){
            return 1;
        }
        $dt = $a / $b;
        $dt = number_format($dt,4);
        return  $dt;
    }


    public function getDetailRoCapaian(Request $request){


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

        if($request->has('parameter') && !empty($request->parameter)){
            $param = $request->parameter;
            $param = $param / 100;
            //echo 'here';
        }

        if($request->parameter == 0){
            $param = 0;
        }
        // print_r($request->parameter);
        // exit;
       
     //   \DB::enableQueryLog();

        $capaianRoByCapaianOutput = KinerjaAnggaran::select('*')
        ->where(function($q) use($tahun, $bulan, $semester, $kl, $intervensi,$param){



            if($param == '0.91'){
                $q->where('prsn_anl_output', '>',$param);
            }else if($param == '0.71'){
                $q->where('prsn_anl_output', '>=',$param);
                $q->where('prsn_anl_output', '<=','0.9');

            }else if($param == '0.51'){
                $q->where('prsn_anl_output', '>=',$param);
                $q->where('prsn_anl_output', '<=','0.7');
            }else if($param == '0.31'){
                $q->where('prsn_anl_output', '>=',$param);
                $q->where('prsn_anl_output', '<=','0.5');
            }else{
                $q->where('prsn_anl_output','<=','0.3');
                $q->where('prsn_anl_output','>=','0');
            }
          
            if($tahun != "all"){
                $q->where('tahun', $tahun);
            }

            if($semester != "all"){
                $q->where('semester', $semester);
            }

            if($kl[0] != "all"){
                $q->whereIn('kementerian_kode', $kl);
            }

            if($intervensi != "all"){
                $q->whereIn('intervensi_kode', $intervensi);
            }
        })->orderBy('kementerian_kode','ASC')->get();



        $result = new \stdClass;
        $kinerjaAnggaranClone = clone $capaianRoByCapaianOutput;
        $lsKementerian = $kinerjaAnggaranClone->map->only(['kementerian_kode', 'kementerian_nama'])->unique()->values();
       // dd($kinerjaAnggaranClone);
        $lsKementerian = $lsKementerian->map(function($objKementerian) use ($kinerjaAnggaranClone){
            $objKementerian = (object)$objKementerian;
            $kinerjaAnggaranKementerian = $kinerjaAnggaranClone->filter(function ($obj) use($objKementerian) {
                return $obj->kementerian_kode == $objKementerian->kementerian_kode;
            });
    
    
            $lsIntervensi = $kinerjaAnggaranKementerian->map->only(['tahun', 'semester', 'intervensi_kode', 'intervensi_nama','program_kode','program_nama','kegiatan_kode','kegiatan_nama','output_kode','output_nama','suboutput_kode','suboutput_nama','prsn_anl_output'])->unique()->values();
    
            $objKementerian->kl_id = $objKementerian->kementerian_kode;
            $objKementerian->name = $objKementerian->kementerian_nama;
    
            $objKementerian->keterangan = "";
    
            $objKementerian->_children = $lsIntervensi->map(function($objIntervensi) use($kinerjaAnggaranKementerian, $objKementerian){
                $objIntervensi = (object)$objIntervensi;
                $kinerjaAnggaranIntervensi = $kinerjaAnggaranKementerian->filter(function ($obj) use($objIntervensi) {
                    return $obj->intervensi_kode == $objIntervensi->intervensi_kode;
                });
    
               /// $lsProgam = $kinerjaAnggaranIntervensi->map->only(['tahun', 'semester', 'program_kode', 'program_nama'])->unique()->values();
               // dd($objIntervensi);
                $objIntervensi->kl_id = $objKementerian->kementerian_kode;
                $objIntervensi->intervensi_id = $objIntervensi->intervensi_kode;
                $objIntervensi->name = $objIntervensi->intervensi_nama;
                $objIntervensi->program_kode = $objIntervensi->program_kode;
                $objIntervensi->program_nama = $objIntervensi->program_nama;
                $objIntervensi->kegiatan_kode = $objIntervensi->kegiatan_kode;
                $objIntervensi->kegiatan_nama = $objIntervensi->kegiatan_nama;
                $objIntervensi->output_kode = $objIntervensi->output_kode;
                $objIntervensi->output_nama = $objIntervensi->output_nama;
                $objIntervensi->prsn_anl_output = (string) number_format($objIntervensi->prsn_anl_output,4);
                
    
                unset($objIntervensi->intervensi_kode);
                unset($objIntervensi->intervensi_nama);
    
                return $objIntervensi;
    
            })->values();
    
            // $objKementerian->jml_program = $objKementerian->_children->sum('jml_program');
            // $objKementerian->jml_kegiatan = $objKementerian->_children->sum('jml_kegiatan');
           // $objKementerian->jml_kro = $objKementerian->_children->sum('jml_kegiatan');
    
            unset($objKementerian->kementerian_kode);
            unset($objKementerian->kementerian_nama);
    
            return $objKementerian;
    
        });
        $result->detail = $lsKementerian;

       

        return $this->returnJsonSuccess("Data fetched successfully", $result);


    }

    
    public function getDetailRoAnggaran(Request $request){


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

        if($request->has('parameter') && !empty($request->parameter)){
            $param = $request->parameter;
            $param = $param / 100;
        }
       

        if($request->parameter == 0){
            $param = 0;
        }
     //   \DB::enableQueryLog();

        $capaianRoByCapaianOutput = KinerjaAnggaran::select('*')
        ->where(function($q) use($tahun, $bulan, $semester, $kl, $intervensi,$param){


            if($param == '0.91'){
                $q->where('prsn_anl_realisasi', '>',$param);
            }else if($param == '0.71'){
                $q->where('prsn_anl_realisasi', '>=',$param);
                $q->where('prsn_anl_realisasi', '<=','0.9');

            }else if($param == '0.51'){
                $q->where('prsn_anl_realisasi', '>=',$param);
                $q->where('prsn_anl_realisasi', '<=','0.7');
            }else if($param == '0.31'){
                $q->where('prsn_anl_realisasi', '>=',$param);
                $q->where('prsn_anl_realisasi', '<=','0.5');
            }else{
                $q->where('prsn_anl_realisasi','<=','0.3');
                $q->where('prsn_anl_realisasi','>=','0');
            }
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
        })->orderBy('kementerian_kode','ASC')->get();



        $result = new \stdClass;
        $kinerjaAnggaranClone = clone $capaianRoByCapaianOutput;
        $lsKementerian = $kinerjaAnggaranClone->map->only(['kementerian_kode', 'kementerian_nama'])->unique()->values();
       // dd($kinerjaAnggaranClone);
        $lsKementerian = $lsKementerian->map(function($objKementerian) use ($kinerjaAnggaranClone){
            $objKementerian = (object)$objKementerian;
            $kinerjaAnggaranKementerian = $kinerjaAnggaranClone->filter(function ($obj) use($objKementerian) {
                return $obj->kementerian_kode == $objKementerian->kementerian_kode;
            });
    
    
            $lsIntervensi = $kinerjaAnggaranKementerian->map->only(['tahun', 'semester', 'intervensi_kode', 'intervensi_nama','program_kode','program_nama','kegiatan_kode','kegiatan_nama','output_kode','output_nama','suboutput_kode','suboutput_nama','prsn_anl_realisasi'])->unique()->values();
    
            $objKementerian->kl_id = $objKementerian->kementerian_kode;
            $objKementerian->name = $objKementerian->kementerian_nama;
    
            $objKementerian->keterangan = "";
    
            $objKementerian->_children = $lsIntervensi->map(function($objIntervensi) use($kinerjaAnggaranKementerian, $objKementerian){
                $objIntervensi = (object)$objIntervensi;
                $kinerjaAnggaranIntervensi = $kinerjaAnggaranKementerian->filter(function ($obj) use($objIntervensi) {
                    return $obj->intervensi_kode == $objIntervensi->intervensi_kode;
                });
    
               /// $lsProgam = $kinerjaAnggaranIntervensi->map->only(['tahun', 'semester', 'program_kode', 'program_nama'])->unique()->values();
               // dd($objIntervensi);
                $objIntervensi->kl_id = $objKementerian->kementerian_kode;
                $objIntervensi->intervensi_id = $objIntervensi->intervensi_kode;
                $objIntervensi->name = $objIntervensi->intervensi_nama;
                $objIntervensi->program_kode = $objIntervensi->program_kode;
                $objIntervensi->program_nama = $objIntervensi->program_nama;
                $objIntervensi->kegiatan_kode = $objIntervensi->kegiatan_kode;
                $objIntervensi->kegiatan_nama = $objIntervensi->kegiatan_nama;
                $objIntervensi->output_kode = $objIntervensi->output_kode;
                $objIntervensi->output_nama = $objIntervensi->output_nama;
                $objIntervensi->prsn_anl_realisasi = $objIntervensi->prsn_anl_realisasi;
                
    
                unset($objIntervensi->intervensi_kode);
                unset($objIntervensi->intervensi_nama);
    
                return $objIntervensi;
    
            })->values();
    
            // $objKementerian->jml_program = $objKementerian->_children->sum('jml_program');
            // $objKementerian->jml_kegiatan = $objKementerian->_children->sum('jml_kegiatan');
            //$objKementerian->jml_kro = $objKementerian->_children->sum('jml_kegiatan');
    
            unset($objKementerian->kementerian_kode);
            unset($objKementerian->kementerian_nama);
    
            return $objKementerian;
    
        });
        $result->detail = $lsKementerian;

       

        return $this->returnJsonSuccess("Data fetched successfully", $result);


    }


    public function getDetailRoIntervensi(Request $request){


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

      
       
     //   \DB::enableQueryLog();

        $capaianRoByCapaianOutput = KinerjaAnggaran::select('*')
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
        })->orderBy('kementerian_kode','ASC')->get();



        $result = new \stdClass;
        $kinerjaAnggaranClone = clone $capaianRoByCapaianOutput;
        $lsKementerian = $kinerjaAnggaranClone->map->only(['kementerian_kode', 'kementerian_nama'])->unique()->values();
       // dd($kinerjaAnggaranClone);
        $lsKementerian = $lsKementerian->map(function($objKementerian) use ($kinerjaAnggaranClone){
            $objKementerian = (object)$objKementerian;
            $kinerjaAnggaranKementerian = $kinerjaAnggaranClone->filter(function ($obj) use($objKementerian) {
                return $obj->kementerian_kode == $objKementerian->kementerian_kode;
            });
    
    
            $lsIntervensi = $kinerjaAnggaranKementerian->map->only(['tahun', 'semester', 'intervensi_kode', 'intervensi_nama','program_kode','program_nama','kegiatan_kode','kegiatan_nama','output_kode','output_nama','suboutput_kode','suboutput_nama'])->unique()->values();
    
            $objKementerian->kl_id = $objKementerian->kementerian_kode;
            $objKementerian->name = $objKementerian->kementerian_nama;
    
            $objKementerian->keterangan = "";
    
            $objKementerian->_children = $lsIntervensi->map(function($objIntervensi) use($kinerjaAnggaranKementerian, $objKementerian){
                $objIntervensi = (object)$objIntervensi;
                $kinerjaAnggaranIntervensi = $kinerjaAnggaranKementerian->filter(function ($obj) use($objIntervensi) {
                    return $obj->intervensi_kode == $objIntervensi->intervensi_kode;
                });
    
               /// $lsProgam = $kinerjaAnggaranIntervensi->map->only(['tahun', 'semester', 'program_kode', 'program_nama'])->unique()->values();
               // dd($objIntervensi);
                $objIntervensi->kl_id = $objKementerian->kementerian_kode;
                $objIntervensi->intervensi_id = $objIntervensi->intervensi_kode;
                $objIntervensi->name = $objIntervensi->intervensi_nama;
                $objIntervensi->program_kode = $objIntervensi->program_kode;
                $objIntervensi->program_nama = $objIntervensi->program_nama;
                $objIntervensi->kegiatan_kode = $objIntervensi->kegiatan_kode;
                $objIntervensi->kegiatan_nama = $objIntervensi->kegiatan_nama;
                $objIntervensi->output_kode = $objIntervensi->output_kode;
                $objIntervensi->output_nama = $objIntervensi->output_nama;
                
    
                unset($objIntervensi->intervensi_kode);
                unset($objIntervensi->intervensi_nama);
    
                return $objIntervensi;
    
            })->values();
    
            // $objKementerian->jml_program = $objKementerian->_children->sum('jml_program');
            // $objKementerian->jml_kegiatan = $objKementerian->_children->sum('jml_kegiatan');
            //$objKementerian->jml_kro = $objKementerian->_children->sum('jml_kegiatan');
    
            unset($objKementerian->kementerian_kode);
            unset($objKementerian->kementerian_nama);
    
            return $objKementerian;
    
        });
        $result->detail = $lsKementerian;

       

        return $this->returnJsonSuccess("Data fetched successfully", $result);


    }

}

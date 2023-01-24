<?php

namespace App\Http\Controllers\Api\V1\KinerjaAnggaranPembangunan;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\Kinerja\PerkembanganPenandaan;
use App\Models\Kinerja\MvPenandaanKementerian;
use App\Models\Kinerja\DataBaseline;
use App\Models\Kinerja\RenjaTaggingKesepakatan;
use App\Models\Kinerja\VKesepakatan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class PerkembanganPenandaanController extends BaseController
{
    public function tahunSemester(Request $request){
        $tahun = PerkembanganPenandaan::select('tahun', 'semester')->groupBy('tahun', 'semester')->get();
        return $this->returnJsonSuccess("Data fetched successfully", $tahun);
    }

    public function kementerian(Request $request){
        $kementerian = PerkembanganPenandaan::select('kementerian_kode', 'kementerian_nama')->groupBy('kementerian_kode', 'kementerian_nama')->get();
        return $this->returnJsonSuccess("Data fetched successfully", $kementerian);
    }

    public function intervensi(Request $request){
        $intervensi = PerkembanganPenandaan::select('intervensi_kode', 'intervensi_nama')->groupBy('intervensi_kode', 'intervensi_nama')->get();
        return $this->returnJsonSuccess("Data fetched successfully", $intervensi);
    }

    public function getRoPerkembanganPenandaan(Request $request){

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

        if($request->has('kesepakatan') && !empty($request->kesepakatan)){
            $kesepakatan = $request->kesepakatan;
        }

        // if($request->has('kl') && !empty($request->kl)){
        //     $kl = $request->kl;
        // }

        // if($request->has('intervensi') && !empty($request->intervensi)){
        //     $intervensi = $request->intervensi;
        // }

        if($request->has('search') && !empty($request->search)){
            $search = strtolower($request->search);
        }
        
        $dataPerkembanganPenandaan = PerkembanganPenandaan::where(function($q) use($tahun, $bulan, $semester, $kl, $intervensi, $kesepakatan){
            if($tahun != "all"){
                $q->where('tahun', $tahun);
            }

            if($semester != "all"){
                $q->where('semester', $semester);
            }
            
        })
        ->where(function ($q) use($search){
            if(!empty($search)){
                $q->where(\DB::raw('LOWER(program_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kegiatan_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(output_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(suboutput_nama)'), 'LIKE', "%$search%");
            }
        })->get();
        
        $xx = [];
        $i = 1;
        foreach($dataPerkembanganPenandaan as $dt){
            $a=[
            "id"=> $i++,
            "kode"=> $dt->kode,
            "tahun"=> $dt->tahun,
            "semester"=> $dt->semester,
            "intervensi_kode"=> $dt->intervensi_kode,
            "intervensi_nama"=> $dt->intervensi_nama,
            "kementerian_kode"=> $dt->kementerian_kode,
            "kementerian_nama"=> $dt->kementerian_nama,
            "program_kode"=> $dt->program_kode,
            "program_nama"=> $dt->program_nama,
            "kegiatan_kode"=> $dt->kegiatan_kode,
            "kegiatan_nama"=> $dt->kegiatan_nama,
            "output_kode"=> $dt->output_kode,
            "output_nama"=> $dt->output_nama,
            "suboutput_kode"=> $dt->suboutput_kode,
            "suboutput_nama"=> $dt->suboutput_nama,
            "komponen_kode"=>$dt->komponen_kode,
            "komponen_nama"=> $dt->komponen_nama,
            "target"=> $dt->target,
            "satuan"=> $dt->satuan,
            "alokasi_0"=> $dt->alokasi_0,
            "alokasi_2"=> $dt->alokasi_2,
            "anl_alokasi"=> $dt->anl_alokasi,
            "status_tagging"=> $dt->status_tagging,
            "lokasi"=> $dt->lokasi,
            "keterangan"=> $dt->keterangan,
            "status_identifikasi"=> $dt->status_identifikasi,
            "kementerian_nama_short"=> $dt->kementerian_nama_short,
            ];
            $tagKesepakatan = RenjaTaggingKesepakatan::where(
                [
                    ["id_ro",$dt->kode],
                    ["kesepakatan",$kesepakatan],
                    ["tahun",$tahun],
                    ["semester",$semester]
                ]
            )->first();
            
            $b=[
                "kodero" => (!is_null($tagKesepakatan)) ? $tagKesepakatan->id_ro : null, 
                "kesepakatan" => (!is_null($tagKesepakatan)) ? $tagKesepakatan->kesepakatan : null,
                "tgl_kesepakatan" => (!is_null($tagKesepakatan)) ? $tagKesepakatan->tgl_kesepakatan : null,
                "tingkat_ro" => (!is_null($tagKesepakatan)) ? $tagKesepakatan->tingkat_ro : null,
                "analisis_lanjutan" => (!is_null($tagKesepakatan)) ? $tagKesepakatan->analisis_lanjutan : null,
                "publish" => (!is_null($tagKesepakatan)) ? $tagKesepakatan->publish : null,
                "ksp_id" => (!is_null($tagKesepakatan)) ? $tagKesepakatan->id : null,
            ];
            $xx[]=array_merge($a,$b);
        }
        return $this->returnJsonSuccess("Data fetched successfully", $xx);
    }

    public function kesepakatanRoPerkembanganPenandaan(Request $request){

        $record = [
            'id_ro' => $request->id_ro,
            'kesepakatan' => $request->kesepakatan,
            'tahun' => $request->tahun,
            'semester' => $request->semester,
            'tgl_kesepakatan' => $request->tgl_kesepakatan,
            'tingkat_ro' => $request->tingkat_ro,
            'publish' => $request->publish,
            'analisis_lanjutan' => $request->analisis_lanjutan
        ];        
        $rules = [
            'id_ro' => 'bail|required',
            'kesepakatan' => 'bail|required',
            'tahun' => 'required',
            'tgl_kesepakatan' => 'required',
            'tingkat_ro' => 'required',
            'publish' => 'required',
            'analisis_lanjutan' => 'required'
        ];
        $validator = Validator::make($record, $rules);

        if($validator->passes()){
            //cek sudah ada record ybs blm
            $is_exist = RenjaTaggingKesepakatan::select('id_ro')
                ->where([
                    'id_ro'=>$record['id_ro'],
                    'semester'=> $record['semester'],
                    'tahun'=> $record['tahun'],
                    'kesepakatan'=> $record['kesepakatan']                    
                    ])->get()->count();
            if( $is_exist ){
                $result =  RenjaTaggingKesepakatan::where([
                    'id_ro'=>$record['id_ro'],
                    'semester'=> $record['semester'],
                    'tahun'=> $record['tahun'],
                    'kesepakatan'=> $record['kesepakatan']                    
                    ])
                    ->update($record);
            }else{
                $result = RenjaTaggingKesepakatan::insert($record);
            }
            return ($result)?
                $this->returnJsonSuccess("Success Insert", []):
                $this->returnJsonError("Failed Insert", []);
        }
        return $this->returnJsonError('Failed Insert', 400, $validator->errors());
    }
    public function kesepakatanRoPublish(Request $request){

        $record = [
            'kode_ro' => $request->kode_ro,
            'kesepakatan' => $request->kesepakatan,
            'tahun' => $request->tahun,
            'semester' => $request->semester,
            'tgl_kesepakatan' => $request->tgl_kesepakatan,
            'publish' => $request->publish
        ];        
        $rules = [
            'kode_ro' => 'bail|required',
            'kesepakatan' => 'bail|required',
            'tahun' => 'required',
            'tgl_kesepakatan' => 'required',
            'semester' => 'required',
            'publish' => 'required'
        ];
        $validator = Validator::make($record, $rules);

        if($validator->passes()){
            foreach($request->kode_ro as $rk){
                    RenjaTaggingKesepakatan::updateOrCreate(
                        [
                        'id_ro' => $rk,
                        'kesepakatan' => $request->kesepakatan,
                        'tahun' => $request->tahun,
                        'semester' => $request->semester
                        ],[
                        'publish' => $request->publish,
                        'tgl_kesepakatan' => $request->tgl_kesepakatan
                    ]);
                }
                $result = true;
            
            return ($result)?
                $this->returnJsonSuccess("Success Insert", []):
                $this->returnJsonError("Failed Insert", []);
        }
        return $this->returnJsonError('Failed Insert', 400, $validator->errors());
    }

    public function getKrisnaUpdate(Request $request){
        $result = \DB::select("select updated_at from renja.krisnarenja_update_date order by updated_at desc limit 1");
        return ($result)?
                $this->returnJsonSuccess("Success Get Data", $result):
                $this->returnJsonError("Failed Get Data", []);
    }

    public function getPerkembanganPenandaan(Request $request){

//        dd($request->get());

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

        $allKementerian = PerkembanganPenandaan::select('kementerian_kode', 'kementerian_nama')->groupBy('kementerian_kode', 'kementerian_nama')->get();
        $allPerkembanganPenandaan = PerkembanganPenandaan::all();

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
        })
        ->where(function ($q) use($search){
            if(!empty($search)){
                $q->where(\DB::raw('LOWER(program_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kegiatan_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(output_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(suboutput_nama)'), 'LIKE', "%$search%");
             }
        })->get();

        // echo "<pre>";
        // print_r($dataPerkembanganPenandaan);
        // exit;;

        $dataTop5 = MvPenandaanKementerian::select('kementerian_kode', 
                                                    'kementerian_nama', 
                                                    'kementerian_short',
                                                    \DB::raw("SUM(CASE WHEN(intervensi_kode = 'A') then jumlah_ro else 0 end) as jumlah_ro_spesifik"),
                                                    \DB::raw("SUM(CASE WHEN(intervensi_kode = 'B') then jumlah_ro else 0 end) as jumlah_ro_sensitif"),
                                                    \DB::raw("SUM(CASE WHEN(intervensi_kode = 'C') then jumlah_ro else 0 end) as jumlah_ro_dukungan"),
                                                    \DB::raw('SUM(tagging) as tagging'),
                                                    \DB::raw('SUM(jumlah_ro) as jumlah_ro'),
                                                    \DB::raw('SUM(alokasi_renja) as alokasi_renja'),
                                                    \DB::raw('SUM(alokasi_rkakl) as alokasi_rkakl'),
                                                    \DB::raw('SUM(alokasi_anal) as alokasi_anal'))
        ->where(function($q) use($tahun, $bulan, $semester, $kl, $intervensi){
            if($tahun != "all"){
                $q->where('tahun', $tahun);
            }

            if($semester != "all"){
                $q->where('semester', $semester);
            }
        })
        ->groupBy('kementerian_kode', 'kementerian_nama', 'kementerian_short')
        ->orderByDesc('jumlah_ro')
        ->orderByDesc('alokasi_renja')
        ->orderByDesc('alokasi_rkakl')
        ->orderByDesc('alokasi_anal')
        // ->limit(5)
        ->get();


        
        $tagging = 0;
        $jumlah_ro = 0;
        $persentase = 0;
$dataTop5 = $dataTop5->map(function ($item){

    $tagging = $item->tagging;
    $jumlah_ro = $item->jumlah_ro;

    if($jumlah_ro == 0){
        $persentase = 0;
    }else{
        $persentase = ($tagging / $jumlah_ro) * 100;
    }

    return collect([
        'kementerian_kode' => $item->kementerian_kode,
        'kementerian_nama' => $item->kementerian_nama,
        'kementerian_short' => $item->kementerian_short,
        'tagging' => (int) $item->tagging,
        'jumlah_ro_spesifik' => (int) $item->jumlah_ro_spesifik,
        'jumlah_ro_sensitif' => (int) $item->jumlah_ro_sensitif,
        'jumlah_ro_dukungan' => (int) $item->jumlah_ro_dukungan,
        'jumlah_ro' => (int)  $item->jumlah_ro,
        'persentase' => (float) $persentase,
        'alokasi_renja' =>  (int) $item->alokasi_renja,
        'alokasi_rkakl' => (int)  $item->alokasi_rkakl,
        'alokasi_anal' => (float)  $item->alokasi_anal]);

    });

        $dataTop5_detail  = [];
        $dataTop5_detail  = MvPenandaanKementerian::select('kementerian_kode', 
        'kementerian_nama', 
        'kementerian_short',
        \DB::raw("CAST(SUM(CASE WHEN(intervensi_kode = 'A') then alokasi_renja else 0 end)  AS DECIMAL) as alokasi_renja_spesifik"),
        \DB::raw("SUM(CASE WHEN(intervensi_kode = 'A') then alokasi_rkakl else 0 end) as alokasi_rkakl_spesifik"),
        \DB::raw("SUM(CASE WHEN(intervensi_kode = 'A') then jumlah_ro else 0 end) as jumlah_ro_spesifik"),
        \DB::raw("SUM(CASE WHEN(intervensi_kode = 'A') then alokasi_anal else 0 end) as alokasi_anal_spesifik"),
        \DB::raw("SUM(CASE  WHEN(intervensi_kode = 'B') then alokasi_renja else 0 end ) as alokasi_renja_sensitif"),
        \DB::raw("SUM(CASE WHEN(intervensi_kode = 'B') then alokasi_rkakl else 0 end ) as alokasi_rkakl_sensitif"),
        \DB::raw("SUM(CASE WHEN(intervensi_kode = 'B') then jumlah_ro else 0 end) as jumlah_ro_sensitif"),
        \DB::raw("SUM(CASE WHEN(intervensi_kode = 'B') then alokasi_anal else 0 end) as alokasi_anal_sensitif"),
        \DB::raw("SUM(CASE WHEN(intervensi_kode = 'C') then alokasi_renja else 0 end) as alokasi_renja_dukungan"),
        \DB::raw("SUM(CASE WHEN(intervensi_kode = 'C') then alokasi_rkakl else 0 end) as alokasi_rkakl_dukungan"),
        \DB::raw("SUM(CASE WHEN(intervensi_kode = 'C') then jumlah_ro else 0 end) as jumlah_ro_dukungan"),
        \DB::raw("SUM(CASE WHEN(intervensi_kode = 'C') then alokasi_anal else 0 end) as alokasi_anal_dukungan"),
        \DB::raw("SUM(jumlah_ro) as jumlah_ro"))->where(function($q) use($tahun, $bulan, $semester, $kl, $intervensi){
    if($tahun != "all"){
        $q->where('tahun', $tahun);
    }

    if($semester != "all"){
        $q->where('semester', $semester);
    }
})
->groupBy('kementerian_kode', 'kementerian_nama', 'kementerian_short')
->orderByDesc('jumlah_ro')
// ->limit(5)
->get();


$dataTop5_detail = $dataTop5_detail->map(function ($item){

    // $tagging = $item->tagging;
    // $jumlah_ro = $item->jumlah_ro;

    // if($jumlah_ro == 0){
    //     $persentase = 0;
    // }else{
    //     $persentase = ($tagging / $jumlah_ro) * 100;
    // }

    return collect([
        'kementerian_kode' => $item->kementerian_kode,
        'kementerian_nama' => $item->kementerian_nama,
        'kementerian_short' => $item->kementerian_short,
        'alokasi_renja_spesifik' => (int) $item->alokasi_renja_spesifik,
        'alokasi_rkakl_spesifik' => (int)  $item->alokasi_rkakl_spesifik,
        'alokasi_anal_spesifik' =>  (float) $item->alokasi_anal_spesifik,
        'jumlah_ro_spesifik' => (int)  $item->jumlah_ro_spesifik,
        'alokasi_renja_sensitif' => (int)  $item->alokasi_renja_sensitif,
        'alokasi_rkakl_sensitif' => (int)  $item->alokasi_rkakl_sensitif,
        'alokasi_anal_sensitif' => (float)  $item->alokasi_anal_sensitif,
        'jumlah_ro_sensitif' => (int)  $item->jumlah_ro_sensitif,
        'alokasi_renja_dukungan' => (int)  $item->alokasi_renja_dukungan,
'alokasi_rkakl_dukungan' => (int)  $item->alokasi_rkakl_dukungan,
'alokasi_anal_dukungan' => (float)  $item->alokasi_anal_dukungan,
'jumlah_ro_dukungan' => (int)  $item->jumlah_ro_dukungan,
'jumlah_ro' =>  (int)  $item->jumlah_ro]);

    });
        



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

        $kinerjaAnggaranTaggingSpesifik = $kinerjaAnggaranClone->filter(function($obj) { //$kinerjaAnggaranTagging
            $obj = (object)$obj;
            return $obj->intervensi_kode == "A";
        });

        $kinerjaAnggaranTaggingSensitif = $kinerjaAnggaranClone->filter(function($obj) { //$kinerjaAnggaranTagging
            $obj = (object)$obj;
            return $obj->intervensi_kode == "B";
        });

        $kinerjaAnggaranTaggingPendamping = $kinerjaAnggaranClone->filter(function($obj) { //$kinerjaAnggaranTagging
            $obj = (object)$obj;
            return $obj->intervensi_kode == "C";
        });

        $realisasiTagging = new \stdClass;
        
        $rt1 = $kinerjaAnggaranClone->count();
        $rt2 = $kinerjaAnggaranTagging->count();

        if($rt1 == 0){
            $hrt1 = 0;
        }else{
            $hrt1 = ($rt2 / $rt1) * 100;
        }


        $realisasiTagging->all =  array(
            "teridentifikasi" => $rt1,
            "tagging" => $rt2,
            'persentase' =>  $hrt1
        );


           
        $ri1 = $_roIntervensiSpesifik->count();
        $ri2 = $roIntervensiSpesifik->count();

        if($ri1 == 0){
            $hri1 = 0;
        }else{
            $hri1 = ($ri2 / $ri1) * 100;
        }


        $realisasiTagging->spesifik = array(
            "teridentifikasi" => $ri1,
            "tagging" => $ri2,
            "persentase" => $hri1
        );

        $rts1 = $_roIntervensiSensitif->count();
        $rts2 = $roIntervensiSensitif->count();

        if($rts1 == 0){
            $hrs1 = 0;
        }else{
            $hrs1 = ($rts2 / $rts1) * 100;
        }


        $realisasiTagging->sensitif = array(
            "teridentifikasi" => $rts1,
            "tagging" => $rts2,
            "persentase" => $hrs1
        );


        $rtp1 = $_roIntervensiPendamping->count();
        $rtp2 = $roIntervensiPendamping->count();

        if($rtp1 == 0){
            $hrp1 = 0;
        }else{
            $hrp1 = ($rtp2 / $rtp1) * 100;
        }


        $realisasiTagging->pendamping = array(
            "teridentifikasi" => $rtp1,
            "tagging" => $rtp2,
            "persentase" => $hrp1
        );
        

        $rekonsiliasi_update_tagging = new \stdClass;
        $rekonsiliasi_update_tagging->c_kl = $kementerianCount;
        $rekonsiliasi_update_tagging->c_ro = $lsRo->count();
        $rekonsiliasi_update_tagging->c_ro_tagging = $lsRoTagging->count();
        $rekonsiliasi_update_tagging->spesifik_ro_tagging = $roIntervensiSpesifik->count();
        $rekonsiliasi_update_tagging->sensitif_ro_tagging = $roIntervensiSensitif->count();
        $rekonsiliasi_update_tagging->pendukung_ro_tagging = $roIntervensiPendamping->count();
        

        $pagu_level_output = new \stdClass;

        $pagu_level_output->sensitif_level_output_renjakl = $kinerjaAnggaranTaggingSensitif->sum('alokasi_0');
        $pagu_level_output->sensitif_level_output_rkakl = $kinerjaAnggaranTaggingSensitif->sum('alokasi_2');
        $pagu_level_output->spesifik_level_output_renjakl = $kinerjaAnggaranTaggingSpesifik->sum('alokasi_0');
        $pagu_level_output->spesifik_level_output_rkakl = $kinerjaAnggaranTaggingSpesifik->sum('alokasi_2');
        $pagu_level_output->dukungan_level_output_renjakl = $kinerjaAnggaranTaggingPendamping->sum('alokasi_0');
        $pagu_level_output->dukungan_level_output_rkakl = $kinerjaAnggaranTaggingPendamping->sum('alokasi_2');

        $pagu_analisis_lanjutan = new \stdClass;
        $pagu_analisis_lanjutan->sensitif_analisis_lanjutan_alokasi = $kinerjaAnggaranTaggingSensitif->sum('anl_alokasi');
        $pagu_analisis_lanjutan->spesifik_analisis_lanjutan_alokasi = $kinerjaAnggaranTaggingSpesifik->sum('anl_alokasi');
        $pagu_analisis_lanjutan->dukungan_analisis_lanjutan_alokasi = $kinerjaAnggaranTaggingPendamping->sum('anl_alokasi');

        

        $tile = new \stdClass;
        $tile->rekonsiliasi_update_tagging = $rekonsiliasi_update_tagging;
        $tile->pagu_level_output = $pagu_level_output;
        $tile->pagu_analisis_lanjutan = $pagu_analisis_lanjutan;
        $tile->top_5_alokasi = $dataTop5;
        $tile->top_5_detail = $dataTop5_detail;
        $tile->realisasi_tagging = $realisasiTagging;

        $lsKementerian = $kinerjaAnggaranClone->map->only(['tahun', 'semester', 'kementerian_kode', 'kementerian_nama'])->unique()->values();
        //dd($lsKementerian);
        $lsKementerian = $lsKementerian->map(function($objKementerian) use ($kinerjaAnggaranClone){
            $objKementerian = (object)$objKementerian;
            $kinerjaAnggaranKementerian = $kinerjaAnggaranClone->filter(function ($obj) use($objKementerian) {
                return $obj->kementerian_kode == $objKementerian->kementerian_kode;
            });

            $lsProgam = $kinerjaAnggaranKementerian->map->only(['tahun', 'semester', 'program_kode', 'program_nama'])->unique()->values();


            $objKementerian->kl_id = $objKementerian->kementerian_kode;
            $objKementerian->name = $objKementerian->kementerian_nama;
            $objKementerian->target = "";
            $objKementerian->satuan = "";
            $objKementerian->alokasi_0 = $kinerjaAnggaranKementerian->sum('alokasi_0');
            $objKementerian->alokasi_2 = $kinerjaAnggaranKementerian->sum('alokasi_2');
            $objKementerian->anl_alokasi = $kinerjaAnggaranKementerian->sum('anl_alokasi');
            $objKementerian->intervensi = "";
            $objKementerian->status_tagging = "'";
            $objKementerian->lokasi = "";
            $objKementerian->keterangan = "";
            $objKementerian->jml_program = $lsProgam->count();
            $objKementerian->jml_kegiatan = 0;
            $objKementerian->jml_kro = 0;
            $objKementerian->jml_ro = $kinerjaAnggaranKementerian->count();


            $objKementerian->_children = $lsProgam->map(function($objProgram) use($kinerjaAnggaranKementerian, $objKementerian){
                $objProgram = (object)$objProgram;
                $kinerjaAnggaranProgram = $kinerjaAnggaranKementerian->filter(function ($obj) use( $objProgram) {
                    return $obj->program_kode == $objProgram->program_kode;
                })->values();

                $lsKegiatan = $kinerjaAnggaranProgram->map->only(['tahun', 'semester', 'kegiatan_kode', 'kegiatan_nama'])->unique()->values();

                $objProgram->kl_id = $objKementerian->kementerian_kode;
                $objProgram->program_id = $objProgram->program_kode;
                $objProgram->name = $objProgram->program_nama;
                
    
                $objProgram->target = "";
                $objProgram->satuan = "";
                $objProgram->alokasi_0 = $kinerjaAnggaranProgram->sum('alokasi_0');
                $objProgram->alokasi_2 = $kinerjaAnggaranProgram->sum('alokasi_2');
                $objProgram->anl_alokasi = $kinerjaAnggaranProgram->sum('anl_alokasi');
                $objProgram->intervensi = "";
                $objProgram->status_tagging = "";
                $objProgram->lokasi = "";
                $objProgram->keterangan = "";
                $objProgram->jml_program = 0;
                $objProgram->jml_kegiatan = $lsKegiatan->count();
                $objProgram->jml_kro = 0;
                $objProgram->jml_ro = $kinerjaAnggaranProgram->count();

                $objProgram->_children = $lsKegiatan->map(function($objKegiatan) use($kinerjaAnggaranProgram, $objKementerian, $objProgram){
                    $objKegiatan = (object)$objKegiatan;
                    $kinerjaAnggaranKegiatan = $kinerjaAnggaranProgram->filter(function ($obj) use($objKegiatan) {
                        return $obj->kegiatan_kode == $objKegiatan->kegiatan_kode;
                    });
    
                    $lsOutput = $kinerjaAnggaranKegiatan->map->only(['tahun', 'semester', 'output_kode', 'output_nama'])->unique()->values();
    
                    $objKegiatan->kl_id = $objKementerian->kementerian_kode;
                    $objKegiatan->program_id = $objProgram->program_kode;
                    $objKegiatan->kegiatan_id = $objKegiatan->kegiatan_kode;
                    $objKegiatan->name = $objKegiatan->kegiatan_nama;
        
                    $objKegiatan->target = "";
                    $objKegiatan->satuan = "";
                    $objKegiatan->alokasi_0 = $kinerjaAnggaranKegiatan->sum('alokasi_0');
                    $objKegiatan->alokasi_2 = $kinerjaAnggaranKegiatan->sum('alokasi_2');
                    $objKegiatan->anl_alokasi = $kinerjaAnggaranKegiatan->sum('anl_alokasi');
                    $objKegiatan->intervensi = "";
                    $objKegiatan->status_tagging = "";
                    $objKegiatan->lokasi = "";
                    $objKegiatan->keterangan = "";
                    $objKegiatan->jml_program = 0;
                    $objKegiatan->jml_kegiatan = 0;
                    $objKegiatan->jml_kro = $lsOutput->count();
                    $objKegiatan->jml_ro = $kinerjaAnggaranKegiatan->count();
    
                    $objKegiatan->_children = $lsOutput->map(function($objOutput) use($kinerjaAnggaranKegiatan, $objKementerian, $objProgram, $objKegiatan){
                        $objOutput = (object)$objOutput;
                        $kinerjaAnggaranOutput = $kinerjaAnggaranKegiatan->filter(function ($obj) use($objOutput) {
                            return $obj->output_kode == $objOutput->output_kode;
                        });
        
                        $objOutput->kl_id = $objKementerian->kementerian_kode;
                        $objOutput->program_id = $objProgram->program_kode;
                        $objOutput->kegiatan_id = $objKegiatan->kegiatan_kode;
                        $objOutput->kro_id = $objOutput->output_kode;
                        $objOutput->name = $objOutput->output_nama;
            
        
                        $objOutput->target = "";
                        $objOutput->satuan = "";
                        $objOutput->alokasi_0 = $kinerjaAnggaranOutput->sum('alokasi_0');
                        $objOutput->alokasi_2 = $kinerjaAnggaranOutput->sum('alokasi_2');
                        $objOutput->anl_alokasi = $kinerjaAnggaranOutput->sum('anl_alokasi');
                        $objOutput->intervensi = "";
                        $objOutput->status_tagging = "";
                        $objOutput->lokasi = "";
                        $objOutput->keterangan = "";
                        $objOutput->jml_program = 0;
                        $objOutput->jml_kegiatan = 0;
                        $objOutput->jml_kro = 0;
                        $objOutput->jml_ro = $kinerjaAnggaranOutput->count();
        
                        $objOutput->_children = $kinerjaAnggaranOutput->map(function($objSubOutput) use($kinerjaAnggaranOutput){
                            
                            $objSubOutput->kl_id = $objSubOutput->kementerian_kode;
                            $objSubOutput->program_id = $objSubOutput->program_kode;
                            $objSubOutput->kegiatan_id = $objSubOutput->kegiatan_kode;
                            $objSubOutput->kro_id = $objSubOutput->output_kode;
                            $objSubOutput->ro_id = $objSubOutput->suboutput_kode;
                            $objSubOutput->name = $objSubOutput->suboutput_nama;

                            // $objSubOutput->lokasi = "";
                            // $objSubOutput->keterangan = "";
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

            $objKementerian->jml_kegiatan = $objKementerian->_children->sum('jml_kegiatan');
            $objKementerian->jml_kro = $objKementerian->_children->sum('jml_kro');

            return $objKementerian;

        });


        $result = new \stdClass;
        $result->tile = $tile;
        $result->baseline = DataBaseline::select('jumlah')->where('tahun',$tahun)->first();
        $result->detail = $lsKementerian;

        return $this->returnJsonSuccess("Data fetched successfully", $result);
    }

    public function getPerkembanganPenandaan2(Request $request){
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

        $allKementerian = PerkembanganPenandaan::select('kementerian_kode', 'kementerian_nama')->groupBy('kementerian_kode', 'kementerian_nama')->get();
        $allPerkembanganPenandaan = PerkembanganPenandaan::all();

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
        })
        ->where(function ($q) use($search){
            if(!empty($search)){
                $q->where(\DB::raw('LOWER(program_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(kegiatan_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(output_nama)'), 'LIKE', "%$search%");
                $q->orWhere(\DB::raw('LOWER(suboutput_nama)'), 'LIKE', "%$search%");
             }
        })->get();


        dd($dataPerkembanganPenandaan);

        $dataTop5 = MvPenandaanKementerian::select('kementerian_kode', 
                                                    'kementerian_nama', 
                                                    'kementerian_short',
                                                    \DB::raw('SUM(jumlah_ro) as jumlah_ro'),
                                                    \DB::raw('SUM(alokasi_renja) as alokasi_renja'),
                                                    \DB::raw('SUM(alokasi_rkakl) as alokasi_rkakl'),
                                                    \DB::raw('SUM(alokasi_anal) as alokasi_anal'))
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
        ->groupBy('kementerian_kode', 'kementerian_nama', 'kementerian_short')
        ->orderByDesc('jumlah_ro')
        ->limit(5)
        ->get();


      


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

        $kinerjaAnggaranTaggingSpesifik = $kinerjaAnggaranTagging->filter(function($obj) {
            $obj = (object)$obj;
            return $obj->intervensi_kode == "A";
        });

        $kinerjaAnggaranTaggingSensitif = $kinerjaAnggaranTagging->filter(function($obj) {
            $obj = (object)$obj;
            return $obj->intervensi_kode == "B";
        });

        $kinerjaAnggaranTaggingPendamping = $kinerjaAnggaranTagging->filter(function($obj) {
            $obj = (object)$obj;
            return $obj->intervensi_kode == "C";
        });

        $realisasiTagging = new \stdClass;
        $realisasiTagging->all =  array(
            "teridentifikasi" => $kinerjaAnggaranClone->count(),
            "tagging" => $kinerjaAnggaranTagging->count()
        );
        $realisasiTagging->spesifik = array(
            "teridentifikasi" => $_roIntervensiSpesifik->count(),
            "tagging" => $roIntervensiSpesifik->count()
        );
        $realisasiTagging->sensitif = array(
            "teridentifikasi" => $_roIntervensiSensitif->count(),
            "tagging" => $roIntervensiSensitif->count()
        );
        $realisasiTagging->pendamping = array(
            "teridentifikasi" => $_roIntervensiPendamping->count(),
            "tagging" => $roIntervensiPendamping->count()
        );
        

        $rekonsiliasi_update_tagging = new \stdClass;
        $rekonsiliasi_update_tagging->c_kl = $kementerianCount;
        $rekonsiliasi_update_tagging->c_ro = $lsRo->count();
        $rekonsiliasi_update_tagging->c_ro_tagging = $lsRoTagging->count();
        $rekonsiliasi_update_tagging->spesifik_ro_tagging = $roIntervensiSpesifik->count();
        $rekonsiliasi_update_tagging->sensitif_ro_tagging = $roIntervensiSensitif->count();
        $rekonsiliasi_update_tagging->pendukung_ro_tagging = $roIntervensiPendamping->count();
        

        $pagu_level_output = new \stdClass;

        $pagu_level_output->sensitif_level_output_renjakl = $kinerjaAnggaranTaggingSensitif->sum('alokasi_0');
        $pagu_level_output->sensitif_level_output_rkakl = $kinerjaAnggaranTaggingSensitif->sum('alokasi_2');
        $pagu_level_output->spesifik_level_output_renjakl = $kinerjaAnggaranTaggingSpesifik->sum('alokasi_0');
        $pagu_level_output->spesifik_level_output_rkakl = $kinerjaAnggaranTaggingSpesifik->sum('alokasi_2');
        $pagu_level_output->dukungan_level_output_renjakl = $kinerjaAnggaranTaggingPendamping->sum('alokasi_0');
        $pagu_level_output->dukungan_level_output_rkakl = $kinerjaAnggaranTaggingPendamping->sum('alokasi_2');

        $pagu_analisis_lanjutan = new \stdClass;
        $pagu_analisis_lanjutan->sensitif_analisis_lanjutan_alokasi = $kinerjaAnggaranTaggingSensitif->sum('anl_alokasi');
        $pagu_analisis_lanjutan->spesifik_analisis_lanjutan_alokasi = $kinerjaAnggaranTaggingSpesifik->sum('anl_alokasi');
        $pagu_analisis_lanjutan->dukungan_analisis_lanjutan_alokasi = $kinerjaAnggaranTaggingPendamping->sum('anl_alokasi');

        

        $tile = new \stdClass;
        $tile->rekonsiliasi_update_tagging = $rekonsiliasi_update_tagging;
        $tile->pagu_level_output = $pagu_level_output;
        $tile->pagu_analisis_lanjutan = $pagu_analisis_lanjutan;
        $tile->top_5_alokasi = $dataTop5;
        $tile->top_5_detail = $dataTop5_detail;
        $tile->realisasi_tagging = $realisasiTagging;

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
            $objKementerian->target = "";
            $objKementerian->satuan = "";
            $objKementerian->alokasi_0 = $kinerjaAnggaranKementerian->sum('alokasi_0');
            $objKementerian->alokasi_2 = $kinerjaAnggaranKementerian->sum('alokasi_2');
            $objKementerian->anl_alokasi = $kinerjaAnggaranKementerian->sum('anl_alokasi');
            $objKementerian->intervensi = "";
            $objKementerian->status_tagging = "";
            $objKementerian->lokasi = "";
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
    

                $objIntervensi->target = 0;
                $objIntervensi->satuan = "";
                $objIntervensi->alokasi_0 = $kinerjaAnggaranIntervensi->sum('alokasi_0');
                $objIntervensi->alokasi_2 = $kinerjaAnggaranIntervensi->sum('alokasi_2');
                $objIntervensi->anl_alokasi = $kinerjaAnggaranIntervensi->sum('anl_alokasi');
                $objIntervensi->intervensi = "";
                $objIntervensi->status_tagging = "";
                $objIntervensi->lokasi = "";
                $objIntervensi->keterangan = "";
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
        
                    $objProgram->target = 0;
                    $objProgram->satuan = "";
                    $objProgram->alokasi_0 = $kinerjaAnggaranProgram->sum('alokasi_0');
                    $objProgram->alokasi_2 = $kinerjaAnggaranProgram->sum('alokasi_2');
                    $objProgram->anl_alokasi = $kinerjaAnggaranProgram->sum('anl_alokasi');
                    $objProgram->intervensi = "";
                    $objProgram->status_tagging = "";
                    $objProgram->lokasi = "";
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
            
            
                        $objKegiatan->target = 0;
                        $objKegiatan->satuan = "";
                        $objKegiatan->alokasi_0 = $kinerjaAnggaranKegiatan->sum('alokasi_0');
                        $objKegiatan->alokasi_2 = $kinerjaAnggaranKegiatan->sum('alokasi_2');
                        $objKegiatan->anl_alokasi = $kinerjaAnggaranKegiatan->sum('anl_alokasi');
                        $objKegiatan->intervensi = "";
                        $objKegiatan->status_tagging = "";
                        $objKegiatan->lokasi = "";
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
                
            
                            $objOutput->target = 0;
                            $objOutput->satuan = "";
                            $objOutput->alokasi_0 = $kinerjaAnggaranOutput->sum('alokasi_0');
                            $objOutput->alokasi_2 = $kinerjaAnggaranOutput->sum('alokasi_2');
                            $objOutput->anl_alokasi = $kinerjaAnggaranOutput->sum('anl_alokasi');
                            $objOutput->intervensi = "";
                            $objOutput->status_tagging = "";
                            $objOutput->lokasi = "";
                            $objOutput->keterangan = "";
                            $objOutput->jml_program = 0;
                            $objOutput->jml_kegiatan = 0;
                            $objOutput->jml_kro = 0;
                            $objOutput->jml_ro = $kinerjaAnggaranOutput->count();
            
                            $objOutput->_children = $kinerjaAnggaranOutput->map(function($objSubOutput) use($kinerjaAnggaranOutput){
                                
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

                                // $objSubOutput->lokasi = "";
                                // $objSubOutput->keterangan = "";
                
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

        $dataKinerjaAnggaran = PerkembanganPenandaan::select(
            'tahun',
            'semester',
            'intervensi_kode',
            'intervensi_nama',
            'kementerian_kode',
            'kementerian_nama',
            \DB::raw("SUM(alokasi_0) as alokasi_0"),
            \DB::raw("SUM(alokasi_2) as alokasi_2"),
            \DB::raw("SUM(anl_alokasi) as anl_alokasi"),
            \DB::raw("COUNT(CASE WHEN (status_tagging = 'Tagging') THEN 1 END) as jml_ro_tagging"),
            \DB::raw("COUNT(0) as jml_ro_teridentifikasi"),
            // \DB::raw("COUNT(CASE WHEN (intervensi_kode = 'A' AND status_tagging = 'Tagging') THEN 1 END) as jml_ro_spesifik_tagging"),
            // \DB::raw("COUNT(CASE WHEN (intervensi_kode = 'A') THEN 1 END) as jml_ro_spesifik_teridentifikasi"),
            // \DB::raw("COUNT(CASE WHEN (intervensi_kode = 'B' AND status_tagging = 'Tagging') THEN 1 END) as jml_ro_sensitif_tagging"),
            // \DB::raw("COUNT(CASE WHEN (intervensi_kode = 'B') THEN 1 END) as jml_ro_sensitif_teridentifikasi"),
            // \DB::raw("COUNT(CASE WHEN (intervensi_kode = 'C' AND status_tagging = 'Tagging') THEN 1 END) as jml_ro_pendamping_tagging"),
            // \DB::raw("COUNT(CASE WHEN (intervensi_kode = 'C') THEN 1 END) as jml_ro_pendamping_teridentifikasi")
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

        $maxAmount = PerkembanganPenandaan::select(\DB::raw("GREATEST(MAX(alokasi_0), MAX(alokasi_2), MAX(anl_alokasi)) max"))
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

        $dataKementerian = PerkembanganPenandaan::select('kementerian_kode', 'kementerian_nama')
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

            $dataIntervensi = PerkembanganPenandaan::select('intervensi_kode', 'intervensi_nama')
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

                $dataPerkembanganPenandaan = PerkembanganPenandaan::select(
                    'tahun',
                    'semester',
                    'intervensi_kode',
                    \DB::raw("CASE WHEN intervensi_kode = 'A' THEN 'Spesifik' WHEN intervensi_kode='B' THEN 'Sensitif' ELSE 'Dukungan' END intervensi_nama"),
                    'kementerian_kode',
                    'kementerian_nama',
                    'kementerian_nama_short',
                    \DB::raw("SUM(alokasi_0) as alokasi_0"),
                    \DB::raw("SUM(alokasi_2) as alokasi_2"),
                    \DB::raw("SUM(anl_alokasi) as anl_alokasi"),
                    \DB::raw("COUNT(CASE WHEN (status_tagging = 'Tagging') THEN 1 END) as jml_ro_tagging"),
                    \DB::raw("COUNT(0) as jml_ro_teridentifikasi"),
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

                $resIntervensi->data = $dataPerkembanganPenandaan;

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

}

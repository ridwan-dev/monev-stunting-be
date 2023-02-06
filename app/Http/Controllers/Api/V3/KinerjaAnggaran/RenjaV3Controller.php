<?php

namespace App\Http\Controllers\Api\V3\KinerjaAnggaran;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use App\Models\V3\KrisnaIntegrasi\MvKrisnaRealisasiRkaKomponen;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class RenjaV3Controller extends BaseController
{
   public function getKrisnaRenjaRKA(Request $request){
      ini_set('memory_limit','-1');
      $tahun = now()->year;
      $kl = [];
      $intervensi = [];
      $search = "";
      
      if($request->has('tahun') && !empty($request->tahun)){
         $tahun = $request->tahun;
      }
      if($request->has('kl') && !empty($request->kl)){
         $kl = $request->kl;
      }
      if($request->has('search') && !empty($request->search)){
         $search = strtolower($request->search);
      }
      $allKementerian = MvKrisnaRealisasiRkaKomponen::select('kementerian_kode','kementerian_nama')->groupBy('kementerian_kode','kementerian_nama')->get();
      $dataRenja = MvKrisnaRealisasiRkaKomponen::where(function($q) use($tahun, $kl){
         if($tahun != "all"){
               $q->where('tahun', $tahun);
         }          
         if($kl != "all"){
               $q->whereIn('kementerian_kode', $kl);
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
      
      $renjaClone = clone $dataRenja;
      $kementerianCount = $renjaClone->pluck('kementerian_kode')->unique()->values()->count();        
      $total_alokasi = MvKrisnaRealisasiRkaKomponen::select(
         \DB::raw('
            SUM(alokasi_totaloutput::numeric) as total_alokasi,
            SUM(alokasi::numeric) as total_realisasi')
         )->where(function($q) use($tahun, $kl){
         if($tahun != "all"){
               $q->where('tahun', $tahun);
         }          
         if($kl != "all"){
               $q->whereIn('kementerian_kode', $kl);
         }            
      })
      ->where(function ($q) use($search){
         if(!empty($search)){
               $q->where(\DB::raw('LOWER(program_nama)'), 'LIKE', "%$search%");
               $q->orWhere(\DB::raw('LOWER(kegiatan_nama)'), 'LIKE', "%$search%");
               $q->orWhere(\DB::raw('LOWER(output_nama)'), 'LIKE', "%$search%");
               $q->orWhere(\DB::raw('LOWER(suboutput_nama)'), 'LIKE', "%$search%");
         }
      })->first();

      $tile = new \stdClass;
      $tile->total_alokasi = $total_alokasi->total_alokasi;
      $tile->total_realisasi = $total_alokasi->total_realisasi;
      $komponen = [];
      $lsKomponen = $renjaClone->map->only(['tahun','kementerian_kode','program_kode', 'kegiatan_kode', 'output_kode', 'suboutput_kode','suboutput_nama','komponen_kode','komponen_nama'])->unique()->values();
      $tile = new \stdClass;
      $lsKementerian = $renjaClone->map->only(['tahun','kementerian_kode', 'kementerian_nama','kementerian_nama_short'])->unique()->values();
      $lsKementerian = $lsKementerian->map(function($objKementerian) use ($renjaClone){
      $objKementerian = (object)$objKementerian;

      $kinerjaAnggaranKementerian = $renjaClone->filter(function ($obj) use($objKementerian) {
         return $obj->kementerian_kode == $objKementerian->kementerian_kode;
      });

      $lsProgam = $kinerjaAnggaranKementerian->map->only(['tahun', 'program_kode', 'program_nama'])->unique()->values();
      $lsKegiatan = $kinerjaAnggaranKementerian->map->only(['tahun', 'kegiatan_kode', 'kegiatan_nama'])->unique()->values();
      $lsOutput = $kinerjaAnggaranKementerian->map->only(['tahun', 'output_kode', 'output_nama'])->unique()->values();
      $lsSubOutput = $kinerjaAnggaranKementerian->map->only(['tahun', 'idro', 'suboutput_nama'])->unique()->values();
      
      $objKementerian->kl_id = $objKementerian->kementerian_kode;
      $objKementerian->name = $objKementerian->kementerian_nama;
      $objKementerian->name_short = $objKementerian->kementerian_nama_short;
      unset($objKementerian->kementerian_nama_short);
      
      $objKementerian->alokasi_totaloutput = $kinerjaAnggaranKementerian->sum('alokasi_totaloutput');
      $objKementerian->alokasi_realisasi = (int) $kinerjaAnggaranKementerian->sum('alokasi')/1000;
         
      //$objKementerian->alokasi_totalrealisasi = $kinerjaAnggaranKementerian->sum('alokasi_totaloutput');
      $objKementerian->keterangan = "";
      $objKementerian->jml_program = $lsProgam->count();
      $objKementerian->jml_kegiatan = $lsKegiatan->count();
      $objKementerian->jml_kro = $lsOutput->count();
      $objKementerian->jml_ro = $lsSubOutput->count();
      $objKementerian->posisi = 'KL';

      $objKementerian->_children = $lsProgam->map(function($objProgram) use($kinerjaAnggaranKementerian,$objKementerian){
         $objProgram = (object)$objProgram;
         $kinerjaAnggaranProgram = $kinerjaAnggaranKementerian->filter(function ($obj) use( $objProgram) {
               return $obj->program_kode == $objProgram->program_kode;
         })->values();

         $lsKegiatan = $kinerjaAnggaranProgram->map->only(['tahun', 'kegiatan_kode', 'kegiatan_nama'])->unique()->values();
         $lsOutput = $kinerjaAnggaranProgram->map->only(['tahun', 'output_kode', 'output_nama'])->unique()->values();
         $lsSubOutput = $kinerjaAnggaranProgram->map->only(['tahun', 'idro', 'suboutput_nama'])->unique()->values();

         $objProgram->kl_id = $objKementerian->kementerian_kode;
         $objProgram->program_id = $objProgram->program_kode;
         $objProgram->name = $objProgram->program_nama;
         $objProgram->alokasi_totaloutput = $kinerjaAnggaranProgram->sum('alokasi_totaloutput');
         $objProgram->alokasi_realisasi = (int) $kinerjaAnggaranProgram->sum('alokasi')/1000;
               
         $objProgram->keterangan = "";
         $objProgram->jml_program = 0;
         $objProgram->jml_kegiatan = $lsKegiatan->count();
         $objProgram->jml_kro = $lsOutput->count();
         $objProgram->jml_ro = $lsSubOutput->count();
         $objProgram->posisi = 'Program';
      
         $objProgram->_children = $lsKegiatan->map(function($objKegiatan) use($kinerjaAnggaranProgram, $objKementerian, $objProgram){
               $objKegiatan = (object)$objKegiatan;
               $kinerjaAnggaranKegiatan = $kinerjaAnggaranProgram->filter(function ($obj) use($objKegiatan) {
                  return $obj->kegiatan_kode == $objKegiatan->kegiatan_kode;
               });

               $lsOutput = $kinerjaAnggaranKegiatan->map->only(['tahun', 'output_kode', 'output_nama','alokasi_lro'])->unique()->values();
               $lsSubOutput = $kinerjaAnggaranKegiatan->map->only(['tahun', 'idro', 'suboutput_nama','alokasi_lro'])->unique()->values();

               $objKegiatan->kl_id = $objKementerian->kementerian_kode;
               $objKegiatan->program_id = $objProgram->program_kode;
               $objKegiatan->kegiatan_id = $objKegiatan->kegiatan_kode;
               $objKegiatan->name = $objKegiatan->kegiatan_nama;
               $objKegiatan->alokasi_totaloutput = $kinerjaAnggaranKegiatan->sum('alokasi_totaloutput');                
               $objKegiatan->alokasi_realisasi = (int) $kinerjaAnggaranKegiatan->sum('alokasi')/1000;
                  
               $objKegiatan->keterangan = "";
               $objKegiatan->jml_program = 0;
               $objKegiatan->jml_kegiatan = 1;
               $objKegiatan->jml_kro = $lsOutput->count();
               $objKegiatan->jml_ro = $lsSubOutput->count();
               $objKegiatan->posisi = 'Kegiatan';
      
               $objKegiatan->_children = $lsOutput->map(function($objOutput) use($kinerjaAnggaranKegiatan, $objKementerian, $objProgram, $objKegiatan){
                  $objOutput = (object)$objOutput;
                  $kinerjaAnggaranOutput = $kinerjaAnggaranKegiatan->filter(function ($obj) use($objOutput) {
                     return $obj->output_kode == $objOutput->output_kode;                        
                  });
                  $lsSubOutput = $kinerjaAnggaranOutput->map->only(['tahun', 'suboutput_kode', 'suboutput_nama','lokasi_ro','alokasi_totaloutput','alokasi_lro'])->unique()->values();
   //dd($objOutput);
                  $objOutput->kl_id = $objKementerian->kementerian_kode;
                  $objOutput->program_id = $objProgram->program_kode;
                  $objOutput->kegiatan_id = $objKegiatan->kegiatan_kode;
                  $objOutput->kro_id = $objOutput->output_kode;
                  $objOutput->name = $objOutput->output_nama;            
                  $objOutput->alokasi_totaloutput = (int) $kinerjaAnggaranOutput->sum('alokasi_totaloutput');
                  $objOutput->alokasi_realisasi = (int) $kinerjaAnggaranOutput->sum('alokasi')/1000;
                     
                  $objOutput->keterangan = "";
                  $objOutput->jml_program = 0;
                  $objOutput->jml_kegiatan = 0;
                  $objOutput->jml_kro = 1;
                  $objOutput->jml_ro = $lsSubOutput->count();
                  $objOutput->posisi = 'KRO';
                  unset($objOutput->alokasi_lro);
                  $objSubOutputd = [];

                  $objOutput->_children = $lsSubOutput->map(function($objSubOutput) use ($kinerjaAnggaranOutput, $objKementerian, $objProgram, $objKegiatan, $objOutput){
                     $objSubOutput = (object) $objSubOutput;
                     $kinerjaAnggaranSubOutput = $kinerjaAnggaranOutput->filter(function ($obj) use($objSubOutput) {
                           return $obj->suboutput_kode == $objSubOutput->suboutput_kode;
                     });  
                     $lsKomponen = $kinerjaAnggaranSubOutput->map->only(['tahun', 'komponen_kode', 'komponen_nama','jenis_komponen','indikator_pbj','alokasi_0','alokasi_1','alokasi_2','alokasi_3','target_0','target_1','target_2','target_3','satuan','indikator_komponen','alokasi','sumber_dana'])->unique()->values();
                     //dd($kinerjaAnggaranSubOutput);
                     $objSubOutput->tahun = $objSubOutput->tahun;
                     $objSubOutput->kl_id = $objKementerian->kementerian_kode;
                     $objSubOutput->program_id = $objProgram->program_kode;
                     $objSubOutput->kegiatan_id = $objKegiatan->kegiatan_kode;
                     $objSubOutput->kro_id = $objOutput->output_kode;
                     $objSubOutput->ro_id = $objSubOutput->suboutput_kode;
                     $objSubOutput->name = $objSubOutput->suboutput_nama;
                     $objSubOutput->alokasi_totaloutput = (int) $objSubOutput->alokasi_totaloutput;                    
                     $objSubOutput->alokasi_realisasi = $objSubOutput->alokasi_lro;
                     $objSubOutput->alokasi_realisasi = (int) $kinerjaAnggaranSubOutput->sum('alokasi')/1000;
            
                     $objSubOutput->keterangan = "";
                     $objSubOutput->jml_program = 0;
                     $objSubOutput->jml_kegiatan = 0;
                     $objSubOutput->jml_kro = 0;
                     $objSubOutput->jml_ro = 1;
                     $objSubOutput->jml_komponen = $lsKomponen->count();                        
                     $objSubOutput->lokasi_ro = json_decode($objSubOutput->lokasi_ro, true, JSON_UNESCAPED_SLASHES);
                     $objSubOutput->posisi = 'RO';
                     unset($objSubOutput->alokasi_lro);
                     $objSubOutput->_children = $lsKomponen->map(function($objKomponen) use ($kinerjaAnggaranSubOutput, $objKementerian, $objProgram, $objKegiatan, $objOutput, $objSubOutput){
                           $objKomponen = (object) $objKomponen;
                           $kinerjaAnggaranKomponen = $kinerjaAnggaranSubOutput->filter(function ($obj) use($objKomponen) {
                              return $obj->komponen_kode == $objKomponen->komponen_kode;
                           });  
                           $objKomponen->program_id = $objProgram->program_kode;
                           $objKomponen->kegiatan_id = $objKegiatan->kegiatan_kode;
                           $objKomponen->kro_id = $objOutput->output_kode;
                           $objKomponen->ro_id = $objSubOutput->suboutput_kode;
                           $objKomponen->name = $objKomponen->komponen_nama;
                           $objKomponen->komponen_jenis = $objKomponen->jenis_komponen;
                           $objKomponen->indikator_pbj = $objKomponen->indikator_pbj;
                           $objKomponen->indikator_komponen = $objKomponen->indikator_komponen;
                           $objKomponen->satuan = $objKomponen->satuan;
                           $objKomponen->posisi = 'Komponen';
                           $objKomponen->alokasi_0 = (int)$objKomponen->alokasi_0;
                           $objKomponen->alokasi_1 = (int)$objKomponen->alokasi_1;
                           $objKomponen->alokasi_2 = (int)$objKomponen->alokasi_2;
                           $objKomponen->alokasi_3 = (int)$objKomponen->alokasi_3;
                           $objKomponen->target_0 = (int)$objKomponen->target_0;
                           $objKomponen->target_1 = (int)$objKomponen->target_1;
                           $objKomponen->target_2 = (int)$objKomponen->target_2;
                           $objKomponen->target_3 = (int)$objKomponen->target_3;
                           $objKomponen->alokasi_totaloutput = $objKomponen->alokasi_0;
                           $objKomponen->alokasi_realisasi = (int)($objKomponen->alokasi/1000);
                           $objKomponen->sumber_dana = $objKomponen->sumber_dana;
                           unset($objKomponen->alokasi);
                           if( !is_null($objKomponen->komponen_nama)){
                              return $objKomponen;
                           }
                     });  
                     if( is_null($objSubOutput->_children[0])){
                           unset($objSubOutput->_children);
                     }
                     
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

         unset($objKementerian->kementerian_kode);
         unset($objKementerian->kementerian_nama);
         return $objKementerian;
      });

      $result = new \stdClass;
      $result->tile = $tile;
      $result->detail = $lsKementerian;
      return $this->returnJsonSuccess("Data fetched successfully", $result);
   }

   public function KrisnaRenjaRKAx(Request $request){
      ini_set("memory_limit", "10056M");
      ini_set('max_execution_time', 300);
      $tahun = now()->year;
      $kl = [];
      $intervensi = [];
      $search = "";
      //dd("me");
      if($request->has('tahun') && !empty($request->tahun)){
         $tahun = $request->tahun;
      }
      if($request->has('kl') && !empty($request->kl)){
         $kl = $request->kl;
      }
      if($request->has('search') && !empty($request->search)){
         $search = strtolower($request->search);
      }
      //$allKementerian = MvKrisnaRealisasiRkaKomponen::select('kementerian_kode','kementerian_nama')->groupBy('kementerian_kode','kementerian_nama')->get();
      //$dataRenja = MvKrisnaRealisasiRkaKomponen::where('tahun',2022)->get();
      //$renjaClone = clone $dataRenja;
      //return response()->json(['success'=>'true','data' => $renjaClone], 200);
      //return $this->returnJsonSuccess("Data fetched successfully", $dataRenja);
      $dataRenja = MvKrisnaRealisasiRkaKomponen::where(function($q) use($tahun, $kl){
         if($tahun != "all"){
               $q->where('tahun', $tahun);
         }          
         if($kl != "all"){
               $q->whereIn('kementerian_kode', $kl);
               //$q->whereIn('kementerian_kode', ['024']);
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
      
      
      return $this->returnJsonSuccess("Data fetched successfully", $dataRenja); 
   }



}
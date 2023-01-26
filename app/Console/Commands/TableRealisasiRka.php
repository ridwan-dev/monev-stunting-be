<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Models\V3\KrisnaIntegrasi\KrisnaRealisasiRka as KrisnaRealisasiRka;
use App\Models\Kinerja\MvRenjaTematikKeywordSepakati as MvRenjaTematikKeywordSepakati;

class TableRealisasiRka extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dump:realisasi-rka {tabel} {tahun}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert Realisasi RKA';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tabel = $this->argument('tabel');
        $tahun = $this->argument('tahun');
        $key = '72373f5e-e4fc-4c88-bdde-2bb16caf4de0';        

        //$all_dept = MvRenjaTematikKeywordSepakati::where('tahun',$tahun)->groupBy('kementerian_kode')->get();
        
        $all_dept = DB::select("
            SELECT 
                a.kementerian_kode, a.kementerian_nama 
            FROM 
                renja.mv_krisna_renja_tematik_keyword_komponen a 
            where tahun = '".$tahun."' group by kementerian_kode, kementerian_nama
        ");
    
        if(count($all_dept) > 0){
            
            foreach($all_dept as $kode_dept){
                \DB::beginTransaction();        
                $url =  'https://sisdur.dit.krisna.systems/api/v1/realisasi-rka/'.$tahun.'?apikey='.$key.'&kddept='.$kode_dept->kementerian_kode;
                try {
                    $client = new Client(['verify' => false]);
                    $res = $client->get($url);
                    $result = $res->getBody();
                    $cek = json_decode($result);
                    $data = $cek->data;        
                } catch (\Exception $e) {
                    $data = [];
                }
                if(count($data) > 0){
                    echo 'Sekarang Proses delete tabel '.$tabel.' K/L '.$kode_dept->kementerian_nama.' Tahun '.$tahun.'\n'; 
                    KrisnaRealisasiRka::where(['tahun'=> $tahun,'kode_kl'=>$kode_dept->kementerian_kode])->delete();
                    echo 'Sekarang Proses insert tabel '.$tabel.' K/L '.$kode_dept->kementerian_nama.' Tahun '.$tahun.'\n';
                    
                    foreach($data as $komponen){
                        $collection = new KrisnaRealisasiRka;
                        foreach($komponen as $kal => $val){
                            $collection->$kal = $val;
                        }
                        $collection->tahun = $tahun;
                        $collection->save();
                    }
                    \DB::commit();
                    $this->info('Data Sync successfully tabel '.$tabel.' K/L '.$kode_dept->kementerian_nama.' Tahun '.$tahun);
                
                }else{
                    //\DB::rollback();
                    $collection = new KrisnaRealisasiRka;
                    $collection->nama_kl = $kode_dept->kementerian_nama;
                    $collection->kode_kl = $kode_dept->kementerian_kode;
                    $collection->tahun = $tahun;
                    $collection->save();
                    \DB::commit();
                    $this->info('Data Kosong K/L '.$kode_dept->kementerian_nama.' tahun '.$tahun);        
                }                
            }
        }else{
            $this->info('Data Kosong K/L Kosong Tahun '.$tahun); 
        }        
        return Command::SUCCESS;      
    }
}
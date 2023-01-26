<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Models\V3\KrisnaIntegrasi\KrisnaDakData as KrisnaDakData;
use App\Models\V3\KrisnaIntegrasi\KrisnaDakPemda as KrisnaDakPemda;

class TableDataDak extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dump:dak-data {tabel} {tahun}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert Data DAK';

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
        $key = '724e62ca-5187-4d3b-8313-4565345ee72f';
        
        $all_pemda = KrisnaDakPemda::where("tahun",$tahun)->whereNotNull('kode')->get();
        
        if(count($all_pemda) > 0){
            
            foreach($all_pemda as $kode_pemda){
                \DB::beginTransaction();        
                $url =  'https://sisdur.dit.krisna.systems/api/v1/dak-stunting/'.$tahun.'?kode_pemda='.$kode_pemda->kode.'&apikey='.$key;
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
                    echo 'Sekarang Proses delete tabel '.$tabel.' Pemda '.$kode_pemda->nama.' Tahun '.$tahun.'\n'; 
                    KrisnaDakData::where(['tahun'=> $tahun,'pemda_kode'=>$kode_pemda->kode])->delete();
                    echo 'Sekarang Proses insert tabel '.$tabel.' Pemda '.$kode_pemda->nama.' Tahun '.$tahun.'\n';
                    
                    foreach($data as $komponen){
                        $collection = new KrisnaDakData;
                        foreach($komponen as $kal => $val){
                            $collection->$kal = $val;
                        }
                        $collection->pemda_kode = $kode_pemda->kode;
                        $collection->pemda_nama = $kode_pemda->nama;
                        $collection->tahun = $tahun;
                        $collection->save();
                    }
                    \DB::commit();
                    $this->info('Data Sync successfully tabel '.$tabel.' Pemda '.$kode_pemda->nama.' Tahun '.$tahun);
                
                }else{
                    //\DB::rollback();
                    $collection = new KrisnaDakData;
                    $collection->pemda_kode = $kode_pemda->kode;
                    $collection->pemda_nama = $kode_pemda->nama;
                    $collection->tahun = $tahun;
                    $collection->save();
                    \DB::commit();
                    $this->info('Data Kosong Pemda '.$kode_pemda->nama.' tahun '.$tahun);        
                }
                
            }
        }else{
            $this->info('Data Kosong Pemda Kosong Tahun '.$tahun); 
        }




        
        return Command::SUCCESS;      
    }
}
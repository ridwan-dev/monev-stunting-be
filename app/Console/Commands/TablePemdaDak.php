<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Models\V3\KrisnaIntegrasi\KrisnaDakPemda as KrisnaDakPemda;

class TablePemdaDak extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dump:dak-pemda {tabel} {tahun}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert Pemda';

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
        
        echo 'Sekarang Proses empty tabel '.$tabel.' Tahun '.$tahun.'\n';

        \DB::beginTransaction();        
        $url =  'https://sisdur.dit.krisna.systems/api/v1/dak-wilayah-pemda/'.$tahun.'?apikey='.$key;
        
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
            KrisnaDakPemda::where('tahun', $tahun)->delete();
            echo 'Sekarang Proses insert tabel '.$tabel.' Tahun '.$tahun.'\n';            
            foreach($data as $komponen){
                $collection = new KrisnaDakPemda;
                foreach($komponen as $kal => $val){
                    $collection->$kal = $val;
                }
                $collection->tahun = $tahun;
                $collection->save();
            }
            \DB::commit();
            $this->info('Data Sync successfully '.$tabel);
        }else{
            \DB::rollback();
            $this->info('Data Kosong https://sisdur.dit.krisna.systems/api/v1/dak-wilayah-pemda/'.$tahun);        
        } 

        return Command::SUCCESS;      
    }
}
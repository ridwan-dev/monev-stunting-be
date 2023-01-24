<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;

use App\Libraries\Services\{
    RenjaService,
    Core\Exception as ServiceException
};
use Schema;
use App\Models\Staging\KrisnaRenja as KrisnaRenja;

class KrisnaRenjaInterkoneksi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    
    protected $signature = 'interkoneksi:krisna-renja {tahun}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command interkoneksi krisna renja';

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
        ini_set('memory_limit','-1');
        $apiKey = "72373f5e-e4fc-4c88-bdde-2bb16caf4de0";
        $tahun = trim($this->argument('tahun'));  
        //echo $tahun;
        //exit;
        //$tahun = '2021';     
        // $tabel = ['t_program','t_progsas','t_progsasin','t_progout','t_progoutin','t_giat','t_giatsas','t_giatin','t_output','t_outputin','t_soutput','t_soutputin','t_kmpnen','t_sasaran','t_sasaranin','t_visi','t_misi','t_prinas','t_prigiat','t_priproy','t_lokasi_suboutput','t_priprog'];
        //  $tabel = ['t_soutput','t_soutputin','t_kmpnen','t_sasaran','t_sasaranin','t_visi','t_misi','t_prinas','t_prigiat','t_priproy','t_lokasi_suboutput','t_priprog'];
        
        $tabel = ['t_alokasi'];
        foreach($tabel as $k => $v){
            // for($tahun=2023;$tahun>=2021;$tahun--){
            echo 'Sekarang Proses '.$v.' Tahun '.$tahun.'\n';
            $url =  'https://sisdur.dit.krisna.systems/api/v1/renjakl-rspp/'.$tahun.'?apikey='.$apiKey.'&table='.$v;
            // $url =  'https://sisdur.dit.krisna.systems/api/v1/renjakl-ref/'.$tahun.'?apikey='.$apiKey.'&table='.$v;
            try {
            $client = new Client(['verify' => false]);
            $res = $client->get($url);
            $result = $res->getBody();
            $cek = json_decode($result);
            $data = $cek->data;        
        } catch (\Exception $e) {
            $data = [];
        }
        echo $url;
        exit;
        //echo count($data);
        //exit;
        // print_r($data);
        // exit;
        \DB::beginTransaction();
        //  echo $url;
        // exit;

        if(count($data) > 0){
            try {
                $nama_tabel = 'renja.krisnarenja_'.$v;
                $nama_tabel2 = 'krisnarenja_'.$v; 
                $adatabel = \DB::select("
                SELECT EXISTS (SELECT FROM  pg_tables WHERE  schemaname = 'renja' AND  tablename  = '$nama_tabel2')
                ");
                $adatabel = $adatabel[0]->exists;
                if($adatabel != 1){
                    Schema::create($nama_tabel, function($table) use ($data){
                        // $table->engine = 'InnoDB';
                        $table->string('id',4)->nullable();
                        foreach($data[0] as $k => $v){
                            $table->text($k)->nullable();
                        }
                            $table->string('tahun',4)->nullable();
                        // print_r($table);
                        // exit;
                    });
                }
                //  exit;
                if(count($data) > 0){
                    \DB::table('renja.krisnarenja_'.$v)->where('tahun',$tahun)->delete();
                // KrisnaRenja(['table' => 'krisnarenja_'.$v])::where('tahun', $tahun)->delete();
                }
                foreach($data as $komponen){
                    $collection = new KrisnaRenja(['table' => 'krisnarenja_'.$v]);
                    foreach($komponen as $kal => $val){
                        $collection->$kal = $val;
                    }
                    $collection->tahun = $tahun;
                    $collection->save();
                }                
                \DB::commit();
                $this->info('Data Sync successfully '.$v);
            } catch (\Exception $e) {
                \DB::rollback();
                $this->info($v.' Failed data sync'.$e);
            //  print_r($e);
            // exit;   
            }
        }else{
            \DB::rollback();
            $this->info('Data Kosong '.$v);
        }
    //}
    }
    //  echo $tahun;
    return Command::SUCCESS;
    }
}

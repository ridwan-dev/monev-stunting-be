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
        //   $tahun = $this->argument('tahun');       
        $tabel = [
            't_program',
            't_progsas',
            't_progsasin',
            't_progout',
            't_progoutin',
            't_giat',
            't_giatsas',
            't_giatin',
            't_output',
            't_outputin',
            't_soutput',
            't_soutputin',
            't_kmpnen',
            't_sasaran',
            't_sasaranin',
            't_visi',
            't_misi',
            't_prinas',
            't_prigiat',
            't_priproy'
        ];
        // $tabel = ['t_sasaran','t_sasaranin','t_visi','t_misi','t_prinas','t_prigiat','t_priproy'];
        $tabel_not_in = ['t_pripro'];
        
        foreach($tabel as $k => $v){

            for($tahun=2021;$tahun<=2023;$tahun++){
                echo 'Sekarang Proses '.$v.' Tahun '.$tahun.'\n';
                $url =  'https://sisdur.dit.krisna.systems/api/v1/renjakl-rspp/'.$tahun.'?apikey='.$apiKey.'&table='.$v;
            try {
                $client = new Client(['verify' => false]);
                $res = $client->get($url);
                $result = $res->getBody();
                $cek = json_decode($result);
                $data = $cek->data;            
            } catch (\Exception $e) {
                $data = [];
            }
                //echo $url;
                //exit;
                //echo count($data);
                //exit;
                // print_r($data);
                // exit;

            \DB::beginTransaction();
                //  echo $url;
                // exit;

            if(count($data) > 0){
                try {

                    $nama_tabel = 'staggingkrisna.krisnarenja_'.$v.'_'.$tahun;
                    $nama_tabel2 = 'krisnarenja_'.$v.'_'.$tahun;                   
                    
                    $adatabel = \DB::select("
                        SELECT EXISTS (SELECT FROM  pg_tables WHERE  schemaname = 'staggingkrisna' AND  tablename  = '$nama_tabel2')
                        ");
                    $adatabel = $adatabel[0]->exists;                   

                if($adatabel != 1){
                    Schema::create($nama_tabel, function($table) use ($data){
                    // $table->engine = 'InnoDB';
                    foreach($data[0] as $k => $v){
                        $table->text($k)->nullable();
                    }
                        $table->string('tahun',4)->nullable();
                    // print_r($table);
                    // exit;
                });
            }
            //exit;
            if(count($data) > 0){
                \DB::table('staggingkrisna.krisnarenja_'.$v.'_'.$tahun)->where('tahun',$tahun)->delete();
               // KrisnaRenja(['table' => 'krisnarenja_'.$v])::where('tahun', $tahun)->delete();
            }
          //  print_r($data);

            foreach($data as $komponen){
              

                $collection = new KrisnaRenja(['table' => 'krisnarenja_'.$v.'_'.$tahun]);
                // print_r($collection);
                // exit;
                foreach($komponen as $kal => $val){
                    $collection->$kal = $val;
                }

                $collection->tahun = $tahun;
             
                $a  = $collection->save();
                // if($a){
                // //    echo 'Berhasil \n';
                // }else{
                //     echo 'Gagal \n';
                // }
            }



        \DB::commit();
            $this->info('Data Sync successfully '.$v);
        } catch (\Exception $e) {
            \DB::rollback();
            $this->info($v.' Failed data sync');
        }

    }else{
        $this->info('Data Kosong '.$v);

    }

        

    }
    
      
       }

      //  echo $tahun;
        
    
      
     return Command::SUCCESS;
    }
}

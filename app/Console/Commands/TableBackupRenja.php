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
use App\Models\Staging\KrisnaRenjaBackup as KrisnaRenjaBackup;

class TableBackupRenja extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'interkoneksi:backup-krisna-renja {subData} {tabel} {tahun}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command interkoneksi krisna renja K/L yang dilakukan tiap awal bulan';

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
        $tabel_live = trim($this->argument('tabel'));
        
        /* subData:
            1.renjakl-rspp
            2.renjakl-ref
        */
        $subData = trim($this->argument('subData'));
        
        /* Start */
        echo 'Sekarang Proses '.$tabel_live.' Tahun '.$tahun.'\n';
        $url =  'https://sisdur.dit.krisna.systems/api/v1/'.$subData.'/'.$tahun.'?apikey='.$apiKey.'&table='.$tabel_live;
        //echo $url;

        try {
            $client = new Client(['verify' => false]);
            $res = $client->get($url);
            $result = $res->getBody();
            $cek = json_decode($result);
            $data = $cek->data;   
        } catch (\Exception $e) {
            $data = [];
        }

        \DB::beginTransaction();
        
        if(count($data) > 0){
            try {
                $date_this = date("Y_m_d");
                $nama_tabel = 'renja.krisnarenja_'.$tabel_live;
                $nama_tabel_backup = 'renja_backup.krisnarenja_'.$tabel_live."_".$date_this;
                $nama_tabel2 = 'krisnarenja_'.$tabel_live; 
                $nama_tabel2_backup = 'krisnarenja_'.$tabel_live."_".$date_this;
                
                $adatabelB = \DB::select("
                    SELECT EXISTS (SELECT FROM  pg_tables WHERE  schemaname = 'renja_backup' AND  tablename  = '$nama_tabel2_backup')
                ");
                $adatabelB = $adatabelB[0]->exists;
                
                if($adatabelB == 1){
                    \DB::select("
                        DROP TABLE ".$nama_tabel_backup."
                    ");
                }

                Schema::create($nama_tabel_backup, function($table) use ($data,$tahun){
                    $field = [];
                    foreach($data[0] as $k => $v){
                        $field[] = $k;
                        $table->text($k)->nullable();
                    }
                    if(!in_array("id",$field)){
                        $table->string('id',4)->nullable();
                    }
                    $table->string('tahun',4)->nullable();
                });
                foreach($data as $komponen){
                    $collection = new KrisnaRenjaBackup(['table' => $nama_tabel2_backup]);
                    foreach($komponen as $kal => $val){
                        $collection->$kal = $val;
                    }
                    $collection->tahun = $tahun;
                    $collection->save();
                }
                
                $adatabel = \DB::select("
                    SELECT EXISTS (SELECT FROM  pg_tables WHERE  schemaname = 'renja' AND  tablename  = '$nama_tabel2')
                ");
                $adatabel = $adatabel[0]->exists;
                
                if($adatabel != 1){
                    Schema::create($nama_tabel, function($table) use ($data){
                        $field = [];
                        foreach($data[0] as $k => $v){
                            $field [] = $k;
                            $table->text($k)->nullable();
                        }
                        if(!in_array("id",$field)){
                            $table->string('id',4)->nullable();
                        }
                        $table->string('tahun',4)->nullable();
                        // print_r($table);
                        // exit;
                    });
                }
                //  cek dan akan dihapus tahun berjalan - exit;
                if(count($data) > 0){
                    \DB::table('renja.krisnarenja_'.$tabel_live)->where('tahun',$tahun)->delete();                
                    foreach($data as $komponen){
                        $collection = new KrisnaRenja(['table' => 'krisnarenja_'.$tabel_live]);
                        foreach($komponen as $kal => $val){
                            $collection->$kal = $val;
                        }
                        $collection->tahun = $tahun;
                        $collection->save();
                    }
                }                
                \DB::commit();
                $this->info('Data Sync successfully '.$tabel_live);
            } catch (\Exception $e) {
                \DB::rollback();
                $this->info($tabel_live.' Failed data sync'.$e);
            }
        }else{
            \DB::rollback();
            $this->info('Data Kosong '.$tabel_live);
        }
        return Command::SUCCESS;
    }
}
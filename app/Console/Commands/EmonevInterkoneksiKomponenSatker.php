<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Libraries\Services\{
    EmonevService,
    Core\Exception as ServiceException
};
use App\Libraries\Services\Core\Auth;

use App\Models\Staging\EmonevStunting;

class EmonevInterkoneksiKomponenSatker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'interkoneksi:emonev-komponen-satker {tahun}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command interkoneksi data emonev bappenas Komponen + Satker';

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
        $tahun = $this->argument('tahun');
        $bulan = \Carbon::now()->subMonths(1)->month;
   
     // print_r($cek->data);
        try {

            $client = new Client(['verify' => false]);
            $res = $client->get(env('SERVICE_KOMPONEN_URI').EmonevService::KOMPONEN_URL . "thn=$tahun",['headers' => EmonevService::HEADERS]);
            $result = $res->getBody();
            $cek = json_decode($result);
            $data = $cek->data;
            // exit;
            
            // $emonevService = EmonevService::withHeaders(EmonevService::HEADERS)->get(EmonevService::KOMPONEN_URL . "thn=$tahun");
            // print_r($emonevService);
          
            // ServiceException::on($emonevService);
      //      $data = $emonevService->data;
           
        } catch (\Exception $e) {
          //  print_r($e);
            $data = [];
        }
       // print_r($data);
       
   //  exit;
       \DB::beginTransaction();

        try {
            if(count($data) > 0){
            EmonevStunting(['table' => 'tes_emonev_stunting_'.$tahun])::where('tahun', $tahun)->where('bulan', $bulan)->delete();
            }

            foreach($data as $komponen){
                $collection = new EmonevStunting(['table' => 'tes_emonev_stunting_'.$tahun]);
                $collection->tahun = $tahun;
                $collection->bulan = $bulan;
                $collection->kdprov = $komponen->kdprov;
                $collection->nmprov = $komponen->nmprov;
                $collection->kdkab = $komponen->kdkab;
                $collection->nmkab = $komponen->nmkab;

                $collection->kdstkr = $komponen->kdstkr;
                $collection->nmstkr = $komponen->nmstkr;
                $collection->kddept = $komponen->kddept;
                $collection->nmdept = $komponen->nmdept;
                $collection->kdunit = $komponen->kdunit;
                $collection->nmunit = $komponen->nmunit;
                $collection->kdprog = $komponen->kdprog;
                $collection->nmprog = $komponen->nmprog;
                $collection->kddit = $komponen->kddit;
                $collection->nmdit = $komponen->nmdit;
                $collection->kdgiat = $komponen->kdgiat;
                $collection->nmgiat = $komponen->nmgiat;
                $collection->kdkro = $komponen->kdkro;
                $collection->nmkro = $komponen->nmkro;
                $collection->kdro = $komponen->kdro;
                $collection->kdkmpn = $komponen->kdkmpn;
                $collection->nmkmpn = $komponen->nmkmpn;
                $collection->jns_kmpn = $komponen->jns_kmpn;
                $collection->satuan = $komponen->satuan;
                $collection->vol = $komponen->vol;
                $collection->rli_fisik = $komponen->rli_fisik;
                $collection->alo = $komponen->alo;
                $collection->serapan = $komponen->serapan;
                $collection->stts_plksn = $komponen->stts_plksn;
                $collection->prcntg_plksn = $komponen->prcntg_plksn;

                $collection->save();
            }

            \DB::commit();
            $this->info('Data synced successfully');
        } catch (\Exception $e) {
            \DB::rollback();
            $this->info('Failed data sync'. $e);
        }
        // return Command::SUCCESS;
    }
}

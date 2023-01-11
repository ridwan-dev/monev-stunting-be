<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Libraries\Services\{
    RkaklService,
    Core\Exception as ServiceException
};
use App\Libraries\Services\Core\Auth;

use App\Models\Staging\Rkakl;
use App\Models\Kinerja\RefKementerian;

class KemenkeuRkaklInterkoneksi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'interkoneksi:rkakl {tahun}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $lsKementerian = RefKementerian::all();

        foreach($lsKementerian as $kementerian){
            \DB::beginTransaction();

            try {
                try {
                    $rkaklService = RkaklService::get(RkaklService::PAGU_URL . "pagu?year=$tahun&kddept=$kementerian->kementerian_kode");
                    ServiceException::on($rkaklService);
                    $data = $rkaklService->data;
                } catch (\Exception $e) {
                    $data = [];
                }
        
                if(count($data) > 0){
                    Rkakl::where('tahun', $tahun)->where('kddept', $kementerian->kementerian_kode)->delete();
                }
        
                foreach($data as $komponen){
                    $collection = new Rkakl();
                    $collection->kddept = $komponen->KDDEPT;
                    $collection->kdunit = $komponen->KDUNIT;
                    $collection->kdprogram = $komponen->KDPROGRAM;
                    $collection->kdgiat = $komponen->KDGIAT;
                    $collection->kdoutput = $komponen->KDOUTPUT;
        
                    $collection->kdsoutput = $komponen->KDSOUTPUT;
                    $collection->jmlpagu = $komponen->JMLPAGU;
                    $collection->tahun = $tahun;
        
                    $collection->save();
                }

                \DB::commit();
                $this->info('Data synced successfully');
            } catch (\Exception $e) {
                \DB::rollback();

                $this->info('Failed data sync'. $e);
            }
        }
        
        // return Command::SUCCESS;
    }
}

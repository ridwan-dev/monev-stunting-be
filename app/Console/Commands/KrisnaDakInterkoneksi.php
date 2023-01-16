<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Libraries\Services\{
    DakService,
    Core\Exception as ServiceException
};
use App\Libraries\Services\Core\Auth;

use App\Models\Staging\KrisnaDak;
use App\Models\Staging\DakData;
use App\Models\Staging\DakPengadaan;
use App\Models\Staging\DakWilayahPemda;

class KrisnaDakInterkoneksi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'interkoneksi:krisna-dak {tahun}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command interkoneksi krisna dak';

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
        $apiKey = "bb48735d-e0ce-472b-b2c6-3f3bac1e6e5f";
        $tahun = $this->argument('tahun');
        
        \DB::beginTransaction();

        try {
            try {
                $dakService = DakService::get(\Str::replaceArray('@', [$tahun, $apiKey], DakService::DAK_PEMDA));
                ServiceException::on($dakService);
                $dataPemda = $dakService->data;
            } catch (\Exception $e) {
                $dataPemda = [];
            }
    
            foreach($dataPemda as $pemda){
                $colPemda = DakWilayahPemda::where('tahun', $tahun)->where('id_pemda', $pemda->id)->first();
                if(!$colPemda){
                    $colPemda = new DakWilayahPemda();
                }else{
                    if(\Carbon::now()->toDateString() == $colPemda->updated_at->toDateString()){
                        continue;
                    }
                }

                $colPemda->tahun = $tahun;
                $colPemda->id_pemda = $pemda->id;
                $colPemda->parent_id = $pemda->parent_id;
                $colPemda->kode = $pemda->kode;
                $colPemda->nama = $pemda->nama;

                $colPemda->save();
                // dd($colPemda);
    
            }

    
            try {
                $dakService = DakService::get(\Str::replaceArray('@', [$tahun, $apiKey], DakService::DAK_PENGADAAN));
                ServiceException::on($dakService);
                $dataPengadaan = $dakService->data;
            } catch (\Exception $e) {
                $dataPengadaan = [];
            }
    
            foreach($dataPengadaan as $pengadaan){
                $colPengadaan = DakPengadaan::where('tahun', $tahun)->where('id_pengadaan', $pengadaan->id)->first();   
                if(!$colPengadaan)
                    $colPengadaan = new DakPengadaan();
                $colPengadaan->tahun = $tahun;
                $colPengadaan->id_pengadaan = $pengadaan->id;
                $colPengadaan->nama = $pengadaan->nama;

                $colPengadaan->save();
            }

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollback();

            $this->info('Failed data sync '. $e);
        }
    
        foreach($dataPemda as $pemda){
            $colPemda = DakWilayahPemda::where('tahun', $tahun)->where('id_pemda', $pemda->id)->first();
            if(!empty($colPemda->last_synced) && \Carbon::now()->toDateString() == $colPemda->last_synced->toDateString()){
                continue;
            }

            \DB::beginTransaction();
            try {

                try {
                    $dakService = DakService::get(\Str::replaceArray('@', [$tahun, $pemda->kode, $apiKey], DakService::DAK_URL));
                    ServiceException::on($dakService);
                    $data = $dakService->data;
                } catch (\Exception $e) {
                    $data = [];
                }
        
                foreach($data as $komponen){
                    $collection = new KrisnaDak();
                    $collection->tahun = $tahun;
                    $collection->kode_pemda = $pemda->kode;
                    $collection->id_detail_rincian = $komponen->id_detail_rincian;
                    $collection->bidang = $komponen->bidang;
                    $collection->sub_bidang = $komponen->sub_bidang;
                    $collection->kementerian = $komponen->kementerian;
                    $collection->menu_kegiatan = $komponen->menu_kegiatan;
                    $collection->pn = $komponen->pn;
                    $collection->pp = $komponen->pp;
                    $collection->tematik = $komponen->tematik;
                    $collection->kewenangan = $komponen->kewenangan;
                    $collection->jenis = $komponen->jenis;
                    $collection->pelaksana = $komponen->pelaksana;
                    $collection->rincian = $komponen->rincian;
                    $collection->detail_rincian = $komponen->detail_rincian;
                    $collection->status_detail = $komponen->status_detail;
                    $collection->satuan = $komponen->satuan;
                    $collection->volume_rk = $komponen->volume_rk;
                    $collection->unit_cost_rk = $komponen->unit_cost_rk;
                    $collection->nilai_rk = $komponen->nilai_rk;
                    $collection->pengadaan_ids = $komponen->pengadaan_ids;
                    $collection->komponens = $komponen->komponens;
                    $collection->criterias = $komponen->criterias;
                    $collection->keterangan = $komponen->keterangan;
                    $collection->coordinate = $komponen->coordinate;
        
                    $collection->save();
                    
                }

                $colPemda->last_synced = \Carbon::now();
                $colPemda->save();

                \DB::commit();
                $this->info('Data synced successfully');
            } catch (\Exception $e) {
                \DB::rollback();

                $this->info('Failed data sync '.$e);
            }

        }
        // return Command::SUCCESS;
    }
}

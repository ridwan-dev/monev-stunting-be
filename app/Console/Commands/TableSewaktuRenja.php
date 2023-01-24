<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Libraries\Services\{
    RenjaService,
    Core\Exception as ServiceException
};
use App\Models\Staging\KrisnaRenja as KrisnaRenja;

class TableSewaktuRenja extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dump:material-view {schema} {tabel}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reload material View';

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
        $schema = $this->argument('schema');
        $tabel = explode(",",$this->argument('tabel'));
        
        \DB::beginTransaction();
        if(count($tabel) > 0){
            $i = 1;
            foreach($tabel as $tbl){
                $adatabel = \DB::select("
                        SELECT EXISTS (
                            select * from pg_matviews
                            where 
                            schemaname = '".$schema."' 
                            and
                            matviewname = '".$tbl."'
                        )
                    ");
                $adatabel = $adatabel[0]->exists;
                if($adatabel){
                    $mvTable = \DB::select("REFRESH MATERIALIZED VIEW ".$schema.".".$tbl);
                    $this->info('Data Sync successfully '.$tbl);
                }else{
                    $this->info('Tabel not Found '.$tbl);
                }
                $i++;
            }
            $collection = new KrisnaRenja(['table' => 'krisnarenja_update_date']);
            $collection->save();
            \DB::commit();            
        }else{
            \DB::rollback();
            $this->info('Data Kosong '.$this->argument('tabel'));        
        }        
    }
}
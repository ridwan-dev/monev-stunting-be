<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RenjaTematikSepakat extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
            JOIN renja.krisnarenja_tagging filter ditandai = 1 && disepakati = 1
            1. Table renja.mv_krisna_renja_tematik_keyword aa
        */
        
        $adatabel = DB::select("
                SELECT EXISTS (
                    select * from pg_matviews where matviewname = 'mv_krisna_renja_tematik_sepakati'
                )
                ");
        $adatabel = $adatabel[0]->exists;

        if(!$adatabel){
            DB::statement("
                CREATE MATERIALIZED VIEW renja.mv_krisna_renja_tematik_sepakati AS
                    SELECT * FROM renja.mv_krisna_renja_tematik_keyword aa
                    LEFT JOIN ( 
                        SELECT 
                            id_ro,ditandai,disepakati,cast(tahun AS varchar) as thn
                        FROM renja.krisnarenja_tagging 
                        WHERE ditandai = 1 and disepakati = 1
                    ) bb
                        ON  ((aa.idro = bb.id_ro) AND (aa.tahun::varchar = bb.thn ))
                    WHERE 
                        bb.ditandai is not null 
                        AND disepakati is not null               
        ");
        }else{
            DB::statement("REFRESH MATERIALIZED VIEW renja.mv_krisna_renja_tematik_sepakati");
        };
    }
}

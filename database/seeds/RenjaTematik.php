<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RenjaTematik extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
            1. Table renja.krisnarenja_t_lokasi_suboutput   a1
            2. Table renja.krisnarenja_ref_wilayah          c1
            3. Table renja.krisnarenja_t_soutput            b
            4. Table renja.krisnarenja_t_output             c 
            5. Table renja.krisnarenja_t_giat               d
            6. Table renja.krisnarenja_t_progout            e
            7. Table renja.krisnarenja_t_program            f
            8. Table api.ref_kementerian                    kl
            9. Table renja.krisnarenja_tagging_ro           aa
            10.Table api.ref_intervensi                     rif
        */
        
        $adatabel = \DB::select("
                SELECT EXISTS (
                    select * from pg_matviews where matviewname = 'mv_krisna_renja_tematik'
                )
                ");
        $adatabel = $adatabel[0]->exists;
        if(!$adatabel){
            DB::statement("
        CREATE MATERIALIZED VIEW mv_krisna_renja_tematik AS
            SELECT 
                b.id AS idro,
                b.tahun,
                b.thang,
                b.kddept AS kementerian_kode,
                concat(b.thang, b.kddept, b.kdprogram, b.kdgiat, b.kdoutput, b.kdsoutput) AS kode_ro,
                b.nmsoutput AS suboutput_nama,
                b.alokasi_total,
                b.kdtema,
                b.sat,
                b.kdprogram AS program_kode,
                b.kdoutput AS output_kode,
                b.kdgiat AS kegiatan_kode,
                b.kdsoutput AS suboutput_kode,
                c.nmoutput AS output_nama,
                c.satuan AS satuan_output,
                c.alokasi_total AS alokasi_totaloutput,
                c.lokasi,
                d.nmgiat AS kegiatan_nama,
                d.nmunit,
                e.nmprogout,
                f.nmprogram AS program_nama,
                f.unit_kerja_eselon1,
                kl.kementerian_nama,
                kl.kementerian_nama_alias,
                intv.kode_intervensi,
                intv.intervensi_nama,
                intv.tipe_id,
                intv.tipe_nama,
                ( 
                    SELECT 
                        jsonb_agg(d_1.*) AS jsonb_agg
                    FROM( 
                        SELECT 
                            c1.kode AS kode_lokus,
                            c1.kewenangan,
                            c1.provinsi AS provinsi_lokus,
                            c1.kabupaten AS kabupaten_lokus,
                            c1.nama AS nama_lokus
                        FROM (
                            renja.krisnarenja_t_lokasi_suboutput a1
                            LEFT JOIN renja.krisnarenja_ref_wilayah c1 
                                ON (( (a1.wilayah_id = c1.id) AND ((a1.tahun)::text = (c1.tahun)::text) ))
                            )
                        WHERE ( (a1.parent_id = b.id) AND ((a1.tahun)::text = (b.tahun)::text) )
                        ) d_1
                ) AS lokasi_ro
            FROM (
                    (
                        (
                            (
                                (
                                    (
                                        renja.krisnarenja_t_soutput b
                                        LEFT JOIN renja.krisnarenja_t_output c 
                                            ON (( (b.parent_id = c.id) AND ((b.tahun)::text = (c.tahun)::text)) ))
                                        LEFT JOIN renja.krisnarenja_t_giat d 
                                            ON (( (c.parent_id = d.id) AND ((c.tahun)::text = (d.tahun)::text)) ))
                                        LEFT JOIN renja.krisnarenja_t_progout e 
                                            ON (( (d.parent_id = e.id) AND ((d.tahun)::text = (e.tahun)::text)) ))
                                        LEFT JOIN renja.krisnarenja_t_program f 
                                            ON (( (b.kdprogram = f.kdprogram) AND (b.kdunit = f.kdunit) AND (b.kddept = f.kddept) AND ((b.tahun)::text = (f.tahun)::text) ))
                                    )
                                    LEFT JOIN api.ref_kementerian kl 
                                        ON (( b.kddept = (kl.kementerian_kode)::text ))
                                )
                            LEFT JOIN ( 
                                SELECT 
                                    aa.id,
                                    aa.id_ro,
                                    aa.kode_intervensi,
                                    aa.tahun,
                                    aa.created_at,
                                    aa.updated_at,
                                    rif.id,
                                    rif.intervensi_kode,
                                    rif.intervensi_nama,
                                    rif.tipe_id,
                                    rif.tipe_nama,
                                    rif.intervensi_nama_alias,
                                    rif.link,
                                    rif.deskripsi
                        FROM (
                            renja.krisnarenja_tagging_ro aa
                            LEFT JOIN api.ref_intervensi rif 
                                ON (( (aa.kode_intervensi)::text = (rif.intervensi_kode)::text   ))
                        )
                    ) intv (id, id_ro, kode_intervensi, tahun, created_at, updated_at, id_1, intervensi_kode, intervensi_nama, tipe_id, tipe_nama, intervensi_nama_alias, link, deskripsi) 
                        ON (( ((intv.id_ro)::text = b.id) AND ((intv.tahun)::text = (b.tahun)::text) ))
                )
            WHERE (b.kdtema ~~* '%008%'::text);        
        ");
        }else{
            DB::statement("REFRESH MATERIALIZED VIEW renja.mv_krisna_renja_tematik");
        };
    }
}

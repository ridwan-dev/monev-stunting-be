<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKinerjaPembangunanRoLokasi extends Migration
{
    private $table = 'versi_tiga.kinerja_pembangunan';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('prov_id');
            $table->unsignedInteger('kab_id');
            $table->text('prov_name');
            $table->text('kab_name');
            $table->text('kab_alias');
            $table->unsignedInteger('kab_total_ro');
            $table->string('pen_terpadu', 1); /*Pendampingan Terpadu 12 Provinsi Kemenko PMK dan lintas K/L (36 kab/kota)*/
            $table->string('kek_pmt_lokal', 1);/*QEA 003-Ibu Hamil Kurang Energi Kronis (KEK) yang mendapat makanan tambahan Berbasis Pangan lokal  (32 kab/kota)*/
            $table->string('bal_pmt_lokal', 1);/*QQEA 006-Balita kurus yang mendapat makanan tambahan Berbasis Pangan lokal (32 kab/kota)*/
            $table->string('pen_pmt_lokal_bumil_bal', 1);/*UBA 002-Kab/Kota mendapatkan pendampingan dalam implementasi edukasi gizi melalui makanan tambahan lokal bagi ibu hamil dan balita ( 12 kab/kota)*/
            $table->string('bal_gizi_mik', 1);/*QEA 007-Anak balita yang mendapat Suplementasi Gizi Mikro (111 kab/kota)*/
            $table->string('pen_kom_pribadi', 1);/*SCM 001-Peningkatan Kapasitas dan Penerapan Komunikasi Antar Pribadi (KAP) (60 kab/kota)*/
            $table->string('pkk_pos_tertata', 1);/*BDB001 Lembaga PKK dan Posyandu yang tertata*/
            $table->string('mon_sup_imun', 1);/*UBA002-Monitoring dan Supervisi Imunisasi*/
            $table->string('pel_pet_kes_pem_pertum', 1);/*SCM103-Pelatihan SDIDTK Bagi Petugas Kesehatan dan Pemantauan Pertumbuhan*/
            $table->string('pel_pem_makn_bay_bal', 1);/*SCM 104-Pelatihan Pemberian Makan Bayi dan Anak*/
            $table->string('pel_kon_menyusui', 1);/*SCM 105-Pelatihan Konseling Menyusui*/
            $table->string('pel_man_terp_bal_sak', 1);/*SCM 106-Pelatihan Manajemen Terpadu Balita Sakit*/
            $table->string('pel_man_berat_bay_lah_ren', 1);/*SCM 107-Pelatihan Manajemen Berat Bayi Lahir Rendah*/
            $table->string('pel_penkus_nakes_indv', 1);/*SCM 101-Pelatihan Penugasan Khusus Tenaga Kesehatan Secara Individu*/
            $table->string('pel_penkus_nakes_team', 1);/*SCM100-Pelatihan Penugasan Khusus Tenaga Kesehatan Secara Team*/
            $table->string('paud_pend_holistik_integ', 1);/*QDB 143-Satuan PAUD Menyelenggarakan Pendekatan Holistik Integratif (50 kab/kota @ masing-masing 100 lembaga PAUD)*/
            $table->string('kaw_padi_kay_gizi', 1);/*RAI 625-Kawasan Padi Kaya Gizi (Biofortifikasi) 159 kab/kota di 29 provinsi*/
            $table->string('desa_aman_pang', 1);/*QDB002 Desa Aman Pangan (87 kab/kota)*/
            $table->string('infra_air_min_bas_masy', 1);/*RBB 007-Infrastruktur Air Minum Berbasis Masyarakat (PAMSIMAS) (171 kab/kota)*/
            $table->string('sis_peng_air_limb_doms_indv', 1);/*RBB 011-Sistem Pengelolaan Air Limbah Domestik Setempat Skala Individu (92 kab/kota)*/
            $table->string('kamp_ger_masy_makn_ikan', 1);/*PEH 001-Kampanye Gerakan Memasyarakatan Makan Ikan (Gemarikan) 52 kab/kota*/
            $table->string('cak_pend_pen_bant_iuaran', 1);/*QEA001-Cakupan penduduk Yang menjadi Penerima Bantuan Iuran (PBI) dalam JKN/KIS*/
            $table->string('jaminan_persalinan', 1);/*Jaminan Persalinan*/
            $table->string('alat_pem_hb', 1);/*RAB004 Alat Pemeriksaan Hb*/
            $table->string('surv_giz_kia', 1);/*QKA001 Surveilans gizi dan KIA Yang ditingkatkan kualitasnYa*/
            $table->string('pel_prog_sos_dis_giz_kia', 1);/*PEF001 Pelaksana program mendapatkan sosialisasi dan Diseminasi Pedoman/Modul/Petunjuk Teknis Terkait Kegiatan Pembinaan Gizi dan KIA*/
            $table->string('med_kie_pen_gizi_bumil_bal', 1);/*RAB001 Buku/Media KIE Terkait Penanggulangan Masalah Gizi Ibu Hamil dan Balita*/
            $table->string('ko_ad_anem_pend_ttd', 1);/*PEA002 Kegiatan Koordinasi dan Advokasi Terkait Pencegahan Anemia dan Pendampingan Tablet Tambah Darah*/
            $table->string('nakes_pusk_gizi_kia', 1);/*SCM003 Tenaga kesehatan di Puskesmas Yang ditingkatkan kapasitasnYa untuk mampu memfasilitasi kader/sektor non kesehatan dalam memberikan pelaYanan Gizi dan KIA*/
            $table->string('nakes_pusk_pen_kelangsungan_ibu_ank', 1);/*SCM002 Tenaga kesehatan Yang ditingkatkan kapasitasnYa terkait upaYa peningkatan kelangsungan hidup ibu dan anak*/
            $table->string('nakes_pusk_pen_kualitas_ibu_ank', 1);/*SCM001 Tenaga kesehatan Yang ditingkatkan kapasitasnYa terkait upaYa peningkatan kualitas hidup ibu dan anak*/
            $table->string('ko_ad_skrining_bay_lhr', 1);/*PEA003 TerlaksananYa Koordinasi dan Advokasi Terkait Pengembangan PelaYanan Skrining Bayi Baru Lahir*/
            $table->string('nspk_prog_giz_kia', 1);/*PEA001 NSPK Terkait Peningkatan Kapasitas Pelaksana Program Gizi dan KIA */
            $table->string('med_kie_pel_kes_ib_bal', 1);/*RAB002 Buku/Media KIE Terkait PelaYanan Kesehatan Ibu dan Balita*/
            $table->string('med_kie_pel_kes_us_sek_rem', 1);/*RAB003 Buku/Media KIE Terkait PelaYanan Kesehatan Anak Usia Sekolah dan Remaja*/
            $table->string('pak_vak_im_rut', 1);/*QEC516 Paket Penyediaan Vaksin Imunisasi Rutin*/
            $table->string('ko_pel_imn', 1);/*PEA002 002-Koordinasi pelaksanaan imunisasi*/
            $table->string('ko_pel_imn_lp', 1);/*PEA001 001-Koordinasi Pelaksanaan Imunisasi (LP)*/
            $table->string('sos_pel_imn_lp', 1);/*PEF001  Sosialisasi pelaksanaan imunisasi (LP) */
            $table->string('prod_inf_germas_med', 1);/*PEH001 Produksi dan Penyebarluasan Informasi Kesehatan Germas dan Stunting Melalui Berbagai Media*/
            $table->string('kamp_pos_akt', 1);/*PEH003 Kampanye PosYandu Aktif*/
            $table->string('rev_posyandu', 1);/*SCM004Revitalasi PosYandu*/
            $table->string('keb_damp_prog_kes_ling', 1);/*PBG001 Kebijakan Analisa Dampak Program Kesehatan Lingkungan*/
            $table->string('ko_ad_prog_kes_ling', 1);/*PEA001 Koordinasi Advokasi Program Kesehatan Lingkungan*/
            $table->string('konf_pel_pen_ling_sehat', 1);/*PEG001 Konferensi dan Event Pelaksanaan Peningkatan Lingkungan Sehat*/
            $table->string('pen_kual_kes_ling', 1);/*QEH001 Peningkatan Kualitas Kesehatan Lingkungan*/
            $table->string('kabkota_kual_kes_ling', 1);/*UBA001 Kab/kota Yang dibina dalam pemenuhan kualitas kesehatan lingkungan*/
            $table->string('kel_badt_fas_pemb_1000hpk', 1);/*QDE001 Keluarga dengan baduta yang mendapatkan fasilitasi dan pembinaan 1000 HPK*/
            $table->string('pemb_kamp_kb', 1);/*UBA002 Pemberdayaan kampung KB dalam rangka penurunan stunting*/
            $table->string('pik_bkr__edu_kespro_giz_rem_put', 1);/*QDD001 PIK Remaja dan BKR yang mendapat fasilitasi dan pembinaan Edukasi Kespro dan Gizi bagi Remaja Putri sebagai Calon Ibu*/
            $table->string('kel_bant_sos_syrt', 1);/*QEB201 Keluarga Yang Mendapat Bantuan Sosial Bersyarat*/
            $table->string('kel_bant_sos_syrt_pen', 1);/*QEB202 Keluarga Yang Mendapat Bantuan Sosial Bersyarat (PEN)*/
            $table->string('kpm_bansos_pen', 1);/*QEB104-QEB106 KPM Yang Memperoleh Bantuan Sosial  Pangan Sembako Pada Direktorat Penanganan Fakir Miskin Wilayah I (PEN)*/
            $table->string('rek_keb_sgi', 1);/*PBG005 Rekomendasi kebijakan status gizi indonesia*/
            $table->string('hsl_an_keb_pen', 1);/*ABG001 Hasil analisis kebijakan dalam rangka peningkatan kapasitas kelembagaan dalam pelaksanaan strategi percepatan pencegahan stunting*/
            $table->string('akta_kel_terbit', 1);/*QA004 Akta Kelahiran yang Diterbitkan*/
            $table->string('fas_pen_kinerja_kabkota', 1);/*FBA032 Fasilitasi Peningkatan Kinerja Kabupaten/Kota dalam Implementasi Konvergensi Penurunan Stunting di Daerah (INEY)*/
            $table->string('kap_aparatur_pen_penanganan', 1);/*UBA011 Daerah yang meningkat kapasitas aparaturnya dalam penilaian kinerja penanganan stunting*/
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->table);
    }
}

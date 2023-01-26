<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKrisnaDakData extends Migration
{
    private $table = 'versi_tiga.krisna_dak_data';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->increments('id');
            $table->string('pemda_kode', 10)->nullable();
            $table->string('pemda_nama', 100)->nullable();
            $table->unsignedInteger('tahun')->nullable();            
            $table->unsignedInteger('id_detail_rincian')->nullable();
            $table->string('bidang', 100)->nullable();
            $table->string('sub_bidang', 150)->nullable();
            $table->string('kementerian', 200)->nullable();
            $table->string('menu_kegiatan', 250)->nullable();
            $table->string('pn', 250)->nullable();
            $table->string('pp', 250)->nullable();
            $table->string('kp', 250)->nullable();
            $table->string('tematik', 100)->nullable();
            $table->string('kewenangan', 100)->nullable();
            $table->string('jenis', 100)->nullable();            
            $table->json('pelaksana')->nullable();            
            $table->string('rincian', 250)->nullable();
            $table->string('detail_rincian', 250)->nullable();
            $table->string('status_detail', 150)->nullable();
            $table->string('satuan', 100)->nullable();
            $table->unsignedInteger('volume_rk')->nullable();
            $table->decimal('unit_cost_rk',20,5)->nullable();
            $table->decimal('nilai_rk',20,5)->nullable();            
            $table->json('pengadaan_ids')->nullable();
            $table->json('komponens')->nullable();
            $table->json('criterias')->nullable();
            $table->string('keterangan', 200)->nullable();
            $table->json('coordinate')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
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

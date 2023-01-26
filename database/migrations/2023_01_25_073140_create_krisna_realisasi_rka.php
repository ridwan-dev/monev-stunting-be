<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKrisnaRealisasiRka extends Migration
{
    private $table = 'versi_tiga.krisna_realisasi_rka';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->increments('id');            
            $table->unsignedInteger('tahun')->nullable();
            $table->string('kode_kl', 10)->nullable();
            $table->string('nama_kl', 100)->nullable();
            $table->string('kode_program', 10)->nullable();
            $table->string('kode_kegiatan', 10)->nullable();
            $table->string('kode_kro', 10)->nullable();
            $table->string('kode_ro', 10)->nullable();
            $table->string('kode_lro', 10)->nullable();
            $table->unsignedInteger('alokasi_lro')->nullable();
            $table->json('attrs')->nullable();
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

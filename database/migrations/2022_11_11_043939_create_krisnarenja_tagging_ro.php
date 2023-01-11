<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKrisnarenjaTaggingRo extends Migration
{
    private $table = 'renja.krisnarenja_tagging_kesepakatan';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->string('id_ro', 25);
            $table->unsignedInteger('kesepakatan');
            $table->unsignedInteger('tahun');
            $table->unsignedInteger('semester');
            $table->timestamp('tgl_kesepakatan');
            $table->unsignedInteger('tingkat_ro');
            $table->unsignedInteger('analisis_lanjutan');
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

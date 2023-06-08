<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('info_tanis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id');
            $table->string('pengalaman_tani')->nullable();
            $table->string('kelompok_tani')->nullable();
            $table->string('nama_kelompok')->nullable();
            $table->integer('jumlah_anggota')->nullable();
            $table->string('status_lahan')->nullable();
            $table->string('luas_lahan')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('kota')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kode_pos')->nullable();
            $table->string('alamat')->nullable();
            $table->timestamps();

            $table->foreign('pengajuan_id')->references('id')->on('pengajuans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('info_tanis');
    }
};

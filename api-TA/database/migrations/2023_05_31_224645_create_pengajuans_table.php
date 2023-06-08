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
        Schema::create('pengajuans', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('pengajuan_name');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('komoditas');
            $table->bigInteger('total_pengajuan')->nullable();  
            $table->dateTime('estimasi_pengembalian');
            $table->string('tenor');
            $table->integer('imbal_hasil')->nullable();
            $table->integer('harga_unit')->nullable();
            $table->bigInteger('total_pengembalian')->nullable();
            $table->string('metode_pelunasan');
            $table->string('resiko')->nullable();
            $table->bigInteger('dana_terkumpul')->nullable();
            $table->longText('deskripsi')->nullable();
            $table->integer('jumlah_unit')->nullable();
            $table->integer('unit_tersedia')->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengajuans');
    }
};

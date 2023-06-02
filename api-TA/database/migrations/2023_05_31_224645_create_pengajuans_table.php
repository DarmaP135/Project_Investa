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
            $table->string('komoditas');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->bigInteger('total_pengajuan')->nullable();
            $table->dateTime('estimasi_pengembalian');
            $table->string('tenor');
            $table->string('resiko')->nullable();
            $table->bigInteger('dana_terkumpul')->nullable();
            $table->integer('imbal_hasil')->nullable();
            $table->bigInteger('total_pengembalian')->nullable();
            $table->longText('deskripsi')->nullable();
            $table->integer('jumlah_unit')->nullable();
            $table->integer('unit_tersedia')->nullable();
            $table->integer('minimal_invest')->nullable();
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

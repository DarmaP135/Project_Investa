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
        Schema::create('info_pemasukans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id');
            $table->dateTime('tanggal');
            $table->string('nama_produk');
            $table->integer('jumlah');
            $table->integer('harga');
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
        Schema::dropIfExists('info_pemasukans');
    }
};

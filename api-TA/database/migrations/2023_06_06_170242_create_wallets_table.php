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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('tipe');
            $table->bigInteger('jumlah_deposit')->nullable();
            $table->bigInteger('jumlah_withdraw')->nullable();
            $table->string('pilih_pembayaran')->nullable();
            $table->string('pilih_bank')->nullable();
            $table->string('note')->nullable();
            $table->string('nama_bank')->nullable();
            $table->string('nama_rekening')->nullable();
            $table->string('nomor_rekening')->nullable();
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
        Schema::dropIfExists('wallets');
    }
};

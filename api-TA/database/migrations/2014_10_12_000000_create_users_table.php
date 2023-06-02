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
        Schema::create('users', function (Blueprint $table) {
            $table->string('id')->primary(); // Menambahkan kolom id baru sebagai primary key
            $table->string('name');
            $table->string('password');
            $table->string('email')->unique();
            $table->string('alamat')->nullable();
            $table->string('phone');
            $table->string('usia')->nullable();
            $table->string('pengalaman')->nullable();
            $table->string('photo')->nullable();
            $table->string('tipeAkun')->nullable();
            $table->bigInteger('saldo')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('reset_password_token')->nullable()->unique();
            $table->rememberToken();
            $table->timestamp('reset_password_token_expiry')->nullable();
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
        Schema::dropIfExists('users');
    }
};

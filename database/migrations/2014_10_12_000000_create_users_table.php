<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable();
            $table->string('password');
            $table->string('nama_lengkap')->nullable();
            $table->string('email')->nullable();
            $table->text('alamat')->nullable();
            $table->string('telp', 30)->nullable();
            $table->text('pengalaman')->nullable();
            $table->enum('level', ['s_admin', 'admin', 'user']);
            $table->string('status')->nullable();
            $table->string('tgl_daftar', 20)->nullable();
            $table->string('terakhir_login', 20)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

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
        Schema::create('surat_keluar', function (Blueprint $table) {
            $table->id();
            $table->text('no_surat')->nullable();
            $table->string('tgl_ns', 12)->nullable();
            $table->text('pengirim')->nullable();
            $table->text('penerima')->nullable();
            $table->text('perihal')->nullable();
            $table->string('token_lampiran')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('dibaca')->default(false);
            $table->text('disposisi')->nullable();
            $table->boolean('peringatan')->default(false);
            $table->string('tgl_sk', 12)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_keluar');
    }
};

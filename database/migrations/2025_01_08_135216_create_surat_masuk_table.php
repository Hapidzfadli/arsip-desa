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
        Schema::create('surat_masuk', function (Blueprint $table) {
            $table->id();
            $table->text('no_surat')->nullable();
            $table->string('tgl_ns', 12)->nullable();
            $table->text('no_asal')->nullable();
            $table->string('tgl_no_asal', 12)->nullable();
            $table->text('pengirim')->nullable();
            $table->text('penerima')->nullable();
            $table->text('perihal')->nullable();
            $table->string('token_lampiran', 100)->nullable();
            $table->boolean('dibaca')->default(false);
            $table->boolean('disposisi')->default(false);
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('tgl_sm', 12)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_masuk');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personils', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('dokumens', function (Blueprint $table) {
            $table->id();
            $table->string('kategori');
            $table->string('judul');
            $table->string('file_path');
            $table->timestamps();
        });

        Schema::create('laporans', function (Blueprint $table) {
            $table->id();
            $table->string('bulan');
            $table->integer('jumlah');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personils');
        Schema::dropIfExists('dokumens');
        Schema::dropIfExists('laporans');
    }
};

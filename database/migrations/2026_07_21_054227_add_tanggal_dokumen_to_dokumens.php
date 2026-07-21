<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
    {
        Schema::table('dokumens', function (Blueprint $table) {
            // Menambahkan kolom tanggal_dokumen
            $table->date('tanggal_dokumen')->nullable()->after('judul'); 
        });
    }

    public function down()
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropColumn('tanggal_dokumen');
        });
    }
};

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
            // Tambahkan default PT lama agar data yang sudah ada tidak error
            $table->string('perusahaan')->default('PT. Dankom Mitra Abadi')->after('id');
        });
    }

    public function down()
    {
        Schema::table('dokumens', function (Blueprint $table) {
            $table->dropColumn('perusahaan');
        });
    }
};

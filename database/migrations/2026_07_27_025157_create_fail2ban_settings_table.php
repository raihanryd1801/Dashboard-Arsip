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
        Schema::create('fail2ban_settings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('maxretry')->default(3); // Maksimal salah
            $table->integer('bantime')->default(3600); // Waktu blokir (detik)
            $table->string('ignoreip')->nullable(); // IP yang kebal blokir
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fail2ban_settings');
    }
};

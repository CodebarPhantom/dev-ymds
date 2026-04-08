<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rabs', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_rab', 20)->unique();
            $table->string('divisi_id', 100);
            $table->date('bulan_pengajuan');
            $table->string('status', 30)->default('DRAFT');
            $table->integer('total_kegiatan')->default(0);
            $table->decimal('total_biaya_anggaran', 15, 2)->default(0);
            $table->foreignId('dibuat_oleh')->constrained('users');
            $table->timestamps();

            $table->index('divisi_id');
            $table->index('status');
            $table->index('bulan_pengajuan');
            $table->index('nomor_rab');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rabs');
    }
};

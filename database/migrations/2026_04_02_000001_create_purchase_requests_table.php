<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengajuan', 20)->unique();
            $table->string('divisi_id', 100);
            $table->string('judul_pengajuan', 255);
            $table->text('keperluan')->nullable();
            $table->date('tanggal_dibutuhkan');
            $table->string('status', 30)->default('DRAFT');
            $table->integer('total_item')->default(0);
            $table->decimal('total_biaya_estimasi', 15, 2)->default(0);
            $table->decimal('total_biaya_aktual', 15, 2)->default(0);
            $table->foreignId('dibuat_oleh')->constrained('users');
            $table->timestamps();

            $table->index('divisi_id');
            $table->index('status');
            $table->index('tanggal_dibutuhkan');
            $table->index('nomor_pengajuan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};

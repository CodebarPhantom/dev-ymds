<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rab_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rab_id')->constrained('rabs')->cascadeOnDelete();
            $table->string('kegiatan', 255);
            $table->text('catatan_kegiatan')->nullable();
            $table->decimal('biaya_anggaran', 15, 2);
            $table->string('waktu_pelaksanaan', 100);
            $table->timestamps();

            $table->index('rab_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rab_items');
    }
};

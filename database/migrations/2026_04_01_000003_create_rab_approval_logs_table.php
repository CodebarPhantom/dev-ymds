<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rab_approval_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rab_id')->constrained('rabs')->cascadeOnDelete();
            $table->string('aksi', 30);
            $table->foreignId('dilakukan_oleh')->constrained('users');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index('rab_id');
            $table->index('dilakukan_oleh');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rab_approval_logs');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained('purchase_requests')->cascadeOnDelete();
            $table->string('aksi', 30);
            $table->foreignId('dilakukan_oleh')->constrained('users');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index('purchase_request_id');
            $table->index('dilakukan_oleh');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_request_logs');
    }
};

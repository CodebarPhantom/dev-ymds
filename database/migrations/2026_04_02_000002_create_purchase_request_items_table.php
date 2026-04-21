<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained('purchase_requests')->cascadeOnDelete();
            $table->string('nama_barang', 255);
            $table->string('satuan', 50);
            $table->integer('jumlah');
            $table->decimal('biaya_estimasi', 15, 2);
            $table->text('catatan_item')->nullable();
            $table->boolean('sudah_dibeli')->default(false);
            $table->decimal('harga_aktual', 15, 2)->nullable();
            $table->timestamps();

            $table->index('purchase_request_id');
            $table->index('sudah_dibeli');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_request_items');
    }
};

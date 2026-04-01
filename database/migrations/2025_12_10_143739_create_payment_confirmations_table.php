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
        Schema::create('payment_confirmations', function (Blueprint $table) {
            $table->id();
            $table->string('confirmation_code')->unique();
            $table->foreignId('location_id')->constrained();
            $table->foreignId('location_category_id')->constrained();
            $table->string('payer_name');
            $table->integer('month');
            $table->integer('year');
            $table->decimal('amount', 15, 2);
            $table->string('proof_file')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['butuh_pengecekan', 'sudah_dicek'])->default('butuh_pengecekan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_confirmations');
    }
};

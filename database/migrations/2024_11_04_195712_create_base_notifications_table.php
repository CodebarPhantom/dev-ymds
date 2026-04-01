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
        Schema::create('base_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index(); // For individual notifications
            $table->unsignedBigInteger('location_id')->nullable()->index(); // For department-specific notifications
            $table->boolean('for_all_users')->default(false); // Flag to indicate notification is for all users
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_read')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('base_notifications');
    }
};

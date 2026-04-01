<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('location_categories', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('type');
        });

        // Update sort order for existing data
        DB::table('location_categories')->where('name', 'Blok A1')->update(['sort_order' => 1]);
        DB::table('location_categories')->where('name', 'Blok A2')->update(['sort_order' => 2]);
        DB::table('location_categories')->where('name', 'Blok A3')->update(['sort_order' => 3]);
        DB::table('location_categories')->where('name', 'Blok A4')->update(['sort_order' => 4]);
        DB::table('location_categories')->where('name', 'Blok A5')->update(['sort_order' => 5]);
        DB::table('location_categories')->where('name', 'Blok A6')->update(['sort_order' => 6]);
        DB::table('location_categories')->where('name', 'Blok B1')->update(['sort_order' => 7]);
        DB::table('location_categories')->where('name', 'Blok B2')->update(['sort_order' => 8]);
        DB::table('location_categories')->where('name', 'Infaq Masjid An-Nahl')->update(['sort_order' => 9]);
        DB::table('location_categories')->where('name', 'Kerja Bakti')->update(['sort_order' => 10]);
        DB::table('location_categories')->where('name', 'Kegiatan RT')->update(['sort_order' => 11]);
    }

    public function down(): void
    {
        Schema::table('location_categories', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Lumintu Property berganti jadi J-Land Property.
 *
 * Bukan sekadar ganti nama: slug adalah ALAMAT PUBLIK halamannya
 * (/lumintu-property → /j-land-property), dan slug tidak fillable sehingga
 * tidak bisa diubah lewat seeder maupun panel.
 *
 * Dikerjakan lewat migrasi supaya ikut jalan di server dengan sendirinya —
 * kalau hanya diubah di BusinessSeeder, database yang sudah ada tidak
 * terpengaruh dan halamannya tetap di alamat lama.
 *
 * Memakai query builder, bukan model: migrasi harus tetap bekerja walau
 * kolom pada model berubah di kemudian hari.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('businesses')
            ->where('slug', 'lumintu-property')
            ->update([
                'slug' => 'j-land-property',
                'name' => 'J-Land Property',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('businesses')
            ->where('slug', 'j-land-property')
            ->update([
                'slug' => 'lumintu-property',
                'name' => 'Lumintu Property',
                'updated_at' => now(),
            ]);
    }
};

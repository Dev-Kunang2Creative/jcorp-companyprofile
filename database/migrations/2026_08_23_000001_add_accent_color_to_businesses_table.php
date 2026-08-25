<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Warna aksen per anak usaha (DESIGN_SYSTEM §2.3, arah A+B).
 *
 * Sampai sekarang keenam entitas memakai palet emas yang sama — palet
 * yang dirancang untuk Sweetness Things, sebuah butik dessert. Palet itu
 * juga yang dipakai PT. Ayodya Utama Logistic, perusahaan pengiriman
 * dengan Maersk dan NYK Line sebagai partner.
 *
 * DESIGN_SYSTEM §2.3 sudah menuliskan aturannya sejak Fase 2:
 * "Palet Sweetness (emas) hanya untuk Sweetness." Kolom ini yang
 * membuat aturan itu bisa dijalankan.
 *
 * Disimpan di database, bukan ditanam di kode, supaya identitas visual
 * bisa menyesuaikan kalau client mengganti merek — tanpa perlu build ulang.
 *
 * KEAMANAN: nilainya masuk ke atribut `style` di HTML. Panjangnya
 * dibatasi 7 karakter (#RRGGBB), dan controller memeriksa bentuknya
 * dengan regex sebelum mengirimkannya. Tanpa pemeriksaan itu, kolom ini
 * jadi jalan masuk untuk menyuntikkan CSS.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('accent_color', 7)->nullable()->after('logo_path');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('accent_color');
        });
    }
};

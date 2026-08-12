<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();

            // Dipakai sebagai URL publik: /sweetness-things
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();

            // Sebutan section di layar — menyesuaikan per anak usaha
            // ("Menu Kami", "Layanan & Harga", "Unit Tersedia").
            $table->string('catalog_label')->default('Katalog');
            $table->string('portfolio_label')->default('Portfolio');

            // Kolom kontak dipisah-pisah, bukan satu kolom serbaguna, supaya
            // nomor WhatsApp bisa divalidasi dan link wa.me dibentuk benar.
            $table->string('whatsapp')->nullable();
            $table->string('instagram')->nullable();
            $table->string('tiktok')->nullable();
            $table->string('address')->nullable();
            $table->string('business_hours')->nullable();

            $table->boolean('is_parent')->default(false);

            // Sakelar terbit. Yang bernilai false mengembalikan 404 di halaman
            // publik, tapi adminnya tetap bisa masuk panel dan mengisi konten.
            $table->boolean('is_published')->default(false);

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Halaman induk mengurutkan kartu anak usaha yang sudah terbit.
            $table->index(['is_published', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};

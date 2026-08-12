<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();

            // Harga disimpan sebagai angka, keterangannya terpisah. Harga
            // berupa teks bebas ("150rb", "Rp 150.000") berantakan karena tiap
            // admin menulis dengan gaya berbeda, dan tidak bisa diurutkan.
            // Presisi 12,2 cukup untuk unit property (miliaran) sekaligus
            // dessert box.
            $table->decimal('price', 12, 2)->nullable();
            $table->string('price_note')->nullable();

            $table->string('category')->nullable();

            // Berbeda dari soft delete: menyembunyikan item sementara dari
            // halaman publik (stok habis) — datanya utuh dan tetap terlihat
            // di panel admin, tinggal dinyalakan lagi.
            $table->boolean('is_available')->default(true);

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Setiap tampilan katalog menyaring per anak usaha lalu mengurutkan.
            $table->index(['business_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_items');
    }
};

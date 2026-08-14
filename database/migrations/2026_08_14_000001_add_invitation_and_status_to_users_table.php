<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Mencabut akses tanpa menghapus akunnya. Tabel `users` tidak
            // memakai soft delete, jadi menghapus berarti hilang selamanya —
            // sedangkan hampir semua alasan ingin menghapus sebenarnya cukup
            // dijawab dengan mematikan akses.
            $table->boolean('is_active')->default(true)->after('business_id');

            // Undangan: super-admin membuat akun tanpa password, lalu
            // mengirim tautan sekali pakai. Pemiliknya sendiri yang membuat
            // passwordnya — jadi password tidak pernah diketahui super-admin
            // maupun melintas lewat form panel.
            //
            // Disimpan sebagai HASH, bukan token mentahnya. Kalau database
            // bocor, token yang bisa dipakai tidak ikut terbawa — perlakuan
            // yang sama dengan password.
            $table->string('invitation_token', 64)->nullable()->unique()->after('is_active');
            $table->timestamp('invitation_expires_at')->nullable()->after('invitation_token');
        });

        // Akun undangan belum punya password sampai pemiliknya membuatnya.
        // Sebelumnya kolom ini NOT NULL, jadi harus dilonggarkan.
        //
        // Ditulis sebagai SQL mentah karena mengubah tipe kolom butuh
        // doctrine/dbal di sebagian versi Laravel, dan menambah dependensi
        // hanya untuk satu perubahan ini tidak sepadan.
        DB::statement(
            'ALTER TABLE users MODIFY password VARCHAR(255) NULL'
        );
    }

    public function down(): void
    {
        // Akun yang masih menunggu undangan tidak punya password. Kolomnya
        // tidak bisa dikembalikan jadi NOT NULL selama baris itu ada, jadi
        // dibersihkan dulu.
        DB::statement(
            'DELETE FROM users WHERE password IS NULL'
        );

        DB::statement(
            'ALTER TABLE users MODIFY password VARCHAR(255) NOT NULL'
        );

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'invitation_token', 'invitation_expires_at']);
        });
    }
};

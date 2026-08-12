<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Dikunci di level database supaya nilai peran yang tidak dikenali
            // ditolak MySQL, bukan cuma oleh validasi di sisi PHP.
            $table->enum('role', ['super_admin', 'business_admin'])
                ->default('business_admin')
                ->after('password');

            // Satu admin memegang satu anak usaha — kolom tunggal, bukan tabel
            // penghubung. null untuk super_admin.
            $table->foreignId('business_id')
                ->nullable()
                ->after('role')
                ->constrained()
                ->nullOnDelete();

            // Verifikasi email tidak dipakai: akun dibuat lewat perintah
            // artisan oleh orang yang memang berhak, bukan lewat pendaftaran.
            $table->dropColumn('email_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('business_id');
            $table->dropColumn('role');
            $table->timestamp('email_verified_at')->nullable()->after('email');
        });
    }
};

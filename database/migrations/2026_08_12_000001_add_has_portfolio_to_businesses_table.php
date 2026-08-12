<?php

use App\Models\Business;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            // Tidak semua anak usaha memamerkan hasil kerja. Dessert, logistik,
            // dan properti menampilkan katalog saja; nail art dan eyelash
            // butuh portfolio (spec §3).
            //
            // Default false: menu portfolio hanya muncul kalau memang sengaja
            // dinyalakan super-admin, bukan sebaliknya.
            $table->boolean('has_portfolio')->default(false)->after('portfolio_label');
        });

        // Anak usaha yang sudah punya foto portfolio jelas memakainya —
        // sakelarnya dinyalakan supaya menunya tidak hilang dari admin yang
        // sedang mengelolanya.
        Business::whereHas('portfolioItems')->update(['has_portfolio' => true]);
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('has_portfolio');
        });
    }
};

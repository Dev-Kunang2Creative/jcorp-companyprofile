<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('cover_image_path')->nullable()->after('logo_path');
        });

        Schema::table('portfolio_items', function (Blueprint $table) {
            $table->boolean('is_featured_on_home')->default(false)->after('caption');
            $table->index(
                ['business_id', 'is_featured_on_home'],
                'portfolio_business_featured_index',
            );
        });
    }

    public function down(): void
    {
        Schema::table('portfolio_items', function (Blueprint $table) {
            $table->dropIndex('portfolio_business_featured_index');
            $table->dropColumn('is_featured_on_home');
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('cover_image_path');
        });
    }
};

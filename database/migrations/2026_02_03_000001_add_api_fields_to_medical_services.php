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
        Schema::table('medical_services', function (Blueprint $table) {
            // Check if columns don't exist before adding
            if (!Schema::hasColumn('medical_services', 'code')) {
                $table->string('code')->nullable()->after('id');
            }
            if (!Schema::hasColumn('medical_services', 'category')) {
                $table->string('category')->nullable()->after('description');
            }
            if (!Schema::hasColumn('medical_services', 'price_updated_at')) {
                $table->datetime('price_updated_at')->nullable()->after('price');
            }
            if (!Schema::hasColumn('medical_services', 'price_source')) {
                $table->text('price_source')->nullable()->after('price_updated_at')->comment('Source of price: api, manual, or seeder');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_services', function (Blueprint $table) {
            $table->dropColumn(['code', 'category', 'price_updated_at', 'price_source']);
        });
    }
};

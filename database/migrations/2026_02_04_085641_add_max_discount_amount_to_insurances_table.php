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
        Schema::table('insurances', function (Blueprint $table) {
            $table->decimal('max_discount_amount', 15, 2)->nullable()->after('discount_percentage')
                ->comment('Maksimal nominal diskon (misal: diskon 30% maksimal Rp 300.000)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('insurances', function (Blueprint $table) {
            $table->dropColumn('max_discount_amount');
        });
    }
};

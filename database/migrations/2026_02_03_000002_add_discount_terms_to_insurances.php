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
            if (!Schema::hasColumn('insurances', 'code')) {
                $table->string('code')->nullable()->after('id');
            }
            if (!Schema::hasColumn('insurances', 'discount_percentage')) {
                $table->decimal('discount_percentage', 5, 2)->default(0)->after('description');
            }
            if (!Schema::hasColumn('insurances', 'terms')) {
                $table->text('terms')->nullable()->after('discount_percentage')
                    ->comment('Ketentuan dan syarat penggunaan asuransi');
            }
            if (!Schema::hasColumn('insurances', 'coverage_limit')) {
                $table->decimal('coverage_limit', 15, 2)->nullable()->after('terms')
                    ->comment('Batas maksimal tanggungan per transaksi');
            }
            if (!Schema::hasColumn('insurances', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('coverage_limit');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('insurances', function (Blueprint $table) {
            $table->dropColumn(['code', 'discount_percentage', 'terms', 'coverage_limit', 'is_active']);
        });
    }
};

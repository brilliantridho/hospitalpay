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
        Schema::table('vouchers', function (Blueprint $table) {
            if (!Schema::hasColumn('vouchers', 'code')) {
                $table->string('code')->nullable()->after('insurance_id');
            }
            if (!Schema::hasColumn('vouchers', 'description')) {
                $table->text('description')->nullable()->after('code');
            }
            if (!Schema::hasColumn('vouchers', 'min_transaction')) {
                $table->decimal('min_transaction', 15, 2)->nullable()->after('max_discount')
                    ->comment('Minimum nilai transaksi untuk menggunakan voucher');
            }
            if (!Schema::hasColumn('vouchers', 'usage_limit')) {
                $table->integer('usage_limit')->nullable()->after('min_transaction')
                    ->comment('Batas maksimal penggunaan voucher');
            }
            if (!Schema::hasColumn('vouchers', 'used_count')) {
                $table->integer('used_count')->default(0)->after('usage_limit')
                    ->comment('Jumlah voucher yang sudah digunakan');
            }
        });
        
        // Generate codes for existing vouchers without code
        DB::statement("UPDATE vouchers SET code = CONCAT('VCHR', id, LPAD(CAST(EXTRACT(EPOCH FROM NOW()) AS VARCHAR), 6, '0')) WHERE code IS NULL");
        
        // Make code unique after filling nulls
        Schema::table('vouchers', function (Blueprint $table) {
            $table->unique('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn(['code', 'description', 'min_transaction', 'usage_limit', 'used_count']);
        });
    }
};

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
            $table->string('code')->nullable()->after('id');
            $table->decimal('discount_percentage', 5, 2)->default(0)->after('description');
        });

        Schema::table('medical_services', function (Blueprint $table) {
            $table->string('code')->nullable()->after('id');
            $table->string('category')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('insurances', function (Blueprint $table) {
            $table->dropColumn(['code', 'discount_percentage']);
        });

        Schema::table('medical_services', function (Blueprint $table) {
            $table->dropColumn(['code', 'category']);
        });
    }
};

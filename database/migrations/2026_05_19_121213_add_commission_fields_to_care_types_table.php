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
        Schema::table('care_types', function (Blueprint $table) {
            $table->tinyInteger('commision_type')->default(1)->comment('0: Fixed, 1: Percent');
            $table->decimal('commision_value', 10, 2)->default(0.00)->after('commision_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('care_types', function (Blueprint $table) {
            $table->dropColumn(['commision_type', 'commision_value']);
        });
    }
};

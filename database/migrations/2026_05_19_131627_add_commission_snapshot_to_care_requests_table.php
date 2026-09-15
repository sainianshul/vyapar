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
        Schema::table('care_requests', function (Blueprint $table) {
            $table->tinyInteger('commission_type')->nullable()->after('status')->comment('0=Fixed/Day, 1=Percent, 2=Fixed Total (Snapshot at booking)');
            $table->decimal('commission_value', 10, 2)->nullable()->after('commission_type')->comment('Snapshot value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('care_requests', function (Blueprint $table) {
            $table->dropColumn(['commission_type', 'commission_value']);
        });
    }
};

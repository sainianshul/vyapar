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
        if (Schema::hasColumn('bookings', 'commission_type')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('commission_type');
            });
        }
        if (Schema::hasColumn('bookings', 'commission_value')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('commission_value');
            });
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('commission_type')->nullable()->after('nurse_amount');
            $table->string('commission_value')->nullable()->after('commission_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['commission_type', 'commission_value']);
        });
    }
};

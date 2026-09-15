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
        Schema::table('booking_sessions', function (Blueprint $table) {
            $table->boolean('is_forced_end')->default(false)->after('status');
            $table->text('force_end_reason')->nullable()->after('is_forced_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_sessions', function (Blueprint $table) {
            $table->dropColumn(['is_forced_end', 'force_end_reason']);
        });
    }
};

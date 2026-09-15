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
            $table->renameColumn('otp_code', 'start_otp');
            $table->string('end_otp', 10)->nullable()->after('start_otp');
            $table->text('user_notes')->nullable()->after('nurse_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_sessions', function (Blueprint $table) {
            $table->renameColumn('start_otp', 'otp_code');
            $table->dropColumn(['end_otp', 'user_notes']);
        });
    }
};

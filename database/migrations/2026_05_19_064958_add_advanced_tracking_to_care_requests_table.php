<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('care_requests', function (Blueprint $table) {
            $table->timestamp('bidding_ends_at')->nullable()->after('status');
            $table->tinyInteger('matching_attempt_level')->default(0)->after('bidding_ends_at');
            $table->timestamp('patient_nudged_at')->nullable()->after('matching_attempt_level');
            $table->integer('total_bids_received')->default(0)->after('patient_nudged_at');
            $table->decimal('tip_amount', 8, 2)->default(0)->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('care_requests', function (Blueprint $table) {
            $table->dropColumn([
                'bidding_ends_at',
                'matching_attempt_level',
                'patient_nudged_at',
                'total_bids_received',
                'tip_amount'
            ]);
        });
    }
};

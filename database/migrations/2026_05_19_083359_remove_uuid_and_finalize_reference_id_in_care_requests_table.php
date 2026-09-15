<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

return new class extends Migration {
    /**
     * Run the migrations.
     * - reference_id already renamed from uuid (previous migration)
     * - Backfill existing rows with proper REQ-YYMMDD-XXXX format
     * - Enforce NOT NULL + UNIQUE index
     */
    public function up(): void
    {
        // Step 1: Make nullable temporarily so we can backfill (keep size 36 for existing UUIDs)
        Schema::table('care_requests', function (Blueprint $table) {
            $table->string('reference_id', 36)->nullable()->change();
        });

        // Step 2: Backfill existing rows that have old UUID or NULL values
        DB::table('care_requests')->get(['id', 'created_at'])->each(function ($row) {
            $date = Carbon::parse($row->created_at)->format('ymd');
            $random = strtoupper(Str::random(4));
            DB::table('care_requests')->where('id', $row->id)->update([
                'reference_id' => "REQ-{$date}-{$random}",
            ]);
        });

        // Step 3: Enforce NOT NULL + UNIQUE
        Schema::table('care_requests', function (Blueprint $table) {
            $table->string('reference_id', 30)->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('care_requests', function (Blueprint $table) {
            $table->dropUnique(['reference_id']);
            $table->string('reference_id', 36)->nullable()->change();
        });
    }
};

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
            if (Schema::hasColumn('care_requests', 'duration_type')) {
                $table->dropColumn('duration_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('care_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('care_requests', 'duration_type')) {
                $table->integer('duration_type')->nullable();
            }
        });
    }
};

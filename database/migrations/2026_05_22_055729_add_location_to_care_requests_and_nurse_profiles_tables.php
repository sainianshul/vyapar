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
            if (!Schema::hasColumn('care_requests', 'location')) {
                $table->geometry('location')->nullable()->after('longitude');
            }
        });

        Schema::table('nurse_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('nurse_profiles', 'location')) {
                $table->geometry('location')->nullable()->after('longitude');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('care_requests', function (Blueprint $table) {
            if (Schema::hasColumn('care_requests', 'location')) {
                $table->dropColumn('location');
            }
        });

        Schema::table('nurse_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('nurse_profiles', 'location')) {
                $table->dropColumn('location');
            }
        });
    }
};

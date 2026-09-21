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
        // Products: rename 'location' → 'address', add 'state'
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('location', 'address');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('state', 100)->nullable()->after('address');
        });

        // Requirements: rename 'delivery_location' → 'address', 'delivery_pincode' → 'pincode', add 'state'
        Schema::table('requirements', function (Blueprint $table) {
            $table->renameColumn('delivery_location', 'address');
            $table->renameColumn('delivery_pincode', 'pincode');
        });

        Schema::table('requirements', function (Blueprint $table) {
            $table->string('state', 100)->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('address', 'location');
            $table->dropColumn('state');
        });

        Schema::table('requirements', function (Blueprint $table) {
            $table->renameColumn('address', 'delivery_location');
            $table->renameColumn('pincode', 'delivery_pincode');
            $table->dropColumn('state');
        });
    }
};

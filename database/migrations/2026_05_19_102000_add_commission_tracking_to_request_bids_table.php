<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('request_bids', function (Blueprint $table) {
            $table->tinyInteger('commission_type')->after('nurse_amount')->comment('1=percentage, 2=flat');
            $table->decimal('commission_value', 10, 2)->after('commission_type')->comment('Raw config value used at time of bid');
        });
    }

    public function down(): void
    {
        Schema::table('request_bids', function (Blueprint $table) {
            $table->dropColumn(['commission_type', 'commission_value']);
        });
    }
};

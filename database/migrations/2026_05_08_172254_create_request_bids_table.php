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
        Schema::create('request_bids', function (Blueprint $table) {
            $table->id();

            $table->foreignId('care_request_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('nurse_id')
                ->constrained('nurse_profiles')
                ->cascadeOnDelete();

            $table->decimal('nurse_amount', 10, 2);

            $table->decimal('commission_amount', 10, 2);

            $table->decimal('total_amount', 10, 2);

            $table->text('notes')->nullable();

            $table->decimal('distance_km', 8, 2)->nullable();

            $table->timestamp('expires_at');

            $table->tinyInteger('status');

            $table->timestamps();

            $table->index('status');
            $table->index(['care_request_id', 'nurse_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_bids');
    }
};

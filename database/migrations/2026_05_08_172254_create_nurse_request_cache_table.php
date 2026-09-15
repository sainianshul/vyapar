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
        Schema::create('nurse_request_cache', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nurse_id')
                ->constrained('nurse_profiles')
                ->cascadeOnDelete();

            $table->foreignId('care_request_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->json('request_snapshot');

            $table->tinyInteger('status')->default(0);

            $table->timestamp('notified_at')->nullable();

            $table->timestamp('viewed_at')->nullable();

            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            $table->index('status');

            $table->unique([
                'nurse_id',
                'care_request_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nurse_request_cache');
    }
};

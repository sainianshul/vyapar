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
        Schema::create('nurse_availability', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nurse_id')
                ->unique()
                ->constrained('nurse_profiles')
                ->cascadeOnDelete();

            $table->time('available_from');

            $table->time('available_to');

            $table->string('timezone')->nullable();

            $table->json('available_days');

            $table->unsignedTinyInteger('status')->default(1);

            $table->timestamps();

            $table->softDeletes();

            $table->index('status');

            $table->index(['nurse_id', 'status']);

            $table->index(['available_from', 'available_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nurse_availability');
    }
};

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
        Schema::create('care_type_nurse', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nurse_id')
                ->constrained('nurse_profiles')
                ->cascadeOnDelete();

            $table->foreignId('care_type_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->tinyInteger('status');

            $table->timestamps();

            $table->index(['nurse_id', 'care_type_id']);
            $table->unique(['nurse_id', 'care_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('care_type_nurse');
    }
};

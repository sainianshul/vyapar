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
        Schema::create('nurse_work_history', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nurse_id')
                ->constrained('nurse_profiles')
                ->cascadeOnDelete();

            $table->string('role_or_position');

            $table->string('organization_name');

            $table->string('location')->nullable();

            $table->date('start_date');

            $table->date('end_date')->nullable();

            $table->boolean('is_currently_working')->default(false);

            $table->text('description')->nullable();

            $table->unsignedTinyInteger('status')->default(1);

            $table->timestamps();

            $table->softDeletes();

            $table->index('status');

            $table->index(['nurse_id', 'status']);

            $table->index('organization_name');

            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nurse_work_history');
    }
};

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
        Schema::create('nurse_education', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nurse_id')
                ->constrained('nurse_profiles')
                ->cascadeOnDelete();

            $table->string('degree_or_course');

            $table->string('institute_name');

            $table->string('field_of_study')->nullable();

            $table->year('start_year');

            $table->year('end_year')->nullable();

            $table->boolean('is_currently_studying')->default(false);

            $table->unsignedTinyInteger('status')->default(1);

            $table->timestamps();

            $table->softDeletes();

            $table->index('status');

            $table->index(['nurse_id', 'status']);

            $table->index('degree_or_course');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nurse_education');
    }
};

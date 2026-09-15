<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('booking_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('session_date');
            $table->unsignedInteger('session_number');

            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // OTP for daily verification
            $table->string('otp_code', 6)->nullable();
            $table->timestamp('otp_verified_at')->nullable();

            // Session lifecycle timestamps
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            // Status: 0=upcoming, 1=started, 2=completed, 3=missed, 4=cancelled
            $table->unsignedTinyInteger('status')->default(0);

            $table->text('nurse_notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['booking_id', 'session_date']);
            $table->index(['booking_id', 'status']);
            $table->index('session_date');
            $table->unique(['booking_id', 'session_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_sessions');
    }
};

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
        Schema::create('nurse_profiles', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            // Basic Information

            $table->string('nurse_id_number')
                ->nullable()
                ->unique();

            $table->string('license_number')
                ->nullable()
                ->unique();

            $table->date('license_expiry_date')
                ->nullable();

            $table->unsignedTinyInteger('years_of_experience')
                ->nullable();

            $table->text('bio')
                ->nullable();

            // Location

            $table->decimal('latitude', 10, 7)
                ->nullable();
            $table->decimal('longitude', 10, 7)
                ->nullable();
            $table->geometry('location', subtype: 'point');

            $table->text('address')
                ->nullable();

            $table->string('city')
                ->nullable();

            $table->string('state')
                ->nullable();

            $table->string('country')
                ->nullable();

            $table->string('pincode')
                ->nullable();

            // Availability

            $table->time('available_from')
                ->nullable();

            $table->time('available_to')
                ->nullable();

            $table->string('timezone')
                ->nullable();

            $table->json('available_days')
                ->nullable();

            $table->boolean('is_available')
                ->default(true);

            // Rating & Trust

            $table->decimal('avg_rating', 3, 2)
                ->default(0);

            $table->unsignedInteger('total_reviews')
                ->default(0);

            $table->decimal('trust_score', 5, 2)
                ->default(0);

            $table->timestamp('trust_score_updated_at')
                ->nullable();

            // Booking Statistics

            $table->unsignedInteger('total_bookings_completed')
                ->default(0);

            $table->unsignedInteger('total_bookings_cancelled')
                ->default(0);

            // Reports

            $table->unsignedInteger('total_reports')
                ->default(0);

            $table->unsignedInteger('resolved_reports')
                ->default(0);

            // Moderation

            $table->text('rejection_reason')
                ->nullable();

            $table->text('suspension_reason')
                ->nullable();

            $table->timestamp('approved_at')
                ->nullable();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('marked_for_review_at')
                ->nullable();

            $table->timestamp('rejected_at')
                ->nullable();

            $table->timestamp('suspended_at')
                ->nullable();

            $table->foreignId('rejected_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('suspended_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            // Onboarding

            $table->unsignedTinyInteger('onboarding_step')
                ->default(1);

            $table->boolean('is_onboarding_completed')
                ->default(false);

            // Status

            $table->unsignedTinyInteger('status')
                ->default(0);

            $table->timestamps();

            $table->softDeletes();

            // Indexes

            $table->index('status');

            $table->index('is_available');

            $table->index([
                'status',
                'is_available',
            ]);

            $table->index([
                'latitude',
                'longitude',
            ]);
            $table->spatialIndex('location');

            $table->index([
                'city',
                'status',
            ]);

            $table->index('license_expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nurse_profiles');
    }
};
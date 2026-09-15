<?php

use App\Enums\CareRequestStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('care_requests', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid')->unique();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('care_type_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('care_for');

            $table->string('patient_name')->nullable();

            $table->string('patient_age')->nullable();

            $table->string('contact_phone', 15);

            $table->string('secondary_phone', 15)->nullable();

            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->geometry('location', subtype: 'point');

            $table->text('address');

            $table->string('city');

            $table->string('state');

            $table->string('country')->nullable();

            $table->string('pincode');

            $table->date('start_date');

            $table->date('end_date')->nullable();

            $table->time('start_time')->nullable();

            $table->time('end_time')->nullable();

            $table->text('notes')->nullable();

            $table->unsignedTinyInteger('cancelled_by')->nullable();

            $table->unsignedTinyInteger('status')->default(0);

            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            $table->softDeletes();

            $table->index('status');

            $table->index('user_id');

            $table->index(['user_id', 'status']);

            $table->index(['care_type_id', 'status']);

            $table->index(['start_date', 'end_date']);

            $table->index(['latitude', 'longitude']);
            $table->spatialIndex('location');

            $table->index(['city', 'status']);

            $table->index(['status', 'start_date']);

            $table->index(['status', 'care_type_id', 'start_date']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('care_requests');
    }
};

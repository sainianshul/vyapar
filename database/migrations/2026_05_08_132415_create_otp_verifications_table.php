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
        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->id();

            $table->string('phone', 15);

            $table->string('otp');

            $table->string('purpose')->nullable();

            $table->unsignedTinyInteger('attempts')->default(0);

            $table->boolean('is_used')->default(false);

            $table->timestamp('expires_at');

            $table->unsignedTinyInteger('status')->default(1);

            $table->timestamps();

            $table->index('status');

            $table->index('phone');

            $table->index(['phone', 'purpose']);

            $table->index(['phone', 'is_used']);

            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_verifications');
    }
};

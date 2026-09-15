<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('communication_logs', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('notifiable'); // Equivalent to notifiable_type and notifiable_id
            $table->string('channel'); // e.g., 'sms', 'mail', 'fcm'
            $table->string('type'); // The notification class name
            $table->text('destination')->nullable(); // Phone number, email, or FCM token
            $table->longText('content')->nullable(); // The message body or JSON payload
            $table->string('status')->default('success'); // 'success', 'failed'
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communication_logs');
    }
};

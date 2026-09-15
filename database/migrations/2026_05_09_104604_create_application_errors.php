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
        Schema::create('application_errors', function (Blueprint $table) {

            $table->id();

            $table->string('error_id')
                ->unique();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();


            $table->string('message', 1000)
                ->nullable();

            $table->string('exception')
                ->nullable();

            $table->text('file')
                ->nullable();

            $table->unsignedInteger('line')
                ->nullable();

            $table->longText('trace')
                ->nullable();


            $table->text('url')
                ->nullable();

            $table->string('method', 10)
                ->nullable();

            $table->string('ip_address', 45)
                ->nullable();

            $table->json('request_data')
                ->nullable();

            $table->unsignedTinyInteger('severity')
                ->default(2);

            $table->unsignedTinyInteger('status')
                ->default(0);

            $table->text('comment')
                ->nullable();

            $table->timestamp('resolved_at')
                ->nullable();

            $table->tinyInteger('is_pinned')
                ->default(0);

            // For Duplicate detection
            $table->string('fingerprint', 64)
                ->nullable();

            $table->timestamps();

            $table->index('error_id');

            $table->index('user_id');

            $table->index('status');

            $table->index('severity');

            $table->index('fingerprint');

            $table->index([
                'status',
                'severity',
                'created_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_errors');
    }
};
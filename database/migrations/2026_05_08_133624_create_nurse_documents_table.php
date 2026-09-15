<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Enums\DocumentStatus;
use App\Enums\NurseDocumentType;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nurse_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nurse_id')
                ->constrained('nurse_profiles')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('document_type');

            $table->string('file_path');

            $table->text('rejection_reason')->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();

            $table->unsignedTinyInteger('status')->default(0);

            $table->timestamps();

            $table->softDeletes();

            $table->index('status');

            $table->index('document_type');

            $table->index(['nurse_id', 'status']);

            $table->index(['document_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nurse_documents');
    }
};

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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('requirement_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('last_message_at')->nullable();
            $table->integer('buyer_unread_count')->default(0);
            $table->integer('seller_unread_count')->default(0);
            $table->tinyInteger('status')->default(1)->comment('1:Active, 2:Archived, 3:Blocked');
            $table->timestamps();
            $table->softDeletes();
            
            // Prevent duplicate active threads for same item between same users
            $table->unique(['product_id', 'requirement_id', 'buyer_id', 'seller_id'], 'conv_unique_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};

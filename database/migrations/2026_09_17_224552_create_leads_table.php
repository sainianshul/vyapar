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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('requirement_id')->nullable()->constrained()->nullOnDelete();
            
            $table->tinyInteger('source')->default(1)->comment('1:Inquiry Form, 2:Call, 3:Chat, 4:View Number');
            $table->tinyInteger('temperature')->default(1)->comment('1:Cold, 2:Warm, 3:Hot');
            
            $table->integer('quantity')->nullable();
            $table->text('message')->nullable();
            
            $table->tinyInteger('status')->default(1)->comment('1:New, 2:Contacted, 3:Converted, 4:Rejected');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Unique constraint to prevent duplicate leads
            $table->unique(['buyer_id', 'seller_id', 'product_id', 'requirement_id'], 'leads_unique_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};

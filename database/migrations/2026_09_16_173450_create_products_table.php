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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            
            $table->decimal('price', 12, 2);
            $table->string('price_unit', 50)->nullable()->comment('e.g. per piece, per kg, per dozen');
            $table->boolean('is_negotiable')->default(true);
            
            $table->unsignedTinyInteger('condition')->comment('1=New, 2=Used');
            $table->unsignedInteger('quantity')->default(1);
            
            $table->string('location')->nullable()->comment('Fallback to seller address');
            $table->string('city', 100)->nullable();
            $table->string('pincode', 10)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            
            $table->unsignedTinyInteger('status')->default(1)->comment('1=Active, 2=Sold, 3=Expired, 4=Blocked');
            
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('leads_count')->default(0);
            
            $table->boolean('is_featured')->default(false);
            $table->timestamp('featured_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('status');
            $table->index('city');
            $table->index('condition');
            $table->index('is_featured');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

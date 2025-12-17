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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticker_id')->constrained()->onDelete('cascade');
            
            // Order details
            $table->enum('side', ['BUY', 'SELL'])->default('BUY');
            $table->decimal('price', 20, 6); // 6 decimals for price (covers crypto/forex/stocks)
            $table->decimal('amount', 30, 18); // 18 decimals for crypto amounts
            
            // Order status
            $table->tinyInteger('status')->default(1); // 1=open, 2=filled, 3=cancelled
            
            // Timestamps for different states
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('filled_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'status']);
            $table->index(['ticker_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('side');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

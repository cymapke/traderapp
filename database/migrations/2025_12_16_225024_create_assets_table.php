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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticker_id')->constrained()->onDelete('cascade');
            
            // For cryptocurrencies with up to 18 decimal places (like Ethereum)
            // Using decimal with 30 total digits and 18 decimal places
            $table->decimal('amount', 30, 18)->default(0);
            $table->decimal('locked_amount', 30, 18)->default(0);
            
            $table->timestamps();
            
            // Unique constraint: each user can only have one record per ticker
            $table->unique(['user_id', 'ticker_id']);
            
            // Indexes for performance
            $table->index(['user_id', 'ticker_id']);
            $table->index('user_id');
            $table->index('ticker_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};

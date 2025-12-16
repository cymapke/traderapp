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
        Schema::create('tickers', function (Blueprint $table) {
            $table->id();
            $table->string('symbol')->unique(); // e.g., BTC, ETH, EURUSD, XAU, AAPL
            $table->enum('type', ['crypto', 'currency', 'commodity', 'stock'])->default('currency');
            $table->timestamps();
            
            // Index for faster queries
            $table->index(['type', 'symbol']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickers');
    }
};

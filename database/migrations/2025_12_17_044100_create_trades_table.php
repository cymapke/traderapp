<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buy_order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('sell_order_id')->constrained('orders')->onDelete('cascade');
            $table->decimal('buy_commission', 10, 2);
            $table->decimal('sell_commission', 10, 2);
            
            // Indexes for performance
            $table->index(['buy_order_id', 'sell_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};

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
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->decimal('total_price', 10, 2);
        $table->decimal('shipping_cost', 10, 2)->default(0);
        $table->decimal('tax', 10, 2)->default(0);
        $table->enum('status', ['pending', 'shipped', 'delivered', 'cancelled'])->default('pending');
        $table->timestamp('order_date')->useCurrent();
        $table->date('estimated_delivery')->nullable();
    });
}

public function down(): void
{
    Schema::dropIfExists('orders');
}

};

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
    Schema::create('cart', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('material_id')->constrained('materials')->onDelete('cascade');
        $table->integer('quantity');
        $table->timestamp('added_at')->useCurrent();
    });
}

public function down(): void
{
    Schema::dropIfExists('cart');
}

};

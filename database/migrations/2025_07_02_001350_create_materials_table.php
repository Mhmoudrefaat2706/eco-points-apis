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
    Schema::create('materials', function (Blueprint $table) {
        $table->id();
        $table->string('name', 100);
        $table->string('category', 50)->nullable();
        $table->text('description')->nullable();
        $table->decimal('price', 10, 2);
        $table->enum('price_unit', ['piece', 'kg', 'm²', 'm³']);
        $table->string('image_url', 255)->nullable();
        $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
        $table->timestamps(); // This adds both created_at and updated_at
    });
}

public function down(): void
{
    Schema::dropIfExists('materials');
}

};

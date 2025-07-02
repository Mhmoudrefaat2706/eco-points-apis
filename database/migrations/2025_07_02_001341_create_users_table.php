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
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name', 50);
        $table->string('email', 50)->unique();
        $table->string('password', 100);
        $table->enum('role', ['seller', 'buyer']);
        $table->string('location', 50)->nullable();
        $table->text('address')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('users');
}

};

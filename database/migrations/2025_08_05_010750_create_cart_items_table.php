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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('obat_id')->constrained('obats')->cascadeOnDelete();
            $table->string('product_name');
            $table->decimal('price', 15, 2);
            $table->integer('quantity')->default(1);
            $table->decimal('line_total', 15, 2);
            $table->timestamps();

            $table->unique(['cart_id', 'obat_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
